<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> {{ $general->sitename(__($pageTitle)) }}</title>

<link rel="icon" type="image/png" href="{{asset($activeTemplateTrue.'images/favicon.png')}}" sizes="16x16">
<!-- bootstrap 4  -->
<link rel="stylesheet" href="{{asset($activeTemplateTrue.'merchant/css/lib/bootstrap.min.css')}}">
<!-- fontawesome 5  -->
<link rel="stylesheet" href="{{asset($activeTemplateTrue.'merchant/css/all.min.css')}}"> 
<!-- lineawesome font -->
<link rel="stylesheet" href="{{asset($activeTemplateTrue.'merchant/css/line-awesome.min.css')}}"> 

<link rel="stylesheet" href="{{asset($activeTemplateTrue.'merchant/css/main.css')}}">
</head>
  <body>

    <!-- checkout section start -->
    @yield('content')
    <!-- checkout section end -->

    <!-- jQuery library -->
    <script src="{{asset($activeTemplateTrue.'merchant/js/lib/jquery-3.5.1.min.js')}}"></script>
    <!-- bootstrap js -->
    <script src="{{asset($activeTemplateTrue.'merchant/js/lib/bootstrap.bundle.min.js')}}"></script>
    @stack('script')
    @include('partials.notify')
  </body>
</html>