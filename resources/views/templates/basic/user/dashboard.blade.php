@extends($activeTemplate.'layouts.user_master')
@section('content')

<div class="col-lg-9 mt-lg-0 mt-5">
    @if(auth()->user()->kyc_status == 0 ||  auth()->user()->kyc_status == 2 ||  auth()->user()->kyc_status == 3 && $userKyc->status == 1)
    <div class="d-user-notification d-flex flex-wrap align-items-center mb-5 {{$kyc['bgColor']}}">
        <div class="icon  {{ $kyc['iconBg']}}">
            <i class="las la-folder"></i> 
        </div>
        <div class="content">
            <p class="text-white"><b>@lang($kyc['msg'])</b> @if(auth()->user()->kyc_status == 3) <a class="btn btn--warning btn-sm reason" href="javascript:void(0)" data-reasons="{{auth()->user()->kyc_reject_reasons}}">@lang('See reasons')</a> @endif</p>
        </div>
        <div class="right text-sm-end text-center">
            <a href="{{$kyc['route']}}" class="btn btn-sm btn--base {{ $kyc['btnBg']}} ">@lang($kyc['btnTxt'])</a>
        </div>
    </div>
    @endif
   
   <div class="d-lg-flex d-none justify-content-between">
    <h6 class="mb-3">@lang('Wallets')</h6>
    <a href="{{route('user.all.wallets')}}" class="font-size--14px text--base">@lang('More Wallets') <i class="las la-long-arrow-alt-right"></i></a>
   </div>
    <div class="row mb-5 gy-4 d-lg-flex d-none">
        @foreach ($wallets as $wallet)
            <div class="col-lg-4 col-md-6">
                <div class="d-widget curve--shape">
                    <div class="d-widget__content">
                        <i class="las la-wallet"></i>
                        <h2 class="d-widget__amount fw-normal">{{ $wallet->currency->currency_symbol }} {{showAmount($wallet->balance)}} {{$wallet->currency->currency_code}}</h2>
                    </div>
                    <div class="d-widget__footer d-flex flex-wrap justify-content-between">
                        <a href="{{url('user/transfer/money?wallet='.$wallet->currency->currency_code)}}" class="font-size--14px">@lang('Transfer Money')<i class="las la-long-arrow-alt-right"></i></a>
                    </div>
                </div><!-- d-widget end -->
            </div>
        @endforeach
    </div><!-- row end -->

   

    <div class="custom--card mb-5 d-lg-block d-none">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-6">
                    <h6>@lang('Quick Links')</h6>
                </div>
            </div>
            <div class="row justify-content-center gy-4">

                <div class="col-lg-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.deposit')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-credit-card"></i></span>
                        <p class="caption">@lang('Add Money')</p>
                    </a><!-- quick-link-card end -->
                </div>

                <div class="col-lg-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.money.out')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-hand-holding-usd"></i></span>
                        <p class="caption">@lang('Money Out')</p>
                    </a><!-- quick-link-card end -->
                </div>
              
                <div class="col-lg-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.payment')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-shopping-bag"></i></span>
                        <p class="caption">@lang('Make Payment')</p>
                    </a><!-- quick-link-card end -->
                </div>

                <div class="col-lg-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.exchange.money')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-exchange-alt"></i></span>
                        <p class="caption">@lang('Exchange')</p>
                    </a><!-- quick-link-card end -->
                </div>
                <div class="col-lg-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.voucher.create')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-share-square"></i></span>
                        <p class="caption">@lang('Create Voucher')</p>
                    </a><!-- quick-link-card end -->
                </div>
                <div class="col-lg-2 col-sm-3 col-6 text-center">
                    <a href="{{route('user.invoices')}}" class="quick-link-card">
                        <span class="icon"><i class="las la-receipt"></i></span>
                        <p class="caption">@lang('Invoice')</p>
                    </a><!-- quick-link-card end -->
                </div>
            </div><!-- row end -->
        </div>
    </div><!-- custom--card end -->

    <div class="card custom--card border-0">
        <div class="card-header border">
            <div class="row align-items-center">
                <div class="col-8 py-3">
                    <h6>@lang('Latest Transactions')</h6>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="accordion table--acordion" id="transactionAccordion">
                @forelse ($histories as $history)
                    <div class="accordion-item transaction-item {{$history->trx_type == '-' ? 'sent-item':'rcv-item'}}">
                        <h2 class="accordion-header" id="h-{{$loop->iteration}}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-{{$loop->iteration}}" aria-expanded="false" aria-controls="c-1">
                            <div class="col-lg-3 col-sm-4 col-6 order-1 icon-wrapper">
                                <div class="left">
                                    <div class="icon">
                                        <i class="las la-long-arrow-alt-right"></i>
                                    </div>
                                    <div class="content">
                                        <h6 class="trans-title">{{__(ucwords(str_replace('_',' ',$history->operation_type)))}}</h6>
                                        <span class="text-muted font-size--14px mt-2">{{showDateTime($history->created_at,'M d Y @g:i:a')}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-5 col-12 order-sm-2 order-3 content-wrapper mt-sm-0 mt-3">
                                <p class="text-muted font-size--14px"><b>{{__($history->details)}} {{$history->receiver ? @$history->receiver->username : ''  }}</b></p>
                            </div>
                            <div class="col-lg-3 col-sm-3 col-6 order-sm-3 order-2 text-end amount-wrapper">
                                <p><b>{{$history->operation_type =='request_money' ? showAmount($history->amount - $history->charge):showAmount($history->amount + $history->charge)}} {{$history->currency->currency_code}}</b></p>
                                
                            </div>
                        </button>
                        </h2>
                        <div id="c-{{$loop->iteration}}" class="accordion-collapse collapse" aria-labelledby="h-1" data-bs-parent="#transactionAccordion">
                            <div class="accordion-body">
                                <ul class="caption-list">
                                    <li>
                                        <span class="caption">@lang('Transaction ID')</span>
                                        <span class="value">{{$history->trx}}</span>
                                    </li>
                                    <li>
                                        <span class="caption">@lang('Wallet')</span>
                                        <span class="value">{{$history->currency->currency_code}}</span>
                                    </li>
                                
                                    <li>
                                        <span class="caption">@lang('Amount')</span>
                                        <span class="value">{{showAmount($history->amount)}} {{$history->currency->currency_code}}</span>
                                    </li>
                                    <li>
                                        <span class="caption">@lang('Charge')</span>
                                        <span class="value">{{showAmount($history->charge)}} {{$history->currency->currency_code}}</span>
                                    </li>
                                    <li>
                                        <span class="caption">@lang('Remaining Balance')</span>
                                        <span class="value">{{showAmount($history->post_balance)}} {{$history->currency->currency_code}}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div><!-- transaction-item end -->
                    @empty
                    <div class="accordion-body text-center">
                        <h4 class="text--muted">@lang('No transaction found')</h4>
                    </div>
                    @endforelse
            </div>
        </div>
      
    </div>

</div>

<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h6>@lang('Reasons')</h6></div>
            <div class="modal-body text-center my-4">
                <p class="reason"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary btn-sm" data-bs-dismiss="modal">@lang('close')</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    'use strict';
    (function ($) {
        $('.reason').on('click', function () {
               $('#reasonModal').find('.reason').text($(this).data('reasons'))
               $('#reasonModal').modal('show')
            });
    })(jQuery);
</script>
@endpush