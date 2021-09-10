<?php

namespace App\Http\Controllers\Agent;

use Image;
use Carbon\Carbon;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\KycForm;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Rules\FileTypeValidate;
use App\Lib\GoogleAuthenticator;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }
    public function home()
    {
        $pageTitle = "Agent Dashboard";
        $wallets = Wallet::where('user_id',agent()->id)->where('user_type','AGENT')
        ->select(DB::raw('*'))
        ->addSelect(DB::raw('
            (select count(*) 
            from transactions
            where wallet_id = wallets.id) 
            as transactions
        '))
        ->orderBy('transactions','desc')
        ->take(3)->get();


        $depositLog = Deposit::where('user_type','AGENT')->where('user_id',agent()->id)->where('status',1)->with('curr')->get();
        $totalDepositAmount[] = 0;
        foreach($depositLog as $depo){
            $totalDepositAmount[] = $depo->amount * $depo->curr->rate;
        }

        $totalAddMoney = array_sum($totalDepositAmount);

        $withdrawLog = Withdrawal::where('user_type','AGENT')->where('user_id',agent()->id)->where('status',1)->with('curr')->get();
      
        $totalWithdrawAmount[] = 0;
        foreach($withdrawLog as $log){
            $totalWithdrawAmount[] = $log->amount * $log->curr->rate;
        }
        $totalWithdraw =  array_sum($totalWithdrawAmount);


         // Transaction Graph
         $report['trx_dates'] = collect([]);
         $report['trx_amount'] = collect([]);
         $rate = currencyRate();
       
         $transactions = Transaction::where('user_type','AGENT')->where('user_id',agent()->id)->where('created_at', '>=', Carbon::now()->subYear())
         ->where('trx_type','+')
         ->selectRaw("SUM(amount * $rate) as totalAmount")
         ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as dates")
         ->orderBy('created_at')->groupBy('dates')->get();
 
         $transactions->map(function ($trxData) use ($report) {
             $report['trx_dates']->push($trxData->dates);
             $report['trx_amount']->push($trxData->totalAmount);
         });
   

        $userKyc = KycForm::where('user_type',userGuard()['type'])->first();
        $histories = Transaction::where([['user_id',agent()->id],['user_type','AGENT']])->with('currency')->latest()->take(7)->get();
        $totalMoneyInOut = totalMoneyInOut();
        $kyc = kycStyle();
        return view($this->activeTemplate.'agent.dashboard',compact('pageTitle','wallets','histories','totalMoneyInOut','userKyc','kyc','totalAddMoney','totalWithdraw','report'));
    }

    public function allWallets()
    {
        $pageTitle = "All Wallets";
        $wallets = Wallet::where('user_id',agent()->id)->where('user_type','AGENT')->orderBy('balance','DESC')->get();
        return view($this->activeTemplate . 'agent.all_wallets',compact('pageTitle','wallets'));
    }

    public function checkInsight(Request $req)
    {
        if($req->day){
            $totalMoneyInOut = totalMoneyInOut($req->day);
            return response()->json($totalMoneyInOut);
        }
        return response()->json(['error' =>'Sorry can\'t process your request right now']);
    }

    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user = agent();
        return view($this->activeTemplate. 'agent.profile_setting', compact('pageTitle','user'));
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'address' => 'sometimes|required|max:80',
            'state' => 'sometimes|required|max:80',
            'zip' => 'sometimes|required|max:40',
            'city' => 'sometimes|required|max:50',
            'image' => ['image',new FileTypeValidate(['jpg','jpeg','png'])]
        ],[
            'firstname.required'=>'First name field is required',
            'lastname.required'=>'Last name field is required'
        ]);

        $user = agent();

        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;

        $in['address'] = [
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => @$user->address->country,
            'city' => $request->city,
        ];


        if ($request->hasFile('image')) {
            $location = imagePath()['profile']['agent']['path'];
            $size = imagePath()['profile']['agent']['size'];
            $filename = uploadImage($request->image, $location, $size, $user->image);
            $in['image'] = $filename;
        }
        $user->fill($in)->save();
        $notify[] = ['success', 'Profile updated successfully.'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change password';
        return view($this->activeTemplate . 'agent.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {

        $password_validation = Password::min(6);
        $general = GeneralSetting::first();
        if ($general->secure_password) {
            $password_validation = $password_validation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $this->validate($request, [
            'current_password' => 'required',
            'password' => ['required','confirmed',$password_validation]
        ]);
        

        try {
            $user = agent();
            if (Hash::check($request->current_password, $user->password)) {
                $password = Hash::make($request->password);
                $user->password = $password;
                $user->save();
                $notify[] = ['success', 'Password changes successfully.'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'The password doesn\'t match!'];
                return back()->withNotify($notify);
            }
        } catch (\PDOException $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function show2faForm()
    {
        $general = GeneralSetting::first();
        $ga = new GoogleAuthenticator();
        $user = agent();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . $general->sitename, $secret);
        $pageTitle = 'Two Factor';
        return view($this->activeTemplate.'agent.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = agent();
        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user,$request->code,$request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = 1;
            $user->save();
            $userAgent = getIpInfo();
            $osBrowser = osBrowser();
            notify($user, '2FA_ENABLE', [
                'operating_system' => @$osBrowser['os_platform'],
                'browser' => @$osBrowser['browser'],
                'ip' => @$userAgent['ip'],
                'time' => @$userAgent['time']
            ]);
            $notify[] = ['success', 'Google authenticator enabled successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }


    public function disable2fa(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = agent();
        $response = verifyG2fa($user,$request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = 0;
            $user->save();
            $userAgent = getIpInfo();
            $osBrowser = osBrowser();
            notify($user, '2FA_DISABLE', [
                'operating_system' => @$osBrowser['os_platform'],
                'browser' => @$osBrowser['browser'],
                'ip' => @$userAgent['ip'],
                'time' => @$userAgent['time']
            ]);
            $notify[] = ['success', 'Two factor authenticator disable successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function trxHistory(Request $req)
    {
        return userTrx($req);
    }

    public function depositHistory()
    {
        $pageTitle = 'Add Money History';
        $emptyMessage = 'No history found.';
        $logs = Deposit::where('user_id',agent()->id)->where('user_type','AGENT')->with(['gateway'])->orderBy('id','desc')->paginate(getPaginate());
        return view($this->activeTemplate.'agent.deposit_history', compact('pageTitle', 'emptyMessage', 'logs'));
    }

    public function kycForm()
    {
        $pageTitle = "Fill Up KYC";
        $user = userGuard()['user'];
        if($user->kyc_status == 1 || $user->kyc_status == 2){
            $notify[]=['error','Your KYC info. is already verified/submitted'];
            return redirect(route('user.home'))->withNotify($notify);
        }
        $userKyc = KycForm::where('user_type',userGuard()['type'])->where('status',1)->firstOrFail();
        return view($this->activeTemplate.'agent.kyc_form',compact('pageTitle','userKyc'));
    }

    public function kycFormSubmit(Request $request)
    {
        $userKyc = KycForm::where('user_type',userGuard()['type'])->where('status',1)->firstOrFail();
        $rules = [];
        $inputField = [];
        if ($userKyc->form_data != null) {
            foreach ($userKyc->form_data as $key => $cus) {
                $rules[$key] = [$cus->validation];
                if ($cus->type == 'file') {
                    array_push($rules[$key], 'image');
                    array_push($rules[$key], 'mimes:jpeg,jpg,png');
                    array_push($rules[$key], 'max:2048');
                }
                if ($cus->type == 'text') {
                    array_push($rules[$key], 'max:191');
                }
                if ($cus->type == 'textarea') {
                    array_push($rules[$key], 'max:300');
                }
                $inputField[] = $key;
            }
        }
        $this->validate($request, $rules);
        
        $path = imagePath()['kyc']['user']['path'];
        $collection = collect($request->except('_token'));
        $reqField = [];
        if ($userKyc->form_data != null) {
            foreach ($collection as $k => $v) {
                foreach ($userKyc->form_data as $inKey => $inVal) {
                  
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {
                                try {
                                    $reqField[$inKey] = [
                                        'field_value' => uploadImage($request[$inKey], $path),
                                        'type' => $inVal->type,
                                    ];
                                } catch (\Exception $exp) {
                                    $notify[] = ['error', 'Could not upload your ' . $request[$inKey]];
                                    return back()->withNotify($notify)->withInput();
                                }
                            }
                        } else {
                            $reqField[$inKey] = $v;
                            $reqField[$inKey] = [
                                'field_value' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }
            
        } else {
            $reqField = null;
        }

        $user = userGuard()['user'];
        $user->kyc_info = $reqField;
        $user->kyc_status = 2;
        $user->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_type = userGuard()['type'];
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'KYC info submitted by '.$user->username;
        $adminNotification->click_url = urlPath('admin.kyc.info.agent.details',$user->id);
        $adminNotification->save();

        $notify[]=['success','KYC info submitted successfully for admin review'];
        return redirect(route('agent.home'))->withNotify($notify);
    }

    public function qrCodeGenerate()
    {
        $pageTitle = 'QR Code';
        $qrCode = qrCode();
        $uniqueCode = $qrCode->unique_code;
        $qrCode = cryptoQR($uniqueCode);
        return view($this->activeTemplate.'agent.qr_code',compact('pageTitle','qrCode','uniqueCode'));
    }

    public function downLoadQrJpg($uniqueCode)
    {
        $general = GeneralSetting::first();
        $file = cryptoQR(route('qr.scan',$uniqueCode));
        $filename = $uniqueCode.'.jpg';
        $template = Image::make('assets/images/qr/'.$general->qr_template);
        $qrCode = Image::make($file)->opacity(100)->fit(2000,2000);
        $template->insert($qrCode,'center'); 
        $template->encode('jpg');
    
        $headers = [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename='. $filename,
        ];
        return response()->stream(function() use ($template) {
            echo $template;
        }, 200, $headers);
    }


    public function commissionLog()
    {
        $pageTitle = "Commission Logs";
        $logs = Transaction::where('user_type','AGENT')->where('user_id',agent()->id)->where('remark','commission')->orderBy('id','DESC')->paginate(getPaginate());
        return view($this->activeTemplate.'agent.commission_log',compact('pageTitle','logs'));
    }
}
