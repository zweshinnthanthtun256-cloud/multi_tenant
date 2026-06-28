<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/SAAScon.png') }}">
    <title>{{ config('app.name', 'Multi-tenent SAAS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>

        body{
    background: linear-gradient(135deg, #f5f1ea 0%, #e7d7c9 50%, #d6bfa8 100%);
    font-family:'Segoe UI', sans-serif;
}

        .sidebar{
    width:260px;
    min-height:100vh;

    background: rgba(221, 208, 203, 0.75);
    backdrop-filter: blur(18px);

    border-right: 1px solid rgba(248, 248, 248, 0.356);

    position:fixed;
    left:0;
    top:0;

    box-shadow: 10px 0 40px rgba(0, 0, 0, 0.25);
}

        .logo{
            font-size:28px;
            font-weight:700;
            color:#010202;
        }

        .menu-title{
            font-size:12px;
            color:#999;
            font-weight:600;
            margin-top:25px;
            margin-bottom:10px;
        }

        .sidebar .nav-link{
    color:#5a4633;
    border-radius:12px;
    padding:12px 15px;
    margin-bottom:6px;
    font-weight:500;
    transition: all 0.2s ease;
}

        .sidebar .nav-link.active{
    background: linear-gradient(90deg, rgba(139,94,60,0.25), rgba(139,94,60,0.08));
    color:#3b2a1f;
    border-left: 4px solid #8b5e3c;
    padding-left: 11px;
    font-weight:600;
}

        .sidebar .nav-link:hover{
    background: rgba(139, 94, 60, 0.12);
    color:#3b2a1f;
    transform: translateX(3px);
}

        .main-content{
            margin-left:260px;
            padding:30px;
        }

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }

        .search-box{
            background:#fff;
            border-radius:30px;
            padding:8px 18px;
            width:300px;
            border:1px solid #eee;
        }

        .search-box input{
            border:none;
            outline:none;
            width:100%;
        }

        .card-box{
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(15px);
    border-radius:18px;
    padding:22px;
    box-shadow:0 10px 30px rgba(80, 50, 20, 0.08);
}
        .stat-number{
            font-size:34px;
            font-weight:700;
        }

        .badge-soft{
            background:#eef2ff;
            color:#5b5ce2;
            padding:5px 10px;
            border-radius:30px;
            font-size:12px;
        }

        .chart-title{
            font-size:20px;
            font-weight:600;
            margin-bottom:20px;
        }

        .user-box img{
            width:42px;
            height:42px;
            border-radius:50%;
        }

        .progress-line{
            height:8px;
            border-radius:10px;
            background:#ececec;
            overflow:hidden;
        }

        .progress-line div{
            height:100%;
        }

    </style>

</head>
<body>

@include('partials.sidebar')

<div class="main-content">

    @yield('content')

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>