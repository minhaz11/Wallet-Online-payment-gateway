<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> {{ $general->sitename(__($pageTitle)) }}</title>
  
  @include('partials.seo')
 
    <link rel="icon" type="image/png" href="{{asset($activeTemplateTrue.'images/favicon.png')}}" sizes="16x16">
  <!-- bootstrap 4  -->

  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'css/lib/bootstrap.min.css')}}">
  <!-- fontawesome 5  -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'css/all.min.css')}}"> 
  <!-- lineawesome font -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'css/line-awesome.min.css')}}"> 
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'css/lightcase.css')}}"> 
  <!-- slick slider css -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'css/lib/slick.css')}}">
  <!-- main css -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'css/main.css')}}">
  <link href="{{ asset($activeTemplateTrue.'color/color.php') }}?color={{$general->base_color}}" rel="stylesheet" />
  @stack('style-lib')

  @stack('style')
</head>
  <body>
    @stack('fbComment')
   
    <div class="main-wrapper">
      
        @yield('content')
      
    </div>

 @php
    $cookie = App\Models\Frontend::where('data_keys','cookie.data')->first();
 @endphp

@if ($cookie->data_values->status == 1)
    <div class="cookies-card bg--default radius--10px text-center cookies--dark style--lg {{session('cookie_accepted') ? 'd-none':''}}">
        <div class="cookies-card__icon">
            <i class="fas fa-cookie-bite"></i>
        </div>
        <p class="mt-4 cookies-card__content"> @php echo @$cookie->data_values->description @endphp</p>
        <div class="cookies-card__btn mt-4">
            <a href="{{ route('cookie.accept') }}" class="cookies-btn left--btn">@lang('Allow')</a>
        </div>
    </div>
@endif

   <!-- jQuery library -->
   <script src="{{asset($activeTemplateTrue.'js/lib/jquery-3.5.1.min.js')}}"></script>
   <!-- bootstrap js -->
   <script src="{{asset($activeTemplateTrue.'js/lib/bootstrap.bundle.min.js')}}"></script>
   <!-- slick slider js -->
   <script src="{{asset($activeTemplateTrue.'js/lib/slick.min.js')}}"></script>
   <!-- scroll animation -->
   <script src="{{asset($activeTemplateTrue.'js/lib/wow.min.js')}}"></script>
   <!-- lightcase js -->
   <script src="{{asset($activeTemplateTrue.'js/lib/lightcase.min.js')}}"></script>
   <script src="{{asset($activeTemplateTrue.'js/lib/jquery.paroller.min.js')}}"></script>
   <!-- main js -->
   <script src="{{asset($activeTemplateTrue.'js/app.js')}}"></script>

   @stack('script-lib')

    @stack('script')

    @include('partials.plugins')

    @include('partials.notify')


    <script>
        (function ($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{route('home')}}/change/"+$(this).val() ;
            });
           
        })(jQuery)
    </script>

   </body>
 </html> 