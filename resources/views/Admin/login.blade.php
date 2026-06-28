@extends('layouts.admin') 

@section('content')
<div class="relative flex min-h-screen items-center justify-center bg-gray-900 font-sans overflow-hidden" 
     style="background-image: url('{{ asset('images/bg.png') }}'); background-size: cover; background-position: center;">
    
    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <div class="relative z-10 w-full max-w-md rounded-3xl bg-[#1a1a1a]/90 p-8 shadow-2xl backdrop-blur-md border border-white/5">
        
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
                Login Account
            </h2> 
        </div>

        <p class="mb-6 text-lg text-gray-300">Sign in with your crediential account.</p>

        @if ($errors->any())
            <div class="bg-red-500 text-white p-3 rounded-xl">
                {{ $errors->first() }}
            </div>
            
        @endif
        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf

            {{-- <div class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="username" placeholder="username" value="{{ old('username') }}" required
                        class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
                </div>
                
            </div> --}}

            <div>
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required
                    class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
            </div>

            <div>
                <input type="password" name="password" placeholder="Password" required
                    class="w-full rounded-2xl bg-[#333333] p-5 text-gray-200 placeholder-gray-500 outline-none focus:ring-2 focus:ring-[#00bfff]">
            </div>

           

            <button type="submit"
                class="w-full rounded-2xl bg-[#00bfff] py-4 text-xl font-bold text-white transition duration-200 hover:bg-[#009ed4] active:scale-[0.98] shadow-lg shadow-[#00bfff]/20">
                Submit
            </button>

            <p class="mt-6 text-center text-lg text-gray-300">
                If u don't have an account? 
                <a href="{{ route ('register') }}" class="text-[#00bfff] font-medium hover:underline decoration-2 underline-offset-4">Register</a>
            </p>
        </form>
    </div>
</div>
@endsection