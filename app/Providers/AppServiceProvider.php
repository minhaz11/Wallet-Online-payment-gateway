<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\User;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Merchant;
use App\Models\Withdrawal;
use App\Models\SupportTicket;
use App\Models\GeneralSetting;
use App\Models\AdminNotification;
use App\Models\KycForm;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['request']->server->set('HTTPS', true);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $activeTemplate = activeTemplate();
        $general = GeneralSetting::first();
        $viewShare['general'] = $general;
        $viewShare['activeTemplate'] = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        $viewShare['language'] = Language::all();
        $viewShare['pages'] = Page::where('tempname',$activeTemplate)->where('slug','!=','home')->get();
        view()->share($viewShare);
        

        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'banned_users_count'           => User::banned()->count(),
                'email_unverified_users_count' => User::emailUnverified()->count(),
                'sms_unverified_users_count'   => User::smsUnverified()->count(),
                'banned_agent_count'           => Agent::banned()->count(),
                'email_unverified_agent_count' => Agent::emailUnverified()->count(),
                'sms_unverified_agent_count'   => Agent::smsUnverified()->count(),
                'banned_merchant_count'           => Merchant::banned()->count(),
                'email_unverified_merchant_count' => Merchant::emailUnverified()->count(),
                'sms_unverified_merchant_count'   => Merchant::smsUnverified()->count(),
                'pending_ticket_count'         => SupportTicket::whereIN('status', [0,2])->count(),
                'pending_deposits_count'    => Deposit::pending()->count(),
                'pending_withdraw_count'    => Withdrawal::pending()->count(),
                'pending_user_kyc'    => User::where('kyc_status',2)->whereNotNull('kyc_info')->count(),
                'pending_agent_kyc'    => Agent::where('kyc_status',2)->whereNotNull('kyc_info')->count(),
                'pending_merchant_kyc'    => Merchant::where('kyc_status',2)->whereNotNull('kyc_info')->count(),
                
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotifications'=>AdminNotification::where('read_status',0)->with('user')->orderBy('id','desc')->get(),
            ]);
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        if($general->force_ssl){
            
            \URL::forceScheme('https');
        } else {
            \URL::forceScheme('http');
        }

        Paginator::useBootstrap();
        
    }
}
