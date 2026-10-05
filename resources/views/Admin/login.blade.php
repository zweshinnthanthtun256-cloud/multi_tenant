@extends('layouts.auth')
@section('title', 'Sign in')
@section('body-class', 'auth-login-page')
@section('content')
<div class="auth-login-heading">
    <div class="eyebrow">WELCOME BACK</div>
    <h1>Sign in to CoreFlow</h1>
    <p>Enter your account details to continue to your workspace.</p>
</div>

<form class="auth-login-form" action="{{ route('login.submit') }}" method="POST">
    @csrf
    <div class="field">
        <label for="email" class="form-label">Email address</label>
        <div class="auth-input-wrap">
            <i class="bi bi-envelope"></i>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="username" autofocus>
        </div>
    </div>
    <div class="field">
        <div class="auth-label-row">
            <label for="password" class="form-label">Password</label>
            <a href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <div class="auth-input-wrap">
            <i class="bi bi-lock"></i>
            <input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
        </div>
    </div>
    <label class="auth-remember"><input type="checkbox" name="remember" value="1"> <span>Keep me signed in on this device</span></label>
    <button class="btn btn-primary auth-submit">Sign in <i class="bi bi-arrow-right"></i></button>
</form>

<p class="auth-register-link">New to CoreFlow? <a href="{{ route('register') }}">Create a workspace</a></p>
@endsection
