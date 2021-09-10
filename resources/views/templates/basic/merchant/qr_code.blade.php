@extends($activeTemplate.'layouts.merchant_master')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card card-deposit text-center">
                    <div class="card-header card-header-bg">
                        <h3>@lang('Your Unique QR Code')</h3>
                    </div>
                    <div class="card-body card-body-deposit text-center">
                        <img src="{{$qrCode}}" alt="@lang('QR')" class="w-50">
                        <div class="d-flex flex-wrap justify-content-center">
                            <a class="btn btn--base m-1 btn-sm" href="{{route('merchant.qr.jpg',$uniqueCode)}}"><i class="las la-cloud-download-alt"></i> @lang('Downlaod as Image')</a>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
