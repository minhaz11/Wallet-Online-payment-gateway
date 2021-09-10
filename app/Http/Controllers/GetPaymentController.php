<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\ApiPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;



class GetPaymentController extends Controller
{
    public function __construct(){
        $this->activeTemplate = activeTemplate();
    }
    public function initiatePayment(Request $request)
    {  
        $validator = Validator::make($request->all(),[
            'identifier'      => 'required|string|max:20',
            'currency'        => 'required|string|max:4',
            'amount'          => 'required|gt:0',
            'details'         => 'required|string|max:100',
            'ipn_url'         => 'required|url',
            'cancel_url'      => 'required|url',  
            'success_url'     => 'required|url',  
            'public_key'      => 'required|string|max:50',  
            'site_logo'       => 'required|url',
            'checkout_theme'  => 'in:dark,light|string|max:5',
            'customer_name'   => 'required|string|max:30',
            'customer_email'  => 'required|email|max:30'
        ]);
    

        if($validator->fails()) {
        	return [
                'error'=> 'true',
                'message' => $validator->errors()->all()
            ] ;
        }

        $currency = Currency::where('currency_code',strtoupper($request->currency))->first();
        if(!$currency){
                return [
                    'error'=> 'true',
                    'message' => 'Currency not supported'
                ] ;
        }

        $merchant = Merchant::where('status',1)->where('public_api_key',$request->public_key)->first();
        if(!$merchant){
            return [
                'error'=> 'true',
                'message' => 'Invalid api key'
            ] ;
        }
        
        $apiPayment                 = new ApiPayment();
        $apiPayment->ip             = request()->ip();
        $apiPayment->trx            = getTrx();
        $apiPayment->merchant_id    = $merchant->id;
        $apiPayment->identifier     = $request->identifier;
        $apiPayment->currency_id    = $currency->id;
        $apiPayment->amount         = getAmount($request->amount,2);
        $apiPayment->details        = $request->details;
        $apiPayment->ipn_url        = $request->ipn_url;
        $apiPayment->cancel_url     = $request->cancel_url;
        $apiPayment->success_url    = $request->success_url;
        $apiPayment->site_logo      = $request->site_logo;
        $apiPayment->checkout_theme = $request->checkout_theme;
        $apiPayment->customer_name  = $request->customer_name;
        $apiPayment->customer_email = $request->customer_email;
        $apiPayment->save();
            
        
        return [
            "success"=>'ok',
            "message"=> "Payment Initiated. Redirect to url.",
            "url" => route('initiate.payment.auth.view',['payment_id'=> encrypt($apiPayment->trx)])
        ] ;

    }

    public function getPaymentInfo()
    {
        
        try{
            $trx = decrypt(session('trx'));
        } catch(Exception $e){
            return [
                'error'=> 'true',
                'message' => 'Invalid transaction request'
            ] ;
        }
      
        $apiPayment = ApiPayment::where('trx',$trx)->first();
        if(!$apiPayment || $apiPayment->status == 1 ||  $apiPayment->status == 2){
            return [
                'error'=> 'true',
                'message' => 'Invalid transaction request'
            ] ;
        }

        return $apiPayment;
    }


    public function initiatePaymentAuthView()
    {
        $pageTitle = "Payment Checkout";
        session()->put('trx',request('payment_id'));
        $apiPayment = $this->getPaymentInfo();
        return view($this->activeTemplate.'api_payment.checkout',compact('pageTitle','apiPayment'));
    }


    public function checkValidCode($apiPayment, $code, $add_min = 10000)
    {
        if (!$code) return false;
        if (!$apiPayment->ver_code_at) return false;
        if (Carbon::parse($apiPayment->ver_code_at)->addMinutes($add_min) < Carbon::now()) return false;
        if ($apiPayment->ver_code !== $code) return false;
        return true;
    }


