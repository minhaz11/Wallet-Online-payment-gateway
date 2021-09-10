<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function(){
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('cron')->name('cron.')->group(function () {
    Route::get('fiat-rate', 'CronController@fiatRate')->name('fiat.rate');
    Route::get('crypto-rate', 'CronController@cryptoRate')->name('crypto.rate');
});


Route::match(['get','post'],'/payment/initiate', 'GetPaymentController@initiatePayment')->name('initiate.payment');
Route::get('initiate/payment/checkout', 'GetPaymentController@initiatePaymentAuthView')->name('initiate.payment.auth.view');
Route::post('initiate/payment/checkout', 'GetPaymentController@checkEmail')->name('payment.check.email');
Route::get('verify/payment', 'GetPaymentController@verifyPayment')->name('payment.verify');
Route::post('confirm/payment', 'GetPaymentController@verifyPaymentConfirm')->name('confirm.payment');
Route::get('resend/verify/code', 'GetPaymentController@sendVerifyCode')->name('resend.code');
Route::get('cancel/payment', 'GetPaymentController@cancelPayment')->name('cancel.payment');

//test payment
Route::prefix('sandbox')->name('test.')->group(function () {
    Route::match(['get','post'],'/payment/initiate', 'TestPaymentController@initiatePayment')->name('initiate.payment');
    Route::get('initiate/payment/checkout', 'TestPaymentController@initiatePaymentAuthView')->name('initiate.payment.auth.view');
    Route::post('initiate/payment/checkout', 'TestPaymentController@checkEmail')->name('payment.check.email');
    Route::get('verify/payment', 'TestPaymentController@verifyPayment')->name('payment.verify');
    Route::post('confirm/payment', 'TestPaymentController@verifyPaymentConfirm')->name('confirm.payment');
    Route::get('cancel/payment', 'TestPaymentController@cancelPayment')->name('cancel.payment');

});



Route::namespace('Gateway')->prefix('ipn')->name('ipn.')->group(function () {
    Route::post('paypal', 'Paypal\ProcessController@ipn')->name('Paypal');
    Route::get('paypal-sdk', 'PaypalSdk\ProcessController@ipn')->name('PaypalSdk');
    Route::post('perfect-money', 'PerfectMoney\ProcessController@ipn')->name('PerfectMoney');
    Route::post('stripe', 'Stripe\ProcessController@ipn')->name('Stripe');
    Route::post('stripe-js', 'StripeJs\ProcessController@ipn')->name('StripeJs');
    Route::post('stripe-v3', 'StripeV3\ProcessController@ipn')->name('StripeV3');
    Route::post('skrill', 'Skrill\ProcessController@ipn')->name('Skrill');
    Route::post('paytm', 'Paytm\ProcessController@ipn')->name('Paytm');
    Route::post('payeer', 'Payeer\ProcessController@ipn')->name('Payeer');
    Route::post('paystack', 'Paystack\ProcessController@ipn')->name('Paystack');
    Route::post('voguepay', 'Voguepay\ProcessController@ipn')->name('Voguepay');
    Route::get('flutterwave/{trx}/{type}', 'Flutterwave\ProcessController@ipn')->name('Flutterwave');
    Route::post('razorpay', 'Razorpay\ProcessController@ipn')->name('Razorpay');
    Route::post('instamojo', 'Instamojo\ProcessController@ipn')->name('Instamojo');
    Route::get('blockchain', 'Blockchain\ProcessController@ipn')->name('Blockchain');
    Route::get('blockio', 'Blockio\ProcessController@ipn')->name('Blockio');
    Route::post('coinpayments', 'Coinpayments\ProcessController@ipn')->name('Coinpayments');
    Route::post('coinpayments-fiat', 'Coinpayments_fiat\ProcessController@ipn')->name('CoinpaymentsFiat');
    Route::post('coingate', 'Coingate\ProcessController@ipn')->name('Coingate');
    Route::post('coinbase-commerce', 'CoinbaseCommerce\ProcessController@ipn')->name('CoinbaseCommerce');
    Route::get('mollie', 'Mollie\ProcessController@ipn')->name('Mollie');
    Route::post('cashmaal', 'Cashmaal\ProcessController@ipn')->name('Cashmaal');
});

// User Support Ticket
Route::prefix('ticket')->group(function () {
    Route::get('/', 'TicketController@supportTicket')->name('ticket');
    Route::get('/new', 'TicketController@openSupportTicket')->name('ticket.open');
    Route::post('/create', 'TicketController@storeSupportTicket')->name('ticket.store');
    Route::get('/view/{ticket}', 'TicketController@viewTicket')->name('ticket.view');
    Route::post('/reply/{ticket}', 'TicketController@replyTicket')->name('ticket.reply');
    Route::get('/download/{ticket}', 'TicketController@ticketDownload')->name('ticket.download');
});


/*
|--------------------------------------------------------------------------
| Start Admin Area
|--------------------------------------------------------------------------
*/

Route::namespace('Admin')->prefix('admin')->name('admin.')->group(function () {
    Route::namespace('Auth')->group(function () {
        Route::get('/', 'LoginController@showLoginForm')->name('login');
        Route::post('/', 'LoginController@login')->name('login');
        Route::get('logout', 'LoginController@logout')->name('logout');
        // Admin Password Reset
        Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset');
        Route::post('password/reset', 'ForgotPasswordController@sendResetCodeEmail');
        Route::post('password/verify-code', 'ForgotPasswordController@verifyCode')->name('password.verify.code');
        Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset.form');
        Route::post('password/reset/change', 'ResetPasswordController@reset')->name('password.change');
    });

    Route::middleware('admin')->group(function () {
        Route::get('dashboard', 'AdminController@dashboard')->name('dashboard');
        Route::get('transaction-detail/graph', 'AdminController@trxDetailGraph')->name('trx.detail');
        Route::get('profile', 'AdminController@profile')->name('profile');
        Route::post('profile', 'AdminController@profileUpdate')->name('profile.update');
        Route::get('password', 'AdminController@password')->name('password');
        Route::post('password', 'AdminController@passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications','AdminController@notifications')->name('notifications');
        Route::get('notification/read/{id}','AdminController@notificationRead')->name('notification.read');
        Route::get('notifications/read-all','AdminController@readAll')->name('notifications.readAll');

        //Report Bugs
        Route::get('request-report','AdminController@requestReport')->name('request.report');
        Route::post('request-report','AdminController@reportSubmit');

        Route::get('system-info','AdminController@systemInfo')->name('system.info');

        //manage currencies
        Route::get('currencies','CurrencyController@currencies')->name('currencies');
        Route::post('currencies/store','CurrencyController@store')->name('currencies.store');
        Route::post('currencies/update','CurrencyController@update')->name('currencies.update');
        Route::post('currencies/api-key/update','CurrencyController@updateApiKey')->name('currencies.api.update');

        //income log
        Route::get('all/profit-logs','AdminController@allProfitLogs')->name('profit.logs.all');
        Route::get('profit-logs','AdminController@profitLogs')->name('profit.logs');
        Route::get('commission-logs','AdminController@commissionLogs')->name('profit.logs.commission');
        Route::get('export-csv','AdminController@exportCsv')->name('export.csv');
        Route::get('profit-logs/date-search/','AdminController@dateSearch')->name('profit.logs.date.search');

        //manage Transfer charge
        Route::get('transaction-charges','TransactionChargeController@manageCharges')->name('transaction.charges');
        Route::post('transaction-charges/update','TransactionChargeController@updateCharges')->name('charges.update');

        // Users Manager
        Route::get('users', 'ManageUsersController@allUsers')->name('users.all');
        Route::get('users/active', 'ManageUsersController@activeUsers')->name('users.active');
        Route::get('users/banned', 'ManageUsersController@bannedUsers')->name('users.banned');
        Route::get('users/email-verified', 'ManageUsersController@emailVerifiedUsers')->name('users.email.verified');
        Route::get('users/email-unverified', 'ManageUsersController@emailUnverifiedUsers')->name('users.email.unverified');
        Route::get('users/sms-unverified', 'ManageUsersController@smsUnverifiedUsers')->name('users.sms.unverified');
        Route::get('users/sms-verified', 'ManageUsersController@smsVerifiedUsers')->name('users.sms.verified');
        Route::get('users/with-balance', 'ManageUsersController@usersWithBalance')->name('users.with.balance');

        Route::get('users/{scope}/search', 'ManageUsersController@search')->name('users.search');
        Route::get('user/detail/{id}', 'ManageUsersController@detail')->name('users.detail');
        Route::post('user/update/{id}', 'ManageUsersController@update')->name('users.update');
        Route::post('user/add-sub-balance/{id}', 'ManageUsersController@addSubBalance')->name('users.add.sub.balance');
        Route::get('user/send-email/{id}', 'ManageUsersController@showEmailSingleForm')->name('users.email.single');
        Route::post('user/send-email/{id}', 'ManageUsersController@sendEmailSingle')->name('users.email.single');
        Route::get('user/login/{id}', 'ManageUsersController@login')->name('users.login');
        Route::get('user/transactions/{id}', 'ManageUsersController@transactions')->name('users.transactions');
        Route::get('user/deposits/{id}', 'ManageUsersController@deposits')->name('users.deposits');
        Route::get('user/deposits/via/{method}/{type?}/{userId}', 'ManageUsersController@depositViaMethod')->name('users.deposits.method');
        Route::get('user/withdrawals/{id}', 'ManageUsersController@withdrawals')->name('users.withdrawals');
        Route::get('user/withdrawals/via/{method}/{type?}/{userId}', 'ManageUsersController@withdrawalsViaMethod')->name('users.withdrawals.method');

        // Agent Manager
        Route::get('agents', 'ManageAgentController@allAgent')->name('agent.all');
        Route::get('agent/active', 'ManageAgentController@activeAgent')->name('agent.active');
        Route::get('agent/banned', 'ManageAgentController@bannedAgent')->name('agent.banned');
        Route::get('agent/email-verified', 'ManageAgentController@emailVerifiedAgent')->name('agent.email.verified');
        Route::get('agent/email-unverified', 'ManageAgentController@emailUnverifiedAgent')->name('agent.email.unverified');
        Route::get('agent/sms-unverified', 'ManageAgentController@smsUnverifiedAgent')->name('agent.sms.unverified');
        Route::get('agent/sms-verified', 'ManageAgentController@smsVerifiedAgent')->name('agent.sms.verified');
        Route::get('agent/with-balance', 'ManageAgentController@agentWithBalance')->name('agent.with.balance');

        Route::get('agent/{scope}/search', 'ManageAgentController@search')->name('agent.search');
        Route::get('agent/detail/{id}', 'ManageAgentController@detail')->name('agent.detail');
        Route::post('agent/update/{id}', 'ManageAgentController@update')->name('agent.update');
        Route::post('agent/add-sub-balance/{id}', 'ManageAgentController@addSubBalance')->name('agent.add.sub.balance');
        Route::get('agent/send-email/{id}', 'ManageAgentController@showEmailSingleForm')->name('agent.email.single');
        Route::post('agent/send-email/{id}', 'ManageAgentController@sendEmailSingle')->name('agent.email.single');
        Route::get('agent/login/{id}', 'ManageAgentController@login')->name('agent.login');
        Route::get('agent/transactions/{id}', 'ManageAgentController@transactions')->name('agent.transactions');
        Route::get('agent/deposits/{id}', 'ManageAgentController@deposits')->name('agent.deposits');
        Route::get('agent/deposits/via/{method}/{type?}/{agentId}', 'ManageAgentController@depositViaMethod')->name('agent.deposits.method');
        Route::get('agent/withdrawals/{id}', 'ManageAgentController@withdrawals')->name('agent.withdrawals');
        Route::get('agent/withdrawals/via/{method}/{type?}/{agentId}', 'ManageAgentController@withdrawalsViaMethod')->name('agent.withdrawals.method');


        // Merchant Manager
        Route::get('merchants', 'ManageMerchantController@allMerchant')->name('merchant.all');
        Route::get('merchant/active', 'ManageMerchantController@activeMerchant')->name('merchant.active');
        Route::get('merchant/banned', 'ManageMerchantController@bannedMerchant')->name('merchant.banned');
        Route::get('merchant/email-verified', 'ManageMerchantController@emailVerifiedMerchant')->name('merchant.email.verified');
        Route::get('merchant/email-unverified', 'ManageMerchantController@emailUnverifiedMerchant')->name('merchant.email.unverified');
        Route::get('merchant/sms-unverified', 'ManageMerchantController@smsUnverifiedMerchant')->name('merchant.sms.unverified');
        Route::get('merchant/sms-verified', 'ManageMerchantController@smsVerifiedMerchant')->name('merchant.sms.verified');
        Route::get('merchant/with-balance', 'ManageMerchantController@merchantWithBalance')->name('merchant.with.balance');

        Route::get('merchant/{scope}/search', 'ManageMerchantController@search')->name('merchant.search');
        Route::get('merchant/detail/{id}', 'ManageMerchantController@detail')->name('merchant.detail');
        Route::post('merchant/update/{id}', 'ManageMerchantController@update')->name('merchant.update');
        Route::post('merchant/add-sub-balance/{id}', 'ManageMerchantController@addSubBalance')->name('merchant.add.sub.balance');
        Route::get('merchant/send-email/{id}', 'ManageMerchantController@showEmailSingleForm')->name('merchant.email.single');
        Route::post('merchant/send-email/{id}', 'ManageMerchantController@sendEmailSingle')->name('merchant.email.single');
        Route::get('merchant/login/{id}', 'ManageMerchantController@login')->name('merchant.login');
        Route::get('merchant/transactions/{id}', 'ManageMerchantController@transactions')->name('merchant.transactions');
        Route::get('merchant/deposits/{id}', 'ManageMerchantController@deposits')->name('merchant.deposits');
        Route::get('merchant/deposits/via/{method}/{type?}/{merchantId}', 'ManageMerchantController@depositViaMethod')->name('merchant.deposits.method');
        Route::get('merchant/withdrawals/{id}', 'ManageMerchantController@withdrawals')->name('merchant.withdrawals');
        Route::get('merchant/withdrawals/via/{method}/{type?}/{userId}', 'ManageMerchantController@withdrawalsViaMethod')->name('merchant.withdrawals.method');


         // Kyc Manager
         Route::get('manage/kyc', 'KycController@manageKyc')->name('manage.kyc');
         Route::get('edit/kyc/{user_type}', 'KycController@editKyc')->name('edit.kyc');
         Route::post('edit/update/', 'KycController@updateKyc')->name('update.kyc');

         //kyc info manage
            # user kyc
         Route::get('manage/user/pending-kyc', 'KycController@userPendingKyc')->name('kyc.info.user.pending');
         Route::get('manage/user/kyc-details/{user_id}', 'KycController@userKycDetails')->name('kyc.info.user.details');
         Route::post('manage/user/kyc/approve', 'KycController@approveUserKyc')->name('kyc.info.user.approve');
         Route::post('manage/user/kyc/reject', 'KycController@rejectUserKyc')->name('kyc.info.user.reject');
         Route::get('manage/user/approved-kyc', 'KycController@userApprovedKyc')->name('kyc.info.user.approved');
            
         # agent kyc
         Route::get('manage/agent/pending-kyc', 'KycController@agentPendingKyc')->name('kyc.info.agent.pending');
         Route::get('manage/agent/kyc-details/{agent_id}', 'KycController@agentKycDetails')->name('kyc.info.agent.details');
         Route::post('manage/agent/kyc/approve', 'KycController@approveAgentKyc')->name('kyc.info.agent.approve');
         Route::post('manage/agent/kyc/reject', 'KycController@rejectAgentKyc')->name('kyc.info.agent.reject');
         Route::get('manage/agent/approved-kyc', 'KycController@agentApprovedKyc')->name('kyc.info.agent.approved');
          
         # merchant kyc
         Route::get('manage/merchant/pending-kyc', 'KycController@merchantPendingKyc')->name('kyc.info.merchant.pending');
         Route::get('manage/merchant/kyc-details/{merchant_id}', 'KycController@merchantKycDetails')->name('kyc.info.merchant.details');
         Route::post('manage/merchant/kyc/approve', 'KycController@approveMerchantKyc')->name('kyc.info.merchant.approve');
         Route::post('manage/merchant/kyc/reject', 'KycController@rejectMerchantKyc')->name('kyc.info.merchant.reject');
         Route::get('manage/merchant/approved-kyc', 'KycController@merchantApprovedKyc')->name('kyc.info.merchant.approved');

        // Login History
        Route::get('users/login/history/{id}', 'ManageUsersController@userLoginHistory')->name('users.login.history.single');
        Route::get('agent/login/history/{id}', 'ManageAgentController@agentLoginHistory')->name('agent.login.history.single');
        Route::get('merchant/login/history/{id}', 'ManageMerchantController@merchantLoginHistory')->name('merchant.login.history.single');

        Route::get('agent/send-email', 'ManageAgentController@showEmailAllForm')->name('agent.email.all');
        Route::post('agent/send-email', 'ManageAgentController@sendEmailAll')->name('agent.email.send');
        Route::get('agent/email-log/{id}', 'ManageAgentController@emailLog')->name('agent.email.log');
        Route::get('agent/email-details/{id}', 'ManageAgentController@emailDetails')->name('agent.email.details');

        Route::get('users/send-email', 'ManageUsersController@showEmailAllForm')->name('users.email.all');
        Route::post('users/send-email', 'ManageUsersController@sendEmailAll')->name('users.email.send');
        Route::get('users/email-log/{id}', 'ManageUsersController@emailLog')->name('users.email.log');
        Route::get('users/email-details/{id}', 'ManageUsersController@emailDetails')->name('users.email.details');

        Route::get('merchant/send-email', 'ManageMerchantController@showEmailAllForm')->name('merchant.email.all');
        Route::post('merchant/send-email', 'ManageMerchantController@sendEmailAll')->name('merchant.email.send');
        Route::get('merchant/email-log/{id}', 'ManageMerchantController@emailLog')->name('merchant.email.log');
        Route::get('merchant/email-details/{id}', 'ManageMerchantController@emailDetails')->name('merchant.email.details');


        // Deposit Gateway
        Route::name('gateway.')->prefix('gateway')->group(function(){
            // Automatic Gateway
            Route::get('automatic', 'GatewayController@index')->name('automatic.index');
            Route::get('automatic/edit/{alias}', 'GatewayController@edit')->name('automatic.edit');
            Route::post('automatic/update/{code}', 'GatewayController@update')->name('automatic.update');
            Route::post('automatic/remove/{code}', 'GatewayController@remove')->name('automatic.remove');
            Route::post('automatic/activate', 'GatewayController@activate')->name('automatic.activate');
            Route::post('automatic/deactivate', 'GatewayController@deactivate')->name('automatic.deactivate');


            // Manual Methods
            Route::get('manual', 'ManualGatewayController@index')->name('manual.index');
            Route::get('manual/new', 'ManualGatewayController@create')->name('manual.create');
            Route::post('manual/new', 'ManualGatewayController@store')->name('manual.store');
            Route::get('manual/edit/{alias}', 'ManualGatewayController@edit')->name('manual.edit');
            Route::post('manual/update/{id}', 'ManualGatewayController@update')->name('manual.update');
            Route::post('manual/activate', 'ManualGatewayController@activate')->name('manual.activate');
            Route::post('manual/deactivate', 'ManualGatewayController@deactivate')->name('manual.deactivate');
        });


        // DEPOSIT SYSTEM
        Route::name('deposit.')->prefix('deposit')->group(function(){
            Route::get('/', 'DepositController@deposit')->name('list');
            Route::get('pending', 'DepositController@pending')->name('pending');
            Route::get('rejected', 'DepositController@rejected')->name('rejected');
            Route::get('approved', 'DepositController@approved')->name('approved');
            Route::get('successful', 'DepositController@successful')->name('successful');
            Route::get('details/{id}', 'DepositController@details')->name('details');

            Route::post('reject', 'DepositController@reject')->name('reject');
            Route::post('approve', 'DepositController@approve')->name('approve');
            Route::get('via/{method}/{type?}', 'DepositController@depositViaMethod')->name('method');
            Route::get('/{scope}/search', 'DepositController@search')->name('search');
            Route::get('date-search/{scope}', 'DepositController@dateSearch')->name('dateSearch');

        });


        // WITHDRAW SYSTEM
        Route::name('withdraw.')->prefix('withdraw')->group(function(){
            Route::get('pending', 'WithdrawalController@pending')->name('pending');
            Route::get('approved', 'WithdrawalController@approved')->name('approved');
            Route::get('rejected', 'WithdrawalController@rejected')->name('rejected');
            Route::get('log', 'WithdrawalController@log')->name('log');
            Route::get('via/{method_id}/{type?}', 'WithdrawalController@logViaMethod')->name('method');
            Route::get('{scope}/search', 'WithdrawalController@search')->name('search');
            Route::get('date-search/{scope}', 'WithdrawalController@dateSearch')->name('dateSearch');
            Route::get('details/{id}', 'WithdrawalController@details')->name('details');
            Route::post('approve', 'WithdrawalController@approve')->name('approve');
            Route::post('reject', 'WithdrawalController@reject')->name('reject');


            // Withdraw Method
            Route::get('method/', 'WithdrawMethodController@methods')->name('method.index');
            Route::get('method/create', 'WithdrawMethodController@create')->name('method.create');
            Route::post('method/create', 'WithdrawMethodController@store')->name('method.store');
            Route::get('method/edit/{id}', 'WithdrawMethodController@edit')->name('method.edit');
            Route::post('method/edit/{id}', 'WithdrawMethodController@update')->name('method.update');
            Route::post('method/activate', 'WithdrawMethodController@activate')->name('method.activate');
            Route::post('method/deactivate', 'WithdrawMethodController@deactivate')->name('method.deactivate');
        });

        // Report
        Route::get('report/transaction', 'ReportController@transaction')->name('report.transaction');
        Route::get('report/transaction/search', 'ReportController@transactionSearch')->name('report.transaction.search');
        Route::get('report/login/history', 'ReportController@loginHistory')->name('report.login.history');
        Route::get('report/login/ipHistory/{ip}', 'ReportController@loginIpHistory')->name('report.login.ipHistory');
        Route::get('report/email/history', 'ReportController@emailHistory')->name('report.email.history');


        // Admin Support
        Route::get('tickets', 'SupportTicketController@tickets')->name('ticket');
        Route::get('tickets/pending', 'SupportTicketController@pendingTicket')->name('ticket.pending');
        Route::get('tickets/closed', 'SupportTicketController@closedTicket')->name('ticket.closed');
        Route::get('tickets/answered', 'SupportTicketController@answeredTicket')->name('ticket.answered');
        Route::get('tickets/view/{id}', 'SupportTicketController@ticketReply')->name('ticket.view');
        Route::post('ticket/reply/{id}', 'SupportTicketController@ticketReplySend')->name('ticket.reply');
        Route::get('ticket/download/{ticket}', 'SupportTicketController@ticketDownload')->name('ticket.download');
        Route::post('ticket/delete', 'SupportTicketController@ticketDelete')->name('ticket.delete');


        // Language Manager
        Route::get('/language', 'LanguageController@langManage')->name('language.manage');
        Route::post('/language', 'LanguageController@langStore')->name('language.manage.store');
        Route::post('/language/delete/{id}', 'LanguageController@langDel')->name('language.manage.del');
        Route::post('/language/update/{id}', 'LanguageController@langUpdate')->name('language.manage.update');
        Route::get('/language/edit/{id}', 'LanguageController@langEdit')->name('language.key');
        Route::post('/language/import', 'LanguageController@langImport')->name('language.importLang');



        Route::post('language/store/key/{id}', 'LanguageController@storeLanguageJson')->name('language.store.key');
        Route::post('language/delete/key/{id}', 'LanguageController@deleteLanguageJson')->name('language.delete.key');
        Route::post('language/update/key/{id}', 'LanguageController@updateLanguageJson')->name('language.update.key');



        // General Setting
        Route::get('general-setting', 'GeneralSettingController@index')->name('setting.index');
        Route::post('general-setting', 'GeneralSettingController@update')->name('setting.update');
        Route::get('optimize', 'GeneralSettingController@optimize')->name('setting.optimize');

        //module setting
        Route::get('module-setting', 'ModuleSettingController@index')->name('module.setting');
        Route::post('module-setting/update', 'ModuleSettingController@update')->name('module.update');
        // Logo-Icon
        Route::get('setting/logo-icon', 'GeneralSettingController@logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'GeneralSettingController@logoIconUpdate')->name('setting.logo.icon');

        Route::get('setting/qr-code/template', 'GeneralSettingController@qrCodeTemplate')->name('qr.template');
        Route::post('setting/qr-code/template', 'GeneralSettingController@qrCodeTemplateUpdate');

        //Custom CSS
        Route::get('custom-css','GeneralSettingController@customCss')->name('setting.custom.css');
        Route::post('custom-css','GeneralSettingController@customCssSubmit');


        //Cookie
        Route::get('cookie','GeneralSettingController@cookie')->name('setting.cookie');
        Route::post('cookie','GeneralSettingController@cookieSubmit');


        // Plugin
        Route::get('extensions', 'ExtensionController@index')->name('extensions.index');
        Route::post('extensions/update/{id}', 'ExtensionController@update')->name('extensions.update');
        Route::post('extensions/activate', 'ExtensionController@activate')->name('extensions.activate');
        Route::post('extensions/deactivate', 'ExtensionController@deactivate')->name('extensions.deactivate');



        // Email Setting
        Route::get('email-template/global', 'EmailTemplateController@emailTemplate')->name('email.template.global');
        Route::post('email-template/global', 'EmailTemplateController@emailTemplateUpdate')->name('email.template.global');
        Route::get('email-template/setting', 'EmailTemplateController@emailSetting')->name('email.template.setting');
        Route::post('email-template/setting', 'EmailTemplateController@emailSettingUpdate')->name('email.template.setting');
        Route::get('email-template/index', 'EmailTemplateController@index')->name('email.template.index');
        Route::get('email-template/{id}/edit', 'EmailTemplateController@edit')->name('email.template.edit');
        Route::post('email-template/{id}/update', 'EmailTemplateController@update')->name('email.template.update');
        Route::post('email-template/send-test-mail', 'EmailTemplateController@sendTestMail')->name('email.template.test.mail');


        // SMS Setting
        Route::get('sms-template/global', 'SmsTemplateController@smsTemplate')->name('sms.template.global');
        Route::post('sms-template/global', 'SmsTemplateController@smsTemplateUpdate')->name('sms.template.global');
        Route::get('sms-template/setting','SmsTemplateController@smsSetting')->name('sms.templates.setting');
        Route::post('sms-template/setting', 'SmsTemplateController@smsSettingUpdate')->name('sms.template.setting');
        Route::get('sms-template/index', 'SmsTemplateController@index')->name('sms.template.index');
        Route::get('sms-template/edit/{id}', 'SmsTemplateController@edit')->name('sms.template.edit');
        Route::post('sms-template/update/{id}', 'SmsTemplateController@update')->name('sms.template.update');
        Route::post('email-template/send-test-sms', 'SmsTemplateController@sendTestSMS')->name('sms.template.test.sms');

        // SEO
        Route::get('seo', 'FrontendController@seoEdit')->name('seo');


        // Frontend
        Route::name('frontend.')->prefix('frontend')->group(function () {


            Route::get('templates', 'FrontendController@templates')->name('templates');
            Route::post('templates', 'FrontendController@templatesActive')->name('templates.active');
            

            Route::get('frontend-sections/{key}', 'FrontendController@frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'FrontendController@frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'FrontendController@frontendElement')->name('sections.element');
            Route::post('remove', 'FrontendController@remove')->name('remove');

            // Page Builder
            Route::get('manage-pages', 'PageBuilderController@managePages')->name('manage.pages');
            Route::post('manage-pages', 'PageBuilderController@managePagesSave')->name('manage.pages.save');
            Route::post('manage-pages/update', 'PageBuilderController@managePagesUpdate')->name('manage.pages.update');
            Route::post('manage-pages/delete', 'PageBuilderController@managePagesDelete')->name('manage.pages.delete');
            Route::get('manage-section/{id}', 'PageBuilderController@manageSection')->name('manage.section');
            Route::post('manage-section/{id}', 'PageBuilderController@manageSectionUpdate')->name('manage.section.update');
        });
    });
});




/*
|--------------------------------------------------------------------------
| Start User Area
|--------------------------------------------------------------------------
*/


Route::namespace('User')->name('user.')->group(function () {
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register')->middleware('regStatus');
    Route::post('check-mail', 'Auth\RegisterController@checkUser')->name('checkUser');

    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetCodeEmail')->name('password.email');
    Route::get('password/code-verify', 'Auth\ForgotPasswordController@codeVerify')->name('password.code.verify');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/verify-code', 'Auth\ForgotPasswordController@verifyCode')->name('password.verify.code');
});

Route::name('user.')->prefix('user')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('authorization', 'User\AuthorizationController@authorizeForm')->name('authorization');
        Route::get('resend-verify', 'User\AuthorizationController@sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'User\AuthorizationController@emailVerification')->name('verify.email');
        Route::post('verify-sms', 'User\AuthorizationController@smsVerification')->name('verify.sms');
        Route::post('verify-g2fa', 'User\AuthorizationController@g2faVerification')->name('go2fa.verify');

        Route::middleware(['checkStatus'])->group(function () {

            // Deposit
            Route::any('/payment', 'Gateway\PaymentController@deposit')->name('deposit');
            Route::post('payment/insert', 'Gateway\PaymentController@depositInsert')->name('deposit.insert');
            Route::get('payment/preview', 'Gateway\PaymentController@depositPreview')->name('deposit.preview');
            Route::get('payment/confirm', 'Gateway\PaymentController@depositConfirm')->name('deposit.confirm');
            Route::get('payment/manual', 'Gateway\PaymentController@manualDepositConfirm')->name('deposit.manual.confirm');
            Route::post('payment/manual', 'Gateway\PaymentController@manualDepositUpdate')->name('deposit.manual.update');
        Route::get('/invoice/payment', 'Gateway\PaymentController@invoicePayment')->name('payment.invoice');
            
            Route::namespace('User')->group(function(){
                Route::get('dashboard', 'UserController@home')->name('home');
                Route::get('check/insight', 'UserController@checkInsight')->name('check.insight');
                
                Route::get('profile-setting', 'UserController@profile')->name('profile.setting');
                Route::post('profile-setting', 'UserController@submitProfile');
                Route::get('change-password', 'UserController@changePassword')->name('change.password');
                Route::post('change-password', 'UserController@submitPassword');
                
                //2FA
                Route::get('twofactor', 'UserController@show2faForm')->name('twofactor');
                Route::post('twofactor/enable', 'UserController@create2fa')->name('twofactor.enable');
                Route::post('twofactor/disable', 'UserController@disable2fa')->name('twofactor.disable');
                Route::get('payment/history', 'UserController@depositHistory')->name('deposit.history');
                

                // Withdraw
                Route::get('/withdraw', 'UserWithdrawController@withdrawMoney')->name('withdraw.money');
                Route::get('/withdraw/methods', 'UserWithdrawController@withdrawMethods')->name('withdraw.methods');
                Route::get('/add/withdraw-method', 'UserWithdrawController@addWithdrawMethod')->name('add.withdraw.method');
                Route::post('/add/withdraw-method', 'UserWithdrawController@withdrawMethodStore');
                Route::get('/edit/withdraw-method/{id}', 'UserWithdrawController@withdrawMethodEdit')->name('withdraw.edit');
                Route::post('/withdraw-method/update', 'UserWithdrawController@withdrawMethodUpdate')->name('withdraw.update');
                Route::post('/withdraw/store', 'UserWithdrawController@withdrawStore')->name('withdraw.store')->middleware('kyc');
                Route::get('/withdraw/preview', 'UserWithdrawController@withdrawPreview')->name('withdraw.preview');
                Route::post('/withdraw/preview', 'UserWithdrawController@withdrawSubmit');
                Route::get('/withdraw/history', 'UserWithdrawController@withdrawLog')->name('withdraw.history');
    
                //Transfer money
                Route::get('/transfer/money', 'UserOperationController@transfer')->name('transfer');
                Route::post('/transfer/money', 'UserOperationController@transferMoney')->middleware('kyc');
                Route::post('/user/exist', 'UserOperationController@checkUser')->name('check.exist');
    
                //more wallets
                Route::get('/all/wallets', 'UserController@allWallets')->name('all.wallets');

                //Request money
                Route::get('/requests', 'UserOperationController@requests')->name('requests');
                Route::get('/request/money', 'UserOperationController@requestMoney')->name('request.money');
                Route::post('/request/money', 'UserOperationController@confirmRequest')->middleware('kyc');
                Route::post('/accept/request', 'UserOperationController@requestAccept')->name('request.accept');
                Route::post('/accept/reject', 'UserOperationController@requestReject')->name('request.reject');
    
    
                //Invoice
                Route::get('/invoice/list', 'InvoiceController@invoices')->name('invoices');
                Route::get('/invoice/create', 'InvoiceController@createInvoice')->name('invoice.create');
                Route::post('/invoice/create', 'InvoiceController@createInvoiceConfirm');
                Route::get('/invoice/edit/{invoice_num}', 'InvoiceController@editInvoice')->name('invoice.edit');
                Route::post('/invoice/update/', 'InvoiceController@updateInvoice')->name('invoice.update');
                Route::get('/send-to-mail/invoice/{invoice_num}', 'InvoiceController@sendInvoiceToMail')->name('invoice.send.mail');
                Route::get('/publish/invoice/{invoice_num}', 'InvoiceController@publishInvoice')->name('invoice.publish');
                Route::post('/discard/invoice/{invoice_num}', 'InvoiceController@discardInvoice')->name('invoice.discard');
                
                //money out
                Route::post('/agent/exist', 'MoneyOutController@checkUser')->name('agent.check.exist');
                Route::get('/money-out', 'MoneyOutController@moneyOut')->name('money.out');
                Route::post('/money-out', 'MoneyOutController@moneyOutConfirm')->middleware('kyc');
    
                //make payment
                Route::post('/merchant/exist', 'MakePaymentController@checkUser')->name('merchant.check.exist');
                Route::get('/make-payment', 'MakePaymentController@paymentFrom')->name('payment');
                Route::post('/make-payment', 'MakePaymentController@paymentConfirm')->middleware('kyc');
    
                //Voucher
                Route::get('/voucher/list', 'VoucherController@userVoucherList')->name('voucher.list');
                Route::get('/create/voucher', 'VoucherController@userVoucher')->name('voucher.create');
                Route::post('/create/voucher', 'VoucherController@userVoucherCreate')->middleware('kyc');
                Route::get('/voucher/redeem', 'VoucherController@userVoucherRedeem')->name('voucher.redeem');
                Route::post('/voucher/redeem', 'VoucherController@userVoucherRedeemConfirm');
                Route::get('/voucher/redeem/log', 'VoucherController@userVoucherRedeemLog')->name('voucher.redeem.log');
    
                //Exchange money
                Route::get('/exchange/money', 'MoneyExchangeController@exchangeForm')->name('exchange.money');
                Route::post('/exchange/money', 'MoneyExchangeController@exchangeConfirm');

                //kyc
                Route::get('/fill-up/kyc', 'UserController@kycForm')->name('kyc');
                Route::post('/fill-up/kyc', 'UserController@kycFormSubmit');

                 //QR code
                Route::get('/qr-code', 'UserController@qrCodeGenerate')->name('qr');
                Route::get('/download/qr-code/{unique_code}', 'UserController@downLoadQrJpg')->name('qr.jpg');
    
                //Trx history
                Route::get('/transaction/history', 'UserController@trxHistory')->name('transactions');
            });
        });
    });
});


