@extends($activeTemplate.'layouts.auth')
@php
    $content = getContent('agent_login.content',true)->data_values;
@endphp
@section('content')

<section class="account-section">
  <div class="left">
    <div class="left-inner w-100">
      <div class="text-center">
        <a class="site-logo" href="{{url('/')}}"><img src="{{ getImage(imagePath()['logoIcon']['path'] .'/dark_logo.png') }}" alt="logo"></a>
      </div>
      <div class="text-center">
       
      </div>
      <form class="account-form mt-5" action="{{ route('agent.password.verify.code') }}" method="POST">
          @csrf
          <input type="hidden" name="email" value="{{ $email }}">

          <div class="form-group">
              <label>@lang('Verification Code')</label>
              <input type="text" name="code" id="code" class="form--control" placeholder="Enter the verificatioin code from your mail." required>
          </div>
          
      
        <div class="form-group">
          <button type="submit" class="btn btn--base w-100">@lang('Send Password Code')</button>
        </div>

        
         <small>@lang('Please check including your Junk/Spam Folder. if not found, you can')</small> 
         <small><a href="{{ route('agent.password.request') }}">@lang('Try to send again')</a></small>
          
     
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