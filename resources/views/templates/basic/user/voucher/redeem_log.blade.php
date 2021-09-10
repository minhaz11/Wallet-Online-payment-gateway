@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="custom--card">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-6">
                <h6>@lang($pageTitle)</h6>
            </div>
            <div class="col-6 text-end">
                <a href="{{route('user.voucher.redeem')}}" class="btn btn--base btn-sm me-2 "><i class="las la-backward"></i> @lang('Back') </a>
            </div>
        </div>
        <div class="table-responsive--sm">
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Voucher Code')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Used At')</th>
                        
                    </tr>
                </thead>
                <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td data-label="@lang('Voucher Code')">{{$log->voucher_code}}</td>
                    <td data-label="@lang('Amount')">{{showAmount($log->amount)}} {{$log->currency->currency_code}}</td>
                    <td data-label="@lang('Used at')">{{showDateTime($log->updated_at,'d M Y')}}</td>

                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center">@lang('No Log Found')</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{$logs->links()}}
    </div>
</div><!-- custom--card end -->
@endsection