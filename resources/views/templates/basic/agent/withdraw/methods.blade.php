@extends($activeTemplate.'layouts.agent_master')

@section('content')
<div class="row justify-content-center mt-5">
  <div class="col-xl-10">
    <div class="card style--two">
        <div class="card-header">
            <h3 class="fw-normal">@lang($pageTitle)</h3>
        </div>
        <div class="card-body px-sm-5 py-sm-4">
            <div class="row gy-4">
              <div class="col-lg-6">
                <div class="bank-card add-bank align-items-center rounded-3 has--link">
                    <a href="{{route('agent.add.withdraw.method')}}" class="item--link"></a>
                    <div class="bank-card__icon">
                        <i class="las la-university"></i>
                    </div>
                    <div class="bank-card__content">
                        <h6 class="fw-normal">@lang('Add New')</h4>
                        <hr>
                        <p class="font-size--14px">@lang('Choose a new withdraw method')</p>
                    </div>
                </div><!-- bank-card end -->
            </div>
              @forelse ($userMethods as $method)
                <div class="col-lg-6">
                    <div class="bank-card  align-items-center rounded-3 has--link">
                        <a href="{{route('agent.withdraw.edit',$method->id)}}" class="item--link withdraw"></a>
                        <div class="bank-card__icon">
                            <img src="{{getImage(imagePath()['withdraw']['method']['path'].'/'.$method->withdrawMethod->image)}}" alt="">
                        </div>
                        <div class="bank-card__content">
                            <h6 class="fw-normal">@lang($method->name)</h4>
                            <span class="font-size--14px mt-2">@lang('Limit :') {{showAmount($method->withdrawMethod->min_limit/$method->currency->rate)}} ~ {{showAmount($method->withdrawMethod->max_limit/$method->currency->rate)}} {{$method->currency->currency_code}}</span>
                            <span class="font-size--14px mt-2">@lang('Charge :') {{showAmount($method->withdrawMethod->fixed_charge/$method->currency->rate)}} {{$method->currency->currency_code}} + {{$method->withdrawMethod->percent_charge}}% </span>
                        </div>
                    </div><!-- bank-card end -->
                </div>
              @empty
              <div class="col-lg-6">
                <div class="bank-card approved warning align-items-center rounded-3 has--link">
                    <a href="#0" class="item--link"></a>
                    <div class="bank-card__icon">
                      <i class="las la-university"></i>
                    </div>
                    <div class="bank-card__content">
                        @lang('No Withdraw Methods')
                    </div>
                </div><!-- bank-card end -->
            </div>
              @endforelse
                
            </div>
        </div>
       
          <div class="p-4">
            {{paginateLinks($userMethods)}}
          </div>
        
    </div>
  </div>
</div>


@endsection

