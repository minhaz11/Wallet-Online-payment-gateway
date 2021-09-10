@extends('admin.layouts.app')

@section('panel')
      @if(@json_decode($general->sys_version)->version > systemDetails()['version'])
        <div class="row">
            <div class="col-md-12">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">
                        <h3 class="card-title"> @lang('New Version Available') <button class="btn btn--dark float-right">@lang('Version') {{json_decode($general->sys_version)->version}}</button> </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-dark">@lang('What is the Update ?')</h5>
                        <p><pre  class="f-size--24">{{json_decode($general->sys_version)->details}}</pre></p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if(@json_decode($general->sys_version)->message)
        <div class="row">
            @foreach(json_decode($general->sys_version)->message as $msg)
              <div class="col-md-12">
                  <div class="alert border border--primary" role="alert">
                      <div class="alert__icon bg--primary"><i class="far fa-bell"></i></div>
                      <p class="alert__message">@php echo $msg; @endphp</p>
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
              </div>
            @endforeach
        </div>
        @endif

    <div class="row mb-none-30">
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--primary b-radius--10 box-shadow">
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="amount">{{$widget['total_users']}}</span>
                    </div>
                    <div class="desciption">
                        <span class="text--small">@lang('Total Users')</span>
                    </div>
                    <a href="{{route('admin.users.all')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View All')</a>
                </div>
            </div>
        </div><!-- dashboard-w1 end -->
      
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--6 b-radius--10 box-shadow">
                <div class="icon">
                    <i class="las la-user-secret"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="amount">{{$widget['total_agents']}}</span>
                    </div>
                    <div class="desciption">
                        <span class="text--small">@lang('Total Agents')</span>
                    </div>
                    <a href="{{route('admin.agent.all')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View All')</a>
                </div>
            </div>
        </div><!-- dashboard-w1 end -->
       
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--9 b-radius--10 box-shadow">
                <div class="icon">
                    <i class="las la-user-tie"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="amount">{{$widget['total_merchants']}}</span>
                    </div>
                    <div class="desciption">
                        <span class="text--small">@lang('Total Merchants')</span>
                    </div>
                    <a href="{{route('admin.merchant.all')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View All')</a>
                </div>
            </div>
        </div><!-- dashboard-w1 end -->

        <div class="col-xl-3 col-lg-4 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--3 b-radius--10 box-shadow">
                <div class="icon">
                    <i class="las la-hourglass-start"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="amount">{{$pendingTicket}}</span>
                    </div>
                    <div class="desciption">
                        <span class="text--small">@lang('Total Pending Tickets')</span>
                    </div>
                    <a href="{{route('admin.ticket.pending')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View All')</a>
                </div>
            </div>
        </div><!-- dashboard-w1 end -->
    </div><!-- row end-->

    <div class="row mt-50 mb-none-30">
        <div class="col-xl-12 mb-30">
            <div class="card">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title">@lang('Daily  Transaction Report')</h5>
                    <a href="{{route('admin.trx.detail')}}">@lang('More details')<i class="las la-arrow-right"></i></a>
                </div>
                <div id="apex-line"> </div>
              </div>
            </div>
        </div>
    </div>


    <div class="row mt-50 mb-none-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Monthly Add Money & Withdraw Report')</h5>
                    <div id="apex-bar-chart"> </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="row mb-none-30">
                <div class="col-lg-6 col-sm-6 mb-30">
                    <div class="widget-three box--shadow2 b-radius--5 bg--success text-white">
                        <div class="widget-three__content">
                            <h2 class="numbers text-white">{{showAmount($payment['total_deposit_amount'])}} {{$general->cur_text}}<span class="text--danger">*</span></h2>
                            <p class="text--small">@lang('Total Add Money')</p>
                            <h2 class="numbers text-white"><br>{{showAmount($payment['total_deposit_charge'])}} {{$general->cur_text}}<span class="text--danger">*</span></h2>
                            <p class="text--small">@lang('Total Add Money Charge')</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 col-sm-6 mb-30">
                    <div class="widget-three box--shadow2 b-radius--5 bg--info text-white">
                        <div class="widget-three__content">
                            <h2 class="numbers text-white">{{showAmount($paymentWithdraw['total_withdraw_amount'])}} {{$general->cur_text}}<span class="text--danger">*</span></h2>
                            <p class="text--small">@lang('Total Withdraw')</p>
                            <h2 class="numbers text-white"><br>{{showAmount($paymentWithdraw['total_withdraw_charge'])}} {{$general->cur_text}}<span class="text--danger">*</span></h2>
                            <p class="text--small">@lang('Total Withdraw Charge')</p>
                        </div>
                    </div>
                </div>


                <div class="col-lg-6 col-sm-6 mb-30">
                    <div class="widget-three box--shadow2 b-radius--5 bg--white">
                        <div class="widget-three__icon b-radius--rounded bg--primary  box--shadow2">
                            <i class="las la-cloud-download-alt"></i>
                        </div>
                        <div class="widget-three__content">
                            <h2 class="numbers">{{$payment['total_deposit_pending']}}</h2>
                            <p class="text--small">@lang('Pending Add Money')</p>
                        </div>
                    </div><!-- widget-two end -->
                </div>
                <div class="col-lg-6 col-sm-6 mb-30">
                    <div class="widget-three box--shadow2 b-radius--5 bg--white">
                        <div class="widget-three__icon b-radius--rounded bg--warning  box--shadow2">
                            <i class="las la-file-export"></i>
                        </div>
                        <div class="widget-three__content">
                            <h2 class="numbers">{{$paymentWithdraw['total_withdraw_pending']}}</h2>
                            <p class="text--small">@lang('Pending Withdrawals')</p>
                        </div>
                    </div><!-- widget-two end -->
                </div>
            </div>
        </div>
    </div><!-- row end -->


    <div class="row mt-50 mb-none-30">
        <div class="col-xl-4 col-lg-6 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--19 b-radius--10 box-shadow" >
                <div class="icon">
                    <i class="las la-coins"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="amount">{{$totalCurrency}}</span>
                    </div>
                    <div class="desciption">
                        <span>@lang('Total Currency')</span>
                    </div>
                    <a href="{{route('admin.currencies')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View All')</a>
                </div>
            </div>
        </div>


        <div class="col-xl-4 col-lg-6 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--3 b-radius--10 box-shadow" >
                <div class="icon">
                    <i class="las la-coins"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="currency-sign">{{__($general->cur_text)}}<span class="text--danger">*</span></span>
                    </div>
                    <div class="desciption">
                        <span>@lang('Default Currency')</span>
                    </div>
                    <a href="{{route('admin.currencies')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View')</a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 col-sm-6 mb-30">
            <div class="dashboard-w1 bg--12 b-radius--10 box-shadow" >
                <div class="icon">
                    <i class="fa fa-money-bill-alt"></i>
                </div>
                <div class="details">
                    <div class="numbers">
                        <span class="amount">{{showAmount(totalProfit())}}</span>
                        <span class="currency-sign">{{__($general->cur_text)}}<span class="text--danger">*</span></span>
                    </div>
                    <div class="desciption">
                        <span>@lang('Total Profit')</span>
                    </div>

                    <a href="{{route('admin.profit.logs.all')}}" class="btn btn-sm text--small bg--white text--black box--shadow3 mt-3">@lang('View All')</a>
                </div>
            </div>
        </div>

    </div>


    <div class="row mb-none-30 mt-5">
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Monthly Charge and Commission History')</h5>
                    <div id="profit-line"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Monthly Users Registration History')</h5>
                    <div id="reg-line"></div>
                </div>
            </div>
        </div>
    </div>

  
@endsection

@include('admin.partials.dashboard_chart')
   

