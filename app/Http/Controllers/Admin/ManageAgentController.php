<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Agent;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\EmailLog;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\WithdrawMethod;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class ManageAgentController extends Controller
{
    public function allAgent()
    {
        $pageTitle = 'Manage Agent';
        $emptyMessage = 'No agent found';
        $users = Agent::orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function activeAgent()
    {
        $pageTitle = 'Manage Active Agent';
        $emptyMessage = 'No active agent found';
        $users = Agent::active()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function bannedAgent()
    {
        $pageTitle = 'Banned Agent';
        $emptyMessage = 'No banned agent found';
        $users = User::banned()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function emailUnverifiedAgent()
    {
        $pageTitle = 'Email Unverified Agent';
        $emptyMessage = 'No email unverified agent found';
        $users = Agent::emailUnverified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }
    public function emailVerifiedAgent()
    {
        $pageTitle = 'Email Verified Agent';
        $emptyMessage = 'No email verified agent found';
        $users = Agent::emailVerified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }


    public function smsUnverifiedAgent()
    {
        $pageTitle = 'SMS Unverified Agent';
        $emptyMessage = 'No sms unverified agent found';
        $users = Agent::smsUnverified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }


    public function smsVerifiedAgent()
    {
        $pageTitle = 'SMS Verified Agent';
        $emptyMessage = 'No sms verified user found';
        $users = Agent::smsVerified()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    


    public function search(Request $request, $scope)
    {
        $search = $request->search;
        $agent = Agent::where(function ($user) use ($search) {
            $user->where('username', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        });
        $pageTitle = '';
        if ($scope == 'active') {
            $pageTitle = 'Active ';
            $agent = $agent->where('status', 1);
        }elseif($scope == 'banned'){
            $pageTitle = 'Banned';
            $agent = $agent->where('status', 0);
        }elseif($scope == 'emailUnverified'){
            $pageTitle = 'Email Unverified ';
            $agent = $agent->where('ev', 0);
        }elseif($scope == 'smsUnverified'){
            $pageTitle = 'SMS Unverified ';
            $agent = $agent->where('sv', 0);
        }elseif($scope == 'withBalance'){
            $pageTitle = 'With Balance ';
            $agent = $agent->where('balance','!=',0);
        }

        $users = $agent->paginate(getPaginate());
        $pageTitle .= 'Agent Search - ' . $search;
        $emptyMessage = 'No search result found';
        return view('admin.agent.list', compact('pageTitle', 'search', 'scope', 'emptyMessage', 'users'));
    }


    public function detail($id)
    {
        $pageTitle = 'Agent Detail';
        $user = Agent::findOrFail($id);
        $totalDeposit = Deposit::where('user_id',$user->id)->where('user_type','AGENT')->where('status',1)->sum('amount');
        $totalWithdraw = Withdrawal::where('user_id',$user->id)->where('user_type','AGENT')->where('status',1)->sum('amount');
        $totalTransaction = Transaction::where('user_id',$user->id)->where('user_type','AGENT')->count();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $wallets = Wallet::where('user_type','AGENT')->where('user_id',$user->id)->get();
        $moneyIn = Transaction::where('user_id',$user->id)->where('user_type','AGENT')->where('operation_type','money_in')->get();
       
        $totalMoneyIn[] = 0;
        foreach($moneyIn as $item){
            $totalMoneyIn[] = $item->amount * $item->wallet->currency->rate;
        }
       
        return view('admin.agent.detail', compact('pageTitle', 'user','totalDeposit','totalWithdraw','totalTransaction','countries','wallets','totalMoneyIn'));
    }


    public function update(Request $request, $id)
    {
        $user = Agent::findOrFail($id);

        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $request->validate([
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'email' => 'required|email|max:90|unique:agents,email,' . $user->id,
            'mobile' => 'required|unique:agents,mobile,' . $user->id,
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

        $notify[] = ['success', 'Agent detail has been updated'];
        return redirect()->back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|gt:0']);

        $user = Agent::findOrFail($id);
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
            $transaction->user_type = 'AGENT';
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
            $transaction->user_type = 'AGENT';
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


    public function agentLoginHistory($id)
    {
        $user = Agent::findOrFail($id);
        $pageTitle = 'Agent Login History - ' . $user->username;
        $emptyMessage = 'No Agent login found.';
        $login_logs = UserLogin::where('agent_id',$user->id)->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.agent.logins', compact('pageTitle', 'emptyMessage', 'login_logs'));
    }



    public function showEmailSingleForm($id)
    {
        $user = Agent::findOrFail($id);
        $pageTitle = 'Send Email To: ' . $user->username;
        return view('admin.agent.email_single', compact('pageTitle', 'user'));
    }

    public function sendEmailSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        $user = Agent::findOrFail($id);
        sendGeneralEmail($user->email, $request->subject, $request->message, $user->username);
        $notify[] = ['success', $user->username . ' will receive an email shortly.'];
        return back()->withNotify($notify);
    }

    public function transactions(Request $request, $id)
    {
        $user = Agent::findOrFail($id);
        if ($request->search) {
            $search = $request->search;
            $pageTitle = 'Search Agent Transactions : ' . $user->username;
            $transactions = $user->transactions()->where('trx', $search)->with('agent')->orderBy('id','desc')->paginate(getPaginate());
            $emptyMessage = 'No transactions';
            return view('admin.reports.agent_transactions', compact('pageTitle', 'search', 'user', 'transactions', 'emptyMessage'));
        }
        $pageTitle = 'Agent Transactions : ' . $user->username;
        $transactions = $user->transactions()->with('agent')->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No transactions';
        return view('admin.reports.agent_transactions', compact('pageTitle', 'user', 'transactions', 'emptyMessage'));
    }

    public function deposits(Request $request, $id)
    {
        $user = Agent::findOrFail($id);
        $userId = $user->id;
        if ($request->search) {
            $search = $request->search;
            $pageTitle = 'Search Agent Deposits : ' . $user->username;
            $deposits = $user->deposits()->where('trx', $search)->orderBy('id','desc')->paginate(getPaginate());
            $emptyMessage = 'No deposits';
            return view('admin.deposit.log', compact('pageTitle', 'search', 'user', 'deposits', 'emptyMessage','userId'));
        }

        $pageTitle = 'Agent Deposit : ' . $user->username;
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
        $user = Agent::findOrFail($userId);
        if ($type == 'approved') {
            $pageTitle = 'Approved Payment Via '.$method->name;
            $deposits = Deposit::where('method_code','>=',1000)->where('user_type','AGENT')->where('user_id',$user->id)->where('method_code',$method->code)->where('status', 1)->orderBy('id','desc')->with(['agent', 'gateway'])->paginate(getPaginate());
        }elseif($type == 'rejected'){
            $pageTitle = 'Rejected Payment Via '.$method->name;
            $deposits = Deposit::where('method_code','>=',1000)->where('user_type','AGENT')->where('user_id',$user->id)->where('method_code',$method->code)->where('status', 3)->orderBy('id','desc')->with(['agent', 'gateway'])->paginate(getPaginate());
        }elseif($type == 'successful'){
            $pageTitle = 'Successful Payment Via '.$method->name;
            $deposits = Deposit::where('status', 1)->where('user_type','AGENT')->where('user_id',$user->id)->where('method_code',$method->code)->orderBy('id','desc')->with(['agent', 'gateway'])->paginate(getPaginate());
        }elseif($type == 'pending'){
            $pageTitle = 'Pending Payment Via '.$method->name;
            $deposits = Deposit::where('method_code','>=',1000)->where('user_type','AGENT')->where('user_id',$user->id)->where('method_code',$method->code)->where('status', 2)->orderBy('id','desc')->with(['agent', 'gateway'])->paginate(getPaginate());
        }else{
            $pageTitle = 'Payment Via '.$method->name;
            $deposits = Deposit::where('status','!=',0)->where('user_type','AGENT')->where('user_id',$user->id)->where('method_code',$method->code)->orderBy('id','desc')->with(['agent', 'gateway'])->paginate(getPaginate());
        }
        $pageTitle = 'Deposit History: '.$user->username.' Via '.$method->name;
        $methodAlias = $method->alias;
        $emptyMessage = 'Deposit Log';
        return view('admin.deposit.log', compact('pageTitle', 'emptyMessage', 'deposits','methodAlias','userId'));
    }



    public function withdrawals(Request $request, $id)
    {
        $user = Agent::findOrFail($id);
        if ($request->search) {
            $search = $request->search;
            $pageTitle = 'Search Agent Withdrawals : ' . $user->username;
            $withdrawals = $user->withdrawals()->where('trx', 'like',"%$search%")->orderBy('id','desc')->paginate(getPaginate());
            $emptyMessage = 'No withdrawals';
            return view('admin.withdraw.withdrawals', compact('pageTitle', 'user', 'search', 'withdrawals', 'emptyMessage'));
        }
        $pageTitle = 'Agent Withdrawals : ' . $user->username;
        $withdrawals = $user->withdrawals()->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No withdrawals';
        $userId = $user->id;
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'user', 'withdrawals', 'emptyMessage','userId'));
    }

    public  function withdrawalsViaMethod($method,$type,$userId){
        $method = WithdrawMethod::findOrFail($method);
        $user = Agent::findOrFail($userId);
        if ($type == 'approved') {
            $pageTitle = 'Approved Withdrawal of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', 1)->where('user_type','AGENT')->where('user_id',$user->id)->with(['agent','method'])->orderBy('id','desc')->paginate(getPaginate());
        }elseif($type == 'rejected'){
            $pageTitle = 'Rejected Withdrawals of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', 3)->where('user_type','AGENT')->where('user_id',$user->id)->with(['agent','method'])->orderBy('id','desc')->paginate(getPaginate());

        }elseif($type == 'pending'){
            $pageTitle = 'Pending Withdrawals of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', 2)->where('user_type','AGENT')->where('user_id',$user->id)->with(['agent','method'])->orderBy('id','desc')->paginate(getPaginate());
        }else{
            $pageTitle = 'Withdrawals of '.$user->username.' Via '.$method->name;
            $withdrawals = Withdrawal::where('status', '!=', 0)->where('user_type','AGENT')->where('user_id',$user->id)->with(['agent','method'])->orderBy('id','desc')->paginate(getPaginate());
        }
        $emptyMessage = 'Withdraw Log Not Found';
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals', 'emptyMessage','method'));
    }

    public function showEmailAllForm()
    {
        $pageTitle = 'Send Email To All Agent';
        return view('admin.agent.email_all', compact('pageTitle'));
    }

    public function sendEmailAll(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        foreach (Agent::where('status', 1)->cursor() as $user) {
            sendGeneralEmail($user->email, $request->subject, $request->message, $user->username);
        }

        $notify[] = ['success', 'All Agent will receive an email shortly.'];
        return back()->withNotify($notify);
    }

    public function login($id){
        $user = Agent::findOrFail($id);
        Auth::guard('agent')->login($user);
        return redirect()->route('agent.home');
    }

    public function emailLog($id){
        $user = Agent::findOrFail($id);
        $pageTitle = 'Email log of '.$user->username;
        $logs = EmailLog::where('user_id',$id)->where('user_type','AGENT')->with('agent')->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No data found';
        return view('admin.agent.email_log', compact('pageTitle','logs','emptyMessage','user'));
    }

    public function emailDetails($id){
        $email = EmailLog::findOrFail($id);
        $pageTitle = 'Email details';
        return view('admin.agent.email_details', compact('pageTitle','email'));
    }

}
