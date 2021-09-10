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
                                <th scope="col">@lang('Currency Full Name/Code')</th>
                                <th scope="col">@lang('Currency Symbol')</th>
                                <th scope="col">@lang('Currency')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($currencies as $currency)
                            <tr class="{{$currency->is_default == 1? 'bg--active':''}}">
                                <td data-label="@lang('Currency Full Name/Code')">
                                    <span class="font-weight-bold">{{$currency->currency_fullname}}</span>
                                    <br>
                                    <span class="small">
                                        {{$currency->currency_code}}
                                    </span>
                                </td>
                             
                                <td data-label="@lang('Currency')"><span class="font-weight-bold">{{$currency->currency_symbol}}</span></td>
                                <td data-label="@lang('Currency Type')">
                                    @if ($currency->currency_type == 1)
                                    <span class="font-weight-bold text--info">@lang('Fiat Currency')</span>
                                    @else
                                    <span class="font-weight-bold text--warning">@lang('Crypto Currency')</span> 
                                    @endif
                                    <br>1   {{$currency->currency_code}} =  {{showAmount($currency->rate,8)}} {{defaultCurrency()}}
                                   
                                </td>
                               
                                <td data-label="@lang('Status')">
                                    @if ($currency->status == 1)
                                    <span class="text--small badge font-weight-normal badge--success">@lang('Active')</span>
                                    @else
                                    <span class="text--small badge font-weight-normal badge--warning">@lang('Inactice')</span>
                                    @endif
                                   
                                </td>
                                <td data-label="@lang('Action')">
                                    <a href="javascript:void(0)" data-currency="{{$currency}}" class="icon-btn edit" data-toggle="tooltip" data-original-title="@lang('Details')">
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
                    {{paginateLinks($currencies)}}
                </div>
            </div><!-- card end -->
        </div>
    </div>


    <!--Add Modal -->
    <div class="modal fade" id="addCurrency" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
           <form action="{{route('admin.currencies.store')}}" method="POST">
            @csrf
                <div class="modal-content">
                    <div class="modal-header bg--primary">
                        <h5 class="modal-title text-white">@lang('Add Currency')</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Currency Name')</label>
                            <input class="form-control" type="text" name="currency_fullname" placeholder="@lang('e.g: United States Dollar')" required value="{{old('currency_fullname')}}">
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Code')</label>
                            <input class="form-control" type="text" name="currency_code" placeholder="@lang('e.g: USD')" required value="{{old('currency_code')}}">
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Symbol')</label>
                            <input class="form-control" type="text" name="currency_symbol" placeholder="@lang('e.g: $')" required value="{{old('currency_symbol')}}">
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Rate')</label>
                            <div class="input-group has_append">
                                <div class="input-group-prepend">
                                    <div class="input-group-text cur_code"></div>
                                </div>
                                <input type="text" class="form-control" placeholder="0" name="rate" value="{{ old('rate') }}"/>
                                <div class="input-group-append">
                                    <div class="input-group-text">{{$general->cur_text}}</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Type')</label>
                            <select class="form-control" name="currency_type" required>
                                <option value="">--@lang('Select Type')--</option>
                                <option value="1">@lang('FIAT')</option>
                                <option value="2">@lang('CRYPTO')</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('Default Currency') </label>
                            <input type="checkbox" data-onstyle="-success" data-offstyle="-danger" data-toggle="toggle" data-on="@lang('SET')" data-off="@lang('UNSET')" data-width="100%" name="is_default">
                        </div>
                        <div class="form-group">
                            <label>@lang('Status') </label>
                            <input type="checkbox" data-onstyle="-success" data-offstyle="-danger" data-toggle="toggle" data-on="@lang('Active')" data-off="@lang('Inactive')" data-width="100%" name="status">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Submit')</button>
                    </div>
                </div>
           </form>
        </div>
    </div>


     <!--Edit Modal -->
     <div class="modal fade" id="editCurrency" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
           <form action="{{route('admin.currencies.update')}}" method="POST">
            @csrf
                <div class="modal-content">
                    <div class="modal-header bg--primary">
                        <h5 class="modal-title text-white">@lang('Edit Currency')</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="currency_id">
                        <div class="form-group">
                            <label>@lang('Currency Name')</label>
                            <input class="form-control" type="text" name="currency_fullname" placeholder="@lang('United States Dollar')" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Code')</label>
                            <input class="form-control" type="text" name="currency_code" id="currency_code" placeholder="@lang('USD')" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Symbol')</label>
                            <input class="form-control" type="text" name="currency_symbol" placeholder="@lang('$')" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Rate')</label>
                            <div class="input-group has_append">
                                <div class="input-group-prepend">
                                    <div class="input-group-text cur_code_edit"></div>
                                </div>
                                <input type="text" class="form-control" placeholder="0" name="rate">
                                <div class="input-group-append">
                                    <div class="input-group-text">{{$general->cur_text}}</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Currency Type')</label>
                            <select class="form-control" name="currency_type" required>
                                <option value="">--@lang('Select Type')--</option>
                                <option value="1">@lang('FIAT')</option>
                                <option value="2">@lang('CRYPTO')</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('Default Currency') </label>
                            <input type="checkbox" data-onstyle="-success" data-offstyle="-danger" data-toggle="toggle" data-on="@lang('SET')" data-off="@lang('UNSET')" data-width="100%" name="is_default">
                        </div>
                        <div class="form-group">
                            <label>@lang('Status') </label>
                            <input type="checkbox" data-onstyle="-success" data-offstyle="-danger" data-toggle="toggle" data-on="@lang('Active')" data-off="@lang('Inactive')" data-width="100%" name="status">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Update')</button>
                    </div>
                </div>
           </form>
        </div>
    </div>

      <!--Currency Api Modal -->
      <div class="modal fade" id="currencyApi" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
           <form action="{{route('admin.currencies.api.update')}}" method="POST">
            @csrf
                <div class="modal-content">
                    <div class="modal-header bg--primary">
                        <h5 class="modal-title text-white">@lang('Currency Api Key')</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold text--info">@lang('Fiat Currency Rate Api Key')</label>
                            ( <small>@lang('For the api key please visit :') 
                                <a target="_blank" class="text--info" href="https://currencylayer.com/">@lang('Currency Layer')</a>
                             </small> )
                            <input class="form-control" type="text" name="fiat_api_key" placeholder="@lang('Fiat Currency Rate Api Key')" required value="{{$general->fiat_currency_api}}">
                        </div>

                       
                        <div class="form-group mb-3">
                            <small class="font-weight-bold">@lang('Set up cron job for update fiat price rate :')</small>
                            <small class="text--danger">{{url('/fiat-rate')}}</small>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="font-weight-bold text--warning">@lang('Crypto Currency Rate Api Key')</label>
                            ( <small>@lang('For the api key please visit :')
                                <a target="_blank" class="text--info" href="https://coinmarketcap.com/">@lang('CoinMarketCap')</a>
                            </small> )
                            <input class="form-control" type="text" name="crypto_api_key" placeholder="@lang('Crypto Currency Rate Api Key')" required value="{{$general->crypto_currency_api}}">
                        </div>

                        <div class="form-group">
                            <small class="font-weight-bold">@lang('Set up cron job for update crypto price rate :')</small> 
                            <small class="text--danger">{{url('/crypto-rate')}}</small>
                        </div>
                        
                      
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Update')</button>
                    </div>
                </div>
           </form>
        </div>
    </div>
