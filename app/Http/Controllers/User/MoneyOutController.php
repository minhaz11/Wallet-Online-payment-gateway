<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;

class MoneyOutController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }

    public function checkUser(Request $request){
        $exist['data'] = Agent::where('username',$request->agent)->orWhere('email',$request->agent)->first();
        return response($exist);
    }

    public function moneyOut()
    {
        $permission = module('money_out');
        if($permission->status == 0){
            $notify[]=['error','Money out is currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = "Money Out";
        $moneyOutCharge = TransactionCharge::where('slug','money_out_charge')->first();
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')->where('balance','>',0)->orderBy('balance','DESC')->get();
        return  view($this->activeTemplate.'user.money_out.money_out_form',compact('pageTitle','wallets','moneyOutCharge'));
    }

    public function moneyOutConfirm(Request $request)
    {
        $permission = module('money_out');
        if($permission->status == 0){
            $notify[]=['error','Money out is currently not available'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'wallet_id' => 'required|integer',
            'amount' => 'required|gt:0',
            'agent' => 'required',
        ]);
 
        if (auth()->user()->ts) {
         $response = verifyG2fa(auth()->user(),$request->ts);
         if (!$response) {
             $notify[] = ['error', 'Wrong verification code'];
             return back()->withNotify($notify)->withInput();
         }   
        }

        $wallet = Wallet::find($request->wallet_id);
        if(!$wallet){
            $notify[]=['error','Wallet Not found'];
            return back()->withNotify($notify)->withInput();
        }
 
        $agent = Agent::where('username',$request->agent)->orWhere('email',$request->agent)->first();
        if(!$agent){
            $notify[]=['error','Sorry! Agent Not Found'];
            return back()->withNotify($notify)->withInput();
        }

       $agentWallet = Wallet::where('user_type','AGENT')->where('user_id',$agent->id)->where('currency_id', $wallet->currency->id)->first();
       if(!$agentWallet){
            $agentWallet = new Wallet();
            $agentWallet->user_id = $agent->id;
            $agentWallet->user_type = 'AGENT';
            $agentWallet->currency_id = $wallet->currency_id;
            $agentWallet->currency_code = $wallet->currency->currency_code;
            $agentWallet->save(); 
        }


       $rate = $wallet->currency->rate;
       $moneyOutCharge = TransactionCharge::where('slug','money_out_charge')->firstOrFail();
       if($request->amount < $moneyOutCharge->min_limit/$rate || $request->amount > $moneyOutCharge->max_limit/$rate){
           $notify[]=['error','Please Follow the money out limit'];
           return back()->withNotify($notify)->withInput();
       }

       if($moneyOutCharge->daily_limit != -1 && auth()->user()->moneyOutDailyLimit() > $moneyOutCharge->daily_limit){
           $notify[]=['error','Your daily money out limit exceeded'];
           return back()->withNotify($notify)->withInput();
       }
       
       if( $moneyOutCharge->monthly_limit != 1 && auth()->user()->moneyOutMonthlyLimit() > $moneyOutCharge->monthly_limit){
           $notify[]=['error','Your monthly money out limit exceeded'];
           return back()->withNotify($notify)->withInput();
       }
       
       
       $fixedCharge = $moneyOutCharge->fixed_charge / $rate;
       $percentCharge = $request->amount * $moneyOutCharge->percent_charge/100;
       $totalCharge = $fixedCharge + $percentCharge;
       
       //agent commission
       $fixedCommission = $moneyOutCharge->agent_commission_fixed / $rate;
       $percentCommission = $request->amount * $moneyOutCharge->agent_commission_percent/100;
       
      
       if($wallet->currency->currency_type == 1){
           $totalAmount = getAmount($request->amount + $totalCharge,2);
           $totalCommission = getAmount($fixedCommission + $percentCommission,2);
           $totalCharge = getAmount($totalCharge,2);
        } else {
            $totalAmount = getAmount( $request->amount + $totalCharge,8);
            $totalCommission = getAmount($fixedCommission + $percentCommission,8);
            $totalCharge = getAmount($totalCharge,8);
        }
     
       if($totalAmount > $wallet->balance){
            $notify[]=['error','Sorry! insufficient balance in this wallet'];
            return back()->withNotify($notify)->withInput(); 
       }

        $wallet->balance -=  $totalAmount;
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
        $senderTrx->operation_type = 'money_out';
        $senderTrx->details = 'Money out to';
        $senderTrx->receiver_id = $agent->id;
        $senderTrx->receiver_type = "AGENT";
        $senderTrx->trx = getTrx();
        $senderTrx->save();

        $agentWallet->balance += $request->amount;
        $agentWallet->save();
        
        $agentTrx = new Transaction();
        $agentTrx->user_id = $agent->id;
        $agentTrx->user_type = 'AGENT';
        $agentTrx->wallet_id = $agentWallet->id;
        $agentTrx->currency_id = $agentWallet->currency_id;
        $agentTrx->amount = $request->amount;
        $agentTrx->post_balance = $agentWallet->balance;
        $agentTrx->charge =  0;
        $agentTrx->trx_type = '+';
        $agentTrx->operation_type = 'money_out';
        $agentTrx->details = 'Money out from ';
        $agentTrx->receiver_id = auth()->id();
        $agentTrx->receiver_type = "USER";
        $agentTrx->trx = $senderTrx->trx;
        $agentTrx->save();
        
        //agent commission
        $agentWallet->balance +=  $totalCommission;
        $agentWallet->save();
        
        $agentCommissionTrx = new Transaction();
        $agentCommissionTrx->user_id = $agent->id;
        $agentCommissionTrx->user_type = 'AGENT';
        $agentCommissionTrx->wallet_id = $agentWallet->id;
        $agentCommissionTrx->currency_id = $agentWallet->currency_id;
        $agentCommissionTrx->amount = $totalCommission;
        $agentCommissionTrx->post_balance = $agentWallet->balance;
        $agentCommissionTrx->charge =  0;
        $agentCommissionTrx->trx_type = '+';
        $agentCommissionTrx->remark = 'commission';
        $agentCommissionTrx->operation_type = 'money_out';
        $agentCommissionTrx->details = 'Money out commission';
        $agentCommissionTrx->trx = $senderTrx->trx;
        $agentCommissionTrx->save();
        
        
            //to user
        notify(auth()->user(),'MONEY_OUT',[
            'amount'=> showAmount($request->amount),
            'charge' => showAmount($totalCharge),
            'curr_code' => $wallet->currency->currency_code,
            'agent' => $agent->fullname.' ( '.$agent->username.' )',
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($wallet->balance),
        ]);
        
        //to agent
        notify($agent,'MONEY_OUT_TO_AGENT',[
            'amount'=> showAmount($request->amount),
            'curr_code' => $wallet->currency->currency_code,
            'user' => auth()->user()->fullname.' ( '.auth()->user()->username.' )',
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($agentWallet->balance - $totalCommission),
        ]);

        //agent commission
        notify($agent,'MONEY_OUT_COMMISSION_AGENT',[
            'amount'=> showAmount($request->amount),
            'curr_code' => $wallet->currency->currency_code,
            'commission' => showAmount($totalCommission),
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($agentWallet->balance),
        ]);

        
      
        $notify[]=['success','Money out successful'];
        return back()->withNotify($notify);

    }
}
