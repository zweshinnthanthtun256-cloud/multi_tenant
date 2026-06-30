<div class="topbar">

    <div>
        <h3 class="fw-bold mb-0">
            Hello, {{ auth()->user()->getRoleNames()->first() ?? 'No Role' }}!
        </h3>
    </div>

    <div class="d-flex align-items-center gap-4">



        <div class="user-box d-flex align-items-center gap-3">

            <!-- Notification -->
            <a href="{{ route('registrations.index') }}" class="position-relative text-dark">

                <i class="bi bi-bell fs-5"></i>

                @if ($notificationCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $notificationCount }}
                    </span>
                @endif

            </a>

            <!-- User -->
            <img src="{{ asset('images/robot.jpg') }}" class="rounded-circle" width="35" height="35"
                alt="">
            <span>Hello, {{ auth()->user()->name }}</span>

        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-danger btn-sm">
                Logout
            </button>
        </form>

    </div>

</div>
