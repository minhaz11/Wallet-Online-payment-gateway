<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Currency;
use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;

class TestPaymentController extends Controller
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
                    'error'=> 'yes',
                    'errors' => $validator->errors()->all()
                ] ;
            }

            $currency = Currency::where('currency_code',strtoupper($request->currency))->first();
            if(!$currency){
                    return [
                        'error'=> 'true',
                        'message' => 'Currency not supported.'
                    ] ;
            }

            $merchant = Merchant::where('public_api_key',$request->public_key)->first();
            if(!$merchant){
                return [
                    'error'=> 'true',
                    'message' => 'Invalid api key.'
                ] ;
            }
      
            $data['identifier'] = $request->identifier;
            $data['amount'] = $request->amount;
            $data['details'] = $request->details;
            $data['public_key'] = @$merchant->public_api_key;
            $data['curr'] = [
                'currency_symbol'=> @$currency->currency_symbol,
                'currency_code'  => @$currency->currency_code,
            ];
            $data['payer_name'] = @$request->customer_name;
            $data['ip'] = request()->ip();
            $data['trx'] = getTrx();
            $data['ipn_url'] = $request->ipn_url;
            $data['cancel_url'] = $request->cancel_url;
            $data['success_url'] = $request->success_url;
            $data['site_logo'] = $request->site_logo;
            $data['checkout_theme'] = $request->checkout_theme;
            $data['created_at'] = now();
            
            return [
                "success" => "ok",
                "message"=> "Payment Initiated. Redirect to url",
                "url" => route('test.initiate.payment.auth.view',['payment_id'=> encrypt(json_encode($data))])
            ] ;
       

    }

    public function getPaymentInfo()
    {
        try{
            $apiPayment = decrypt(session('data'));
        } catch(Exception $e){
            return [
                'error'=> 'true',
                'message' => 'Invalid transaction request'
            ] ;
        }
        return json_decode($apiPayment);
    }


    public function initiatePaymentAuthView()
    {
        $pageTitle = "Payment Checkout";
        session()->put('data',request('payment_id'));
        $apiPayment = $this->getPaymentInfo();
        return view($this->activeTemplate.'api_payment.checkout',compact('pageTitle','apiPayment'));
    }


    public function checkEmail(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email'
            ]
        );
      
        if($request->email != 'test_mode@mail.com'){
            $notify[]=['error','Invalid Email'];
            return back()->withNotify($notify);
        }

        return redirect(route('test.payment.verify'));
        
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
        if($request->code != '222666'){
            $notify[]=['error','Sorry! verification code mismatch'];
            return back()->withNotify($notify);
        }
     
        $paymentCharge = TransactionCharge::where('slug','api_charge')->first();
        if(!$paymentCharge){
            $notify[]=['error','Sorry! something went wrong'];
            return back()->withNotify($notify);
        }
        $rate  = @$apiPayment->curr->rate;
        $fixedCharge    = $paymentCharge->fixed_charge/$rate;
        $percentCharge  = ($apiPayment->amount*$paymentCharge->percent_charge)/100;
        $totalCharge    = $fixedCharge + $percentCharge;

        $customKey = $apiPayment->amount.$apiPayment->identifier;
    
        $merchant = Merchant::where('public_api_key',$apiPayment->public_key)->first();
        if(!$merchant){
            $notify[]=['error','Sorry! something went wrong'];
            return back()->withNotify($notify);
        }
         curlPostContent($apiPayment->ipn_url,[
            'status'     => 'success',
            'signature' => strtoupper(hash_hmac('sha256', $customKey , $merchant->secret_api_key)),
            'identifier' => $apiPayment->identifier,
            'data' => [
                'payment_trx' =>  $apiPayment->trx,
                'amount'      => $apiPayment->amount,
                'account_holder'   => @$apiPayment->payer_name,
                'payment_type'   => 'hosted', 
                'payment_timestamp' => $apiPayment->created_at,
                'charge' => $totalCharge,
                'currency' => [
                    'code'   => @$apiPayment->curr->currency_code,
                    'symbol' => @$apiPayment->curr->currency_symbol,
                ]
            ],
           
            
         ]);

        return redirect($apiPayment->success_url);

   }

   public function cancelPayment()
   {
       $apiPayment = $this->getPaymentInfo();
       if($apiPayment->cancel_url) {
           return redirect($apiPayment->cancel_url);
       }
       
       
   }
}
