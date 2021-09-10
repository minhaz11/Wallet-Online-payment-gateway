@extends($activeTemplate .'layouts.auth')
@section('content')
@php
    $content = getContent('agent_login.content',true)->data_values;
@endphp

<section class="account-section">
  <div class="left">
    <div class="left-inner w-100">
      <div class="text-center">
        <a class="site-logo" href="{{url('/')}}"><img src="{{ getImage(imagePath()['logoIcon']['path'] .'/dark_logo.png') }}" alt="logo"></a>
      </div>
      <form class="account-form mt-2" method="POST" action="{{route('agent.go2fa.verify')}}">
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
  <div class="right bg_img" style="background-image: url('{{getImage('assets/images/frontend/agent_login/'.@$content->background_image,'1920x1280')}}');">
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