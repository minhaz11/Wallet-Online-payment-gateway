<?php

namespace App\Http\Controllers\Gateway;

use App\Models\User;
use App\Models\Agent;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Rules\FileTypeValidate;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function __construct()
    {
        return $this->activeTemplate = activeTemplate();
    }


    public function invoicePayment()
    {
        $pageTitle ="Invoice Payment";
        $invoice = session('invoice') ? decrypt(session('invoice')) : null;
        if(!$invoice){
            $notify[]=['error','Something went wrong. Please try again.'];
            return back()->withNotify($notify);
        }
        $code = $invoice->currency->currency_code;
        $wallet = Wallet::where('user_type','USER')->where('user_id',auth()->id())->where('currency_id',$invoice->currency->id)->first();
        if(!$wallet){
            $wallet = new Wallet();
            $wallet->user_id = auth()->id();
            $wallet->user_type = 'USER';
            $wallet->currency_id = $invoice->currency->id;
            $wallet->currency_code = $code;
            $wallet->save(); 
        }
        $gateways = GatewayCurrency::whereHas('method', function ($gate) use($code){
            $gate->where('status', 1)->whereJsonContains("supported_currencies->$code",$code);
        })->with('method')->where('currency',$code)->orderby('method_code')->get();
        return view($this->activeTemplate . gatewayView('invoice_payment'), compact('gateways', 'pageTitle','invoice','wallet','code'));

    }

    public function deposit()
    {
        $permission = module('add_money');
        if($permission->status == 0){
            $notify[]=['error','Add money is currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = 'Payment Methods';
        return view($this->activeTemplate . gatewayView('deposit'), compact('pageTitle'));
    }

    public function depositInsert(Request $request)
    {
       
        $permission = module('add_money');
        if($permission->status == 0){
            $notify[]=['error','Add money is currently not available'];
            return back()->withNotify($notify);
        }
        $invoice = session('invoice') ? decrypt(session('invoice')) : null;
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method_code' => 'required',
            'currency' => 'required',
        ]);

        $user = userGuard()['user'];

        if(isset($invoice) && $invoice->user_id == $user->id){
            $notify[]=['error','Sorry, you can\'t pay your own invoice.'];
            return back()->withNotify($notify);
        }
        if(isset($invoice) && getAmount($invoice->total_amount,2) != $request->amount){
            $notify[]=['error','Sorry Amount mismatch'];
            return back()->withNotify($notify);
        }
        if(isset($invoice) && $invoice->currency_id != $request->currency_id){
            $notify[]=['error','Sorry currency mismatch'];
            return back()->withNotify($notify);
        }


    
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();
       
        if(!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount >  $request->amount || $gate->max_amount <  $request->amount) {
            $notify[] = ['error', 'Please follow the limits'];
            return back()->withNotify($notify);
        }

        $charge = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $final_amo = $request->amount + $charge;

        $data = new Deposit();
        $data->user_id = $user->id;
        $data->user_type = userGuard()['type'];
        $data->wallet_id = $request->wallet_id;
        $data->currency_id = $request->currency_id;
        $data->method_code = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount = $request->amount;
        $data->charge = $charge;
        $data->rate = $gate->rate;
        $data->final_amo = $final_amo;
        $data->btc_amo = 0;
        $data->btc_wallet = "";
        $data->trx = getTrx();
        $data->try = 0;
        $data->status = 0;
        $data->save();
        session()->put('Track', $data->trx);
        return redirect()->route(strtolower(userGuard()['type']).'.deposit.confirm');
    }


    public function depositPreview()
    {
        $track = session()->get('Track');
        $data = Deposit::where('trx', $track)->where('status',0)->orderBy('id', 'DESC')->firstOrFail();
        $pageTitle = 'Payment Preview';
        return view($this->activeTemplate .gatewayView('preview'), compact('data', 'pageTitle'));
    }


    public function depositConfirm()
    {
        $track = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status',0)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();
        
        if ($deposit->method_code >= 1000) {
            $this->userDataUpdate($deposit);
            $notify[] = ['success', 'Your payment request is queued for approval.'];
            return back()->withNotify($notify);
        }


        $dirName = $deposit->gateway->alias;
        $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return redirect()->route(gatewayRedirectUrl())->withNotify($notify);
        }
        if(isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if(@$data->session){
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view($this->activeTemplate . $data->view, compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($trx)
    {
        $data = Deposit::where('trx', $trx)->first();
        if ($data->status == 0) {
            $data->status = 1;
            $data->save();

            if($data->user_type == 'USER'){
                $user = User::find($data->user_id);
            } else if($data->user_type == 'AGENT'){
                $user = Agent::find($data->user_id);
            }
            $userWallet = Wallet::find($data->wallet_id);
            $userWallet->balance += $data->amount;
            $userWallet->save();

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->user_type = $data->user_type;
            $transaction->wallet_id = $userWallet->id;
            $transaction->currency_id = $data->currency_id;
            $transaction->amount = $data->amount;
            $transaction->post_balance = $userWallet->balance;
            $transaction->charge =  $data->charge;
            $transaction->operation_type =  'add_money';
            $transaction->trx_type = '+';
            $transaction->details = 'Add Money successful';
            $transaction->trx = $data->trx;
            $transaction->save();

            if(session('invoice')){
                $invoice = decrypt(session('invoice'));
                $userWallet = Wallet::find($data->wallet_id);
                $userWallet->balance -= $invoice->total_amount;
                $userWallet->save();

                $userTrx = new Transaction();
                $userTrx->user_id = $user->id;
                $userTrx->user_type = $data->user_type;
                $userTrx->wallet_id = $userWallet->id;
                $userTrx->currency_id = $data->currency_id;
                $userTrx->amount = $data->amount;
                $userTrx->post_balance = $userWallet->balance;
                $userTrx->charge =  $data->charge;
                $userTrx->trx_type = '-';
                $userTrx->operation_type =  'invoice_payment';
                $userTrx->details = "Invoice payment successful to";
                $userTrx->receiver_id = $invoice->user_id;
                $userTrx->receiver_type = "USER";
                $userTrx->trx = getTrx();
                $userTrx->save();


                $rcvWallet = Wallet::where([['user_type',$data->user_type],['currency_id',$invoice->currency_id],['user_id',$invoice->user_id]])->first();
                $rcvWallet->balance += $invoice->get_amount;
                $rcvWallet->save();

                $rcvTrx = new Transaction();
                $rcvTrx->user_id = $invoice->user_id;
                $rcvTrx->user_type = $invoice->user_type;
                $rcvTrx->wallet_id = $rcvWallet->id;
                $rcvTrx->currency_id = $invoice->currency_id;
                $rcvTrx->amount = $invoice->get_amount;
                $rcvTrx->post_balance = $rcvWallet->balance;
                $rcvTrx->charge =  $invoice->charge;
                $rcvTrx->operation_type =  'invoice_payment';
                $rcvTrx->trx_type = '+';
                $rcvTrx->details = 'Got payment of invoice from';
                $rcvTrx->receiver_id = $user->id;
                $rcvTrx->receiver_type = "USER";
                $rcvTrx->trx = $userTrx->trx;
                $rcvTrx->save();

                $invoice->pay_status = 1;
                $invoice->save();

                notify($rcvWallet->user, 'GET_INVOICE_PAYMENT', [
                    'total_amount' => showAmount($invoice->total_amount),
                    'get_amount' => showAmount($invoice->get_amount),
                    'charge' => showAmount($invoice->charge),
                    'curr_code' => $invoice->currency->currency_code,
                    'invoice_id' => $invoice->invoice_num,
                    'from_user' => $user->username,
                    'trx' => $userTrx->trx,
                    'post_balance' => showAmount($rcvWallet->balance),
                    'time' => showDateTime($data->created_at,'d/M/Y @h:i a')
                ]);

                notify($user, 'PAY_INVOICE_PAYMENT', [
                    'total_amount' =>showAmount($invoice->total_amount),
                    'curr_code' =>  $invoice->currency->currency_code,
                    'invoice_id' => $invoice->invoice_num,
                    'time' => showDateTime($data->created_at,'d/M/Y @h:i a'),
                    'to_user' => $rcvWallet->user->username,
                    'trx' => $userTrx->trx,
                    'post_balance' => showAmount($userWallet->balance)
                ]);
                
            }

          
            $adminNotification = new AdminNotification();
            $adminNotification->user_type = $data->user_type;
            $adminNotification->user_id = $user->id;
            $adminNotification->title = 'Add money successful via '.$data->gatewayCurrency()->name;
            $adminNotification->click_url = urlPath('admin.deposit.successful');
            $adminNotification->save();

            notify($user, 'DEPOSIT_COMPLETE', [
                'method_name' => $data->gatewayCurrency()->name,
                'method_currency' => $data->method_currency,
                'method_amount' => showAmount($data->final_amo),
                'amount' => showAmount($data->amount),
                'charge' => showAmount($data->charge),
                'currency' => $data->curr->currency_code,
                'rate' => showAmount($data->curr->rate),
                'trx' => $data->trx,
                'post_balance' => showAmount($userWallet->balance)
            ]);


        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', 0)->where('trx', $track)->first();
        if (!$data) {
            return redirect()->route(gatewayRedirectUrl());
        }
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Payment';
            $method = $data->gatewayCurrency();
            return view($this->activeTemplate . gatewayView('manual_confirm',true), compact('data', 'pageTitle', 'method'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', 0)->where('trx', $track)->first();
        if($data->user_type == 'USER'){
            $user = User::find($data->user_id);
        } else if($data->user_type == 'AGENT'){
            $user = Agent::find($data->user_id);
        } 
        if (!$data) {
            return redirect()->route(gatewayRedirectUrl());
        }

        $params = json_decode($data->gatewayCurrency()->gateway_parameter);

        $rules = [];
        $inputField = [];
        $verifyImages = [];

        if ($params != null) {
            foreach ($params as $key => $custom) {
                $rules[$key] = [$custom->validation];
                if ($custom->type == 'file') {
                    array_push($rules[$key], 'image');
                    array_push($rules[$key], new FileTypeValidate(['jpg','jpeg','png']));
                    array_push($rules[$key], 'max:2048');

                    array_push($verifyImages, $key);
                }
                if ($custom->type == 'text') {
                    array_push($rules[$key], 'max:191');
                }
                if ($custom->type == 'textarea') {
                    array_push($rules[$key], 'max:300');
                }
                $inputField[] = $key;
            }
        }
        $this->validate($request, $rules);


        $directory = date("Y")."/".date("m")."/".date("d");
        $path = imagePath()['verify']['deposit']['path'].'/'.$directory;
        $collection = collect($request);
        $reqField = [];
        if ($params != null) {
            foreach ($collection as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {
                                try {
                                    $reqField[$inKey] = [
                                        'field_name' => $directory.'/'.uploadImage($request[$inKey], $path),
                                        'type' => $inVal->type,
                                    ];
                                } catch (\Exception $exp) {
                                    $notify[] = ['error', 'Could not upload your ' . $inKey];
                                    return back()->withNotify($notify)->withInput();
                                }
                            }
                        } else {
                            $reqField[$inKey] = $v;
                            $reqField[$inKey] = [
                                'field_name' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }
            $data->detail = $reqField;
        } else {
            $data->detail = null;
        }

        $data->status = 2; // pending
        $data->save();


        $adminNotification = new AdminNotification();
        $adminNotification->user_type = $data->user_type;
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'Add money request from '.$user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details',$data->id);
        $adminNotification->save();

        notify($user, 'DEPOSIT_REQUEST', [
            'method_name' => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount' => showAmount($data->final_amo),
            'amount' => showAmount($data->amount),
            'charge' => showAmount($data->charge),
            'currency' => $data->curr->currency_code,
            'rate' => showAmount($data->curr->rate),
            'trx' => $data->trx,          
        ]);

        $notify[] = ['success', 'You add money request has been taken.'];
        return redirect()->route(strtolower(userGuard()['type']).'.deposit.history')->withNotify($notify);
    }


}
