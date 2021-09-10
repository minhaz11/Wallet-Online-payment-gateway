@extends($activeTemplate .'layouts.frontend')
@php
    $content = getContent('login.content',true)->data_values;
@endphp
@section('content')
<section class="pt-100 pb-100 d-flex flex-wrap align-items-center justify-content-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="account-wrapper">
            <div class="left bg_img" style="background-image: url('{{getImage('assets/images/frontend/login/'.@$content->background_image,'768x1200')}}');">
            </div>
            <div class="right">
              <div class="inner">
                <div class="text-center">
                  <h2 class="title">@lang('Verify Your Phone No.')</h2>
                  <p class="font-size--14px mt-1">@lang('Enter the verification code that we\'ve sent in your phone number.')</p>
                </div>
                <form class="account-form mt-5" method="POST" action="{{route('user.verify.sms')}}">
                    @csrf
                    <div class="form-group">
                        <p class="text-center">@lang('Your Phone No.'):  <strong>{{auth()->user()->mobile}}</strong></p>
                    </div>

                    <div class="form-group">
                        <label>@lang('Verification Code')</label>
                        <input type="text" name="sms_verified_code" class="form--control" maxlength="7" id="code" required>
                   </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                  </div>

                  <div class="form-group">
                    <small>@lang('Didn\'nt get code in your phone yet?')</small> 
                    <small class="text-center"><a href="{{route('user.send.verify.code')}}?type=sms" class="forget-pass"> @lang('Resend code')</a></small>
                    @if ($errors->has('resend'))
                        <br/>
                        <small class="text-danger">{{ $errors->first('resend') }}</small>
                    @endif
                </div>
                </form>
              
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>
@endsection
@push('script')
<script>
    (function($){
        "use strict";
        $('#code').on('input change', function () {
          var xx = document.getElementById('code').value;
          $(this).val(function (index, value) {
             value = value.substr(0,7);
              return value.replace(/\W/gi, '').replace(/(.{3})/g, '$1 ');
          });
      });
    })(jQuery)
</script>
@endpush