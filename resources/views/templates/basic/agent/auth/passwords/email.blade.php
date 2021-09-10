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
      <form class="account-form mt-5" method="POST" action="{{ route('agent.password.email') }}">
        @csrf
        <div class="form-group">
            <label>@lang('Select One')</label>
            <select class="form--control" name="type" required>
                <option value="email">@lang('E-Mail Address')</option>
                <option value="username">@lang('Username')</option>
            </select>
        </div>
        <div class="form-group">
            <label class="my_value"></label>
            <input type="text" class="form--control @error('value') is-invalid @enderror" name="value" value="{{ old('value') }}" required autofocus="off">

            @error('value')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
         
        </div>
    
      <div class="form-group">
        <button type="submit" class="btn btn--base w-100">@lang('Send Password Code')</button>
      </div>
    </form>
  
      <p class="font-size--14px text-center">@lang('Haven\'t an account?') <a href="{{route('agent.register')}}">@lang('Registration here').</a></p>
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
        
        myVal();
        $('select[name=type]').on('change',function(){
            myVal();
        });
        function myVal(){
            $('.my_value').text($('select[name=type] :selected').text());
        }
    })(jQuery)
</script>
@endpush