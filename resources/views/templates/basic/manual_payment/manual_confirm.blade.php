@extends($activeTemplate.'layouts.'.strtolower(userGuard()['type']).'_master')
@php
    $class = '';
    if (userGuard()['type'] == 'AGENT' || userGuard()['type'] == 'MERCHANT'){
        $class = 'row justify-content-center mt-5';
    } 
@endphp
@section('content')
   <div class="{{$class}}">
    <div class="col-xl-10">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                <div class="bank-icon  me-2">
                    <i class="las la-university"></i>
                </div>
                <h4 class="fw-normal">{{__($pageTitle)}}</h4>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <form action="{{ route(strtolower(userGuard()['type']).'.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <p class="text-center mt-2">@lang('You have requested') <b class="text--success">{{ showAmount($data['amount'])  }} {{__($general->cur_text)}}</b> , @lang('Please pay')
                                        <b class="text--success">{{showAmount($data['final_amo']) .' '.$data['method_currency'] }} </b> @lang('for successful payment')
                                    </p>
                                    <h4 class="text-center mb-4">@lang('Please follow the instruction below')</h4>
        
                                    <p class="my-4 text-center">@php echo  $data->gateway->description @endphp</p>
        
                                </div>
        
                                @if($method->gateway_parameter)
        
                                    @foreach(json_decode($method->gateway_parameter) as $k => $v)
        
                                        @if($v->type == "text")
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><strong>{{__(inputTitle($v->field_level))}} @if($v->validation == 'required') <span class="text-danger">*</span>  @endif</strong></label>
                                                    <input type="text" class="form--control" name="{{$k}}" value="{{old($k)}}" placeholder="{{__($v->field_level)}}">
                                                </div>
                                            </div>
                                        @elseif($v->type == "textarea")
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label><strong>{{__(inputTitle($v->field_level))}} @if($v->validation == 'required') <span class="text-danger">*</span>  @endif</strong></label>
                                                        <textarea name="{{$k}}"  class="form--control"  placeholder="{{__($v->field_level)}}" rows="3">{{old($k)}}</textarea>
        
                                                    </div>
                                                </div>
                                        @elseif($v->type == "file")
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><strong>{{__($v->field_level)}} @if($v->validation == 'required') <span class="text-danger">*</span>  @endif</strong></label>
                                                    <input type="file" class="form--control" name="{{$k}}" @if($v->validation == 'required') required @endif>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                <div class="col-md-12">
                                    <div class="form-group text-end">
                                        <button type="submit" class="btn btn--base btn-block mt-2">@lang('Pay Now')</button>
                                    </div>
                                </div>
        
                            </div>
        
                        </form>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>   

@endsection
@push('style')
<style>
    .withdraw-thumbnail{
        max-width: 220px;
        max-height: 220px
    }
</style>
@endpush
@push('script-lib')
<script src="{{asset($activeTemplateTrue.'/js/bootstrap-fileinput.js')}}"></script>
@endpush
@push('style-lib')
<link rel="stylesheet" href="{{asset($activeTemplateTrue.'/css/bootstrap-fileinput.css')}}">
@endpush