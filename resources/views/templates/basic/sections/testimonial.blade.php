@php
    $content = getContent('testimonial.content',true)->data_values;
    $elements = getContent('testimonial.element',false,'',1);
@endphp

<section class="pt-100 pb-100">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-header">
            <span class="section-subtitle border-left wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.1s">{{__(@$content->title)}}</span>
            <h2 class="section-title wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">{{__(@$content->heading)}}</h2>
            <p class="mt-3 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.5s">{{__(@$content->sub_heading)}}</p>
          </div>
        </div>
      </div><!-- row end -->
      <div class="row">
        <div class="col-lg-12">
          <div class="testimonial-slide-area">
              <div class="thumb">
                  <div class="thumb-slider">
                  @foreach ($elements as $el)
                    <div class="single-slide">
                    <img src="{{getImage('assets/images/frontend/testimonial/'.@$el->data_values->author_image,'1080x620')}}" alt="img" data-animation="fadeInUp" data-delay=".3s">
                    </div>
                  @endforeach
              </div>
            </div>
            <div class="content">
              <div class="content-slider">
                @foreach ($elements as $el)
                    
                <div class="single-slide">
                  <h3 class="name text-white">{{@$el->data_values->author_name}}</h3>
                  <span class="mt-1">{{__(@$el->data_values->designation)}}</span>
                  <p class="mt-3">{{__(@$el->data_values->quote)}}</p>
                </div>
                @endforeach
                
              </div>
            </div>
          </div><!-- testimonial-single end -->
        </div>
      </div>
    </div>
   </section>