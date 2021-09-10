<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;

class MakePaymentController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }

    public function checkUser(Request $request){
        $exist['data'] = Merchant::where('username',$request->agent)->orWhere('email',$request->agent)->first();
        return response($exist);
    }

    public function paymentFrom()
    {
        $permission = module('make_payment');
        if($permission->status == 0){
            $notify[]=['error','Make Payment is currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = "Make Payment";
        $paymentCharge = TransactionCharge::where('slug','make_payment')->first();
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')->where('balance','>',0)->orderBy('balance','DESC')->get();
        return  view($this->activeTemplate.'user.makepayment.make_payment',compact('pageTitle','wallets','paymentCharge'));
    }

    public function paymentConfirm(Request $request)
    {
        $permission = module('make_payment');
        if($permission->status == 0){
            $notify[]=['error','Make Payment is currently not available'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'wallet_id' => 'required|integer',
            'amount' => 'required|gt:0',
            'merchant' => 'required',
        ]);
 
        if (auth()->user()->ts) {
            $response = verifyG2fa(auth()->user(),$request->ts);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify)->withInput();
            }   
        }

        $paymentCharge = TransactionCharge::where('slug','make_payment')->firstOrFail();
 
        $wallet = Wallet::find($request->wallet_id);
        if(!$wallet){
            $notify[]=['error','Wallet Not found'];
            return back()->withNotify($notify)->withInput();
        }
 
        $merchant = Merchant::where('username',$request->merchant)->orWhere('email',$request->merchant)->first();
        if(!$merchant){
            $notify[]=['error','Sorry! Merchant Not Found'];
            return back()->withNotify($notify)->withInput();
        }

       $merchantWallet = Wallet::where('user_type','MERCHANT')->where('user_id',$merchant->id)->where('currency_id', $wallet->currency->id)->first();
       if(!$merchantWallet){
            $newWallet = new Wallet();
            $newWallet->user_id = $merchant->id;
            $newWallet->user_type = 'MERCHANT';
            $newWallet->currency_id = $wallet->currency_id;
            $newWallet->currency_code = $wallet->currency->currency_code;
            $newWallet->save(); 
        }

        //user charge
       $rate = $wallet->currency->rate;
       $fixedCharge = $paymentCharge->fixed_charge / $rate;
       $percentCharge = $request->amount * $paymentCharge->percent_charge/100;
       $totalCharge = $fixedCharge + $percentCharge;
       
       //merchant charge
       $merchantFixedCharge = $paymentCharge->merchant_fixed_charge / $rate;
       $merchantPercentCharge = $request->amount * $paymentCharge->merchant_percent_charge/100;
       $merchantTotalCharge = $merchantFixedCharge + $merchantPercentCharge;
    
       if($wallet->currency->currency_type == 1){
           $userTotalAmount = getAmount($request->amount + $totalCharge,2);
           $merchantTotalAmount = getAmount($request->amount - $merchantTotalCharge,2);
           $totalCharge = getAmount($totalCharge,2);
           $merchantTotalCharge = getAmount($merchantTotalCharge,2);
        } else {
            $userTotalAmount = getAmount( $request->amount + $totalCharge,8);
            $merchantTotalAmount = getAmount($request->amount - $merchantTotalCharge,8);
            $totalCharge = getAmount($totalCharge,8);
            $merchantTotalCharge = getAmount($merchantTotalCharge,8);
        }
     
       if($userTotalAmount > $wallet->balance){
            $notify[]=['error','Sorry! insufficient balance in wallet'];
            return back()->withNotify($notify)->withInput(); 
       }

        $wallet->balance -=  $userTotalAmount;
        $wallet->save();

        $senderTrx = new Transaction();
        $senderTrx->user_id = auth()->id();
        $senderTrx->user_type = 'USER';
        $senderTrx->wallet_id = $wallet->id;
        $senderTrx->currency_id = $wallet->currency_id;
        $senderTrx->amount = $request->amount;
        $senderTrx->post_balance = $wallet->balance;
        $senderTrx->charge =  $totalCharge;
        $senderTrx->trx_type = '-';
        $senderTrx->operation_type = 'make_payment';
        $senderTrx->details = 'Payment successful to';
        $senderTrx->receiver_id = $merchant->id;
        $senderTrx->receiver_type = "MERCHANT";
        $senderTrx->trx = getTrx();
        $senderTrx->save();

        $merchantWallet->balance += $merchantTotalAmount;
        $merchantWallet->save();

        $merchantTrx = new Transaction();
        $merchantTrx->user_id = $merchant->id;
        $merchantTrx->user_type = 'MERCHANT';
        $merchantTrx->wallet_id = $merchantWallet->id;
        $merchantTrx->currency_id = $merchantWallet->currency_id;
        $merchantTrx->amount = $request->amount;
        $merchantTrx->post_balance = $merchantWallet->balance;
        $merchantTrx->charge =  $merchantTotalCharge;
        $merchantTrx->trx_type = '+';
        $merchantTrx->operation_type = 'make_payment';
        $merchantTrx->details = 'Payment successful from';
        $merchantTrx->receiver_id = auth()->id();
        $merchantTrx->receiver_type = "USER";
        $merchantTrx->trx = $senderTrx->trx;
        $merchantTrx->save();

      
        notify(auth()->user(),'MAKE_PAYMENT',[
            'amount'=> showAmount($request->amount),
            'charge' => showAmount($totalCharge),
            'curr_code' => $wallet->currency->currency_code,
            'merchant' => $merchant->fullname.' ( '.$merchant->username.' )',
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($wallet->balance),
        ]);

        notify($merchant,'MAKE_PAYMENT_MERCHANT',[
            'amount'=> showAmount($request->amount),
            'charge' => showAmount($merchantTotalCharge),
            'curr_code' => $wallet->currency->currency_code,
            'user' => auth()->user()->fullname.' ( '.auth()->user()->username.' )',
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($merchantWallet->balance),
        ]);
       
        $notify[]=['success','Payment successful'];
        return back()->withNotify($notify);

    }

}
