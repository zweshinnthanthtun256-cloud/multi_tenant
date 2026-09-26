<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','Workspace') · CoreFlow</title>
@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><a class="visually-hidden-focusable" href="#main">Skip to content</a>
@include('partials.sidebar')
<div class="main-content"><header class="topbar">
<div class="d-flex align-items-center gap-3"><button class="btn btn-outline-secondary mobile-toggle" data-nav-toggle aria-label="Toggle navigation" aria-expanded="false" aria-controls="workspace-nav"><i class="bi bi-list"></i></button><span class="topbar-context">Workspace <span class="mx-2">/</span> @yield('title','Overview')</span></div>
<div class="user-info"><div class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div><div class="user-name"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->getRoleNames()->first() }}</small></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-link" title="Sign out" aria-label="Sign out"><i class="bi bi-box-arrow-right"></i></button></form></div>
</header><main id="main">@include('partials.feedback') @yield('content')</main></div>
@stack('scripts')</body></html>
