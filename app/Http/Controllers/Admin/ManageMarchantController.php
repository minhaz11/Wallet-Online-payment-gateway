<?php
namespace App\Http\Controllers\Admin;

use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\EmailLog;
use App\Models\Merchant;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\WithdrawMethod;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class ManageMerchantController extends Controller
{
    public function allMerchant()
    {
        $pageTitle = 'Manage Merchant';
        $emptyMessage = 'No merchant found';
        $users = Merchant::orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function activeMerchant()
    {
        $pageTitle = 'Manage Active Merchant';
        $emptyMessage = 'No active merchant found';
        $users = Merchant::active()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function bannedMerchant()
    {
        $pageTitle = 'Banned Merchant';
        $emptyMessage = 'No banned merchant found';
        $users = Merchant::banned()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function emailUnverifiedMerchant()
    {
        $pageTitle = 'Email Unverified Merchant';
        $emptyMessage = 'No email unverified merchant found';
        $users = Merchant::emailUnverified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }
    public function emailVerifiedMerchant()
    {
        $pageTitle = 'Email Verified Merchant';
        $emptyMessage = 'No email verified merchant found';
        $users = Merchant::emailVerified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }


    public function smsUnverifiedMerchant()
    {
        $pageTitle = 'SMS Unverified Merchant';
        $emptyMessage = 'No sms unverified merchant found';
        $users = Merchant::smsUnverified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }


    public function smsVerifiedMerchant()
    {
        $pageTitle = 'SMS Verified Merchant';
        $emptyMessage = 'No sms verified merchant found';
        $users = Merchant::smsVerified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    


    public function search(Request $request, $scope)
    {
        $search = $request->search;
        $merchant = Merchant::where(function ($user) use ($search) {
            $user->where('username', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        });
        $pageTitle = '';
        if ($scope == 'active') {
            $pageTitle = 'Active ';
            $merchant = $merchant->where('status', 1);
        }elseif($scope == 'banned'){
            $pageTitle = 'Banned';
            $merchant = $merchant->where('status', 0);
        }elseif($scope == 'emailUnverified'){
            $pageTitle = 'Email Unverified ';
            $merchant = $merchant->where('ev', 0);
        }elseif($scope == 'smsUnverified'){
            $pageTitle = 'SMS Unverified ';
            $merchant = $merchant->where('sv', 0);
        }elseif($scope == 'withBalance'){
            $pageTitle = 'With Balance ';
            $merchant = $merchant->where('balance','!=',0);
        }

        $users = $merchant->paginate(getPaginate());
        $pageTitle .= 'Merchant Search - ' . $search;
        $emptyMessage = 'No search result found';
        return view('admin.merchant.list', compact('pageTitle', 'search', 'scope', 'emptyMessage', 'users'));
    }


    public function detail($id)
    {
        $pageTitle = 'Merchant Detail';
        $user = Merchant::findOrFail($id);
        $totalWithdraw = Withdrawal::where('user_id',$user->id)->where('user_type','MERCHANT')->where('status',1)->sum('amount');
        $totalTransaction = Transaction::where('user_id',$user->id)->where('user_type','MERCHANT')->count();
        $getPaid = Transaction::where('user_id',$user->id)->where('user_type','MERCHANT')->where('operation_type','merchant_payment')->whereHas('wallet')->get();
        $totalGetPaid[] = 0;
        foreach($getPaid as $paid){
            $totalGetPaid[] = $paid->amount * $paid->wallet->currency->rate;
        }
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $wallets = Wallet::where('user_type','MERCHANT')->where('user_id',$user->id)->get();
        return view('admin.merchant.detail', compact('pageTitle', 'user','totalWithdraw','totalTransaction','countries','wallets','totalGetPaid','totalGetPaid'));
    }


    public function update(Request $request, $id)
    {
        $user = Merchant::findOrFail($id);

        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $request->validate([
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'email' => 'required|email|max:90|unique:merchants,email,' . $user->id,
            'mobile' => 'required|unique:merchants,mobile,' . $user->id,
            'country' => 'required',
        ]);
        $countryCode = $request->country;
        $user->mobile = $request->mobile;
        $user->country_code = $countryCode;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->address = [
                            'address' => $request->address,
                            'city' => $request->city,
                            'state' => $request->state,
                            'zip' => $request->zip,
                            'country' => @$countryData->$countryCode->country,
                        ];
        $user->status = $request->status ? 1 : 0;
        $user->ev = $request->ev ? 1 : 0;
        $user->sv = $request->sv ? 1 : 0;
        $user->ts = $request->ts ? 1 : 0;
        $user->tv = $request->tv ? 1 : 0;
        $user->save();

        $notify[] = ['success', 'Merchant detail has been updated'];
        return redirect()->back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|gt:0']);

        $user = Merchant::findOrFail($id);
        $wallet = Wallet::find($request->wallet_id);
        if(!$wallet){
            $notify[]=['error','Sorry wallet not found'];
            return back()->withNotify($notify);
        }
        $amount = $request->amount;
        $trx = getTrx();

        if ($request->act) {
            $wallet->balance += $amount;
            $wallet->save();
            $notify[] = ['success', $wallet->currency->currency_symbol . $amount . ' has been added to this wallet'];

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->user_type = 'MERCHANT';
            $transaction->wallet_id = $wallet->id;
            $transaction->currency_id = $wallet->currency_id;
            $transaction->amount = $amount;
            $transaction->post_balance =  $wallet->balance;
            $transaction->charge =  0;
            $transaction->trx_type = '+';
            $transaction->operation_type = 'add_balance';
            $transaction->details = 'Added Balance Via Admin';
            $transaction->trx = $trx;
            $transaction->save();

            notify($user, 'BAL_ADD', [
                'trx' => $trx,
                'amount' => showAmount($amount),
                'currency' => $wallet->currency->currency_code,
                'post_balance' => showAmount($wallet->balance),
            ]);

        } else {
            if ($amount > $wallet->balance) {
                $notify[] = ['error', 'This wallet has insufficient balance.'];
                return back()->withNotify($notify);
            }
            $wallet->balance -= $amount;
            $wallet->save();

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->user_type = 'MERCHANT';
            $transaction->wallet_id = $wallet->id;
            $transaction->currency_id = $wallet->currency_id;
            $transaction->amount = $amount;
            $transaction->post_balance =  $wallet->balance;
            $transaction->charge =  0;
            $transaction->trx_type = '-';
            $transaction->operation_type = 'sub_balance';
            $transaction->details = 'Subtract Balance Via Admin';
            $transaction->trx = $trx;
            $transaction->save();


            notify($user, 'BAL_SUB', [
                'trx' => $trx,
                'amount' => showAmount($amount),
                'currency' => $wallet->currency->currency_code,
                'post_balance' => showAmount($wallet->balance)
            ]);
            $notify[] = ['success', $wallet->currency->currency_symbol. $amount . ' has been subtracted from this wallet'];
        }
        return back()->withNotify($notify);
    }


    public function merchantLoginHistory($id)
    {
        $user = Merchant::findOrFail($id);
        $pageTitle = 'Merchant Login History - ' . $user->username;
        $emptyMessage = 'No Merchant login found.';
        $login_logs = UserLogin::where('merchant_id',$user->id)->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.merchant.logins', compact('pageTitle', 'emptyMessage', 'login_logs'));
    }



    public function showEmailSingleForm($id)
    {
        $user = Merchant::findOrFail($id);
        $pageTitle = 'Send Email To: ' . $user->username;
        return view('admin.merchant.email_single', compact('pageTitle', 'user'));
    }

    public function sendEmailSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        $user = Merchant::findOrFail($id);
        sendGeneralEmail($user->email, $request->subject, $request->message, $user->username);
        $notify[] = ['success', $user->username . ' will receive an email shortly.'];
        return back()->withNotify($notify);
    }

    public function transactions(Request $request, $id)
    {
        $user = Merchant::findOrFail($id);
        if ($request->search) {
            $search = $request->search;
            $pageTitle = 'Search Merchant Transactions : ' . $user->username;
            $transactions = $user->transactions()->where('trx', $search)->with('merchant')->orderBy('id','desc')->paginate(getPaginate());
            $emptyMessage = 'No transactions';
            return view('admin.reports.merchant_transactions', compact('pageTitle', 'search', 'user', 'transactions', 'emptyMessage'));
        }
        $pageTitle = 'Merchant Transactions : ' . $user->username;
        $transactions = $user->transactions()->with('merchant')->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No transactions';
        return view('admin.reports.merchant_transactions', compact('pageTitle', 'user', 'transactions', 'emptyMessage'));
    }

    public function deposits(Request $request, $id)
    {
        $user = Merchant::findOrFail($id);
        $userId = $user->id;
        if ($request->search) {
            $search = $request->search;
            $pageTitle = 'Search Merchant Deposits : ' . $user->username;
            $deposits = $user->deposits()->where('trx', $search)->orderBy('id','desc')->paginate(getPaginate());
            $emptyMessage = 'No deposits';
            return view('admin.deposit.log', compact('pageTitle', 'search', 'user', 'deposits', 'emptyMessage','userId'));
        }

        $pageTitle = 'Merchant Deposit : ' . $user->username;
        $deposits = $user->deposits()->orderBy('id','desc')->paginate(getPaginate());
        $successful = $user->deposits()->orderBy('id','desc')->sum('amount');
        $pending = $user->deposits()->orderBy('id','desc')->sum('amount');
        $rejected = $user->deposits()->orderBy('id','desc')->sum('amount');
        $emptyMessage = 'No deposits';
        $scope = 'all';
        return view('admin.deposit.log', compact('pageTitle', 'user', 'deposits', 'emptyMessage','userId','scope','successful','pending','rejected'));
    }


    public function depViaMethod($method,$type = null,$userId){
        $method = Gateway::where('alias',$method)->firstOrFail();        
        $user = Merchant::findOrFail($userId);
        if ($type == 'approved') {
            $pageTitle = 'Approved Payment Via '.$method->name;
            $deposits = Deposit::where('method_code','>=',1000)->where('user_type','MERCHANT')->where('user_id',$user->id)->where('method_code',$method->code)->where('status', 1)->orderBy('id','desc')->with(['merchant', 'gateway'])->paginate(getPaginate());
        }elseif($type == 'rejected'){
            $pageTitle = 'Rejected Payment Via '.$method->name;
            $deposits = Deposit::where('method_code','>=',1000)->where('user_type','MERCHANT')->where('user_id',$user->id)->where('method_code',$method->code)->where('status', 3)->orderBy('id','desc')->with(['merchant', 'gateway'])->paginate(getPaginate());
        }elseif($type == 'successful'){
            $pageTitle = 'Successful Payment Via '.$method->name;
            $deposits = Deposit::where('status', 1)->where('user_type','MERCHANT')->where('user_id',$user->id)->where('method_code',$method->code)->orderBy('id','desc')->with(['merchant', 'gateway'])->paginate(getPaginate());
        }elseif($type == 'pending'){
            $pageTitle = 'Pending Payment Via '.$method->name;
            $deposits = Deposit::where('method_code','>=',1000)->where('user_type','MERCHANT')->where('user_id',$user->id)->where('method_code',$method->code)->where('status', 2)->orderBy('id','desc')->with(['merchant', 'gateway'])->paginate(getPaginate());
        }else{
            $pageTitle = 'Payment Via '.$method->name;
            $deposits = Deposit::where('status','!=',0)->where('user_type','MERCHANT')->where('user_id',$user->id)->where('method_code',$method->code)->orderBy('id','desc')->with(['merchant', 'gateway'])->paginate(getPaginate());
        }
        $pageTitle = 'Deposit History: '.$user->username.' Via '.$method->name;
        $methodAlias = $method->alias;
        $emptyMessage = 'Deposit Log';
        return view('admin.deposit.log', compact('pageTitle', 'emptyMessage', 'deposits','methodAlias','userId'));
    }



    public function withdrawals(Request $request, $id)
    {
        $user = Merchant::findOrFail($id);
        if ($request->search) {
            $search = $request->search;
            $pageTitle = 'Search Merchant Withdrawals : ' . $user->username;
            $withdrawals = $user->withdrawals()->where('trx', 'like',"%$search%")->orderBy('id','desc')->paginate(getPaginate());
            $emptyMessage = 'No withdrawals';
            return view('admin.withdraw.withdrawals', compact('pageTitle', 'user', 'search', 'withdrawals', 'emptyMessage'));
        }
        $pageTitle = 'Merchant Withdrawals : ' . $user->username;
        $withdrawals = $user->withdrawals()->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No withdrawals';
        $userId = $user->id;
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'user', 'withdrawals', 'emptyMessage','userId'));
    }

    public  function withdrawalsViaMethod($method,$type,$userId){
        $method = WithdrawMethod::findOrFail($method);
        $user = Merchant::findOrFail($userId);
        if ($type == 'approved') {
            $pageTitle = 'Approved Withdrawal of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', 1)->where('user_type','MERCHANT')->where('user_id',$user->id)->with(['merchant','method'])->orderBy('id','desc')->paginate(getPaginate());
        }elseif($type == 'rejected'){
            $pageTitle = 'Rejected Withdrawals of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', 3)->where('user_type','MERCHANT')->where('user_id',$user->id)->with(['merchant','method'])->orderBy('id','desc')->paginate(getPaginate());

        }elseif($type == 'pending'){
            $pageTitle = 'Pending Withdrawals of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', 2)->where('user_type','MERCHANT')->where('user_id',$user->id)->with(['merchant','method'])->orderBy('id','desc')->paginate(getPaginate());
        }else{
            $pageTitle = 'Withdrawals of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', '!=', 0)->where('user_type','MERCHANT')->where('user_id',$user->id)->with(['merchant','method'])->orderBy('id','desc')->paginate(getPaginate());
        }
        $emptyMessage = 'Withdraw Log Not Found';
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals', 'emptyMessage','method'));
    }

    public function showEmailAllForm()
    {
        $pageTitle = 'Send Email To All Merchant';
        return view('admin.merchant.email_all', compact('pageTitle'));
    }

    public function sendEmailAll(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        foreach (Merchant::where('status', 1)->cursor() as $user) {
            sendGeneralEmail($user->email, $request->subject, $request->message, $user->username);
        }

        $notify[] = ['success', 'All Merchant will receive an email shortly.'];
        return back()->withNotify($notify);
    }

    public function login($id){
        $user = Merchant::findOrFail($id);
        Auth::guard('merchant')->login($user);
        return redirect()->route('merchant.home');
    }

    public function emailLog($id){
        $user = Merchant::findOrFail($id);
        $pageTitle = 'Email log of '.$user->username;
        $logs = EmailLog::where('user_id',$id)->where('user_type','MERCHANT')->with('agent')->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No data found';
        return view('admin.merchant.email_log', compact('pageTitle','logs','emptyMessage','user'));
    }

    public function emailDetails($id){
        $email = EmailLog::findOrFail($id);
        $pageTitle = 'Email details';
        return view('admin.merchant.email_details', compact('pageTitle','email'));
    }

}
