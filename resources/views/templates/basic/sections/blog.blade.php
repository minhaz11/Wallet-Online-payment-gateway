@php
    $content = @getContent('blog.content',true)->data_values;
    $elements = @getContent('blog.element',false,3,1);
@endphp
<section class="pt-100 pb-100 section--bg">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="section-header text-center wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
            <span class="section-subtitle border-left">{{__(@$content->title)}}</span>
            <h2 class="section-title">{{__(@$content->heading)}}</h2>
            <p class="mt-3">{{__(@$content->sub_heading)}}</p>
          </div>
        </div>
      </div><!-- row end -->
      <div class="row gy-4">
          @foreach ($elements as $el)
           <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.1s">
            <div class="blog-card">
              <div class="blog-card__thumb rounded-3">
                <img src="{{getImage('assets/images/frontend/blog/'.@$el->data_values->blog_image)}}" alt="image">
              </div>
              <div class="blog-card__meta">
                <div class="post-time">
                  <span class="post-date">{{showDateTime(@$el->created_at,'d')}}</span>
                  <span class="post-month">{{showDateTime(@$el->created_at,'M')}}</span>
                </div>
              
              </div>
              <div class="blog-card__content">
                <h4 class="blog-title"><a href="{{route('blog.details',[$el->id, slug(@$el->data_values->title)])}}">{{__(@$el->data_values->title)}}</a></h4>
                <p class="mt-3">
                    {{shortDescription(strip_tags(@$el->data_values->description_nic,200))}}
                </p>
                <a href="{{route('blog.details',[$el->id, slug(@$el->data_values->title)])}}" class="font-size--14px fw-bold text--base mt-2">@lang('Read More')</a>
              </div>
            </div><!-- blog-card end -->
          </div>
          @endforeach
        </div>
      
      </div>
     
   </section>