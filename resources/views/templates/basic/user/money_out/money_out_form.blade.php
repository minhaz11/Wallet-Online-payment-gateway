@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="col-xl-8">
    <div class="card style--two">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
            <div class="bank-icon  me-2">
                <i class="las la-hand-holding-usd"></i>
            </div>
            <h4 class="fw-normal">@lang('Money Out')</h4>
        </div>
        <div class="card-body p-4">
            <form action="" method="POST" id="form">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                        @csrf
                        <div class="d-widget">
                            <div class="d-widget__header">
                                <h6>@lang('Money Out Detail')</h4>
                            </div>
                            <div class="d-widget__content px-5">

                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Agent Username/E-mail')<span class="text--danger">*</span> </label>
                                            <div class="input-group align-items-center border-bottom"> 
                                                <input type="text" class="form--control style--two checkUser border-bottom-0" id="username" name="agent" placeholder="@lang('Agent Username/E-mail')" value="{{old('agent')}}" required>
                                                <button type="button" class="input-text bg-transparent scan" data-toggle="tooltip" title="Scan QR"><i class="las la-camera"></i></button>
                                            </div>
                                           
                                        </div>
                                        <label class="exist text-end"></label>
                                    </div><!-- row end -->
                                </div>

                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Select Wallet')</label>
                                            <select class="select style--two currency" name="wallet_id" required>
                                                <option value="" selected>@lang('Select Wallet')</option>
                                                @foreach ($wallets as $wallet)
                                                <option value="{{$wallet->id}}" data-code="{{$wallet->currency->currency_code}}" data-rate="{{$wallet->currency->rate}}" data-type="{{$wallet->currency->currency_type}}">{{$wallet->currency->currency_code}}</option>
                                                @endforeach
                                            </select>
                                          </div>
                                       
                                        <span class="charge" data-charge="{{$moneyOutCharge}}"></span>
                                        
                                    </div><!-- row end -->
                                </div>

                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Amount')<span class="text--danger">*</span> </label>
                                            <input type="text" class="form--control style--two amount" name="amount" placeholder="@lang('Amount')" required value="{{old('amount')}}">
                                        </div>
                                        <label> 
                                            <span class="text--warning min">@lang('Min: '){{getAmount($moneyOutCharge->min_limit)}} {{defaultCurrency()}} --</span>
                                            <span class="text--warning max">@lang('Max: '){{getAmount($moneyOutCharge->max_limit)}} {{defaultCurrency()}}</span>
                                         </label>
                                    </div><!-- row end -->
                                </div>
                        
                              

                                @if (auth()->user()->ts == 1)
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
                    <button type="button" class="btn btn-md btn--base mt-4 money_out">@lang('Money Out')</button>
                </div>
            </div>

                <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered " role="document">
                    <div class="modal-content"> 
                        <div class="modal-header">
                            <h6 class="modal-title">@lang('Money out Preview')</h6>
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
</div>
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">     
            <div class="modal-body text-center">
                <video id="preview" class="p-1 border" style="width:300px;"></video>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('close')</button>
            </div>
      </div>
    </div>
</div>
@endsection

@push('script')
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

     <script>
            'use strict';
            (function ($) {

                $('.scan').click(function(){
                    var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
                    scanner.addListener('scan',function(content){
                        var route = '{{url('qr/scan/')}}'+'/'+content
                        $.get(route, function( data ) {
                            if(data.error){
                                alert(data.error)
                            } else{
                                $("#username").val(data); 
                                $("#username").focus()
                            }
                            $('#scanModal').modal('hide')
                        });
                    });

                    Instascan.Camera.getCameras().then(function (cameras){
                        if(cameras.length>0){
                            $('#scanModal').modal('show')
                            scanner.start(cameras[1]);
                        } else{
                           alert('No cameras found.');
                        }
                    }).catch(function(e){
                        alert('No cameras found.');
                    });
                });

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
                    var url = '{{ route('user.agent.check.exist') }}';
                    var value = $(this).val();
                    var token = '{{ csrf_token() }}';
                    var data = {agent:value,_token:token}
                    
                    $.post(url,data,function(response) {
                        if(response['data'] != null){
                            if($('.exist').hasClass('text--danger')){
                               $('.exist').removeClass('text--danger');
                            }
                            $('.exist').text(`Valid agent for money out.`).addClass('text--success');
                        } else {
                            if($('.exist').hasClass('text--success')){
                               $('.exist').removeClass('text--success');
                            }
                            $('.exist').text('Agent not found.').addClass('text--danger');
                            
                        }
                    });
                }); 

                $('.currency').on('change',function () { 
                        var rate  =  $('.currency option:selected').data('rate')
                        var currCode  =  $('.currency option:selected').data('code')
                        var type  =  $('.currency option:selected').data('type')
                        var min_limit = '{{getAmount($moneyOutCharge->min_limit)}}'
                        var max_limit = '{{getAmount($moneyOutCharge->max_limit)}}'

                        var min = min_limit/rate
                        var max = max_limit/rate

                        if(type==1){
                            var precision = 2
                        } else {
                            var precision = 8
                        }
                        $('.min').text("@lang('Min'): "+min.toFixed(precision)+' '+currCode+' -- ')
                        $('.max').text("@lang('Max'): "+max.toFixed(precision)+' '+currCode)
                       
                   })

                $('.money_out').on('click', function () {
                    var selected = $('.currency option:selected')
                    if(selected.val()=='' || $('.amount').val()=='' || $('#username').val() == ''){
                        notify('error','Each fields are required for money out')
                        return false
                    }
                    var rate = parseFloat(selected.data('rate'))
                    var code = selected.data('code')
                    var chargeData = $('.charge').data('charge')
                    var amount = parseFloat($('.amount').val())
                 
                    chargeCalc(amount,chargeData,rate,code)
                }); 

                $('.req_confirm').on('click',function () { 
                       
                        $('#form').submit()
                        $(this).attr('disabled',true)
                     })
            })(jQuery);
     </script>
@endpush
