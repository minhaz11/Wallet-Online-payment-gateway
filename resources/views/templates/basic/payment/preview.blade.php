@extends($activeTemplate.'layouts.'.strtolower(userGuard()['type']).'_master')
@php
    $class = '';
    if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT'){
        $class = 'mt-5';
    } 
@endphp
@section('content')
<div class="row justify-content-center {{$class}}">
    <div class="col-xl-6 col-lg-6 col-md-8">
        <div class="d-widget shadow-sm">
            <div class="d-widget__header text-center">
                <h6>{{__($data->gateway->name)}}</h6>
            </div>
            <div class="d-widget__content">
                <div class="w-50 mx-auto">
                    <img src="{{ $data->gatewayCurrency()->methodImage() }}" alt="image" class="rounded-2">
                </div>
                <ul class="cmn-list-two text-center mt-4">
                    <li>
                        @lang('Amount'):
                        <strong>{{showAmount($data->amount)}} </strong> {{$data->convertedCurr->currency_code}}
                    </li>
                    <li>
                        @lang('Charge'):
                        <strong>{{showAmount($data->charge/$data->convertedCurr->rate)}}</strong> {{$data->convertedCurr->currency_code}}
                    </li>
                    <li>
                        @lang('Payable'): <strong> {{showAmount($data->amount + $data->charge/$data->convertedCurr->rate)}}</strong> {{$data->convertedCurr->currency_code}}
                    </li>
                   
                    <li>
                        @lang('In') {{$data->baseCurrency()}}:
                        <strong>{{showAmount($data->final_amo)}} {{$data->method_currency}}</strong>
                    </li>


                    @if($data->gateway->crypto==1)
                        <li>
                            @lang('Conversion with')
                            <b> {{ __($data->method_currency) }}</b> @lang('and final value will Show on next step')
                        </li>
                    @endif
                </ul>
            </div>
            <div class="d-widget__footer text-center border-0 pb-3">
                
                @if( 1000 >$data->method_code)
                   <a href="{{route(strtolower(userGuard()['type']).'.deposit.confirm')}}" class="btn btn-md w-100 d-block btn--base">@lang('Pay Now') <i class="las la-long-arrow-alt-right"></i></a>
                @else
                  <a href="{{route(strtolower(userGuard()['type']).'.deposit.manual.confirm')}}" class="btn btn-md w-100 d-block btn--base">@lang('Pay Now') <i class="las la-long-arrow-alt-right"></i></a>
                 @endif
            </div>
        </div><!-- d-widget end -->
    </div>
</div>
@endsection



