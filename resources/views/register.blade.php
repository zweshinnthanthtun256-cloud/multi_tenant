@extends('layouts.admin')

@section('content')
<div class="relative flex min-h-screen items-center justify-center bg-gray-900 font-sans overflow-hidden"
     style="background-image: url('{{ asset('images/bg.png') }}'); background-size: cover; background-position: center;">

    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <div class="relative z-10 w-full max-w-2xl rounded-3xl bg-[#1a1a1a]/90 p-8 shadow-2xl backdrop-blur-md border border-white/5">

        <div class="mb-8 flex items-center gap-4">
            <div class="relative flex h-12 w-12 items-center justify-center">

                <div class="absolute inset-0 rounded-full bg-[#00bfff]/30 animate-ping"></div>
                <div class="absolute inset-2 rounded-full bg-[#00bfff]/20 animate-pulse"></div>

                <div class="relative h-full w-full overflow-hidden rounded-full bg-transparent p-1 shadow-lg">
                    <img src="{{ asset('images/SAAScon.png') }}"
                         alt="SAAScon"
                         class="h-full w-full rounded-full object-cover">
                </div>
            </div>

            <h2 class="text-[32px] font-bold tracking-tight text-[#00bfff]">
                Register Account
            </h2>
        </div>

        <p class="mb-6 text-lg text-gray-300">
    Create an account request for SAASCon.
</p>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-500/20 border border-green-500 p-4 text-green-300">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-500/20 border border-red-500 p-4 text-red-300">
        {{ $errors->first() }}
    </div>
@endif

        <form action="{{ route('register.submit') }}"
              method="POST"
              class="space-y-4">

            @csrf

            {{-- Role --}}
            <div>
                <label class="mb-2 block text-sm text-gray-300">
                    Select Role
                </label>

                <select id="role"
                        name="role"
                        required
                        class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 outline-none focus:ring-2 focus:ring-[#00bfff]">

                    <option value="">Choose Role</option>
                    <option value="company_owner">Company Owner</option>
                    <option value="manager">Manager</option>
                    <option value="lead_user">Lead User</option>
                </select>
            </div>

            {{-- Company Owner Fields --}}
            <div id="companyFields">

                <div>
                    <input type="text"
                           name="company_name"
                           placeholder="Company Name"
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <input type="text"
                           name="username"
                           placeholder="Username"
                           required
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

                <div>
                    <input type="email"
                           name="email"
                           placeholder="Email"
                           required
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

            </div>

            {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <input type="password"
                           name="password"
                           placeholder="Password"
                           required
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

                <div>
                    <input type="password"
                           name="password_confirmation"
                           placeholder="Confirm Password"
                           required
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

            </div> --}}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <input type="text"
                           name="phone"
                           placeholder="Phone Number"
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

                <div>
                    <input type="text"
                           name="address"
                           placeholder="Address"
                           class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>

            </div>

            <div class="">

    <a href="{{ route('home') }}"
       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl border border-white/10 text-gray-300 hover:text-white hover:border-cyan-400 transition">
        ← Back
    </a>

    <button type="submit"
        class="rounded-2xl bg-[#00bfff] px-2 py-2 text-xl font-bold text-white transition hover:bg-[#009ed4] active:scale-[0.98] shadow-lg shadow-[#00bfff]/20">

        Register Request
    </button>
    <p class="mt-6 text-center text-lg text-gray-300">
                If u have an account? 
                <a href="{{ route ('admin.login') }}" class="text-[#00bfff] font-medium hover:underline decoration-2 underline-offset-4">Login</a>
            </p>

</div>
   
            </p>

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const role = document.getElementById('role');
    const companyFields = document.getElementById('companyFields');

    function toggleCompanyField() {

        if (role.value === 'company_owner') {
            companyFields.style.display = 'block';
        } else {
            companyFields.style.display = 'none';
        }
    }

    toggleCompanyField();

    role.addEventListener('change', toggleCompanyField);

});
</script>
@endsection
