@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="col-xl-10">
  <div class="card style--two">
      <div class="card-header">
          <h3 class="fw-normal">@lang($pageTitle)</h3>
      </div>
      <div class="card-body px-sm-5 py-sm-4">
          <div class="row gy-4">
            @forelse ($userMethods as $method)
              <div class="col-lg-6">
                  <div class="bank-card  align-items-center rounded-3 has--link">
                      <a href="javascript:void(0)" class="item--link withdraw"
                        data-currency="{{$method->currency->currency_code}}"
                         data-method="{{$method}}"></a>
                      <div class="bank-card__icon">
                          <img src="{{getImage(imagePath()['withdraw']['method']['path'].'/'.$method->withdrawMethod->image)}}" alt="">
                      </div>
                      <div class="bank-card__content">
                          <h6 class="fw-normal">@lang($method->name)</h4>
                          <span class="font-size--14px mt-2">@lang('Limit :') {{showAmount($method->withdrawMethod->min_limit/$method->currency->rate)}} ~ {{showAmount($method->withdrawMethod->max_limit/$method->currency->rate)}} {{$method->currency->currency_code}}</span>
                          <span class="font-size--14px mt-2">@lang('Charge :') {{showAmount($method->withdrawMethod->fixed_charge/$method->currency->rate)}} {{$method->currency->currency_code}} + {{$method->withdrawMethod->percent_charge}}% </span>
                      </div>
                  </div><!-- bank-card end -->
              </div>
            @empty
            <div class="col-lg-6">
              <div class="bank-card approved warning align-items-center rounded-3 has--link">
                  <a href="#0" class="item--link"></a>
                  <div class="bank-card__icon">
                    <i class="las la-university"></i>
                  </div>
                  <div class="bank-card__content">
                      @lang('No Withdraw Methods')
                  </div>
              </div><!-- bank-card end -->
          </div>
            @endforelse
              
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="withdraw" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
  <div class="modal-dialog">
  <form action="{{route('user.withdraw.store')}}" method="post" id="form">
          @csrf
    <div class="modal-content border-0 rounded-0">
      <div class="modal-header">
        <h5 class="modal-title fw-normal method-name" id="depositModalLabel">@lang('Withdraw Amount')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
     
          <input type="hidden" name="method_id" class="method">
          <input type="hidden" name="user_method_id" class="user_method">
       
         
          <div class="input-group mb-3">
            <input id="amount" type="text"  class="form--control" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" name="amount" placeholder="0.00" required>
              <span class="input-group-text base_symbol"></span>
          </div>

          @if (userGuard()['user']->ts == 1)
                <div class="form-group">
                    <label class="mb-0">@lang('Google Authenticator')<span class="text--danger">*</span> </label>
                    <input type="text" class="form--control style--two" name="ts" placeholder="@lang('Your google authentication code')" required>
                </div>
         @endif
      </div>
      <div class="modal-footer p-0 border-0">
        <button type="submit" class="btn btn--primary btn-md m-0 w-100 rounded-0 req_confirm"><i class="las la-wallet font-size--18px"></i> @lang('Submit')</button>
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
              $('.withdraw').on('click',function () { 
                 var code = $(this).data('currency')
                 var method = $(this).data('method')
                 $('#withdraw').find('.base_symbol').text(code)
                 $('#withdraw').find('.method').val(method.method_id)
                 $('#withdraw').find('.user_method').val(method.id)
                 $('#withdraw').find('.user_method').val(method.id)
                 $('#withdraw').modal('show')
               })  

               $('.req_confirm').on('click',function () { 
                 if($('#amount').val()==''){
                   return false;
                 }
                $('#form').submit()
                $(this).attr('disabled',true)
              })
            })(jQuery);
     </script>
@endpush

