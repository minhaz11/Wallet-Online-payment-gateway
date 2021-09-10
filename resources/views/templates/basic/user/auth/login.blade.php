@extends($activeTemplate.'layouts.frontend')
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
                      <h2 class="title">{{__($pageTitle)}}</h2>
                      <p class="font-size--14px mt-1">@lang('Welcome to') {{$general->sitename}}</p>
                    </div>
                    <form class="account-form mt-5" method="POST" action="{{ route('user.login')}}" onsubmit="return submitUserForm();">
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
                       <a href="{{route('user.password.request')}}">@lang('Forgot Password?')</a>
                      </div>
                      <div class="form-group">
                        <button type="submit" class="btn btn--base w-100">@lang('Sign In')</button>
                      </div>
                    </form>
                    <p class="font-size--14px text-center">@lang('Haven\'t an account?') <a href="{{route('user.register')}}">@lang('Registration here').</a></p>
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