    public function checkEmail(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email'
            ]
        );

        $user = User::where('email',$request->email)->first();
        if(!$user){
            $notify[]=['error','User not found associated with this email'];
            return back()->withNotify($notify);
        }

        $apiPayment              = $this->getPaymentInfo();
        $apiPayment->ver_code    = verificationCode(6);
        $apiPayment->ver_code_at = Carbon::now();
        $apiPayment->payer_id    = $user->id;
        $apiPayment->save();

        sendEmail($user, 'PAYMENT_VER_CODE', [
            'code' => $apiPayment->ver_code
        ]);

        return redirect(route('payment.verify'));
        
    }

    public function sendVerifyCode()
    {
        $apiPayment = $this->getPaymentInfo();
        if ($this->checkValidCode($apiPayment, $apiPayment->ver_code, 2)) {
            $target_time = Carbon::parse($apiPayment->ver_code_at)->addMinutes(2)->timestamp;
            $delay = $target_time - time();
            throw ValidationException::withMessages(['resend' => 'Please Try after ' . $delay . ' Seconds']);
        }
        if (!$this->checkValidCode($apiPayment, $apiPayment->ver_code)) {
            $apiPayment->ver_code    = verificationCode(6);
            $apiPayment->ver_code_at = Carbon::now();
            $apiPayment->save();
        } else {
            $apiPayment->ver_code    = $apiPayment->ver_code;
            $apiPayment->ver_code_at = Carbon::now();
            $apiPayment->save();
        }
        sendEmail($apiPayment->payer, 'PAYMENT_VER_CODE', [
            'code' => $apiPayment->ver_code
        ]);

        return redirect(route('payment.verify'));

    }

    public function verifyPayment()
    {
        $pageTitle ="Verify Payment";
        $apiPayment = $this->getPaymentInfo();
        return view($this->activeTemplate.'api_payment.verify_payment',compact('pageTitle','apiPayment'));
    }

    public function verifyPaymentConfirm(Request $request)
    {
        $request->validate([
            'code'      => 'required|integer',
        ]);
        $apiPayment = $this->getPaymentInfo();

        if($request->code != $apiPayment->ver_code){
            $notify[]=['error','Sorry! verification code mismatch'];
            return back()->withNotify($notify);
        }

        $payer = User::find($apiPayment->payer_id);
        if(!$payer){
            $notify[]=['error','User account not found'];
            return back()->withNotify($notify);
        }

        $payerWallet = Wallet::where('user_type','USER')->where('user_id',$payer->id)->where('currency_id',$apiPayment->currency_id)->first();
        if(!$payerWallet){
            $notify[]=['error','User wallet not found'];
            return back()->withNotify($notify);
        }

        $merchant = Merchant::find($apiPayment->merchant_id);
        if(!$merchant){
            $notify[]=['error','Merchant  not found'];
            return back()->withNotify($notify);
        }

        $merchantWallet = Wallet::where('user_type','MERCHANT')->where('user_id',$merchant->id)->where('currency_id',$apiPayment->currency_id)->first();
        if(!$merchantWallet){
            $merchantWallet = new Wallet();
            $merchantWallet->user_id = $merchant->id;
            $merchantWallet->user_type = 'MERCHANT';
            $merchantWallet->currency_id = $payerWallet->currency_id;
            $merchantWallet->currency_code = $payerWallet->currency->currency_code;
            $merchantWallet->save(); 
        }
        
        if($apiPayment->amount  > $payerWallet->balance){
            $notify[]=['error','Sorry! insufficient balance'];
            return back()->withNotify($notify);
        }

        $paymentCharge = TransactionCharge::where('slug','api_charge')->first();
        if(!$paymentCharge){
            $notify[]=['error','Sorry! something went wrong'];
            return back()->withNotify($notify);
        }

        $rate           = @$apiPayment->curr->rate;
        $fixedCharge    = $paymentCharge->fixed_charge/$rate;
        $percentCharge  = ($apiPayment->amount*$paymentCharge->percent_charge)/100;
        $totalCharge    = $fixedCharge + $percentCharge;

        $cap = $paymentCharge->cap/$rate;
        if($paymentCharge->cap != -1 && $totalCharge > $cap){
            $totalCharge = $cap;
        }

        $payerWallet->balance -= $apiPayment->amount;
        $payerWallet->save();

        $payerTrx                    = new Transaction();
        $payerTrx->user_id           = $payer->id;
        $payerTrx->user_type         = 'USER';
        $payerTrx->wallet_id         = $payerWallet->id;
        $payerTrx->currency_id       = $payerWallet->currency_id;
        $payerTrx->amount            = $apiPayment->amount;
        $payerTrx->post_balance      = $payerWallet->balance;
        $payerTrx->charge            =  0;
        $payerTrx->trx_type          = '-';
        $payerTrx->operation_type    = 'make_payment';
        $payerTrx->details           = 'Payment successful to';
        $payerTrx->receiver_id       = $merchant->id;
        $payerTrx->receiver_type     = 'MERCHANT';
        $payerTrx->trx               =  $apiPayment->trx;
        $payerTrx->save();

        $merchantWallet->balance += ($apiPayment->amount - $totalCharge);
        $merchantWallet->save();

        $merchantTrx                  = new Transaction();
        $merchantTrx->user_id         = $merchant->id;
        $merchantTrx->user_type       = 'MERCHANT';
        $merchantTrx->wallet_id       = $merchantWallet->id;
        $merchantTrx->currency_id     = $merchantWallet->currency_id;
        $merchantTrx->amount          = $apiPayment->amount;
        $merchantTrx->post_balance    = $merchantWallet->balance;
        $merchantTrx->charge          = $totalCharge;
        $merchantTrx->trx_type        = '+';
        $merchantTrx->operation_type  = 'make_payment';
        $merchantTrx->details         = 'Payment successful from';
        $merchantTrx->receiver_id     =  $payer->id;
        $merchantTrx->receiver_type   = 'USER';
        $merchantTrx->trx             =  $apiPayment->trx;
        $merchantTrx->save();

        $apiPayment->status = 1;
        $apiPayment->save();
        
        $customKey = $apiPayment->amount.$apiPayment->identifier;
        curlPostContent($apiPayment->ipn_url,[
            'status'     => 'success',
            'signature' => strtoupper(hash_hmac('sha256', $customKey , $merchant->secret_api_key)),
            'identifier' => $apiPayment->identifier,
            'data' => [
                'payment_trx' =>  $apiPayment->trx,
                'amount'      => $apiPayment->amount,
                'account_holder'   => @$apiPayment->payer->fullname,
                'payment_type'   => 'hosted', 
                'payment_timestamp' => $apiPayment->created_at,
                'charge' => $totalCharge,
                'currency' => [
                    'code'   => @$apiPayment->curr->currency_code,
                    'symbol' => @$apiPayment->curr->currency_symbol,
                   
                ]
            ],
            
         ]);

         notify($merchant,'MERCHANT_PAYMENT',[
             'amount' => showAmount($apiPayment->amount),
             'curr_code' => @$apiPayment->curr->currency_code,
             'customer_name' => @$apiPayment->payer->fullname,
             'charge' =>  $totalCharge,
             'trx' => $apiPayment->trx,
             'time' => showDateTime($apiPayment->created_at,'d M Y @ g:i a'),
             'balance' => showAmount($merchantWallet->balance)
         ]);
        
        return redirect($apiPayment->success_url);

   }

   public function cancelPayment()
   {
       $apiPayment = $this->getPaymentInfo();
       if($apiPayment->cancel_url) {
           $apiPayment->status = 2;
           $apiPayment->save();
           return redirect($apiPayment->cancel_url);
       }
   }
   
}

