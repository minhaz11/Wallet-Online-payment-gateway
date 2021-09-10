@extends('admin.layouts.app')

@section('panel')

    <div class="row">

        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th scope="col">@lang('Sl')</th>
                                <th scope="col">@lang('User Type')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($kyc as $key => $item)
                            <tr>
                                <td data-label="@lang('Sl')">{{++$key}}</td>
                                <td data-label="@lang('User Type')">{{$item->user_type}}</td>
                                <td data-label="@lang('Status')">
                                    @if ($item->status == 1)
                                    <span class="text--small badge font-weight-normal badge--success">@lang('Active')</span>
                                    @else
                                    <span class="text--small badge font-weight-normal badge--warning">@lang('Inactive')</span>
                                    @endif
                                </td>
                              
                                <td data-label="@lang('Action')">
                                    <a href="{{route('admin.edit.kyc',$item->user_type)}}" class="icon-btn" data-toggle="tooltip" title="@lang('Edit')">
                                        <i class="las la-edit text--shadow"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ $emptyMessage }}</td>
                                </tr>
                            @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                <div class="card-footer py-4">
                 
                </div>
            </div><!-- card end -->
        </div>


    </div>
@endsection



@push('breadcrumb-plugins')
    
@endpush