@endsection



@push('breadcrumb-plugins')
<button type="button" class="btn btn--primary mb-2  mr-2" data-toggle="modal" data-target="#currencyApi">
  <i class="las la-key"></i> @lang('Currency Api Key')
</button>

<button type="button" class="btn btn--primary mb-2  mr-2" data-toggle="modal" data-target="#addCurrency">
  <i class="las la-plus"></i> @lang('Add Currency')
</button>

<form action="" method="GET" class="form-inline float-sm-right bg--white">
    <div class="input-group has_append">
        <input type="text" name="search" class="form-control" placeholder="@lang('Currency Code, Full name')" value="{{$search ?? ''}}" autocomplete="off">
        <div class="input-group-append">
            <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
        </div>
    </div>
</form>
@endpush


@push('script')
     <script>
            'use strict';
            (function ($) {
                $('.edit').on('click',function () { 
                    var currency = $(this).data('currency')
                    $('#editCurrency').find('input[name=currency_id]').val(currency.id)
                    $('#editCurrency').find('input[name=currency_fullname]').val(currency.currency_fullname)
                    $('#editCurrency').find('input[name=currency_code]').val(currency.currency_code)
                    $('#editCurrency').find('.cur_code_edit').text(1+' '+currency.currency_code+' =')
                    $('#editCurrency').find('input[name=currency_symbol]').val(currency.currency_symbol)
                    $('#editCurrency').find('input[name=rate]').val(currency.rate)
                    $('#editCurrency').find('select[name=currency_type]').val(currency.currency_type)
                    if(currency.is_default == 1){
                        $('#editCurrency').find('input[name=is_default]').bootstrapToggle('on')
                    }
                    if(currency.status == 1){
                        $('#editCurrency').find('input[name=status]').bootstrapToggle('on')
                    }
                    $('#editCurrency').modal('show')
                 })

                 $('input[name=currency_code]').on('input',function () { 
                        var code = $(this).val().toUpperCase()
                        $('.cur_code').text(1 +' '+code+' =')
                  })
                 $('#currency_code').on('input',function () { 
                        var code = $(this).val().toUpperCase()
                        $('.cur_code_edit').text(1 +' '+code+' =')
                  })
            })(jQuery);
     </script>
@endpush

@push('style')
    <style>
        .bg--active{
            background: #7367f029
        }
    </style>
@endpush