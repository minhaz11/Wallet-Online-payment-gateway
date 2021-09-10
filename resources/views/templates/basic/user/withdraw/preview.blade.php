@extends($activeTemplate.'layouts.user_master')

@section('content')
    <div class="col-xl-6 col-lg-6 col-md-8">
       <form action="" method="POST">
           @csrf
            <div class="d-widget shadow-sm">
                <div class="d-widget__header text-center">
                    <h6>{{__($withdraw->method->name)}}</h6>
                </div>
                <div class="d-widget__content">
                    <div class="w-50 mx-auto">
                        <img src="{{getImage(imagePath()['withdraw']['method']['path'].'/'.$withdraw->method->image,'800x800')}}" alt="image" class="rounded-2">
                    </div>
                    <ul class="cmn-list-two text-center mt-4">
                        <li>
                            @lang('Requested Amount '):
                            <strong>{{showAmount($withdraw->amount)}} </strong> {{$withdraw->curr->currency_code}}
                        </li>
                        <li>
                            @lang('Withdraw Charge '):
                            <strong>{{showAmount($withdraw->charge)}}</strong> {{$withdraw->curr->currency_code}}
                        </li>
                        <li>
                            @lang('You will get '): <strong> {{showAmount($withdraw->final_amount)}}</strong> {{$withdraw->curr->currency_code}}
                        </li>
                        <li>
                            @lang('Your balance will be '): <strong> {{showAmount($withdraw->wallet->balance-$withdraw->final_amount)}}</strong> {{$withdraw->curr->currency_code}}
                        </li>
                    </ul>
                </div>
                <div class="d-widget__footer text-center border-0 pb-3">
                    <button type="submit" class="btn btn-md w-100 d-block btn--base">@lang('Confirm') <i class="las la-long-arrow-alt-right"></i></button>    
                </div>
            </div><!-- d-widget end -->
       </form>
    </div>
@endsection

