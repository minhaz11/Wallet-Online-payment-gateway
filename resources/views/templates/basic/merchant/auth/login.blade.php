@extends($activeTemplate.'layouts.auth')
@php
    $content = getContent('merchant_login.content',true)->data_values;
@endphp
@section('content')
    <section class="account-section" >
       <div class="left">
         <div class="left-inner">
            <div class="text-center">
              <a class="site-logo" href="{{url('/')}}"><img src="{{ getImage(imagePath()['logoIcon']['path'] .'/dark_logo.png') }}" alt="logo"></a>
            </div>
            <form class="account-form mt-4" method="POST" action="{{ route('merchant.login')}}" onsubmit="return submitUserForm();">
                @csrf
              <div class="form-group">
                <label>@lang('Username & Email')</label>
                <input type="text" name="username" placeholder="@lang('Enter username or email address')" class="form--control" required value="{{old('username')}}">
              </div>
              <div class="form-group">
                <label>@lang('Password')</label>
                <input type="password" name="password" placeholder="@lang('Enter password')" class="form--control" required>
              </div>
              @include($activeTemplate.'partials.custom_captcha')
              <div class="form-group">
                @php echo loadReCaptcha() @endphp
              </div>

              <div class="form-group">
              <a href="{{route('merchant.password.request')}}">@lang('Forgot Password?')</a>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn--base w-100">@lang('Sign In')</button>
              </div>
            </form>
            <p class="font-size--14px text-center">@lang('Haven\'t an account?') <a href="{{route('merchant.register')}}">@lang('Registration here').</a></p>
         </div>
       </div>
       <div class="right bg_img" style="background-image: url('{{getImage('assets/images/frontend/merchant_login/'.@$content->background_image,'1920x1280')}}');"></div>
    </section>
@endsection

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
    </script>
@endpush
