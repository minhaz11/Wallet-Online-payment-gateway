@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30 justify-content-center">
        <div class="col-xl-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Information of') {{$user->fullname}}</h5>
                    <div class="row">
                        <div class="col-md-8 mb-30">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title border-bottom pb-2">@lang('KYC info details')</h5>
                                        @if ($user->kyc_info)
                                           <ul class="list-group">
                                                @foreach (json_decode($user->kyc_info) as $k => $info)
                                                    @if ($info->type == 'text')
                                                        <li class="list-group-item {{$info->type == 'file' ? 'mb-2':''}} d-flex justify-content-between">
                                                            <h6>{{ucwords(str_replace('_',' ',$k))}} :</h6>
                                                             <span>{{$info->field_value}}</span>
                                                        </li>
                                                    @elseif($info->type == 'file')
                                                    
                                                    @endif
                                                @endforeach
                                           </ul>
                                        @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-30">
                                @if ($user->kyc_info)
                                        @foreach (json_decode($user->kyc_info) as $k => $info)
                                            @if($info->type == 'file')
                                            <div class="card b-radius--10 overflow-hidden box--shadow1 mt-3">
                                                
                                                    <div class="p-3 bg--white">
                                                        <div class="mb-15">
                                                            <h6>{{ucwords(str_replace('_',' ',$k))}} :</h6>
                                                        </div>
                                                        <div class="">
                                                            <img src="{{ getImage(imagePath()['kyc']['user']['path'].'/'.$info->field_value,'250x250')}}" class="b-radius--10 w-100" width="250" height="250">
                                                        </div>
                                                        
                                                    </div>
                                                
                                            </div>
                                            @endif
                                        @endforeach
                                    
                                @endif
                                
                            </div>
                        </div>
                    </div>
                    @if ($user->kyc_status != 1)
                    <div class="row p-3">
                        <div class="col-md-6 mb-2">
                            <a href="javascript:void(0)" data-userid = "{{$user->id}}"  class="btn btn--danger btn-block reject">@lang('Reject')</a>
                        </div>
                        <div class="col-md-6">
                            <a href="javascript:void(0)" data-userid = "{{$user->id}}"  class="btn btn--primary btn-block approve">@lang('Approve')</a>
                        </div>
                        
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
           <button type="button" class="close ml-auto m-3" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
           </button>
                <form action="{{route('admin.kyc.info.user.approve')}}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id">
                    <div class="modal-body text-center">
                        <i class="las la-exclamation-circle text--primary display-2 mb-15"></i>
                        <h4 class="text--secondary mb-15 msg">@lang('Are you sure to approve?')</h4>
                    </div>
                <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                  <button type="submit"  class="btn btn--primary">@lang('Confirm')</button>
                </div>
              </form>
          </div>
        </div>
      </div>
      {{-- reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
           <div class="modal-header">
            <h4 class="text--secondary msg">@lang('Are sure to reject?')</h4>
            <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
           </div>
           </button>
                <form action="{{route('admin.kyc.info.user.reject')}}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id">
                    <div class="modal-body text-center">
                        <label for="">@lang('Reasons')</label>
                        <textarea name="reasons" cols="30" rows="5" required></textarea>
                    </div>
                <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                  <button type="submit"  class="btn btn--danger">@lang('Reject')</button>
                </div>
              </form>
          </div>
        </div>
      </div>

@endsection

@push('script')
     <script>
            'use strict';
            (function ($) {
                $('.approve').on('click',function () { 
                    $('#approveModal').find('input[name=user_id]').val($(this).data('userid'))
                    $('#approveModal').modal('show')
                })  
                $('.reject').on('click',function () { 
                    $('#rejectModal').find('input[name=user_id]').val($(this).data('userid'))
                    $('#rejectModal').modal('show')
                })  
            })(jQuery);
     </script>
@endpush

@push('breadcrumb-plugins')
  <a href="{{$prevUrl}}" class="btn btn--primary"> <i class="las la-backward"></i> @lang('Back')</a>
@endpush