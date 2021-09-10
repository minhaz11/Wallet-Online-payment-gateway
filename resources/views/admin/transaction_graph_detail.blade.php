@extends('admin.layouts.app')

@section('panel')
  
    <div class="row mt-50 mb-none-30">
        <div class="col-xl-12 mb-30">
            <div class="card">
              <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between">
                    <h5 class="card-title ">@lang('Daily Transaction Report')</h5>
                        <form action="" method="GET" class="form-inline float-sm-right">

                           <div class="form-group">
                                <select name="user_type" class="form-control mr-2" >
                                    <option value="" {{request('user_type') ? '':"selected"}}>@lang('Select User Type')</option>
                                    <option value="user" {{request('user_type')=='user'?'selected':""}}>@lang('USER')</option>
                                    <option value="agent" {{request('user_type')=='agent'?'selected':""}}>@lang('AGENT')</option>
                                    <option value="merchant" {{request('user_type')=='merchant'?'selected':""}}>@lang('MERCHANT')</option>
                                </select>
                           </div>

                           <div class="form-group">
                                <select name="currency" class="form-control mr-2" >
                                    <option value="" {{request('currency') ? '':"selected"}}>@lang('Select Currency')</option>
                                    @foreach ($currencies as $curr)
                                    <option value="{{strtolower($curr->currency_code)}}" {{request('currency')==$curr->currency_code?'selected':""}}>{{$curr->currency_code}}</option>
                                    @endforeach
                                </select>
                           </div>

                            <div class="input-group has_append">
                                <input name="date" type="text" data-range="true" data-multiple-dates-separator=" - " data-language="en" class="datepicker-here form-control" data-position='bottom right' placeholder="@lang('From date - To date')" autocomplete="off" value="{{@$search}}">
                                <div class="input-group-append">
                                    <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </div>

                            
                        </form>
                </div>
                <div id="apex-line"> </div>
              </div>
            </div>
        </div>
    </div>

  
@endsection

@push('script-lib')
<script src="{{asset('assets/admin/js/vendor/apexcharts.min.js')}}"></script>
<script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush

@push('script')
<script>
// apex-line chart
    var options = {
    chart: {
        height: 320,
        type: "area",
        toolbar: {
        show: false
        },
        dropShadow: {
        enabled: true,
        enabledSeries: [0],
        top: -2,
        left: 0,
        blur: 10,
        opacity: 0.08
        },
        animations: {
        enabled: true,
        easing: 'linear',
        dynamicAnimation: {
            speed: 1000
        }
        },
    },
    dataLabels: {
        enabled: false
    },
    series: [
        {
        name: "Total Amount",
        data: @json( $report['trx_amount'])
        }
    ],

    fill: {
        type: "gradient",
        gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.7,
        opacityTo: 0.9,
        stops: [0, 90, 100]
        }
    },
    tooltip: {
                y: {
                    formatter: function (val) {
                        return "{{__($general->cur_sym)}}" + val + " "
                    }
                }
            },
    xaxis: {
        categories: @json( $report['trx_dates'])
    },
    grid: {
        padding: {
        left: 5,
        right: 5
        },
        xaxis: {
        lines: {
            show: true
        }
        },   
        yaxis: {
        lines: {
            show: true
        }
        }, 
    },
    };

    var chart = new ApexCharts(document.querySelector("#apex-line"), options);
    chart.render()
</script>
@endpush
   

