@extends('admin.layouts.app')

@php
    $currencies = \App\Models\Currency::where('status',1)->get(['id','currency_code']);
@endphp

@section('panel')
<div class="row">

    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Trx')</th>
                                <th>@lang('User/User type')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Currency')</th>
                                <th>@lang('Operation Type/Remark')</th>
                                <th>@lang('Time & Date')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td data-label="@lang('Trx')">
                                   {{$log->trx}}
                                </td>

                                <td data-label="@lang('User/User Type')">
                                    <span class="font-weight-bold">{{ $log->user_type}}</span> <br>
                                    @if ($log->user)
                                        <a href="{{route('admin.users.detail',$log->user_id)}}">{{ $log->user->username }}</a>
                                    @elseif($log->agent)
                                        <a href="{{route('admin.agent.detail',$log->user_id)}}">{{ $log->agent->username }}</a>
                                    @else
                                        <a href="{{route('admin.merchant.detail',$log->user_id)}}">{{ $log->merchant->username }}</a>
                                    @endif
                                    
                                </td>
                             
                                <td data-label="@lang('Amount')">
                                    <strong class=" {{$log->remark != null ? 'text--danger':'text--success'}}">{{$log->currency->currency_symbol}} {{ showAmount($log->amount) }}</strong>
                                </td>

                                <td data-label="@lang('Currency')">
                                    <strong>{{$log->currency->currency_code}}</strong>
                                </td>

                                <td data-label="@lang('Operation Type')">
                                    <strong class="text--primary">
                                       {{ucwords(str_replace('_',' ',$log->operation_type))}}
                                    </strong> <br>
                                    <span class="{{$log->remark ? 'text--danger':'text--success'}} ">{{$log->remark ? str_replace('_',' ',$log->remark) : 'profit' }}</span>
                                </td>
                                
                                 <td data-label="@lang('Time & Date')"> 
                                    {{ showDateTime($log->created_at) }}<br>{{ diffForHumans($log->created_at) }}
                                </td>
                           </tr>
                           @empty
                           <tr>
                            <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                        </tr>
                        @endforelse

                    </tbody>
                </table><!-- table end -->
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between py-4">
            @if(request()->routeIs('admin.profit.logs.all'))
            <strong class="text--success"><span class="text--dark">@lang('Total Profit : ')</span> {{showAmount($totalProfit)}} {{$general->cur_text}}<span class="text--danger">*</span> </strong>
            @endif
            {{ paginateLinks($logs) }}
        </div>
    </div><!-- card end -->
</div> 
</div>

@endsection


@push('script-lib')
  <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush

@push('breadcrumb-plugins')
    @if(request()->routeIs('admin.profit.logs.all'))
    <a href="{{route('admin.export.csv')}}" class="btn btn--success mr-2 mb-2"><i class="las la-file-export"></i> @lang('Export CSV')</a>
    @elseif(request()->routeIs('admin.profit.logs'))
    <a href="{{route('admin.export.csv',['log=profit-logs'])}}" class="btn btn--success mr-2 mb-2"><i class="las la-file-export"></i> @lang('Export CSV')</a>
    @else
    <a href="{{route('admin.export.csv',['log=commission-logs'])}}" class="btn btn--success mr-2 mb-2"><i class="las la-file-export"></i> @lang('Export CSV')</a>
    @endif

    @php
        if(request()->routeIs('admin.profit.logs.all')){
            $scope = 'all-logs';
        } else if(request()->routeIs('admin.profit.logs')){
            $scope = 'only-profits';
        } else {
            $scope = 'only-commissions';
        }
    @endphp

<form action="{{route('admin.profit.logs.date.search')}}" method="GET" class="form-inline float-sm-right ml-2 mb-2">
    <div class="form-group mr-2">
      <select class="form-control" name="currency">
        <option value="">@lang('Select Currency')</option>
        @foreach ($currencies as $item)
        <option value="{{$item->currency_code}}">{{$item->currency_code}}</option>
        @endforeach
      </select>
    </div>
    <div class="input-group">
        <input type="hidden" name="scope" value="{{$scope}}">
        <input name="date" type="text" data-range="true" data-multiple-dates-separator=" - " data-language="en" class="datepicker-here form-control" data-position='bottom right' placeholder="@lang('Start date - End date')" autocomplete="off" value="{{ @$dateSearch }}">
        <div class="input-group-append">
            <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
        </div>
    </div>
</form>

@endpush


