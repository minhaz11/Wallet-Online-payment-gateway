<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\AgentPasswordReset;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('agent.guest');
    }


    public function showLinkRequestForm()
    {
        $pageTitle = "Agent Forgot Password";
        return view(activeTemplate() . 'agent.auth.passwords.email', compact('pageTitle'));
    }

    public function sendResetCodeEmail(Request $request)
    {
        if ($request->type == 'email') {
            $validationRule = [
                'value'=>'required|email'
            ];
            $validationMessage = [
                'value.required'=>'Email field is required',
                'value.email'=>'Email must be a valid email'
            ];
        }elseif($request->type == 'username'){
            $validationRule = [
                'value'=>'required'
            ];
            $validationMessage = ['value.required'=>'Username field is required'];
        }else{
            $notify[] = ['error','Invalid selection'];
            return back()->withNotify($notify);
        }

        $request->validate($validationRule,$validationMessage);

        $user = Agent::where($request->type, $request->value)->first();
        
        if (!$user) {
            $notify[] = ['error', 'Agent not found.'];
            return back()->withNotify($notify);
        }

        AgentPasswordReset::where('email', $user->email)->delete();
        $code = verificationCode(6);
        $password = new AgentPasswordReset();
        $password->email = $user->email;
        $password->token = $code;
        $password->created_at = \Carbon\Carbon::now();
        $password->save();

        $userIpInfo = getIpInfo();
        $userBrowserInfo = osBrowser();
        sendEmail($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => @$userBrowserInfo['os_platform'],
            'browser' => @$userBrowserInfo['browser'],
            'ip' => @$userIpInfo['ip'],
            'time' => @$userIpInfo['time']
        ]);

        $email = $user->email;
        session()->put('pass_res_mail',$email);
        $notify[] = ['success', 'Password reset email sent successfully'];
        return redirect()->route('agent.password.code.verify')->withNotify($notify);
    }

    public function codeVerify(){
        $pageTitle = 'Account Recovery';
        $email = session()->get('pass_res_mail');
        if (!$email) {
            $notify[] = ['error','Oops! session expired'];
            return redirect()->route('agent.password.request')->withNotify($notify);
        }
        return view(activeTemplate().'agent.auth.passwords.code_verify',compact('pageTitle','email'));
    }

    public function verifyCode(Request $request)
    {  
        $request->validate([
            'code' => 'required',
            'email' => 'required'
            ]);
            $code =  str_replace(' ', '', $request->code);

        if (AgentPasswordReset::where('token', $code)->where('email', $request->email)->count() != 1) {
            $notify[] = ['error', 'Invalid token'];
            return redirect()->route('agent.password.request')->withNotify($notify);
        }
        
        $notify[] = ['success', 'You can change your password.'];
        session()->flash('fpass_email', $request->email);
        return redirect()->route('agent.password.reset', $code)->withNotify($notify);
    }

    public function broker()
    {
        return Password::broker('agents');
    }

}
