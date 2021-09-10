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
                                <th scope="col">@lang('Full Name')</th>
                                <th scope="col">@lang('Email')</th>
                                <th scope="col">@lang('Username')</th>
                                <th scope="col">@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($kycInfo as $info)                            
                            <tr>
                                <td data-label="@lang('Full Name')">{{$info->fullname}}</td>
                                <td data-label="@lang('Email')">{{$info->email}}</td>
                                <td data-label="@lang('Username')">{{$info->username}}</td>
                                <td data-label="@lang('Action')">
                                    
                                    <a href="{{route('admin.kyc.info.'.$type.'.details',$info->id)}}" class="icon-btn" data-toggle="tooltip" title="@lang('details')">
                                        <i class="las la-eye text--shadow"></i>
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
                  {{paginateLinks($kycInfo)}}
                </div>
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    @if (Route::currentRouteName()=='admin.kyc.info.'.$type.'.pending')
      <a href="{{route('admin.kyc.info.'.$type.'.approved')}}" class="btn btn--primary">@lang('Approved KYC')</a>
    @else
      <a href="{{route('admin.kyc.info.'.$type.'.pending')}}" class="btn btn--primary">@lang('Pending KYC')</a>
    @endif
@endpush
