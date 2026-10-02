@extends('layouts.auth')
@section('title','Create a workspace')
@section('content')<div class="eyebrow">LET'S GET STARTED</div><h1>A home for your business</h1><p>Request your workspace. After approval, we’ll email a secure link to set your password.</p>
<form method="POST" action="{{ route('register.submit') }}">@csrf
@foreach(['username'=>'Your name','company_name'=>'Company name','email'=>'Work email','phone'=>'Phone (optional)'] as $name=>$label)
<div class="field"><label class="form-label" for="{{ $name }}">{{ $label }}</label><input class="form-control" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" type="{{ $name==='email'?'email':'text' }}" @required($name !== 'phone') maxlength="120"></div>@endforeach
<button class="btn btn-primary">Request workspace <i class="bi bi-arrow-right ms-2"></i></button></form><p class="mt-4 text-center">Already have a workspace? <a href="{{ route('admin.login') }}">Sign in</a></p>@endsection
