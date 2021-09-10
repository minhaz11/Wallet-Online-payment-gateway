<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\RequestMoney;
use Illuminate\Http\Request;
use App\Models\TransferDetail;
use App\Models\TransactionCharge;
use App\Http\Controllers\Controller;

class UserOperationController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }

    public function checkUser(Request $request){
        $exist['data'] = User::where('username',$request->user)->orWhere('email',$request->user)->first();
        
        $user = auth()->user(); 
        if(@$exist['data'] && $user->username == @$exist['data']->username || $user->email == @$exist['data']->email){
            return response()->json(['own'=>'Can\'t transfer/request to your own']);
        }
        return response($exist);
    }

    public function transfer()
    {
        $permission = module('transfer_money');
        if($permission->status == 0){
            $notify[]=['error','Transfer money is currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = "Transfer Money";
        $transferCharge = TransactionCharge::where('slug','money_transfer')->first();
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')->where('balance','>',0)->orderBy('balance','DESC')->get();
        return view($this->activeTemplate.'user.operations.transfer_money',compact('pageTitle','transferCharge','wallets'));
    }

    public function transferMoney(Request $request)
    {
        $permission = module('transfer_money');
        if($permission->status == 0){
            $notify[]=['error','Transfer money is currently not available'];
            return back()->withNotify($notify);
        }

       $request->validate([
           'wallet_id' => 'required|integer',
           'amount' => 'required|gt:0',
           'user' => 'required',
        ],
        [
            'wallet_id.required' => 'Please select a wallet'
        ]
     );


       if(auth()->user()->username == $request->user || auth()->user()->email == $request->user){
            $notify[] = ['error', 'Can\'t transfer balance to your own'];
            return back()->withNotify($notify)->withInput();
       }

       if (auth()->user()->ts) {
            $response = verifyG2fa(auth()->user(),$request->ts);
            if (!$response) {
                $notify[] = ['error', 'Wrong authentication code'];
                return back()->withNotify($notify)->withInput();
            }   
       }

       $wallet = Wallet::find($request->wallet_id);
       if(!$wallet){
           $notify[]=['error','Wallet Not found'];
           return back()->withNotify($notify)->withInput();
       }

       $rate = $wallet->currency->rate;
      
       $transferCharge = TransactionCharge::where('slug','money_transfer')->firstOrFail();
       if($request->amount < $transferCharge->min_limit/$rate || $request->amount > $transferCharge->max_limit/$rate){
           $notify[]=['error','Please follow the transfer limit'];
           return back()->withNotify($notify)->withInput();
       }


       if($transferCharge->daily_limit != -1 && auth()->user()->dailyTransferLimit() >= $transferCharge->daily_limit){
           $notify[]=['error','Daily transfer limit has been exceeded'];
           return back()->withNotify($notify)->withInput();
       }

       $receiver = User::where('username',$request->user)->orWhere('email',$request->user)->first();
       if(!$receiver){
           $notify[]=['error','Sorry! Receiver Not Found'];
           return back()->withNotify($notify)->withInput();
       }

       $receiverWallet = Wallet::where('user_type','USER')->where('user_id',$receiver->id)->where('currency_id',$wallet->currency_id)->first();
        if(!$receiverWallet){
           $receiverWallet = new Wallet();
           $receiverWallet->user_id = $receiver->id;
           $receiverWallet->user_type = 'USER';
           $receiverWallet->currency_id = $wallet->currency_id;
           $receiverWallet->currency_code = $wallet->currency->currency_code;
           $receiverWallet->save(); 
        }

       
       $fixedCharge = $transferCharge->fixed_charge / $rate;
       $percentCharge = $request->amount * $transferCharge->percent_charge/100;
       $totalCharge = $fixedCharge + $percentCharge;
      
        $cap = $transferCharge->cap/$rate;
        if($transferCharge->cap != -1 && $totalCharge > $cap){
            $totalCharge = $cap;
        }
        

       if($wallet->currency->currency_type == 1){
            $totalAmount = getAmount($request->amount + $totalCharge,2);
        } else {
            $totalAmount = getAmount( $request->amount + $totalCharge,8);
        }
     
        if($totalAmount > $wallet->balance){
                $notify[]=['error','Sorry! insufficient balance in this wallet'];
                return back()->withNotify($notify)->withInput(); 
        }
     
      
    
        $wallet->balance -= $totalAmount;
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
        $senderTrx->operation_type = 'transfer_money';
        $senderTrx->details = 'Transfer Money to';
        $senderTrx->receiver_id = $receiver->id;
        $senderTrx->receiver_type = "USER";
        $senderTrx->trx = getTrx();
        $senderTrx->save();

        $receiverWallet->balance += $request->amount;
        $receiverWallet->save();

        $receiverTrx = new Transaction();
        $receiverTrx->user_id = $receiver->id;
        $receiverTrx->user_type = 'USER';
        $receiverTrx->wallet_id = $receiverWallet->id;
        $receiverTrx->currency_id = $receiverWallet->currency_id;
        $receiverTrx->amount = $request->amount;
        $receiverTrx->post_balance =  $receiverWallet->balance;
        $receiverTrx->charge =  0;
        $receiverTrx->trx_type = '+';
        $receiverTrx->operation_type = 'transfer_money';
        $receiverTrx->details = 'Received Money From';
        $receiverTrx->receiver_id = auth()->id();
        $receiverTrx->receiver_type = "USER";
        $receiverTrx->trx = $senderTrx->trx;
        $receiverTrx->save();

        notify(auth()->user(),'TRANSFER_MONEY',[
            'amount'=> showAmount($totalAmount),
            'charge' => showAmount($totalCharge),
            'curr_code' => $wallet->currency->currency_code,
            'to_user' => $receiver->fullname.' ( '.$receiver->username.' )',
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($wallet->balance),
        ]);

        notify($receiver,'RECEIVED_MONEY',[
            'amount'=> showAmount($request->amount),
            'curr_code' => $receiverWallet->currency->currency_code,
            'from_user' => auth()->user()->fullname.' ( '.auth()->user()->username.' )',
            'trx' => $senderTrx->trx,
            'time' => showDateTime($senderTrx->created_at,'d/M/Y @h:i a'),
            'balance' => showAmount($receiverWallet->balance),
        ]);

        $notify[]=['success','Money Transferred successfully'];
        return redirect(route('user.home'))->withNotify($notify);
    
    }


    //request money starts here
    public function requests()
    {
        $pageTitle = "Money Requests To You";
        $requests = RequestMoney::where('receiver_id',auth()->id())
                    ->where('status',0)
                    ->with(['currency','sender'])->whereHas('currency')
                    ->whereHas('sender')->latest()
                    ->paginate(getPaginate());
        return view($this->activeTemplate.'user.operations.money_requests',compact('pageTitle','requests'));
    }

    public function requestMoney()
    {
        $permission = module('request_money');
        if($permission->status == 0){
            $notify[]=['error','Request money currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = "Request Money";
        $transferCharge = TransactionCharge::where('slug','money_transfer')->first();
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type','USER')->get();
        return view($this->activeTemplate.'user.operations.request_money',compact('pageTitle','transferCharge','wallets'));
    }

    public function confirmRequest(Request $request)
    {
        $permission = module('request_money');
        if($permission->status == 0){
            $notify[]=['error','Request money currently not available'];
            return back()->withNotify($notify);
        }

       $request->validate([
           'wallet_id' => 'required|integer',
           'amount' => 'required|gt:0',
           'user' => 'required',
       ]);

       if(auth()->user()->username == $request->user || auth()->user()->email == $request->user){
            $notify[] = ['error', 'Can\'t make request to your own'];
            return back()->withNotify($notify)->withInput();
       }

       $wallet = Wallet::find($request->wallet_id);
       if(!$wallet){
           $notify[]=['error','Your Wallet Not found'];
           return back()->withNotify($notify)->withInput();
       }

       $rate = $wallet->currency->rate;

       $transferCharge = TransactionCharge::findOrFail($request->charge_id);
       if($request->amount < $transferCharge->min_limit/$rate || $request->amount > $transferCharge->max_limit/$rate){
           $notify[]=['error','Please follow the request amount limit'];
           return back()->withNotify($notify)->withInput();
       }


       $receiver = User::where('username',$request->user)->orWhere('email',$request->user)->first();
       if(!$receiver){
           $notify[]=['error','Sorry! Receiver Not Found'];
           return back()->withNotify($notify)->withInput();
       }

       $rate = $wallet->currency->rate;
       $fixedCharge = $transferCharge->fixed_charge / $rate;
       $percentCharge = $request->amount * $transferCharge->percent_charge/100;
       $totalCharge = $fixedCharge + $percentCharge;
     
      
        $cap = $transferCharge->cap/$rate;
        if($transferCharge->cap != -1 && $totalCharge > $cap){
            $totalCharge = $cap;
        }

       $requestDetail = new RequestMoney();
       $requestDetail->wallet_id = $wallet->id;
       $requestDetail->currency_id = $wallet->currency->id;
       $requestDetail->charge = $totalCharge;
       $requestDetail->request_amount = $request->amount;
       $requestDetail->sender_id = auth()->id();
       $requestDetail->receiver_id = $receiver->id;
       $requestDetail->note = $request->note;
       $requestDetail->save();
      
       notify($receiver,'REQUEST_MONEY',[
           'amount' => $request->amount,
           'curr_code' => $wallet->currency->currency_code,
           'requestor' => auth()->user()->username,
           'time' => showDateTime($requestDetail->created_at,'d/M/Y @h:i a'),
           'note'=> $request->note,
       ]);
      
       $notify[]=['success','Request money successful'];
       return back()->withNotify($notify);
    }


    public function requestAccept(Request $request)
    {
          $request->validate([
              'request_id' => 'required|integer'
          ],
            
          [
            'request_id.required' => 'Transfer details is required'
          ]
        );

        if (auth()->user()->ts) {
            $response = verifyG2fa(auth()->user(),$request->ts);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify);
            }   
        }

        $requestDetail = RequestMoney::findOrFail($request->request_id);
        $requestor = User::find($requestDetail->sender_id);
        if(!$requestor){
            $notify[]=['error','Requestor user not found'];
            return back()->withNotify($notify);
        }
       
        $userWallet = Wallet::where('user_type','USER')->where('user_id',auth()->id())->where('currency_id',$requestDetail->currency_id)->first();
        if(!$userWallet){
            $notify[]=['error','Your wallet not found'];
            return back()->withNotify($notify);
        }
      
        $requestorWallet = Wallet::where('user_type','USER')->where('user_id',$requestDetail->sender_id)->where('currency_id',$requestDetail->currency_id)->first();
       
        if(!$requestorWallet){
            $requestorWallet = new Wallet();
            $requestorWallet->user_id = $requestor->id;
            $requestorWallet->user_type = 'USER';
            $requestorWallet->currency_id = $requestDetail->currency_id;
            $requestorWallet->currency_code = $requestDetail->currency->currency_code;
            $requestorWallet->save(); 
      
        }

        if( $requestDetail->request_amount > $userWallet->balance) {
            $notify[]=['error','Sorry! insufficient balance to your wallet'];
            return back()->withNotify($notify);
        }
    
        $userWallet->balance -= $requestDetail->request_amount;
        $userWallet->save();

        $userTrx = new Transaction();
        $userTrx->user_id = auth()->id();
        $userTrx->user_type = 'USER';
        $userTrx->wallet_id = $userWallet->id;
        $userTrx->currency_id = $userWallet->currency_id;
        $userTrx->amount = $requestDetail->request_amount;
        $userTrx->post_balance =  $userWallet->balance;
        $userTrx->charge =  0;
        $userTrx->trx_type = '-';
        $userTrx->operation_type = 'request_money';
        $userTrx->details = 'Accept money request from';
        $userTrx->receiver_id = $requestor->id;
        $userTrx->receiver_type = "USER";
        $userTrx->trx =  getTrx();
        $userTrx->save();
       

        $requestorWallet->balance += ($requestDetail->request_amount - $requestDetail->charge);
        $requestorWallet->save();

        $requestorTrx = new Transaction();
        $requestorTrx->user_id = $requestor->id;
        $requestorTrx->user_type = 'USER';
        $requestorTrx->wallet_id = $requestorWallet->id;
        $requestorTrx->currency_id = $requestorWallet->currency_id;
        $requestorTrx->amount = $requestDetail->request_amount;
        $requestorTrx->post_balance =  $requestorWallet->balance;
        $requestorTrx->charge =  $requestDetail->charge;
        $requestorTrx->trx_type = '+';
        $requestorTrx->operation_type = 'request_money';
        $requestorTrx->details = 'Money request has been accepted from';
        $requestorTrx->receiver_id = auth()->id();
        $requestorTrx->receiver_type = "USER";
        $requestorTrx->trx = $userTrx->trx;
        $requestorTrx->save();

        notify($requestor,'ACCEPT_REQUEST_MONEY_REQUESTOR',[
            'amount' => showAmount($requestDetail->request_amount),
            'curr_code' => $userWallet->currency->currency_code,
            'to_requested' => auth()->user()->username,
            'charge' => showAmount($requestDetail->charge),
            'balance' => showAmount($requestorWallet->balance),
            'trx' => $userTrx->trx,
            'time' => showDateTime($userTrx->created_at,'d/M/Y @h:i a')
        ]);
      
        notify(auth()->user(),'ACCEPT_REQUEST_MONEY',[
            'amount' => showAmount($requestDetail->request_amount),
            'curr_code' => $userWallet->currency->currency_code,
            'requestor' => $requestor->username,
            'balance' => showAmount($userWallet->balance),
            'trx' => $userTrx->trx,
            'time' => showDateTime($userTrx->created_at,'d/M/Y @h:i a')
        ]);

        $requestDetail->status = 1;
        $requestDetail->save();
        $notify[]=['success','Money request has been accepted'];
        return back()->withNotify($notify);

    }

    public function requestReject(Request $request)
    {
        $request->validate(
            [
                'request_id' => 'required|integer'
            ],
            
            [
                'request_id.required' => 'Transfer details is required'
            ]
        );
     
      $transfer = RequestMoney::findOrFail($request->request_id);
      $transfer->status = 1;
      $transfer->save();
      $notify[]=['success','Request has been rejected'];
      return back()->withNotify($notify);

    }
    
}


