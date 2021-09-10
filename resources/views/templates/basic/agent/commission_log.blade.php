@extends($activeTemplate.'layouts.agent_master')
@section('content')

    <div class="custom--card mt-5">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-6">
                    <h6>@lang($pageTitle)</h6>
                </div>
                <div class="col-6 text-end">
                    
                </div>
            </div>
            <div class="table-responsive--sm">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Transaction ID')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Post Balance')</th>
                            <th>@lang('Operation Type')</th>
                            <th>@lang('Time')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($logs) >0)
                        @foreach($logs as $k=>$data)
                            <tr>
                                <td data-label="#@lang('Transaction ID')">{{$data->trx}}</td>
                                <td data-label="@lang('Amount')">
                                    <strong class="text--success">{{$data->currency->currency_symbol}}{{showAmount($data->amount)}} {{$data->currency->currency_code}}</strong>
                                </td>
                               
                                <td data-label="@lang('Post Balance')">
                                    {{$data->currency->currency_symbol}}{{showAmount($data->post_balance)}} {{$data->currency->currency_code}}
                                </td>
                                <td data-label="@lang('Operation Type')">
                                    {{ucwords(str_replace('_',' ',$data->operation_type))}} 
                                </td>
                                <td data-label="@lang('Time')">
                                    <i class="fa fa-calendar"></i> {{showDateTime($data->created_at,'d M Y @ g:i a')}}
                                </td>

                              
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="100%">{{ __($emptyMessage) }}</td>
                        </tr>
                    @endif
                     
                    </tbody>
                </table>
            </div>
            {{paginateLinks($logs)}}
        </div>
    </div><!-- custom--card end -->

  
    </div>
@endsection
