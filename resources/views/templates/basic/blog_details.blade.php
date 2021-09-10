@extends($activeTemplate.'layouts.frontend')
@section('content')
<section class="pt-100 pb-100">
	<div class="container">
	  <div class="row gy-5">
		<div class="col-lg-8">
		  <div class="blog-post__date fs--14px d-inline-flex align-items-center"><i class="las la-calendar-alt fs--18px me-2"></i>{{__(showDateTime($blog->created_at,'d M Y'))}}</div>
		  <h2 class="blog-details-title mb-3">{{__($blog->data_values->title)}}</h2>
		  <div class="blog-details-thumb">
			<img src="{{getImage('assets/images/frontend/blog/'.$blog->data_values->blog_image,'640x480')}}" alt="image" class="rounded-3 w-100">
		  </div>
		  <div class="blog-details-content mt-4">
			<p class="fs--18px">
				@php
					echo $blog->data_values->description_nic;
				@endphp
			</p>
		  </div>
		  <ul class="post-share d-flex flex-wrap align-items-center justify-content-center mt-5">
			<li class="caption">@lang('Share') : </li>
			<li data-bs-toggle="tooltip" data-bs-placement="top" title="Facebook">
			  <a  target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{urlencode(url()->current()) }}"><i class="lab la-facebook-f"></i></a>
			</li>
			<li data-bs-toggle="tooltip" data-bs-placement="top" title="Linkedin">
			  <a target="_blank"  href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{urlencode(url()->current()) }}"><i class="lab la-linkedin-in"></i></a>
			</li>
			<li data-bs-toggle="tooltip" data-bs-placement="top" title="Twitter">
			  <a target="_blank"  href="https://twitter.com/intent/tweet?text=my share text&amp;url={{urlencode(url()->current()) }}"><i class="lab la-twitter"></i></a>
			</li>
		
		  </ul>

		  <div class="fb-comments mt-3" data-href="{{ route('blog.details',[$blog->id,slug($blog->data_values->title)]) }}" data-numposts="5" data-width="auto"></div>

		</div>
		<div class="col-lg-4 ps-xl-5">
		  <div class="blog-sidebar rounded-3 section--bg">
			<h4 class="title">@lang('Latest Posts')</h4>
			<ul class="s-post-list">
				@foreach ($recentBlogs as $item)
				<li class="s-post d-flex flex-wrap">
				  <div class="s-post__thumb">
					<img src="{{getImage('assets/images/frontend/blog/'.$item->data_values->blog_image,'640x480')}}" alt="image">
				  </div>
				  <div class="s-post__content">
					<h6 class="s-post__title"><a href="{{route('blog.details',[$item->id, slug(@$item->data_values->title)])}}">{{__($item->data_values->title)}}</a></h6>
					<p class="fs--12px mt-2"><i class="las la-calendar-alt fs--14px me-1"></i>{{showDateTime($item->created_at,'d M Y')}}</p>
				  </div>
				</li>
					
				@endforeach
			
			</ul>              
		  </div>
		</div>
	  </div>
	</div>
  </section>
@endsection

@push('fbComment')
	@php echo loadFbComment() @endphp
@endpush