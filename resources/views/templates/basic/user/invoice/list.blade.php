@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="custom--card">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-6">
                <h6>@lang($pageTitle)</h6>
            </div>
            <div class="col-6 text-end">
                <a  class="btn btn--base btn-sm" href="{{route('user.invoice.create')}}"><i class="las la-plus"></i> @lang('Creaet Invoice') </a>
            </div>
        </div>
        <div class="table-responsive--sm">
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Invoice To')</th>
                        <th>@lang('Email')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Payment Status')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Created at')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($invoices as $invoice)
                <tr>
                    <td data-label="@lang('Invoice To')">{{$invoice->invoice_to}}</td>
                    <td data-label="@lang('Email')">{{$invoice->email}}</td>
                    <td data-label="@lang('Amount')">{{showAmount($invoice->total_amount)}} {{$invoice->currency->currency_code}}</td>
                    <td data-label="@lang('Payment Status')">
                        @if ($invoice->pay_status == 1)
                        <span class="badge badge--success">@lang('Paid')</span>
                        @else
                        <span class="badge badge--warning">@lang('Unpaid')</span>
                        @endif
                    </td>
                    <td data-label="@lang('Status')">
                        @if ($invoice->status == 1)
                        <span class="badge badge--success">@lang('Published')</span>
                        @elseif ($invoice->status == 2)
                        <span class="badge badge--danger">@lang('Discarded')</span>
                        @else
                        <span class="badge badge--warning">@lang('Not Published')</span>
                        @endif
                    </td>
                    <td data-label="@lang('Created at')">{{showDateTime($invoice->created_at,'d M Y @ g:i a')}}</td>
                    <td data-label="@lang('Action')">
                        @if ($invoice->pay_status == 1 ||  $invoice->status == 1 || $invoice->status == 2)
                            <a target="_blank" href="{{route('invoice.payment',encrypt($invoice->invoice_num))}}" class="btn btn--dark btn-sm" data-toggle="tooltip" title="@lang('See')"><i class="la la-eye"></i></a>
                        @else
                            <a href="{{route('user.invoice.edit',$invoice->invoice_num)}}" class="btn btn--base btn-sm"><i class="la la-edit"></i></a>
                        @endif

                      
                    </td>
                    
                    
                </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="12">@lang('No Invoice Found')</td>
                    </tr>
                @endforelse
                 
                </tbody>
            </table>
        </div>
        {{paginateLinks($invoices)}}
    </div>
</div><!-- custom--card end -->
@endsection