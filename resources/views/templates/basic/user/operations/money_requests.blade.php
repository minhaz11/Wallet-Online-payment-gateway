@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="custom--card">
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
                        <th>@lang('Request From')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Wallet Currency')</th>
                        <th>@lang('Sender Note')</th>
                        <th>@lang('Sent at')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($requests as $request)
                <tr>
                    <td data-label="@lang('Request From')">{{$request->sender->fullname}}</td>
                    <td data-label="@lang('Amount')">{{showAmount($request->request_amount)}} {{$request->currency->currency_code}}</td>
                    <td data-label="@lang('Wallet Currency')">{{$request->currency->currency_code}}</td>
                    <td data-label="@lang('Sender Note')"><button class="btn--base btn-sm note" data-note="{{$request->note}}">@lang('see')</button></td>
                    <td data-label="@lang('Sent at')">{{showDateTime($request->created_at,'d M Y @g : i a')}}</td>
                    <td data-label="@lang('Action')">
                        <a href="javascript:void(0)" class="btn btn-sm icon-btn accept" data-id="{{$request->id}}" data-amount="{{getAmount($request->request_amount)}}" data-curr="{{$request->currency->currency_code}}"><i class="las la-check-double"></i></a>
                        <a href="javascript:void(0)" class="btn btn-sm icon-btn btn--danger reject"  data-id="{{$request->id}}"><i class="las la-ban"></i></a>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center">@lang('No request Found')</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{$requests->links()}}
    </div>


    {{-- request confirm --}}
    <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
         <form  action="{{route('user.request.accept')}}" method="POST">
             @csrf
             <input type="hidden" name="request_id">
            <div class="modal-content"> 
                
                  <div class="modal-body text-center p-4">
                      <i class="las la-exclamation-circle text-secondary display-2 mb-15"></i>
                      <p class="mb-15 warning"></p>
                      <h6 class="text--base mb-15">@lang('Are you sure want to confirm?')</h6>

                     @if(auth()->user()->ts == 1)
                     <div class="form-group mt-2">
                        <label>@lang('Google authentication code')</label>
                        <input  class="form--control" type="text" name="ts" placeholder="@lang('Google authentication code')" required>
                    </div>
                     @endif
                  </div>
                  <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit"  class="btn btn--base btn-sm del">@lang('Confirm')</button>
                  </div>
             </div>
         </form>
        </div>
    </div>

     {{-- reject confirm --}}
     <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
         <form  action="{{route('user.request.reject')}}" method="POST">
             @csrf
             <input type="hidden" name="request_id">
            <div class="modal-content"> 
                
                  <div class="modal-body text-center p-4">
                      <i class="las la-exclamation-circle text-danger display-2 mb-15"></i>
                      <h6 class="text--base mb-15">@lang('Are you sure want to reject?')</h6>
                  </div>
                  <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit"  class="btn btn--danger btn-sm del">@lang('Reject')</button>
                  </div>
             </div>
         </form>
        </div>
    </div>


    {{-- see note --}}
    <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
        <div class="modal-content"> 
              <div class="modal-header">
                <h6 class="modal-title">@lang('Note')</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
                <div class="modal-body text-center">
                   <div class="form-group">
                       <textarea class="form--control"  id="note" cols="30" disabled></textarea>
                   </div>
                </div>
         
           </div>
        </div>
    </div>
</div><!-- custom--card end -->
@endsection

@push('script')
     <script>
            'use strict';
            (function ($) {
                $('.accept').on('click', function () {
                    var id = $(this).data('id')
                    var amount = $(this).data('amount')
                    var curr = $(this).data('curr')
                    $('#confirm').find('input[name=request_id]').val(id)
                    $('#confirm').find('.warning').text(amount+' '+curr+' will be reduced from your '+curr+' wallet.')
                    $('#confirm').modal('show')
                });  
                $('.reject').on('click', function () {
                    var id = $(this).data('id')
                    $('#rejectModal').find('input[name=request_id]').val(id)
                    $('#rejectModal').modal('show')
                });  
             
                $('.note').on('click', function () {
                    var note = $(this).data('note')
                    $('#noteModal').find('#note').text(note)
                    $('#noteModal').modal('show')
                });  
            })(jQuery);
     </script>
@endpush