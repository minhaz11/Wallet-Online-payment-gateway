@push('script')
<script src="{{asset('assets/admin/js/vendor/apexcharts.min.js')}}"></script>
<script src="{{asset('assets/admin/js/vendor/chart.js.2.8.0.js')}}"></script>

<script>
    "use strict";
    // apex-bar-chart js
    var options = {
        series: [{
            name: 'Total Add Money',
            data: [
              @foreach($months as $month)
                {{ getAmount(@$depositsMonth->where('months',$month)->first()->depositAmount) }},
              @endforeach
            ]
        }, {
            name: 'Total Withdraw',
            data: [
              @foreach($months as $month)
                {{ getAmount(@$withdrawalMonth->where('months',$month)->first()->withdrawAmount) }},
              @endforeach
            ]
        }],
        chart: {
            type: 'bar',
            height: 400,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: @json($months),
        },
        yaxis: {
            title: {
                text: "{{__($general->cur_sym)}}",
                style: {
                    color: '#7c97bb'
                }
            }
        },
        grid: {
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: false
                }
            },
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "{{__($general->cur_sym)}}" + val + " "
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#apex-bar-chart"), options);
    chart.render();


 // apex-line chart
 

 var options = {
    chart: {
        height: 430,
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
    colors: ["#2E93fA",'#FF1654'],
    series: [
        {
        name: "Charges",
        data: @json( $report['charge_month_amount'])
        },
        {
        name: "Commissions",
        data: @json( $report['commission_month_amount'])
        },
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
        categories: @json( $report['profit_months'])
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

    var chart = new ApexCharts(document.querySelector("#profit-line"), options);
    chart.render()


//user registration
var options = {
    chart: {
        height: 430,
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
        name: "User",
        data: @json( $report['user_reg_count'])
        },
        {
        name: "Agent",
        data: @json( $report['agent_reg_count'])
        },
        {
        name: "Merchant",
        data: @json( $report['merchant_reg_count'])
        },
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
   
    xaxis: {
        categories: @json( $report['reg_months'])
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

    var chart = new ApexCharts(document.querySelector("#reg-line"), options);
    chart.render()



   
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