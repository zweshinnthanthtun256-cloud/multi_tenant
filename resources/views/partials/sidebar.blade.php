<div class="sidebar p-4">

    <div class="logo mb-5 flex items-center gap-3">
    
    <div class="relative">
        <img 
            src="{{ asset('images/SAAScon.png') }}" 
            alt="SAAScon"
            class="h-18 w-30 rounded-2xl object-cover shadow-lg border border-gray-200"
        >

        <!-- optional online/glow dot -->
        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-white"></span>
    </div>

    <div>
        <h4 class="text-2xl font-bold tracking-wide text-gray-800">
            CoreFlow Dashboard
        </h4>

        
    </div>

</div>

    <ul class="nav flex-column">

        <li class="nav-item">
            <a href="{{ url('/admin/dashboard') }}"
   class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door me-2"></i>
                Dashboard
            </a>
        </li>

        <div class="menu-title">APPLICATIONS</div>

        <li class="nav-item">
            <a href="{{ route('companies.index') }}"
   class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                <i class="bi bi-ticket me-2"></i>
                Company List
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('owners.index') }}" class="nav-link">
                <i class="bi bi-chat-left-text me-2"></i>
                Owner List
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('registrations.index') }}" class="nav-link">
                <i class="bi bi-calendar me-2"></i>
                Register List
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('roles.index') }}" class="nav-link">
                <i class="bi bi-calendar me-2"></i>
                Role List
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('company_admin.employees.index') }}" class="nav-link">
                <i class="bi bi-calendar me-2"></i>
                Employee List
            </a>
        </li>

        

        <div class="menu-title">COMPONENTS</div>

        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="bi bi-file-earmark-text me-2"></i>
                Forms
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="bi bi-table me-2"></i>
                Tables
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="bi bi-bar-chart me-2"></i>
                Charts
            </a>
        </li>

    </ul>

</div>