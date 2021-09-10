<?php
namespace App\Http\Controllers\User;
use Image;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\KycForm;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Rules\FileTypeValidate;
use App\Lib\GoogleAuthenticator;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }
    
    public function home()
    {
        $pageTitle = 'Dashboard';
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')
        ->select(DB::raw('*'))
        ->addSelect(DB::raw('
            (select count(*) 
            from transactions
            where wallet_id = wallets.id) 
            as transactions
        '))
        ->orderBy('transactions','desc')
        ->take(3)->get();

        foreach($wallets as $wallet){
            $totalBalance[] = $wallet->balance * $wallet->currency->rate;
        }
        $userKyc = KycForm::where('user_type',userGuard()['type'])->first();
        $histories = Transaction::where([['user_id',auth()->id()],['user_type','USER']])->with('currency')->orderBy('id','desc')->take(7)->get();
        $totalMoneyInOut = totalMoneyInOut();
        $kyc = kycStyle();
        return view($this->activeTemplate . 'user.dashboard', compact('pageTitle','wallets','totalBalance','totalMoneyInOut','histories','userKyc','kyc'));
    }

    public function allWallets()
    {
        $pageTitle = "All Wallets";
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')->orderBy('balance','DESC')->get();
        return view($this->activeTemplate . 'user.all_wallets',compact('pageTitle','wallets'));
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
        $user = Auth::user();
        return view($this->activeTemplate. 'user.profile_setting', compact('pageTitle','user'));
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

        $user = Auth::user();

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
            $location = imagePath()['profile']['user']['path'];
            $size = imagePath()['profile']['user']['size'];
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
        return view($this->activeTemplate . 'user.password', compact('pageTitle'));
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
            $user = auth()->user();
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


    public function depositHistory()
    {
        $pageTitle = 'Add Money History';
        $emptyMessage = 'No history found.';
        $logs = Deposit::where('user_id',auth()->id())->where('user_type','USER')->with(['gateway'])->orderBy('id','desc')->paginate(getPaginate());
        return view($this->activeTemplate.'user.deposit_history', compact('pageTitle', 'emptyMessage', 'logs'));
    }


    public function show2faForm()
    {
        $general = GeneralSetting::first();
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . $general->sitename, $secret);
        $pageTitle = 'Two Factor';
        return view($this->activeTemplate.'user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
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

        $user = auth()->user();
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

    public function kycForm()
    {
        $pageTitle = "Fill Up KYC";
        $user = userGuard()['user'];
        if($user->kyc_status == 1 || $user->kyc_status == 2){
            $notify[]=['error','Your KYC info. is already verified/submitted'];
            return redirect(route('user.home'))->withNotify($notify);
        }
        $userKyc = KycForm::where('user_type',userGuard()['type'])->where('status',1)->firstOrFail();
        return view($this->activeTemplate.'user.kyc_form',compact('pageTitle','userKyc'));
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
        $adminNotification->click_url = urlPath('admin.kyc.info.user.details',$user->id);
        $adminNotification->save();

        $notify[]=['success','KYC info submitted successfully for admin review'];
        return redirect(route('user.home'))->withNotify($notify);
    }

    public function qrCodeGenerate()
    {
        $pageTitle = 'QR Code';
        $qrCode = qrCode();
        $uniqueCode = $qrCode->unique_code;
        $qrCode = cryptoQR($uniqueCode);
        return view($this->activeTemplate.'user.qr_code',compact('pageTitle','qrCode','uniqueCode'));
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

}
