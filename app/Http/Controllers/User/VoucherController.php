<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;

use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Voucher;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;

class VoucherController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }

    public function userVoucherList()
    {
        $pageTitle = "Voucher List";
        $vouchers = Voucher::where('user_type','USER')->where('user_id',auth()->id())->whereHas('currency')->orderBy('is_used',"ASC")->orderBy('id',"DESC")->paginate(getPaginate());
        return view($this->activeTemplate.'user.voucher.list',compact('pageTitle','vouchers'));
    }

    public function userVoucher()
    {
        $permission = module('create_voucher');
        if($permission->status == 0){
            $notify[]=['error','Create voucher Creation is currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = "Create Voucher";
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')->where('balance','>',0)->orderBy('balance','DESC')->get();
        $voucherCharge = TransactionCharge::where('slug','voucher_charge')->first();
        return view($this->activeTemplate.'user.voucher.create',compact('pageTitle','wallets','voucherCharge'));
    }

    public function userVoucherCreate(Request $request)
    {
        $permission = module('create_voucher');
        if($permission->status == 0){
            $notify[]=['error','Create voucher is currently not available'];
            return back()->withNotify($notify);
        }
        
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'wallet_id' => 'required|integer'
        ]);

        if (auth()->user()->ts) {
            $response = verifyG2fa(auth()->user(),$request->ts);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify)->withInput();
            }   
        }

        $voucherCharge = TransactionCharge::where('slug','voucher_charge')->first();
        if(!$voucherCharge){
            $notify[]=['error','Sorry! something went wrong. Please try again'];
            return redirect(route('user.voucher.create'))->withNotify($notify);
        }

        $wallet = Wallet::where('id',$request->wallet_id)->where('user_id',auth()->id())->where('user_type','USER')->first();
        if(!$wallet){
            $notify[]=['error','Sorry! Wallet not found'];
            return redirect(route('user.voucher.create'))->withNotify($notify);
        }

        $rate = $wallet->currency->rate;
        if($request->amount < $voucherCharge->min_limit/$rate || $request->amount > $voucherCharge->max_limit/$rate){
            $notify[]=['error','Please Follow the voucher limit'];
            return back()->withNotify($notify)->withInput();
        }
        if($voucherCharge->voucher_limit != -1 && auth()->user()->dailyVoucherLimit() >= $voucherCharge->voucher_limit){
            $notify[]=['error','Daily voucher create limit has been exceeded'];
            return back()->withNotify($notify)->withInput();
        }

       

        $voucher = new Voucher();
        $voucher->user_id = auth()->id();
        $voucher->user_type =  'USER';
        $voucher->currency_id = $wallet->currency_id;
        $voucher->amount = $request->amount;
        $voucher->voucher_code = getNumber(20);
        $voucher->save();
        
        $rate = $wallet->currency->rate;
        $fixedCharge = $voucherCharge->fixed_charge / $rate;
        $percentCharge = $request->amount * $voucherCharge->percent_charge/100;
        $totalCharge = $fixedCharge + $percentCharge;
     
       
        $cap = $voucherCharge->cap/$rate;
        if($voucherCharge->cap != -1 && $totalCharge > $cap){
            $totalCharge = $cap;
        }
    
        if($wallet->currency->currency_type == 1){
            $totalCharge = getAmount($totalCharge,2);
            $totalAmount = getAmount($request->amount + $totalCharge,2);
         } else {
             $totalCharge = getAmount($totalCharge,8);
             $totalAmount = getAmount($request->amount + $totalCharge,8);
         }

        if($totalAmount > $wallet->balance){
            $notify[]=['error','Insufficient balance'];
            return back()->withNotify($notify)->withInput();
        }

        $commission = ($totalAmount*$voucherCharge->commission)/100;
        $wallet->balance -=  $totalAmount;
        $wallet->save();

        $trx = new Transaction();
        $trx->user_id = auth()->id();
        $trx->user_type = 'USER';
        $trx->wallet_id = $wallet->id;
        $trx->currency_id = $wallet->currency_id;
        $trx->amount = $request->amount;
        $trx->post_balance =  $wallet->balance;
        $trx->charge =  $totalCharge;
        $trx->trx_type = '-';
        $trx->operation_type = 'create_voucher';
        $trx->details = 'Voucher created successfully';
        $trx->trx = getTrx();
        $trx->save();

        $wallet->balance +=  $commission;
        $wallet->save();

        $commissionTrx = new Transaction();
        $commissionTrx->user_id = auth()->id();
        $commissionTrx->user_type = 'USER';
        $commissionTrx->wallet_id = $wallet->id;
        $commissionTrx->currency_id = $wallet->currency_id;
        $commissionTrx->amount = $commission;
        $commissionTrx->post_balance =  $wallet->balance;
        $commissionTrx->charge =  0;
        $commissionTrx->trx_type = '+';
        $commissionTrx->remark = 'commission';
        $commissionTrx->operation_type = 'create_voucher';
        $commissionTrx->details = 'Voucher Commission';
        $commissionTrx->trx = $trx->trx;
        $commissionTrx->save();

      
        $notify[]=['success','Voucher Created Successfully'];
        return redirect(route('user.voucher.list'))->withNotify($notify);

    }

    public function userVoucherRedeem()
    {
        $pageTitle = "Redeem Voucher";
        return view($this->activeTemplate.'user.voucher.redeem',compact('pageTitle'));
    }

    public function userVoucherRedeemConfirm(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);
        $voucher = Voucher::where('voucher_code',$request->code)->where('is_used',0)->first();
        if(!$voucher || $voucher->user_id == auth()->id()){
            $notify[]=['error','Invalid Voucher Code'];
            return back()->withNotify($notify);
        }

        $user = auth()->user();
        $wallet = Wallet::where('currency_id',$voucher->currency_id)->where('user_id',$user->id)->where('user_type','USER')->first();

        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->user_type = 'USER';
        $deposit->method_code = 0;
        $deposit->amount = $voucher->amount;
        $deposit->wallet_id = $wallet->id;
        $deposit->currency_id = $wallet->currency_id;
        $deposit->method_currency = $wallet->currency->currency_code;
        $deposit->charge = 0;
        $deposit->rate = 0;
        $deposit->final_amo = $voucher->amount;
        $deposit->btc_amo = 0;
        $deposit->btc_wallet = "";
        $deposit->trx = getTrx();
        $deposit->status = 1;
        $deposit->save();


        $wallet->balance += $voucher->amount;
        $wallet->save();

        $trx = new Transaction();
        $trx->user_id = $user->id;
        $trx->user_type = 'USER';
        $trx->wallet_id = $wallet->id;
        $trx->currency_id = $wallet->currency_id;
        $trx->amount = $voucher->amount;
        $trx->post_balance = $wallet->balance;
        $trx->charge =  0;
        $trx->trx_type = '+';
        $trx->operation_type = 'redeem_voucher';
        $trx->details = 'Redeemed Voucher ';
        $trx->trx = $deposit->trx;
        $trx->save();

        $voucher->is_used = 1;
        $voucher->redeemer_id = $user->id;
        $voucher->save();

    //    notify($user, 'VOUCHER_DEPOSIT', [
    //        'method_name' => 'Voucher Code',
    //        'amount' => getAmount($deposit->amount),
    //        'currency' => $gnl->cur_text,
    //        'trx' => $deposit->trx,
    //        'post_balance' => getAmount($user->deposit_wallet)
    //    ]);

    $notify[]=['success',getAmount($voucher->amount).' '.$deposit->method_currency.' has been added to your wallet'];
    return back()->withNotify($notify);

    }
    
    public function userVoucherRedeemLog()
    {
        $pageTitle = "Voucher Redeem Log";
        $logs = Voucher::where('redeemer_id',auth()->id())->where('is_used',1)->whereHas('currency')->paginate(getPaginate());
        return view($this->activeTemplate.'user.voucher.redeem_log',compact('pageTitle','logs'));
    }
    



}
