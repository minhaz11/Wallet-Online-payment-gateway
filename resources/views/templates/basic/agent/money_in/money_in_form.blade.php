@extends($activeTemplate.'layouts.agent_master')

@section('content')

    <div class="card custom--card border-0 mt-5">
        <div class="card-body p-4">
            <form action="" method="POST" id="form">
            <div class="row justify-content-center">
                <div class="col-lg-8 ">
                        @csrf
                        <div class="d-widget shadow-sm">
                            <div class="d-widget__header">
                                <h6>@lang('Money In Detail')</h4>
                            </div>
                            <div class="d-widget__content px-5">
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Select Wallet')<span class="text--danger">*</span></label>
                                            <select class="select style--two currency" name="wallet_id" required>
                                                <option value="" selected>@lang('Select Wallet')</option>
                                                @foreach ($wallets as $wallet)
                                                <option value="{{$wallet->id}}" data-code="{{$wallet->currency->currency_code}}" data-rate="{{$wallet->currency->rate}}" data-type="{{$wallet->currency->currency_type}}">{{$wallet->currency->currency_code}}</option>
                                                @endforeach
                                            </select>
                                          </div>
                                       
                                        <span class="charge" data-charge="{{$moneyInCharge}}"></span>
                                        
                                    </div><!-- row end -->
                                </div>


                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('User Username/E-mail')<span class="text--danger">*</span> </label>
                                            <input type="text" class="form--control style--two checkUser" name="user" placeholder="@lang('User Username/E-mail')" required value="{{old('user')}}">
                                        </div>
                                        <label class="exist text-end"></label>
                                    </div><!-- row end -->
                                </div>

                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Amount')<span class="text--danger">*</span> </label>
                                            <input type="text" class="form--control style--two amount" name="amount" placeholder="@lang('Amount')" required value="{{old('amount')}}">
                                        </div>
                                        <label> 
                                            <span class="text--warning min">@lang('Min: '){{getAmount($moneyInCharge->min_limit)}} {{defaultCurrency()}} --</span>
                                            <span class="text--warning max">@lang('Max: '){{getAmount($moneyInCharge->max_limit)}} {{defaultCurrency()}}</span>
                                         </label>
                                    </div><!-- row end -->
                                </div>
                        
                               
                                @if (agent()->ts == 1)
                                    <div class="p-4 border mb-4">
                                        <div class="row">
                                            <div class="col-lg-12 form-group">
                                                <label class="mb-0">@lang('Google Authenticator')<span class="text--danger">*</span> </label>
                                                <input type="text" class="form--control style--two" name="ts" placeholder="@lang('Your google authentication code')" required>
                                            </div>
                                        </div><!-- row end -->
                                    </div>  
                                @endif
                                
                            </div>
                        </div>  
                   </div>
               </div>
            <div class="row">
                <div class="text-center">
                    <button type="button" class="btn btn-md btn--base mt-4 money_in">@lang('Money In')</button>
                </div>
            </div>

                <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered " role="document">
                    <div class="modal-content"> 
                        <div class="modal-header">
                            <h6 class="modal-title">@lang('Cash in Preview')</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                            <div class="modal-body text-center p-0">
                                <div class="d-widget border-start-0 shadow-sm">
                                    <div class="d-widget__content">
                                        <ul class="cmn-list-two text-center mt-4">
                                            <li class="list-group-item">@lang('Total Amount'): <strong class="total_amount"></strong></li>
                                            <li class="list-group-item">@lang('Total Charge'): <strong class="total_charge"></strong></li> 
                                            <li class="list-group-item">@lang('Payable'): <strong class="payable"></strong></li> 
                                        </ul>
                                    </div>
                                    <div class="d-widget__footer text-center border-0 pb-3">
                                        <button type="submit" class="btn btn-md w-100 d-block btn--base req_confirm">@lang('Confirm') <i class="las la-long-arrow-alt-right"></i></button>
                                    </div>
                                </div>
                            </div>
                    
                    </div>
                    </div>
                </div>
          </form>
        </div>
    </div>

@endsection

@push('script')
     <script>
            'use strict';
            (function ($) {
                function chargeCalc(amount,chargeData,rate,code) { 
                    var percentCharge = amount * chargeData.percent_charge/100;
                    var fixedCharge = chargeData.fixed_charge/rate;
                    var totalCharge = fixedCharge+percentCharge;
                   
                    var totalAmount = amount + totalCharge 
                   
                    $('#confirm').find('.total_amount').text(amount+' '+code)
                    $('#confirm').find('.total_charge').text(totalCharge.toFixed(2)+' '+code)
                    $('#confirm').find('.payable').text(totalAmount.toFixed(2)+' '+code)
                    $('#confirm').modal('show')
                   
                }

                $('.checkUser').on('focusout',function(e){
                    var url = '{{ route('agent.user.check.exist') }}';
                    var value = $(this).val();
                    var token = '{{ csrf_token() }}';
                    var data = {user:value,_token:token}
                    
                    $.post(url,data,function(response) {
                        if(response['data'] != null){
                            if($('.exist').hasClass('text--danger')){
                               $('.exist').removeClass('text--danger');
                            }
                            $('.exist').text(`Valid user to money in.`).addClass('text--success');
                        } else {
                            if($('.exist').hasClass('text--success')){
                               $('.exist').removeClass('text--success');
                            }
                            $('.exist').text('User not found.').addClass('text--danger');
                            
                        }
                    });
                }); 

                $('.money_in').on('click', function () {
                    var selected = $('.currency option:selected')
                    if(selected.val()=='' || $('.amount').val()==''){
                        notify('error','Each fields are required for money in')
                        return false
                    }
                    var rate = parseFloat(selected.data('rate'))
                    var code = selected.data('code')
                    var chargeData = $('.charge').data('charge')
                    var amount = parseFloat($('.amount').val())
                    chargeCalc(amount,chargeData,rate,code)
                }); 


                $('.currency').on('change', function () {
                    var selected = $('.currency option:selected')
                    if(selected.val()== ''){
                        return false;
                    }
                    var rate = selected.data('rate')
                    var code = selected.data('code')
                    var type = selected.data('type')
                
                    var min_limit = '{{getAmount($moneyInCharge->min_limit)}}'
                    var max_limit = '{{getAmount($moneyInCharge->max_limit)}}'

                    var min = min_limit/rate
                    var max = max_limit/rate
                    if(type==1){
                        var precision = 2
                    } else {
                        var precision = 8
                    }
                    $('.min').text("@lang('Min'): "+min.toFixed(precision)+' '+code+' -- ')
                    $('.max').text("@lang('Max'): "+max.toFixed(precision)+' '+code)

                });

                $('.req_confirm').on('click',function () { 
                        $('#form').submit()
                        $(this).attr('disabled',true)
                     })
            })(jQuery);
     </script>
@endpush
