@extends($activeTemplate.'layouts.auth')
@php
    $content = getContent('merchant_login.content',true)->data_values;
    $policies = getContent('policies.element',false,'',1);
@endphp
@section('content')

<section class="account-section style--two">
    <div class="left">
      <div class="left-inner w-100">
        <div class="text-center">
          <a class="site-logo" href="{{url('/')}}"><img src="{{ getImage(imagePath()['logoIcon']['path'] .'/dark_logo.png') }}" alt="logo"></a>
        </div>
        <form class="account-form mt-5" action="{{ route('merchant.register') }}" method="POST" onsubmit="return submitUserForm();">
            <div class="row">
                @csrf
                @if(session()->get('reference') != null)
                    <div class="form-group">
                        <label for="referenceBy">@lang('Reference By')</label>
                        <input type="text" name="referBy" id="referenceBy" class="form--control" value="{{session()->get('reference')}}" readonly>
                    </div>
                @endif

                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="firstname">@lang('First Name')</label>
                    <input id="firstname" type="text" class="form--control" name="firstname" placeholder="@lang('First Name')"  value="{{ old('firstname') }}" required>
                </div>

                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="lastname">@lang('Last Name')</label>
                    <input id="lastname" type="text" class="form--control" name="lastname" placeholder="@lang('Last Name')" value="{{ old('lastname') }}" required>
                </div>


                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="country">{{ __('Country') }}</label>
                    <select name="country" id="country" class="form--control">
                        @foreach($countries as $key => $country)
                            <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" data-code="{{ $key }}">{{ __($country->country) }}</option>
                        @endforeach
                    </select>
                </div>

                
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="mobile">@lang('Mobile')</label>
                    <div class="input-group">
                        <span class="input-group-text mobile-code">
                        </span>
                        <input type="hidden" name="mobile_code">
                        <input type="hidden" name="country_code"> 
                        <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}" class="form--control checkUser" placeholder="@lang('Your Phone Number')">
                    </div>
                    <small class="text-danger mobileExist"></small>
                </div>

                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="username">{{ __('Username') }}</label>
                    <input id="username" type="text" class="form--control checkUser" placeholder="@lang('Username')" name="username" value="{{ old('username') }}" required>
                    <small class="text-danger usernameExist"></small>
                </div>

                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="email">@lang('E-Mail Address')</label>
                    <input id="email" type="email" class="form--control checkUser" placeholder="@lang('Email Address')" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group col-xl-6 col-lg-12 col-md-6 hover-input-popup">
                    <label for="password">@lang('Password')</label>
                    <input id="password" type="password" class="form--control" placeholder="@lang('Password')" name="password" required>
                    @if($general->secure_password)
                        <div class="input-popup">
                            <p class="error lower">@lang('1 small letter minimum')</p>
                            <p class="error capital">@lang('1 capital letter minimum')</p>
                            <p class="error number">@lang('1 number minimum')</p>
                            <p class="error special">@lang('1 special character minimum')</p>
                            <p class="error minimum">@lang('6 character password')</p>
                        </div>
                    @endif
                </div>

                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <label for="password-confirm">@lang('Confirm Password')</label>
                        <input id="password-confirm" type="password" class="form--control" placeholder="@lang('Confirm Password')" name="password_confirmation" required autocomplete="new-password">
                </div>

                @include($activeTemplate.'partials.custom_captcha')
                <div class="form-group">
                    @php echo loadReCaptcha() @endphp
                </div>

                @if ($general->agree)
                <div class="form-group col-xl-6 col-lg-12 col-md-6">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input" type="checkbox"  name="agree" id="termsAndConditions">
                        <label class="form-check-label mb-0 ms-2" for="termsAndConditions">
                            @lang('I read & agree to')
                            @foreach ($policies as $policy)
                            <a href="{{route('links',[slug($policy->data_values->title),$policy->id])}}"> {{$policy->data_values->title}}</a>
                            @endforeach
                        
                        </label>
                    </div>
                </div>
                @endif
                <div class="form-group col-xl-12">
                    <button type="submit" class="btn btn--base w-100">@lang('Create Account')</button>
                </div>
            </div>
        </form>
        <p class="font-size--14px text-center">@lang('Have an account?') <a href="{{route('merchant.login')}}">@lang('Sign In Here').</a></p>
      </div>
    </div>
    <div class="right bg_img" style="background-image: url('{{getImage('assets/images/frontend/merchant_login/'.@$content->background_image,'768x1200')}}');">
    </div>
  </section>


