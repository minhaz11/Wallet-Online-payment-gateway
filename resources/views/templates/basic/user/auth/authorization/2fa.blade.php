@extends($activeTemplate .'layouts.frontend')
@section('content')
@php
    $content = getContent('login.content',true)->data_values;
@endphp
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
                  <h2 class="title">@lang('2FA Verification')</h2>
                  <p class="font-size--14px mt-1">@lang('Please provide the verification code.')</p>
                </div>
                <form class="account-form mt-2" method="POST" action="{{route('user.go2fa.verify')}}">
                    @csrf
                    <div class="form-group">
                        <p class="text-center">@lang('Current Time'): {{\Carbon\Carbon::now()}}</p>
                    </div>
                    <div class="form-group">
                        <label>@lang('Verification Code')</label>
                        <input type="text" name="code" id="code" class="form--control" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
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