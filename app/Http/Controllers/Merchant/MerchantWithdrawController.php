<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;

use App\Models\Wallet;
use App\Models\Currency;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use App\Rules\FileTypeValidate;
use App\Models\AdminNotification;
use App\Models\UserWithdrawMethod;


class MerchantWithdrawController extends Controller
{
    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }
    public function withdrawMoney()
    {
        $permission = module('withdraw_money');
        if($permission->status == 0){
            $notify[]=['error','Withdraw money is currently not available'];
            return back()->withNotify($notify);
        }
        $userMethods = UserWithdrawMethod::where([['user_type','MERCHANT'],['user_id',merchant()->id],['status',1]])->whereHas('withdrawMethod')->get();
        $pageTitle = 'Withdraw Money';
        return view($this->activeTemplate.'merchant.withdraw.withdraw_money', compact('pageTitle','userMethods'));
    }
    public function withdrawMethods()
    {
        $userMethods = UserWithdrawMethod::where('user_type','MERCHANT')->where('user_id',merchant()->id)->whereHas('withdrawMethod')->paginate(getPaginate());
        $pageTitle = 'Withdraw Methods';
        return view($this->activeTemplate.'merchant.withdraw.methods', compact('pageTitle','userMethods'));
    }

    public function addWithdrawMethod()
    {
        $pageTitle = "Add Withdraw Method";
        $withdrawMethod = WithdrawMethod::whereJsonContains('user_guards',userGuard()['type'])->where('status',1)->get();
        $currencies = Currency::pluck('id','currency_code');
        return view($this->activeTemplate.'merchant.withdraw.add_method', compact('pageTitle','withdrawMethod','currencies'));
    }

    public function storeHelper($request,$rules)
    {
        $withdrawMethod = WithdrawMethod::where('id',$request->method_id)->whereJsonContains('user_guards',userGuard()['type'])->where('status',1)->firstOrFail();
        if (!$withdrawMethod) {
            $notify[]=['error','Something went wrong!'];
            return back()->withNotify($notify);
        }
       
        if ($withdrawMethod->user_data != null) {
            foreach ($withdrawMethod->user_data as $key => $cus) {
                $rules[$key] = [$cus->validation];
                if ($cus->type == 'file') {
                    array_push($rules[$key], 'image');
                    array_push($rules[$key], new FileTypeValidate(['jpg','jpeg','png']));
                    array_push($rules[$key], 'max:2048');
                }
                if ($cus->type == 'text') {
                  
                    array_push($rules[$key], 'max:191');
                }
                if ($cus->type == 'textarea') {
                    array_push($rules[$key], 'max:300');
                }
            }
        }


        $directory = date("Y")."/".date("m")."/".date("d");
        $path = imagePath()['verify']['withdraw']['path'].'/'.$directory;
        $collection = collect($request);
        $reqField = [];
        if ($withdrawMethod->user_data != null) {
            foreach ($collection as $k => $v) {
                foreach ($withdrawMethod->user_data as $inKey => $inVal) {
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {
                                try {
                                    $reqField[$inKey] = [
                                        'field_name' =>$directory.'/'.uploadImage($request[$inKey], $path),
                                        'type' => $inVal->type,
                                        'validation' => $inVal->validation
                                    ];
                                                                        
                                } catch (\Exception $exp) {
                                    $notify[] = ['error', 'Could not upload your ' . $request[$inKey]];
                                    return back()->withNotify($notify)->withInput();
                                }
                            }
                        } else {
                            $reqField[$inKey] = [
                                'field_name' => $v,
                                'type' => $inVal->type,
                                'validation' => $inVal->validation
                            ];
                           
                        }
                    }
                }
            }
            
        }

        return ['user_data'=>$reqField,'rules'=>$rules];
    }
    
    public function withdrawMethodStore(Request $request)
    {
        $rules = ['name'=>'required','method_id'=>'required','currency_id'=>'required'];
        $storeHelper = $this->storeHelper($request, $rules);

        $this->validate($request, $storeHelper['rules']);
        $userMethod = new UserWithdrawMethod();
        $userMethod->name =$request->name;
        $userMethod->user_id = userGuard()['user']->id;
        $userMethod->user_type = userGuard()['type'];
        $userMethod->method_id = $request->method_id;
        $userMethod->currency_id = $request->currency_id;
        $userMethod->user_data = $storeHelper['user_data'];
        $userMethod->save();
        $notify[]=['success','Withdraw method Updated successfully'];
        return redirect(route('merchant.withdraw.methods'))->withNotify($notify);
     

    }

    public function withdrawStore(Request $request)
    {
        $permission = module('withdraw_money');
        if($permission->status == 0){
            $notify[]=['error','Withdraw money is currently not available'];
            return back()->withNotify($notify);
        }

        $this->validate($request, [
            'method_id' => 'required',
            'user_method_id' => 'required',
            'amount' => 'required|numeric'
        ]);

        $user = userGuard()['user'];
        if ($user->ts) {
            $response = verifyG2fa($user,$request->authenticator_code);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify);
            }   
        }


        $method = WithdrawMethod::where('id', $request->method_id)->where('status', 1)->firstOrFail();
        $userMethod = UserWithdrawMethod::findOrFail($request->user_method_id);
      
        $currency = Currency::find($userMethod->currency_id);
        if(!$currency){
            $notify[] = ['error', 'Currency not found'];
            return back()->withNotify($notify);
        }
        $wallet = Wallet::where('user_type','MERCHANT')->where('user_id',merchant()->id)->where('currency_id',$currency->id)->first();
        if(!$wallet){
            $notify[] = ['error', 'Wallet not found'];
            return back()->withNotify($notify);
        }

       
        if ($method->min_limit/$currency->rate >  $request->amount || $method->max_limit/$currency->rate <  $request->amount) {
            $notify[] = ['error', 'Please follow the limits'];
            return back()->withNotify($notify);
        }
       
        if ($request->amount > $wallet->balance) {
            $notify[] = ['error', 'You do not have sufficient balance for withdraw.'];
            return back()->withNotify($notify);
        }


        $charge = ($method->fixed_charge/$currency->rate) + ($request->amount * $method->percent_charge / 100);
        $finalAmount = $request->amount - $charge;


        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id; 
        $withdraw->user_id = $user->id;
        $withdraw->user_type = userGuard()['type'];
        $withdraw->amount = $request->amount;
        $withdraw->currency_id = $currency->id;
        $withdraw->wallet_id = $wallet->id;
        $withdraw->currency = $currency->currency_code;
        $withdraw->charge = $charge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $finalAmount;
        $withdraw->	withdraw_information = $userMethod->user_data;
        $withdraw->trx = getTrx();
        $withdraw->save();
        session()->put('wtrx',$withdraw->trx);
        return redirect(route('merchant.withdraw.preview'));

    }

    public function withdrawPreview()
    {
        $withdraw = Withdrawal::with('method','merchant')->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id','desc')->firstOrFail();
        $pageTitle = 'Withdraw Preview';
        return view($this->activeTemplate . 'merchant.withdraw.preview', compact('pageTitle','withdraw'));
    }


    public function withdrawSubmit()
    {
        $permission = module('withdraw_money');
        if($permission->status == 0){
            $notify[]=['error','Withdraw money is currently not available'];
            return back()->withNotify($notify);
        }
        if(!session('wtrx')){
            $notify[]=['error','Sorry! something went  wrong'];
            return back()->withNotify($notify);
        }
        $withdraw = Withdrawal::with('method','merchant')->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id','desc')->firstOrFail();

        $wallet = Wallet::find($withdraw->wallet_id);
        if(!$wallet){
            $notify[]=['error','wallet not found'];
            return back()->withNotify($notify);
        }
     
        $withdraw->status = 2;
        $withdraw->save();
       
        $wallet->balance  -=  $withdraw->amount;
        $wallet->save();

        $transaction = new Transaction();
        $transaction->user_id = $withdraw->user_id;
        $transaction->user_type = $withdraw->user_type;
        $transaction->wallet_id = $wallet->id;
        $transaction->currency_id = $withdraw->currency_id;
        $transaction->amount = $withdraw->amount;
        $transaction->post_balance = $wallet->balance;
        $transaction->charge = $withdraw->charge;
        $transaction->trx_type = '-';
        $transaction->operation_type = 'withdraw_money';
        $transaction->details = 'Money withdrawal';
        $transaction->trx =  $withdraw->trx;
        $transaction->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = userGuard()['user']->id;
        $adminNotification->user_type = $withdraw->user_type;
        $adminNotification->title = 'New withdraw request from '.userGuard()['user']->username;
        $adminNotification->click_url = urlPath('admin.withdraw.details',$withdraw->id);
        $adminNotification->save();

        notify(userGuard()['user'], 'WITHDRAW_REQUEST', [
            'method_name' => $withdraw->method->name,
            'method_currency' => $wallet->currency->currency_code,
            'method_amount' => showAmount($withdraw->final_amount),
            'amount' => showAmount($withdraw->amount),
            'charge' => showAmount($withdraw->charge),
            'currency' => $wallet->currency->currency_code,
            'trx' => $withdraw->trx,
            'post_balance' => showAmount($wallet->balance),
        ]);

        $notify[] = ['success', 'Withdraw request sent successfully'];
        return redirect()->route('merchant.withdraw.history')->withNotify($notify);
    }


    public function withdrawMethodEdit($id)
    {
        $pageTitle = 'Withdraw Method Edit';
        $userMethod = UserWithdrawMethod::where('id',$id)->where('user_type','MERCHANT')->where('user_id',merchant()->id)->whereHas('withdrawMethod')->first();
        if (!$userMethod) {
            $notify[]=['error','Withdraw method not found'];
            return back()->withNotify($notify);
        }
        $withdrawMethod = WithdrawMethod::whereJsonContains('user_guards',userGuard()['type'])->where('status',1)->get();
        $currencies = Currency::pluck('id','currency_code');
        return view($this->activeTemplate.'merchant.withdraw.withdraw_method_edit', compact('pageTitle','userMethod','withdrawMethod','currencies'));
    }

    public function withdrawMethodUpdate(Request $request)
    {
        $userMethod = UserWithdrawMethod::where('id',$request->id)->where('user_type','MERCHANT')->where('user_id',merchant()->id)->first();
        if (!$userMethod) {
            $notify[]=['error','Withdraw method not found'];
            return back()->withNotify($notify);
        }
        
        $rules = ['name'=>'required'];
        $storeHelper = $this->storeHelper($request, $rules);

        $this->validate($request, $storeHelper['rules']);
      
        $userMethod->name         = $request->name;
        $userMethod->user_id      = userGuard()['user']->id;
        $userMethod->user_type    = userGuard()['type'];
        $userMethod->user_data    = $storeHelper['user_data'];
        $userMethod->status       = $request->status ? 1:0;
        $userMethod->save();
        $notify[]=['success','Withdraw method added successfully'];
        return back()->withNotify($notify);
    }

    public function withdrawLog()
    {
        $pageTitle = "Withdraw Log";
        $user = userGuard()['user'];
        $userType = userGuard()['type'];
        $withdraws = Withdrawal::where('user_id',$user->id)->where('user_type',$userType)->where('status', '!=', 0)->with('method')->whereHas('method')->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = "No Data Found!";
        return view($this->activeTemplate.'merchant.withdraw.log', compact('pageTitle','withdraws','emptyMessage'));
    }
}