<div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
            <div class="modal-body text-center">
                <i class="las la-exclamation-circle text-secondary display-2 mb-15"></i>
                <h6 class="text-center">@lang('You already have an account please Sign in ')</h6>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn--secondary btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                <a href="{{ route('merchant.login') }}" class="btn btn--base btn-sm">@lang('Sign In')</a>
            </div>
      </div>
    </div>
</div>

@endsection
@push('style')
<style>
    .country-code .input-group-prepend .input-group-text{
        background: #fff !important;
    }
    .country-code select{
        border: none;
    }
    .country-code select:focus{
        border: none;
        outline: none;
    }
    .hover-input-popup {
        position: relative;
    }
    .hover-input-popup:hover .input-popup {
        opacity: 1;
        visibility: visible;
    }
    .input-popup {
        position: absolute;
        bottom: 130%;
        left: 50%;
        width: 280px;
        background-color: #1a1a1a;
        color: #fff;
        padding: 20px;
        border-radius: 5px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        -ms-border-radius: 5px;
        -o-border-radius: 5px;
        -webkit-transform: translateX(-50%);
        -ms-transform: translateX(-50%);
        transform: translateX(-50%);
        opacity: 0;
        visibility: hidden;
        -webkit-transition: all 0.3s;
        -o-transition: all 0.3s;
        transition: all 0.3s;
    }
    .input-popup::after {
        position: absolute;
        content: '';
        bottom: -19px;
        left: 50%;
        margin-left: -5px;
        border-width: 10px 10px 10px 10px;
        border-style: solid;
        border-color: transparent transparent #1a1a1a transparent;
        -webkit-transform: rotate(180deg);
        -ms-transform: rotate(180deg);
        transform: rotate(180deg);
    }
    .input-popup p {
        padding-left: 20px;
        position: relative;
    }
    .input-popup p::before {
        position: absolute;
        content: '';
        font-family: 'Line Awesome Free';
        font-weight: 900;
        left: 0;
        top: 4px;
        line-height: 1;
        font-size: 18px;
    }
    .input-popup p.error {
        text-decoration: line-through;
    }
    .input-popup p.error::before {
        content: "\f057";
        color: #ea5455;
    }
    .input-popup p.success::before {
        content: "\f058";
        color: #28c76f;
    }
</style>
@endpush
@push('script-lib')
<script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
@endpush
@push('script')
    <script>
      "use strict";
        function submitUserForm() {
            var response = grecaptcha.getResponse();
            if (response.length == 0) {
                document.getElementById('g-recaptcha-error').innerHTML = '<span class="text-danger">@lang("Captcha field is required.")</span>';
                return false;
            }
            return true;
        }
        (function ($) {
            @if($mobile_code)
            $(`option[data-code={{ $mobile_code }}]`).attr('selected','');
            @endif

            $('select[name=country]').change(function(){
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
            @if($general->secure_password)
                $('input[name=password]').on('input',function(){
                    secure_password($(this));
                });
            @endif

            $('.checkUser').on('focusout',function(e){
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('.mobile-code').text().substr(1)}${value}`;
                    var data = {mobile:mobile,_token:token}
                }
                if ($(this).attr('name') == 'email') {
                    var data = {email:value,_token:token}
                }
                if ($(this).attr('name') == 'username') {
                    var data = {username:value,_token:token}
                }
                $.post(url,data,function(response) {
                  if (response['data'] && response['type'] == 'email') {
                    $('#existModalCenter').modal('show');
                  }else if(response['data'] != null){
                    $(`.${response['type']}Exist`).text(`${response['type']} already exist`);
                  }else{
                    $(`.${response['type']}Exist`).text('');
                  }
                });
            });

        })(jQuery);

    </script>
@endpush