/*
|--------------------------------------------------------------------------
| Start Agent Area
|--------------------------------------------------------------------------
*/


Route::middleware('agent')->name('agent.')->prefix('agent')->group(function () {
    Route::any('/payment', 'Gateway\PaymentController@deposit')->name('deposit');
    Route::post('payment/insert', 'Gateway\PaymentController@depositInsert')->name('deposit.insert');
    Route::get('payment/preview', 'Gateway\PaymentController@depositPreview')->name('deposit.preview');
    Route::get('payment/confirm', 'Gateway\PaymentController@depositConfirm')->name('deposit.confirm');
    Route::get('payment/manual', 'Gateway\PaymentController@manualDepositConfirm')->name('deposit.manual.confirm');
    Route::post('payment/manual', 'Gateway\PaymentController@manualDepositUpdate')->name('deposit.manual.update');
});

Route::namespace('Agent')->name('agent.')->prefix('agent')->group(function () {
    Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register')->middleware('regStatus');
    Route::post('check-mail', 'Auth\RegisterController@checkUser')->name('checkUser');

    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetCodeEmail')->name('password.email');
    Route::get('password/code-verify', 'Auth\ForgotPasswordController@codeVerify')->name('password.code.verify');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/verify-code', 'Auth\ForgotPasswordController@verifyCode')->name('password.verify.code');

    Route::middleware('agent')->group(function () {
        Route::get('authorization', 'AuthorizationController@authorizeForm')->name('authorization');
        Route::get('resend-verify', 'AuthorizationController@sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'AuthorizationController@emailVerification')->name('verify.email');
        Route::post('verify-sms', 'AuthorizationController@smsVerification')->name('verify.sms');
        Route::post('verify-g2fa', 'AuthorizationController@g2faVerification')->name('go2fa.verify');

        Route::middleware(['checkAgentStatus'])->group(function () {
            Route::get('dashboard', 'AgentController@home')->name('home');
            Route::get('check/insight', 'AgentController@checkInsight')->name('check.insight');
                
            Route::get('profile-setting', 'AgentController@profile')->name('profile.setting');
            Route::post('profile-setting', 'AgentController@submitProfile');
            Route::get('change-password', 'AgentController@changePassword')->name('change.password');
            Route::post('change-password', 'AgentController@submitPassword');
            
            //2FA
            Route::get('twofactor', 'AgentController@show2faForm')->name('twofactor');
            Route::post('twofactor/enable', 'AgentController@create2fa')->name('twofactor.enable');
            Route::post('twofactor/disable', 'AgentController@disable2fa')->name('twofactor.disable');

            //Money In
            Route::post('/user/check', 'MoneyInController@checkUser')->name('user.check.exist');
            Route::get('/money-in', 'MoneyInController@moneyInForm')->name('money.in');
            Route::post('/money-in', 'MoneyInController@confirmMoneyIn');

            
             //more wallets
             Route::get('/all/wallets', 'AgentController@allWallets')->name('all.wallets');
              
            // Withdraw
            Route::get('/withdraw/money', 'AgentWithdrawController@withdrawMoney')->name('withdraw.money');
            Route::get('/withdraw/methods', 'AgentWithdrawController@withdrawMethods')->name('withdraw.methods');
            Route::get('/add/withdraw-method', 'AgentWithdrawController@addWithdrawMethod')->name('add.withdraw.method');
            Route::post('/add/withdraw-method', 'AgentWithdrawController@withdrawMethodStore');
            Route::get('/edit/withdraw-method/{id}', 'AgentWithdrawController@withdrawMethodEdit')->name('withdraw.edit');
            Route::post('/withdraw-method/update', 'AgentWithdrawController@withdrawMethodUpdate')->name('withdraw.update');
            Route::post('/withdraw/store', 'AgentWithdrawController@withdrawStore')->name('withdraw.store')->middleware('kyc');
            Route::get('/withdraw/preview', 'AgentWithdrawController@withdrawPreview')->name('withdraw.preview');
            Route::post('/withdraw/preview', 'AgentWithdrawController@withdrawSubmit');
            Route::get('/withdraw/history', 'AgentWithdrawController@withdrawLog')->name('withdraw.history');

             //kyc
             Route::get('/fill-up/kyc', 'AgentController@kycForm')->name('kyc');
             Route::post('/fill-up/kyc', 'AgentController@kycFormSubmit');

            //QR code
            Route::get('/qr-code', 'AgentController@qrCodeGenerate')->name('qr');
            Route::get('/download/qr-code/{unique_code}', 'AgentController@downLoadQrJpg')->name('qr.jpg');

            //trx history
            Route::get('/commission-log', 'AgentController@commissionLog')->name('commission.log');
            Route::get('/transaction/history', 'AgentController@trxHistory')->name('transactions');
            Route::get('/add-money/history', 'AgentController@depositHistory')->name('deposit.history');

        });
    });
});

/*
|--------------------------------------------------------------------------
| Start Merchant Area
|--------------------------------------------------------------------------
*/

Route::namespace('Merchant')->name('merchant.')->prefix('merchant')->group(function () {
    Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register')->middleware('regStatus');
    Route::post('check-mail', 'Auth\RegisterController@checkUser')->name('checkUser');

    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetCodeEmail')->name('password.email');
    Route::get('password/code-verify', 'Auth\ForgotPasswordController@codeVerify')->name('password.code.verify');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/verify-code', 'Auth\ForgotPasswordController@verifyCode')->name('password.verify.code');
    Route::middleware('merchant')->group(function () {
        Route::get('authorization', 'AuthorizationController@authorizeForm')->name('authorization');
        Route::get('resend-verify', 'AuthorizationController@sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'AuthorizationController@emailVerification')->name('verify.email');
        Route::post('verify-sms', 'AuthorizationController@smsVerification')->name('verify.sms');
        Route::post('verify-g2fa', 'AuthorizationController@g2faVerification')->name('go2fa.verify');

        Route::middleware(['checkMerchantStatus'])->group(function () {
            Route::get('dashboard', 'MerchantController@home')->name('home');
            Route::get('check/insight', 'MerchantController@checkInsight')->name('check.insight');
            Route::get('business/api/key', 'MerchantController@apiKey')->name('api.key');
            Route::post('generate/api/key', 'MerchantController@generateApiKey')->name('generate.key');

            Route::get('profile-setting', 'MerchantController@profile')->name('profile.setting');
            Route::post('profile-setting', 'MerchantController@submitProfile');
            Route::get('change-password', 'MerchantController@changePassword')->name('change.password');
            Route::post('change-password', 'MerchantController@submitPassword');

            //2FA
            Route::get('twofactor', 'MerchantController@show2faForm')->name('twofactor');
            Route::post('twofactor/enable', 'MerchantController@create2fa')->name('twofactor.enable');
            Route::post('twofactor/disable', 'MerchantController@disable2fa')->name('twofactor.disable');

            //all wallets
            Route::get('/all/wallets', 'MerchantController@allWallets')->name('all.wallets');

           // Withdraw
           Route::get('/withdraw/money', 'MerchantWithdrawController@withdrawMoney')->name('withdraw.money');
           Route::get('/withdraw/methods', 'MerchantWithdrawController@withdrawMethods')->name('withdraw.methods');
           Route::get('/add/withdraw-method', 'MerchantWithdrawController@addWithdrawMethod')->name('add.withdraw.method');
           Route::post('/add/withdraw-method', 'MerchantWithdrawController@withdrawMethodStore');
           Route::get('/edit/withdraw-method/{id}', 'MerchantWithdrawController@withdrawMethodEdit')->name('withdraw.edit');
           Route::post('/withdraw-method/update', 'MerchantWithdrawController@withdrawMethodUpdate')->name('withdraw.update');
           Route::post('/withdraw/store', 'MerchantWithdrawController@withdrawStore')->name('withdraw.store')->middleware('kyc');
           Route::get('/withdraw/preview', 'MerchantWithdrawController@withdrawPreview')->name('withdraw.preview');
           Route::post('/withdraw/preview', 'MerchantWithdrawController@withdrawSubmit');
           Route::get('/withdraw/history', 'MerchantWithdrawController@withdrawLog')->name('withdraw.history');

            //kyc
            Route::get('/fill-up/kyc', 'MerchantController@kycForm')->name('kyc');
            Route::post('/fill-up/kyc', 'MerchantController@kycFormSubmit');

            //QR code
            Route::get('/qr-code', 'MerchantController@qrCodeGenerate')->name('qr');
            Route::get('/download/qr-code/{unique_code}', 'MerchantController@downLoadQrJpg')->name('qr.jpg');

           Route::get('/transaction/history', 'MerchantController@trxHistory')->name('transactions');

        });
    });
});



Route::get('/invoice/payment/{invoice_num}', 'User\InvoiceController@invoicePayment')->name('invoice.payment');
Route::get('/invoice/payment/confirm/{invoice_num}', 'User\InvoiceController@invoicePaymentConfirm')->name('invoice.payment.confirm');

Route::get('/invoice/download/{invoice_num}', 'InvoiceController@downloadInvoice')->name('invoice.download');

Route::get('/api-documentation', 'SiteController@apiDocumentation')->name('documentation');
Route::get('/contact', 'SiteController@contact')->name('contact');
Route::post('/contact', 'SiteController@contactSubmit');
Route::get('/change/{lang?}', 'SiteController@changeLanguage')->name('lang');

Route::get('/cookie/accept', 'SiteController@cookieAccept')->name('cookie.accept');

Route::get('/announces', 'SiteController@blog')->name('blog');
Route::get('announce-details/{id}/{slug}', 'SiteController@blogDetails')->name('blog.details');

Route::get('policy-terms/{slug}/{id}', 'SiteController@policyAndTerms')->name('links');

Route::get('placeholder-image/{size}', 'SiteController@placeholderImage')->name('placeholder.image');
Route::get('qr/scan/{unique_code}','SiteController@qrScan')->name('qr.scan');

Route::get('/{slug}', 'SiteController@pages')->name('pages');
Route::get('/', 'SiteController@index')->name('home');