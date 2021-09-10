<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> {{ $general->sitename(__($pageTitle)) }}</title>
    @include('partials.seo')
  <link rel="icon" type="image/png" href="{{asset($activeTemplateTrue.'images/favicon.png')}}" sizes="16x16">
  <!-- bootstrap 4  -->

  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/lib/bootstrap.min.css')}}">
  <!-- fontawesome 5  -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/all.min.css')}}"> 
  <!-- lineawesome font -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/line-awesome.min.css')}}"> 
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/lightcase.css')}}"> 
  <!-- slick slider css -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/lib/slick.css')}}">
  <!-- main css -->
  <link rel="stylesheet" href="{{asset($activeTemplateTrue.'agent/css/main.css')}}">
  <link href="{{ asset($activeTemplateTrue.'color/color.php') }}?color={{$general->base_color}}" rel="stylesheet">
  @stack('style-lib')

  @stack('style')
</head>
  <body>
    @stack('fbComment')
    <div class="agent-dashboard">
        @include($activeTemplate.'partials.agent_sidenav')
        @include($activeTemplate.'partials.agent_topbar')
        <div class="agent-dashboard__body">
            @yield('content')
        </div>
    </div>
  
 @php
    $cookie = App\Models\Frontend::where('data_keys','cookie.data')->first();
 @endphp

<!--Cookie Modal -->
    <div class="modal fade" id="cookieModal" tabindex="-1" role="dialog" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="cookieModalLabel">@lang('Cookie Policy')</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            @php echo @$cookie->data_values->description @endphp
            <a href="{{ @$cookie->data_values->link }}" target="_blank">@lang('Read Policy')</a>
        </div>
        <div class="modal-footer">
            <a href="{{ route('cookie.accept') }}" class="btn btn-primary">@lang('Accept')</a>
        </div>
        </div>
    </div>
    </div>
   <!-- jQuery library -->
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/jquery-3.5.1.min.js')}}"></script>
   <!-- bootstrap js -->
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/bootstrap.bundle.min.js')}}"></script>
   <!-- slick slider js -->
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/slick.min.js')}}"></script>
   <!-- scroll animation -->
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/wow.min.js')}}"></script>
   <!-- lightcase js -->
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/lightcase.min.js')}}"></script>
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/jquery.paroller.min.js')}}"></script>
   <!-- main js -->
   <script src="{{asset($activeTemplateTrue.'agent/js/app.js')}}"></script>
   <script src="{{asset($activeTemplateTrue.'agent/js/lib/jquery.slimscroll.min.js')}}"></script>

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