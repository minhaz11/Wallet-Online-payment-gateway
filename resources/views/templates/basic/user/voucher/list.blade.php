@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="custom--card">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-6">
                <h6>@lang($pageTitle)</h6>
            </div>
            <div class="col-6 text-end">
                <a  class="btn btn--base btn-sm" href="{{route('user.voucher.create')}}"> <i class="las la-plus"></i> @lang('Create Voucher')</a>
            </div>
        </div>
        <div class="table-responsive--sm">
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Voucher Code')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Use Status')</th>
                        <th>@lang('Created at')</th>
                        <th>@lang('Used at')</th>
                        
                    </tr>
                </thead>
                <tbody>
                @foreach ($vouchers as $voucher)
                <tr>
                    <td data-label="@lang('Voucher Code')">{{$voucher->voucher_code}}</td>
                    <td data-label="@lang('Amount')">{{showAmount($voucher->amount)}} {{$voucher->currency->currency_code}}</td>
                    <td data-label="@lang('Use Status')">
                        @if ($voucher->is_used == 1)
                        <span class="badge badge--warning">@lang('USED')</span>
                        @else
                            <span class="badge badge--success">@lang('NOT USED')</span>
                        @endif
                    </td>
                     <td data-label="@lang('Created at')">{{showDateTime($voucher->created_at,'d M Y')}}</td>
                   
                        @if ($voucher->is_used == 1)
                        <td data-label="@lang('Used at')">{{showDateTime($voucher->updated_at,'d M Y @g:h:a')}}</td>
                        @else
                        <td data-label="@lang('Used at')">N/A</td>
                        @endif
                  
                     
                    
                </tr>
                    
                @endforeach
                 
                </tbody>
            </table>
        </div>
        {{$vouchers->links()}}
    </div>
</div><!-- custom--card end -->
@endsection