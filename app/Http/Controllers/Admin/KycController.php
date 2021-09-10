<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\KycForm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Merchant;

class KycController extends Controller
{
    public function manageKyc()
    {
        $pageTitle = "Manage KYC Form";
        $kyc       = KycForm::latest()->get();
        $emptyMessage = "No Data Found";
        return view('admin.kyc.list',compact('pageTitle','kyc','emptyMessage'));
    }
    public function editKyc($userType)
    {
        $pageTitle = "Edit KYC Form";
        $kyc       = KycForm::where('user_type',$userType)->firstOrFail();
        return view('admin.kyc.edit',compact('pageTitle','kyc'));
    }

    public function updateKyc(Request $request)
    {
        $request->validate([
            'field_name.*'   => 'sometimes|required'
        ],
        [
            'field_name.*.required'=>'All form data field is required'
        ]);

        $input_form = [];
        if ($request->has('field_name')) {
            for ($a = 0; $a < count($request->field_name); $a++) {
                $arr = [];
                $arr['field_name'] = strtolower(str_replace(' ', '_', sanitizedParam($request->field_name[$a])));
                $arr['field_level'] = $request->field_name[$a];
                $arr['type'] = $request->type[$a];
                $arr['validation'] = $request->validation[$a];
                $input_form[$arr['field_name']] = $arr;
            }
        }

        $kyc = KycForm::findOrFail($request->id);
        $kyc->status = $request->status ? 1 : 0;
        $kyc->form_data = $input_form;
        $kyc->save();
        $notify[]=['success','Kyc form updated'];
        return back()->withNotify($notify);
    }

    //user kyc
    public function userPendingKyc()
    {
        $pageTitle = "User Pending KYC's";
        $kycInfo = User::where('status',1)->where('kyc_status',2)->whereNotNull('kyc_info')->paginate(getPaginate());
        $type = 'user';
        $emptyMessage = "No Data Found";
        return view('admin.kyc.kyc_list',compact('pageTitle','kycInfo','emptyMessage','type'));
    }
    public function userApprovedKyc()
    {
        $pageTitle = "User Approved KYC's";
        $kycInfo = User::where('status',1)->where('kyc_status',1)->whereNotNull('kyc_info')->paginate(getPaginate());
        $type = 'user';
        $emptyMessage = "No Data Found";
        return view('admin.kyc.kyc_list',compact('pageTitle','kycInfo','emptyMessage','type'));
    }

    public function userKycDetails($userId)
    {
        $user =  User::where('id',$userId)->where('status',1)->whereNotNull('kyc_info')->firstOrFail();
        $pageTitle = "KYC Info of $user->username";
        $prevUrl = url()->previous();
        return view('admin.kyc.kyc_details',compact('pageTitle','user','prevUrl'));
    }

    public function approveUserKyc(Request $request)
    {
        $user =  User::findOrFail($request->user_id);
        $user->kyc_status = 1;
        $user->save();
        $notify[]=['success','KYC approved successfully'];
        return redirect(route('admin.kyc.info.user.pending'))->withNotify($notify);
    }
    public function rejectUserKyc(Request $request)
    {
        $request->validate([
            'reasons'=>'required'
        ]);
        $user =  User::findOrFail($request->user_id);
        $user->kyc_status = 3;
        $user->kyc_reject_reasons = $request->reasons;
        $user->save();
        $notify[]=['success','KYC has been rejected'];
        return redirect(route('admin.kyc.info.user.pending'))->withNotify($notify);
    }


    //agent kyc
    public function agentPendingKyc()
    {
        $pageTitle = "Agent Pending KYC's";
        $kycInfo = Agent::where('status',1)->where('kyc_status',2)->whereNotNull('kyc_info')->paginate(getPaginate());
        $type = 'agent';
        $emptyMessage = "No Data Found";
        return view('admin.kyc.kyc_list',compact('pageTitle','kycInfo','emptyMessage','type'));
    }
    public function agentApprovedKyc()
    {
        $pageTitle = "User Approved KYC's";
        $kycInfo = Agent::where('status',1)->where('kyc_status',1)->whereNotNull('kyc_info')->paginate(getPaginate());
        $type = 'agent';
        $emptyMessage = "No Data Found";
        return view('admin.kyc.kyc_list',compact('pageTitle','kycInfo','emptyMessage','type'));
    }

    public function agentKycDetails($userId)
    {
        $user =  Agent::where('id',$userId)->where('status',1)->whereNotNull('kyc_info')->firstOrFail();
        $pageTitle = "KYC Info of $user->username";
        $prevUrl = url()->previous();
        return view('admin.kyc.kyc_details',compact('pageTitle','user','prevUrl'));
    }

    public function approveAgentKyc(Request $request)
    {
        $user =  Agent::findOrFail($request->user_id);
        $user->kyc_status = 1;
        $user->save();
        $notify[]=['success','KYC approved successfully'];
        return redirect(route('admin.kyc.info.agent.pending'))->withNotify($notify);
    }
    public function rejectAgentKyc(Request $request)
    {
        $request->validate([
            'reasons'=>'required'
        ]);
        $user =  Agent::findOrFail($request->user_id);
        $user->kyc_status = 3;
        $user->kyc_reject_reasons = $request->reasons;
        $user->save();
        $notify[]=['success','KYC has been rejected'];
        return redirect(route('admin.kyc.info.agent.pending'))->withNotify($notify);
    }


   
    //merchant kyc
    public function merchantPendingKyc()
    {
        $pageTitle = "Merchant Pending KYC's";
        $kycInfo = Merchant::where('status',1)->where('kyc_status',2)->whereNotNull('kyc_info')->paginate(getPaginate());
        $type = 'merchant';
        $emptyMessage = "No Data Found";
        return view('admin.kyc.kyc_list',compact('pageTitle','kycInfo','emptyMessage','type'));
    }
    public function merchantApprovedKyc()
    {
        $pageTitle = "Merchant Approved KYC's";
        $kycInfo = Merchant::where('status',1)->where('kyc_status',1)->whereNotNull('kyc_info')->paginate(getPaginate());
        $type = 'merchant';
        $emptyMessage = "No Data Found";
        return view('admin.kyc.kyc_list',compact('pageTitle','kycInfo','emptyMessage','type'));
    }

    public function merchantKycDetails($userId)
    {
        $user =  Merchant::where('id',$userId)->where('status',1)->whereNotNull('kyc_info')->firstOrFail();
        $pageTitle = "KYC Info of $user->username";
        $prevUrl = url()->previous();
        return view('admin.kyc.kyc_details',compact('pageTitle','user','prevUrl'));
    }

    public function approveMerchantKyc(Request $request)
    {
        $user =  Merchant::findOrFail($request->user_id);
        $user->kyc_status = 1;
        $user->save();
        $notify[]=['success','KYC approved successfully'];
        return redirect(route('admin.kyc.info.merchant.pending'))->withNotify($notify);
    }
    public function rejectMerchantKyc(Request $request)
    {
        $request->validate([
            'reasons'=>'required'
        ]);
        $user =  Merchant::findOrFail($request->user_id);
        $user->kyc_status = 3;
        $user->kyc_reject_reasons = $request->reasons;
        $user->save();
        $notify[]=['success','KYC has been rejected'];
        return redirect(route('admin.kyc.info.merchant.pending'))->withNotify($notify);
    }


}
