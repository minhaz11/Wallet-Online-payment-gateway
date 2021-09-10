@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-xl-4">
                                <div class="image-upload">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="profilePicPreview logoPicPrev" style="background-image: url({{ getImage(imagePath()['logoIcon']['path'].'/light_logo.png', '?'.time()) }})">
                                                        <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                               
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" class="profilePicUpload" id="profilePicUpload1" accept=".png, .jpg, .jpeg" name="light_logo">
                                            <label for="profilePicUpload1" class="bg--primary">@lang('Select Light Logo') </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-xl-4">
                                <div class="image-upload">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="row">
                                                <div class="col-sm-12 mt-sm-0 mt-4">
                                                    <div class="profilePicPreview logoPicPrev bg--dark" style="background-image: url({{ getImage(imagePath()['logoIcon']['path'].'/dark_logo.png', '?'.time()) }})">
                                                        <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" class="profilePicUpload" id="profilePicUpload5" accept=".png, .jpg, .jpeg" name="dark_logo">
                                            <label for="profilePicUpload5" class="bg--primary">@lang('Select Dark Logo') </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-xl-4">
                                <div class="image-upload">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="profilePicPreview logoPicPrev" style="background-image: url({{ getImage(imagePath()['logoIcon']['path'] .'/favicon.png', '?'.time()) }})">
                                                        <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 mt-sm-0 mt-4">
                                                    <div class="profilePicPreview logoPicPrev bg--dark" style="background-image: url({{ getImage(imagePath()['logoIcon']['path'] .'/favicon.png', '?'.time()) }})">
                                                        <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" class="profilePicUpload" id="profilePicUpload2" accept=".png" name="favicon">
                                            <label for="profilePicUpload2" class="bg--primary">@lang('Select Favicon')</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary btn-block btn-lg">@lang('Update')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection