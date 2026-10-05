<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Welcome') · CoreFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body @yield('body-class')">
<div class="auth-page">
    <aside class="auth-story" aria-label="CoreFlow customer relationship workspace">
        <img
            class="auth-story-image"
            src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1800&q=85"
            alt="A collaborative team working together in a modern workspace"
            fetchpriority="high"
            referrerpolicy="no-referrer"
        >
        <div class="auth-story-shade"></div>
        <a class="brand auth-brand" href="{{ route('home') }}">
            <span class="brand-mark"><i class="bi bi-intersect"></i></span>
            CoreFlow
        </a>
        <div class="auth-story-content">
            <div class="auth-story-kicker"><i class="bi bi-stars"></i> Built for growing teams</div>
            <h1>Turn every customer conversation into momentum.</h1>
            <p>Bring relationships, opportunities, and follow-ups into one clear workspace your whole team can trust.</p>
            <div class="auth-story-proof">
                <span><i class="bi bi-shield-check"></i> Private workspaces</span>
                <span><i class="bi bi-graph-up-arrow"></i> Clear pipelines</span>
                <span><i class="bi bi-people"></i> Connected teams</span>
            </div>
        </div>
        <small class="auth-story-foot">CoreFlow · Multi-tenant AI CRM</small>
    </aside>

    <main class="auth-form-wrap">
        <div class="auth-form">
            <a class="brand auth-mobile-brand" href="{{ route('home') }}">
                <span class="brand-mark"><i class="bi bi-intersect"></i></span>
                CoreFlow
            </a>
            @include('partials.feedback')
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
