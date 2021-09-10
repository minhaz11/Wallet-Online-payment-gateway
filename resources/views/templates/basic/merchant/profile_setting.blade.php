@extends($activeTemplate.'layouts.merchant_master')
@section('content')
<div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card style--two">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-center">
                        <div class="bank-icon  me-2">
                            <i class="las la-user"></i>
                        </div>
                        <h4 class="fw-normal">@lang('Profile Setting')</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <form class="register prevent-double-click" action="" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="InputFirstname" class="col-form-label">@lang('First Name'):</label>
                                            <input type="text" class="form--control" id="InputFirstname" name="firstname" placeholder="@lang('First Name')" value="{{$user->firstname}}" minlength="3">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="lastname" class="col-form-label">@lang('Last Name'):</label>
                                            <input type="text" class="form--control" id="lastname" name="lastname" placeholder="@lang('Last Name')" value="{{$user->lastname}}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="email" class="col-form-label">@lang('E-mail Address'):</label>
                                            <input class="form--control" id="email" placeholder="@lang('E-mail Address')" value="{{$user->email}}" readonly>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="phone" class="col-form-label">@lang('Mobile Number')</label>
                                            <input class="form--control" id="phone" value="{{$user->mobile}}" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <label for="address" class="col-form-label">@lang('Address'):</label>
                                            <input type="text" class="form--control" id="address" name="address" placeholder="@lang('Address')" value="{{@$user->address->address}}" required="">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="state" class="col-form-label">@lang('State'):</label>
                                            <input type="text" class="form--control" id="state" name="state" placeholder="@lang('state')" value="{{@$user->address->state}}" required="">
                                        </div>
                                    </div>
        
        
                                    <div class="row">
                                        <div class="form-group col-sm-4">
                                            <label for="zip" class="col-form-label">@lang('Zip Code'):</label>
                                            <input type="text" class="form--control" id="zip" name="zip" placeholder="@lang('Zip Code')" value="{{@$user->address->zip}}" required="">
                                        </div>
        
                                        <div class="form-group col-sm-4">
                                            <label for="city" class="col-form-label">@lang('City'):</label>
                                            <input type="text" class="form--control" id="city" name="city" placeholder="@lang('City')" value="{{@$user->address->city}}" required="">
                                        </div>
        
                                        <div class="form-group col-sm-4">
                                            <label class="col-form-label">@lang('Country'):</label>
                                            <input class="form--control" value="{{@$user->address->country}}" disabled>
                                        </div>
        
                                    </div>
        
                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                            <div class="form-group">
                                                <label for="">@lang('Profile Image')</label>
                                                <input type="file" name="image" class="form--control" id="">
                                                <code>@lang('Image size') {{imagePath()['profile']['agent']['size']}}</code>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12 text-center">
                                            <button type="submit" class="btn btn--base">@lang('Update Profile')</button>
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
