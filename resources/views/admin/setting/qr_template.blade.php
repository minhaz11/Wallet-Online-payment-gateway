@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30 justify-content-center">
        <div class="col-xl-10 col-lg-7 col-md-7 mb-30">
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card mt-50">
                    <div class="card-body">
                       <h5 class="card-title border-bottom pb-2">@lang('Template of QR code')</h5>
                        <div class="row justify-content-center">
                           <div class="col-md-6 mb-30">
                                <div class="card b-radius--10 overflow-hidden box--shadow1 mt-3">
                                   <div class="card-body p-0">
                                       <div class="p-3 bg--white">
                                           <div class="">
                                               <img src="{{ getImage(imagePath()['qr_template']['path'].'/'.$general->qr_template,'2480x3508')}}" class="b-radius--10 w-100" >
                                           </div>
                                           
                                       </div>
                                   </div>
                                </div>
                               
                            </div>
                           </div>
                           <div class="row justify-content-center">
                               <div class="col-md-9">
                                   <div class="form-group">
                                       <label for="">@lang('Upload Template') <code>(A4 size : 2480 x 3508 px)</code> </label>
                                       <li class="list-group-item d-flex justify-content-between">
                                          <span><input class="form-control-file" type="file" name="template"></span>
                                       </li>
                                    </div>
                                       
                               </div>
                               <div class="col-md-12">
                                   <button type="submit" class="btn btn--primary btn-block">@lang('Submit')</a>
                               </div>
                           </div>
                     </div>
                  </div>
               </div>
            </form>
        </div>


@endsection


