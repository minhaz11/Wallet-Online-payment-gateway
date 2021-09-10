<?php

namespace App\Http\Controllers\Agent;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;
use App\Http\Controllers\Controller;

class MoneyInController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }

    public function checkUser(Request $request){
        $exist['data'] = User::where('username',$request->user)->orWhere('email',$request->user)->first();
        return response($exist);
    }

   
    public function moneyInForm()
    {
        $pageTitle = "Money In";
        $moneyInCharge = TransactionCharge::where('slug','money_in_charge')->first();
        $wallets = Wallet::where('user_type','AGENT')->where('user_id',agent()->id)->where('balance','>',0)->with('currency')->get();
        return view($this->activeTemplate.'agent.money_in.money_in_form',compact('pageTitle','moneyInCharge','wallets'));
    }

    public function confirmMoneyIn(Request $request)
    {
        
            $request->validate([
                'wallet_id' => 'required|integer',
                'amount' => 'required|gt:0',
                'user' => 'required',
            ]);
     
            if (agent()->ts) {
             $response = verifyG2fa(agent(),$request->ts);
             if (!$response) {
                 $notify[] = ['error', 'Wrong verification code'];
                 return back()->withNotify($notify)->withInput();
             }   
            }
            $moneyInCharge = TransactionCharge::where('slug','money_in_charge')->firstOrFail();

            if($moneyInCharge->daily_limit != -1 && agent()->moneyOutDailyLimit() > $moneyInCharge->daily_limit){
                $notify[]=['error','Your daily money in limit exceeded'];
                return back()->withNotify($notify)->withInput();
            }
            
            if( $moneyInCharge->monthly_limit != 1 && agent()->moneyOutMonthlyLimit() > $moneyInCharge->monthly_limit){
                $notify[]=['error','Your monthly money in limit exceeded'];
                return back()->withNotify($notify)->withInput();
            }
    
            $agentWallet = Wallet::find($request->wallet_id);
            if(!$agentWallet){
                $notify[]=['error','Wallet Not found'];
                return back()->withNotify($notify)->withInput();
            }
     
            $user = User::where('username',$request->user)->orWhere('email',$request->user)->first();
            if(!$user){
                $notify[]=['error','Sorry! User Not Found'];
                return back()->withNotify($notify)->withInput();
            }
    
           $userWallet = Wallet::where('user_type','USER')->where('user_id',$user->id)->where('currency_id', $agentWallet->currency->id)->first();
           if(!$userWallet){
                $userWallet = new Wallet();
                $userWallet->user_id = $user->id;
                $userWallet->user_type = 'USER';
                $userWallet->currency_id =  $agentWallet->currency_id;
                $userWallet->currency_code =  $agentWallet->currency->currency_code;
                $userWallet->save(); 
            
            }
    
           $rate = $agentWallet->currency->rate;
        
           
           if($request->amount < $moneyInCharge->min_limit/$rate || $request->amount > $moneyInCharge->max_limit/$rate){
               $notify[]=['error','Please Follow the money in limit'];
               return back()->withNotify($notify)->withInput();
           }

           $fixedCharge = $moneyInCharge->fixed_charge / $rate;
           $percentCharge = $request->amount * $moneyInCharge->percent_charge/100;
           $totalCharge = $fixedCharge + $percentCharge;
           
           //agent commission
           $fixedCommission = $moneyInCharge->agent_commission_fixed / $rate;
           $percentCommission = $request->amount * $moneyInCharge->agent_commission_percent/100;
           
          
           if($agentWallet->currency->currency_type == 1){
               $totalAmount = getAmount($request->amount + $totalCharge,2);
               $totalCommission = getAmount($fixedCommission + $percentCommission,2);
               $totalCharge = getAmount($totalCharge,2);
            } else {
                $totalAmount = getAmount( $request->amount + $totalCharge,8);
                $totalCommission = getAmount($fixedCommission + $percentCommission,8);
                $totalCharge = getAmount($totalCharge,8);
            }
         
           if($totalAmount > $agentWallet->balance){
                $notify[]=['error','Sorry! insufficient balance in this wallet'];
                return back()->withNotify($notify)->withInput(); 
           }
    
            $agentWallet->balance -=  $totalAmount;
            $agentWallet->save();
    
            $agentTrx = new Transaction();
            $agentTrx->user_id = agent()->id;
            $agentTrx->user_type = 'AGENT';
            $agentTrx->wallet_id = $agentWallet->id;
            $agentTrx->currency_id = $agentWallet->currency_id;
            $agentTrx->amount = $request->amount;
            $agentTrx->post_balance = $agentWallet->balance;
            $agentTrx->charge =  $totalCharge;
            $agentTrx->trx_type = '-';
            $agentTrx->operation_type = 'money_in';
            $agentTrx->details = 'Money in to';
            $agentTrx->receiver_id = $user->id;
            $agentTrx->receiver_type = 'USER';
            $agentTrx->trx = getTrx();
            $agentTrx->save();
          
            $userWallet->balance += $request->amount;
            $userWallet->save();
            
            $userTrx = new Transaction();
            $userTrx->user_id = $user->id;
            $userTrx->user_type = 'USER';
            $userTrx->wallet_id = $userWallet->id;
            $userTrx->currency_id = $userWallet->currency_id;
            $userTrx->amount = $request->amount;
            $userTrx->post_balance = $userWallet->balance;
            $userTrx->charge =  0;
            $userTrx->trx_type = '+';
            $userTrx->operation_type = 'money_in';
            $userTrx->details = 'Money in money from';
            $agentTrx->receiver_id =  agent()->id;
            $agentTrx->receiver_type = 'AGENT';
            $userTrx->trx = $agentTrx->trx;
            $userTrx->save();
            
            //agent commission
            $agentWallet->balance +=  $totalCommission;
            $agentWallet->save();
            
            $agentCommissionTrx = new Transaction();
            $agentCommissionTrx->user_id = $user->id;
            $agentCommissionTrx->user_type = 'AGENT';
            $agentCommissionTrx->wallet_id = $agentWallet->id;
            $agentCommissionTrx->currency_id = $agentWallet->currency_id;
            $agentCommissionTrx->amount = $totalCommission;
            $agentCommissionTrx->post_balance = $agentWallet->balance;
            $agentCommissionTrx->charge =  0;
            $agentCommissionTrx->trx_type = '+';
            $agentCommissionTrx->remark = 'commission';
            $agentCommissionTrx->operation_type = 'money_in';
            $agentCommissionTrx->details = 'Money in commission';
            $agentCommissionTrx->trx = $agentTrx->trx;
            $agentCommissionTrx->save();
            
            
            // to user
            notify($user,'MONEY_IN',[
                'amount'=> showAmount($request->amount),             
                'curr_code' => $userWallet->currency->currency_code,
                'agent' => agent()->username,
                'trx' => $agentTrx->trx,
                'time' => showDateTime($agentTrx->created_at,'d/M/Y @h:i a'),
                'balance' => showAmount($userWallet->balance),
            ]);
            
            //to agent
            notify(agent(),'MONEY_IN_AGENT',[
                'amount'=> showAmount($request->amount),
                'charge' => showAmount($totalCharge),
                'curr_code' => $agentWallet->currency->currency_code,
                'user' => $user->fullname,
                'trx' => $agentTrx->trx,
                'time' => showDateTime($agentTrx->created_at,'d/M/Y @h:i a'),
                'balance' => showAmount($agentWallet->balance - $totalCommission),
            ]);
    
            //agent commission
            notify(agent(),'MONEY_IN_COMMISSION_AGENT',[
                'amount'=> showAmount($request->amount),
                'curr_code' => $agentWallet->currency->currency_code,
                'commission' => showAmount($totalCommission),
                'trx' => $agentTrx->trx,
                'time' => showDateTime($agentTrx->created_at,'d/M/Y @h:i a'),
                'balance' => showAmount($agentWallet->balance),
            ]);
    
        
            $notify[]=['success','Money in successful'];
            return back()->withNotify($notify);

  }
}