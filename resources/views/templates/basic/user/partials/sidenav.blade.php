@php
    $balanceWallets = App\Models\Wallet::where('user_id',auth()->id())->where('user_type','USER')->where('balance','>',0)->with('currency')->get(['currency_id','balance'])
@endphp
<div class="col-lg-3">
    
    <div class="custom--card mb-4">
        <div class="card-body">
            <h6 class="mb-4 font-size--16px">{{$general->sitename}} @lang('Balance')</h6>
            <h2 class="fw-normal main-balance">{{$general->cur_sym}}{{showAmount(array_sum($totalBalance))}} {{$general->cur_text}}<sup>*</sup></h2>
          
                <div class="d-flex flex-wrap align-items-center justify-content-between mt-3">
                    <p class="text-muted">@lang('Availabe')</p>
                    <a href="{{route('user.all.wallets')}}" class="font-size--14px text--base d-lg-none">@lang('More Wallets') <i class="las la-long-arrow-alt-right"></i></a>
                </div>
                <ul class="caption-list-two mt-2">
                    @foreach ($balanceWallets as $wallet)
                    <li>
                        <span class="caption">{{$wallet->currency->currency_code}}</span>
                        <span class="value">{{$wallet->currency->currency_symbol}} {{showAmount($wallet->balance)}}</span>
                    </li>
                    @endforeach
                    
                </ul>
                <p class="font-size--12px mt-2">* @lang('Estimate total balance based on the most recent conversion rate .')</p>

            <a href="{{route('user.transfer')}}" class="btn btn--base btn-sm d-block mt-4">@lang('Transfer Money')</a>
        </div>
    </div><!-- custom--card end -->
    <div class="custom--card mobile-quick-links mb-5">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-6">
                    <h6>@lang('Quick Links')</h6>
                </div>
            </div>
            <div class="row justify-content-center gy-4">

                <div class="col-md-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.deposit')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-credit-card"></i></span>
                        <p class="caption">@lang('Add Money')</p>
                    </a><!-- quick-link-card end -->
                </div>

                <div class="col-md-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.money.out')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-hand-holding-usd"></i></span>
                        <p class="caption">@lang('Money Out')</p>
                    </a><!-- quick-link-card end -->
                </div>
              
                <div class="col-md-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.payment')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-shopping-bag"></i></span>
                        <p class="caption">@lang('Make Payment')</p>
                    </a><!-- quick-link-card end -->
                </div>

                <div class="col-md-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.exchange.money')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-exchange-alt"></i></span>
                        <p class="caption">@lang('Exchange')</p>
                    </a><!-- quick-link-card end -->
                </div>
                <div class="col-md-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.voucher.create')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-share-square"></i></span>
                        <p class="caption">@lang('Create Voucher')</p>
                    </a><!-- quick-link-card end -->
                </div>
                <div class="col-md-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.invoices')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-receipt"></i></span>
                        <p class="caption">@lang('Invoice')</p>
                    </a><!-- quick-link-card end -->
                </div>
            </div><!-- row end -->
        </div>
    </div><!-- custom--card end -->
    <div class="row align-items-center mb-3">
        <div class="col-6">
            <h6 class="fw-normal">@lang('Insights')</h6>
        </div>
        <div class="col-6 text-end">
            <div class="dropdown custom--dropdown has--arrow">
                <button class="text-btn dropdown-toggle font-size--14px text--base" type="button" id="latestAcvitiesButton" data-bs-toggle="dropdown" aria-expanded="false">
                    @lang('Select')
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="latestAcvitiesButton">
                    <li><a class="dropdown-item money" data-day="7" href="javascript:void(0)">@lang('Last 7 days')</a></li>
                    <li><a class="dropdown-item money" data-day="15" href="javascript:void(0)">@lang('Last 15 days')</a></li>
                    <li><a class="dropdown-item money" data-day="31" href="javascript:void(0)">@lang('Last month')</a></li>
                    <li><a class="dropdown-item money" data-day="365" href="javascript:void(0)">@lang('Last year')</a></li>
                </ul>
            </div>
        </div>
    </div><!-- row end -->
    <div class="custom--card mb-4">
        <div class="card-body">
            <h6 class="mb-4 font-size--16px">@lang('Money in') <small class="text--muted last-time">( @lang('last 7 days') )</small></h6> 
            <h3 class="fw-normal money-in">{{$general->cur_sym}}{{showAmount($totalMoneyInOut['totalMoneyIn'])}} {{$general->cur_text}}<sup>*</sup></h3>
            <a href="{{url('user/transaction/history?type=money-in')}}" class="text--link text-muted font-size--14px">@lang('Total received')</a>
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4">
                <a href="{{route('user.request.money')}}" class="font-size--14px fw-bold">@lang('Request Money')</a>
                <a href="{{url('user/transaction/history?type=money-in')}}" class="font-size--14px fw-bold">@lang('View Transactions')</a>
            </div>
        </div>
    </div><!-- custom--card end -->
    <div class="custom--card">
        <div class="card-body">
            <h6 class="mb-4 font-size--16px">@lang('Money out') <small class="text--muted last-time">( @lang('last 7 days') )</small> </h6>
            <h3 class="fw-normal money-out">{{$general->cur_sym}}{{showAmount($totalMoneyInOut['totalMoneyOut'])}} {{$general->cur_text}}<sup>*</sup></h3>
            <a href="{{url('user/transaction/history?type=money-out')}}" class="text--link text-muted font-size--14px">@lang('Total spent')</a>
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-4">
                <a href="{{route('user.transfer')}}" class="font-size--14px fw-bold">@lang('Send Money')</a>
                <a href="{{url('user/transaction/history?type=money-out')}}" class="font-size--14px fw-bold">@lang('View Transactions')</a>
            </div>
        </div>
    </div><!-- custom--card end -->
</div>


@push('script')
     <script>
            'use strict';
            (function ($) {
                $('.money').on('click', function () {
                    var url = '{{ route('user.check.insight') }}';
                    var day = $(this).data('day');
                    var text = $(this).text();
                    var data = {
                        day:day
                    }
                    $.get(url,data,function(response) {
                       if(response.error){
                           notify('error',response.error)
                           return false;
                       }
                       var moneyIn = response.totalMoneyIn;
                       var moneyOut = response.totalMoneyOut;
                       var curSym = '{{$general->cur_sym}}';
                       var curTxt = '{{$general->cur_text}}';

                       $('.money-in').text(curSym+moneyIn.toFixed(2)+' '+curTxt);
                       $('.money-out').text(curSym+moneyOut.toFixed(2)+' '+curTxt);
                       $('.last-time').text('( '+text.toLowerCase()+' )');
                       $('#latestAcvitiesButton').text(text);               
                    });
                });
            })(jQuery);
     </script>
@endpush