<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\ChargeLog;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Rules\FileTypeValidate;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function dashboard()
    {
        $pageTitle = 'Dashboard';
       
        $widget['total_users'] = User::count();
        $widget['total_agents'] = Agent::count();
        $widget['total_merchants'] = Merchant::count();
       
        $rate  = currencyRate();
        $totalCurrency = Currency::count();
     
        // Monthly Deposit & Withdraw Report Graph
        $report['months'] = collect([]);
        $report['deposit_month_amount'] = collect([]);
        $report['withdraw_month_amount'] = collect([]);

        $depositsMonth = Deposit::where('created_at', '>=', Carbon::now()->subYear())
            ->where('status', 1)
            ->selectRaw("SUM( CASE WHEN status = 1 THEN amount*$rate END) as depositAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $depositsMonth->map(function ($depositData) use ($report) {
            $report['months']->push($depositData->months);
            $report['deposit_month_amount']->push(showAmount($depositData->depositAmount));
        });
        $withdrawalMonth = Withdrawal::where('created_at', '>=', Carbon::now()->subYear())->where('status', 1)
            ->selectRaw("SUM( CASE WHEN status = 1 THEN amount*$rate END) as withdrawAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();
    
        $withdrawalMonth->map(function ($withdrawData) use ($report){
            if (!in_array($withdrawData->months,$report['months']->toArray())) {
                $report['months']->push($withdrawData->months);
            }
            $report['withdraw_month_amount']->push(showAmount($withdrawData->withdrawAmount));
        });

        //monthly charge and commission graph
        $report['profit_months'] = collect([]);
        $report['charge_month_amount'] = collect([]);
        $report['commission_month_amount'] = collect([]);


        $charges = ChargeLog::where('created_at', '>=', Carbon::now()->subYear())
            ->selectRaw("SUM(amount*$rate) as chargeAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $charges->map(function ($chargeData) use ($report) {
            $report['profit_months']->push($chargeData->months);
            $report['charge_month_amount']->push(showAmount($chargeData->chargeAmount));
        });

        $commissions = ChargeLog::where('created_at', '>=', Carbon::now()->subYear())->where('remark', 'commission')
            ->selectRaw("SUM(amount*$rate) as commissionAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();
    
        $commissions->map(function ($commissionData) use ($report){
            if (!in_array($commissionData->months,$report['months']->toArray())) {
                $report['profit_months']->push($commissionData->months);
            }
            $report['commission_month_amount']->push(showAmount($commissionData->commissionAmount));
        });

        //monthly user Registration
        $report['reg_months'] = collect([]);
        $report['user_reg_count'] = collect([]);
        $report['agent_reg_count'] = collect([]);
        $report['merchant_reg_count'] = collect([]);

        $userReg = User::where('created_at', '>=', Carbon::now()->subYear())->where('status',1)
        ->selectRaw("COUNT(id) as userCount")
        ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
        ->orderBy('created_at')
        ->groupBy('months')->get();

        $userReg->map(function ($userData) use ($report){
            if (!in_array($userData->months,$report['reg_months']->toArray())) {
                $report['reg_months']->push($userData->months);
            }
            $report['user_reg_count']->push($userData->userCount);
        });

        $agentReg = Agent::where('created_at', '>=', Carbon::now()->subYear())->where('status',1)
        ->selectRaw("COUNT(id) as agentCount")
        ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
        ->orderBy('created_at')
        ->groupBy('months')->get();

        $agentReg->map(function ($agentData) use ($report){
            if (!in_array($agentData->months,$report['reg_months']->toArray())) {
                $report['reg_months']->push($agentData->months);
            }
            $report['agent_reg_count']->push($agentData->agentCount);
        });

        $merchantReg = Merchant::where('created_at', '>=', Carbon::now()->subYear())->where('status',1)
        ->selectRaw("COUNT(id) as merchantCount")
        ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
        ->orderBy('created_at')
        ->groupBy('months')->get();

        $merchantReg->map(function ($merchantData) use ($report){
            if (!in_array($merchantData->months,$report['reg_months']->toArray())) {
                $report['reg_months']->push($merchantData->months);
            }
            $report['merchant_reg_count']->push($merchantData->merchantCount);
        });
      
        $months = $report['months'];
        for($i = 0; $i < $months->count(); ++$i) {
            $d_val      = Carbon::parse($months[$i]);
            if(isset($months[$i+1])){
                $d_val_next = Carbon::parse($months[$i+1]);
                if($d_val_next < $d_val){
                    $temp = $months[$i];
                    $months[$i]   = Carbon::parse($months[$i+1])->format('F-Y');
                    $months[$i+1] = Carbon::parse($temp)->format('F-Y');
                }else{
                    $months[$i]   = Carbon::parse($months[$i])->format('F-Y');
                }
            }
        }



        // Transaction Graph
        $report['trx_dates'] = collect([]);
        $report['trx_amount'] = collect([]);
      
        $transactions = Transaction::where('created_at', '>=', Carbon::now()->subYear())
        ->where('trx_type','+')
        ->selectRaw("SUM(amount * $rate) as totalAmount")
        ->selectRaw("DATE_FORMAT(created_at,'%dth-%M') as dates")
        ->orderBy('created_at')->groupBy('dates')->get();

        $transactions->map(function ($trxData) use ($report) {
            $report['trx_dates']->push($trxData->dates);
            $report['trx_amount']->push($trxData->totalAmount);
        });
  
      


        $depositLog = Deposit::where('status',1)->with('curr')->get();
        foreach($depositLog as $depo){
            $totalDepositAmount[] = $depo->amount * $depo->curr->rate;
            $totalDepositCharge[] =  $depo->charge * $depo->curr->rate;
        }

        $payment['total_deposit_amount'] = array_sum($totalDepositAmount);
        $payment['total_deposit_charge'] = array_sum($totalDepositCharge);
        $payment['total_deposit_pending'] = Deposit::where('status',2)->count();

        $withdrawLog = Withdrawal::where('status',1)->with('curr')->get();
        foreach($withdrawLog as $log){
            $totalWithdrawAmount[] = $log->amount * $log->curr->rate;
            $totalWithdrawCharge[] =  $log->charge * $log->curr->rate;
        }
        $paymentWithdraw['total_withdraw_amount'] =  array_sum($totalWithdrawAmount);
        $paymentWithdraw['total_withdraw_charge'] =  array_sum($totalWithdrawCharge);
        $paymentWithdraw['total_withdraw_pending'] = Withdrawal::where('status',2)->count();

        $pendingTicket = SupportTicket::where('status',0)->count();
        return view('admin.dashboard', compact('pageTitle','pendingTicket','widget', 'report','payment','paymentWithdraw','depositsMonth','withdrawalMonth','months','totalCurrency'));
    }

    public function trxDetailGraph(Request $request)
    {
          $pageTitle="Transaction Detail Graph";
         
          $curr = strtoupper($request->currency);
          $search = $request->date;
          $userType = strtoupper($request->user_type);
          $start = null;
          $end = null;

            if ($search) {
                $date = explode('-',$search);
                $start = showDateTime(@$date[0],'Y-m-d');
                $end = showDateTime(@str_replace(' ','',@$date[1]),'Y-m-d');
         
             
                $pattern = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
                if ($start && !preg_match($pattern,$start)) {
                    $notify[] = ['error','Invalid date format'];
                    return redirect()->route('admin.trx.detail')->withNotify($notify);
                }
                if ($end && !preg_match($pattern,$end)) {
                    $notify[] = ['error','Invalid date format'];
                    return redirect()->route('admin.trx.detail')->withNotify($notify);
                }
            }
            

            $report['trx_dates'] = collect([]);
            $report['trx_amount'] = collect([]);

            $rate = Currency::where('is_default',1)->first()->rate;
            $currencies = Currency::get(['currency_code','id']);
        
            $transactions = Transaction::where('created_at', '>=', Carbon::now()->subYear())
            ->where('trx_type','+')
            ->selectRaw("DATE_FORMAT(created_at,'%dth-%M') as dates")
            ->when($search, function($q) use($start,$end){
                return $q->whereBetween('created_at',[$start,$end]);
             })
            ->when($curr, function($q,$curr){
                return $q->whereHas('currency',function($cur) use($curr){
                    $cur->where('currency_code',$curr);
                });
              })
              ->when($userType, function($q,$userType){
                return $q->where('user_type',$userType);
             })
            ->selectRaw("SUM(amount * $rate) as totalAmount")
            ->orderBy('created_at')->groupBy('dates')->get();


            $transactions->map(function ($trxData) use ($report) {
                $report['trx_dates']->push($trxData->dates);
                $report['trx_amount']->push($trxData->totalAmount);
            });

          return view('admin.transaction_graph_detail',compact('report','pageTitle','currencies','search'));
    }

    public function profile()
    {
        $pageTitle = 'Profile';
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'image' => ['nullable','image',new FileTypeValidate(['jpg','jpeg','png'])]
        ]);
        $user = Auth::guard('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old = $user->image ?: null;
                $user->image = uploadImage($request->image, imagePath()['profile']['admin']['path'], imagePath()['profile']['admin']['size'], $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Your profile has been updated.'];
        return redirect()->route('admin.profile')->withNotify($notify);
    }


    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin = Auth::guard('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|min:5|confirmed',
        ]);

        $user = Auth::guard('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password do not match !!'];
            return back()->withNotify($notify);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return redirect()->route('admin.password')->withNotify($notify);
    }

    public function notifications(){
        $notifications = AdminNotification::orderBy('id','desc')->with('user')->paginate(getPaginate());
        $pageTitle = 'Notifications';
        return view('admin.notifications',compact('pageTitle','notifications'));
    }


    public function notificationRead($id){
        $notification = AdminNotification::findOrFail($id);
        $notification->read_status = 1;
        $notification->save();
        return redirect($notification->click_url);
    }

    public function requestReport()
    {
        $pageTitle = 'Your Listed Report & Request';
        $arr['app_name'] = env('APP_NAME');
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASE_CODE');
        $url = "https://license.viserlab.com/issue/get?".http_build_query($arr);
        $response = json_decode(curlContent($url));
        if ($response->status == 'error') {
            return redirect()->route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports',compact('reports','pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type'=>'required|in:bug,feature',
            'message'=>'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name'] = env('APP_NAME');
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASE_CODE');
        $arr['req_type'] = $request->type;
        $arr['message'] = $request->message;
        $response = json_decode(curlPostContent($url,$arr));
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success',$response->message];
        return back()->withNotify($notify);
    }

    public function systemInfo(){
        $laravelVersion = app()->version();
        $serverDetails = $_SERVER;
        $currentPHP = phpversion();
        $timeZone = config('app.timezone');
        $pageTitle = 'System Information';
        return view('admin.info',compact('pageTitle', 'currentPHP', 'laravelVersion', 'serverDetails','timeZone'));
    }

    public function readAll(){
        AdminNotification::where('read_status',0)->update([
            'read_status'=>1
        ]);
        $notify[] = ['success','Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function allProfitLogs(Request $request)
    {
        $search = $request->search;
        if($search){
            $pageTitle = "Search Results of $search";
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->where('trx','like',"%$search%")->whereHas('currency')->with(['currency','user','agent','merchant'])->orderBy('id','DESC')->paginate(getPaginate());
        } else {
            $pageTitle = "All Profit Logs";
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereHas('currency')->orderBy('id','DESC')->with(['currency','user','agent','merchant'])->paginate(getPaginate());
        }
        $totalProfit = totalProfit();
        $emptyMessage = "No Data Found";
        return view('admin.reports.profit_logs',compact('pageTitle','logs','emptyMessage','search','totalProfit'));
    }
    public function profitLogs(Request $request)
    {
        $search = $request->search;
        if($search){
            $pageTitle = "Search Results of $search";
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereNull('remark')->where('trx','like',"%$search%")->whereHas('currency')->with(['currency','user','agent','merchant'])->orderBy('id','DESC')->paginate(getPaginate());
        } else {
            $pageTitle = "Profit Logs";
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereNull('remark')->whereHas('currency')->with(['currency','user','agent','merchant'])->orderBy('id','DESC')->paginate(getPaginate());
        }
        $totalProfit = totalProfit();
        $emptyMessage = "No Data Found";
        return view('admin.reports.profit_logs',compact('pageTitle','logs','emptyMessage','search','totalProfit'));
    }
    public function commissionLogs(Request $request)
    {
        $search = $request->search;
        if($search){
            $pageTitle = "Search Results of $search";
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereNotNull('remark')->where('trx','like',"%$search%")->whereHas('currency')->with(['currency','user','agent','merchant'])->orderBy('id','DESC')->paginate(getPaginate());
        } else {
            $pageTitle = "Commission Logs";
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereNotNull('remark')->whereHas('currency')->with(['currency','user','agent','merchant'])->orderBy('id','DESC')->paginate(getPaginate());
        }
        $totalProfit = totalProfit();
        $emptyMessage = "No Data Found";
        return view('admin.reports.profit_logs',compact('pageTitle','logs','emptyMessage','search','totalProfit'));
    }

    public function dateSearch(Request $request){
        $search = $request->date;
        if (!$search) {
            return back();
        }
        $date = explode('-',$search);
        $start = @$date[0];
        $end = @$date[1];
        // date validation
        $pattern = "/\d{2}\/\d{2}\/\d{4}/";
        if ($start && !preg_match($pattern,$start)) {
            $notify[] = ['error','Invalid date format'];
            return redirect()->route('admin.profit.logs.all')->withNotify($notify);
        }
        if ($end && !preg_match($pattern,$end)) {
            $notify[] = ['error','Invalid date format'];
            return redirect()->route('admin.profit.logs.all')->withNotify($notify);
        }

        if ($start) {
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereDate('created_at',Carbon::parse($start));
        }
        if($end){
            $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereDate('created_at',Carbon::parse($end));
        }
      
        if ($request->scope == 'only-profits') {
            $logs = $logs->whereNull('remark');
        }elseif($request->scope == 'only-commissions'){
            $logs = $logs->whereNotNull('remark');
        }
     
        $currency = $request->currency;
        if ($currency) {
            $logs = $logs->whereHas('currency',function($curr) use($currency){
                $curr->where('currency_code',$currency);
            });
        }

        $date['start'] = $start;
        $date['end'] = $end;
        $totalProfit = totalProfit($date);

        $logs = $logs->with(['user', 'agent','merchant','currency'])->whereHas('currency')->orderBy('id','desc')->paginate(getPaginate());
        $pageTitle = 'Profit Logs';
        $emptyMessage = 'No Data Found';
        $dateSearch = $search;
        return view('admin.reports.profit_logs', compact('pageTitle', 'emptyMessage', 'logs','dateSearch','totalProfit'));
    }

    public function exportCsv(Request $request)
    {
            
            if($request->log == 'profit-logs'){
                $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereNull('remark')->orderBy('id','DESC')->get();
                $fileName = 'profit_logs_'.showDateTime(now(),'d_m_Y').'.csv';
            } else if($request->log == 'commission-logs'){
                $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->whereNotNull('remark')->orderBy('id','DESC')->get();
                $fileName = 'commission_logs_'.showDateTime(now(),'d_m_Y').'.csv';
            } else {
                $logs = ChargeLog::where([['operation_type','!=','add_money'],['operation_type','!=','withdraw_money']])->latest()->get();
                $fileName = 'all_profit_commission_logs_'.showDateTime(now(),'d_m_Y').'.csv';
            }

            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            $columns = array('Trx', 'User', 'User type', 'Amount', 'Currency', 'Operation Type', 'Remark', 'Time & Date');

            $callback = function() use($logs, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($logs as $log) {
                    if($log->user){
                        $user = $log->user->username;
                    } else if($log->agent){
                        $user = $log->agent->username;
                    } else{
                        $user = $log->merchant->username;
                    }

                    $row['Trx']             = $log->trx;
                    $row['User']            = $user;
                    $row['User type']       = $log->user_type;
                    $row['Amount']          = $log->amount;
                    $row['Currency']        = $log->currency->currency_code;
                    $row['Operation Type']  = ucwords(str_replace('_',' ',$log->operation_type));
                    $row['Remark']          = $log->remark ? str_replace('_',' ',$log->remark) : 'N/A';
                    $row['Time & Date']     = showDateTime($log->created_at);

                    fputcsv($file, array($row['Trx'], $row['User'], $row['User type'], $row['Amount'], $row['Currency'], $row['Operation Type'], $row['Remark'], $row['Time & Date']));
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
    }

}
