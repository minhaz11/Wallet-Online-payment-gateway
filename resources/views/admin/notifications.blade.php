@extends('admin.layouts.app')
@section('panel')
    <div class="notify__area">
    	@foreach($notifications as $notification)
        @php
             if ($notification->user_type == 'USER'){
                 $class = 'text--primary';
             } else if($notification->user_type == 'AGENT') {
                $class = 'text--warning';
             } elseif($notification->user_type == 'MERCHANT') {
                $class = 'text--success';
             } 
        
        
        @endphp
        <a class="notify__item @if($notification->read_status == 0) unread--notification @endif" href="{{ route('admin.notification.read',$notification->id) }}">
            <div class="notify__thumb bg--primary">
                <img src="{{ getImage(imagePath()['profile'][strtolower(userGuard()['type'])]['path'].'/'.@$notification->user->image,imagePath()['profile'][strtolower(userGuard()['type'])]['size'])}}">
            </div>
            <div class="notify__content">
                <h6 class="title">{{ __($notification->title) }}  <small class="{{$class ?? ''}}">({{$notification->user_type ?? 'GUEST'}})</small></h6>
                <span class="date"><i class="las la-clock"></i> {{ $notification->created_at->diffForHumans() }}</span>
            </div>
        </a>
        @endforeach
        {{ paginateLinks($notifications) }}
    </div>
@endsection
@push('breadcrumb-plugins')
<a href="{{ route('admin.notifications.readAll') }}" class="btn btn--primary">@lang('Mark all as read')</a>
@endpush