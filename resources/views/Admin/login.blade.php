@extends('layouts.auth')
@section('title','Sign in')
@section('content')<div class="eyebrow">WELCOME BACK</div><h1>Sign in to your workspace</h1><p>Pick up where your team left off.</p>
<form action="{{ route('login.submit') }}" method="POST">@csrf
<div class="field"><label for="email" class="form-label">Email address</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autocomplete="username" autofocus></div>
<div class="field"><label for="password" class="form-label">Password</label><input id="password" name="password" type="password" class="form-control" required autocomplete="current-password"></div>
<div class="d-flex justify-content-between mb-4"><label><input type="checkbox" name="remember" value="1"> Keep me signed in</label><a href="{{ route('password.request') }}">Forgot password?</a></div>
<button class="btn btn-primary">Sign in <i class="bi bi-arrow-right ms-2"></i></button></form>
<p class="mt-4 text-center">New to CoreFlow? <a href="{{ route('register') }}">Create a workspace</a></p>@endsection
