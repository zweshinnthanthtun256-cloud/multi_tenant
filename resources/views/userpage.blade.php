<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAASCon - Multi Tenant SaaS Platform</title>

    @vite(['resources/css/app.css'])
</head>

<body class="bg-slate-950 text-white overflow-x-hidden">

    <!-- Background Glow -->
    <div class="fixed top-0 left-0 w-[500px] h-[500px] bg-cyan-500/10 blur-[120px] rounded-full"></div>
    <div class="fixed bottom-0 right-0 w-[500px] h-[500px] bg-lime-500/10 blur-[120px] rounded-full"></div>

    <!-- Navbar -->
    <nav class="relative z-50 flex items-center justify-between px-6 lg:px-24 py-6">

        <!-- Logo -->
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/SAAScon.png')}}" width="40px" height="40px">

            <div>
                <h1 class="text-2xl font-bold">SAASCon</h1>
            </div>
        </div>

        <!-- Menu -->
        <div class="hidden lg:flex items-center gap-10 text-gray-300">
            <a href="#features" class="hover:text-white transition">Features</a>
            <a href="#pricing" class="hover:text-white transition">Pricing</a>
            <a href="#faq" class="hover:text-white transition">FAQ</a>
            <a href="#contact" class="hover:text-white transition">Contact</a>
        </div>

        <!-- Buttons -->
        <div class="flex items-center gap-4">

            <a href="{{ route('register') }}"
                class="hidden md:block text-gray-300 hover:text-white">
                Register
            </a>

            <a href="#"
                class="bg-cyan-500 hover:bg-cyan-400 text-black font-semibold px-6 py-3 rounded-full transition">
                Start Free Trial
            </a>

        </div>
    </nav>

    <!-- Hero -->
    <section class="relative px-6 lg:px-24 pt-16 pb-24">

        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- Left -->
            <div>

                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-300 text-sm mb-8">
                    Multi-Tenant SaaS Platform
                </span>

                <h1 class="text-5xl md:text-7xl font-bold leading-tight mb-8">
                    Build & Manage Multiple Companies
                    <span class="text-cyan-400">
                        On One Platform
                    </span>
                </h1>

                <p class="text-xl text-gray-400 leading-relaxed mb-10">
                    Create companies, assign owners,
                    manage isolated databases,
                    roles, permissions and subscriptions
                    from a single admin dashboard.
                </p>

                <div class="flex flex-col sm:flex-row gap-5">

                    <a href="#"
                        class="bg-cyan-500 hover:bg-cyan-400 text-black px-8 py-4 rounded-full font-semibold transition">
                        Start Free Trial →
                    </a>

                    <a href="#"
                        class="border border-white/20 hover:border-cyan-400 px-8 py-4 rounded-full transition">
                        View Demo
                    </a>

                </div>

            </div>

            <!-- Right Dashboard Preview -->
            <div>

                <div
                    class="bg-white/5 border border-white/10 rounded-3xl p-4 backdrop-blur-xl shadow-2xl">

                    <img
                        src="{{ asset('images/dashboard.PNG') }}"
                        alt="Dashboard Preview"
                        class="rounded-2xl w-full">

                </div>

            </div>

        </div>

    </section>

    <!-- Features -->
    <section id="features" class="px-6 lg:px-24 py-24">

        <div class="text-center mb-16">

            <h2 class="text-4xl font-bold mb-4">
                Powerful SaaS Features
            </h2>

            <p class="text-gray-400">
                Everything needed to run a modern multi-tenant platform.
            </p>

        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">

            <div class="bg-white/5 border border-white/10 rounded-3xl p-8">
                <h3 class="font-semibold text-xl mb-3">🏢 Company Management</h3>
                <p class="text-gray-400">
                    Create and manage unlimited companies.
                </p>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-8">
                <h3 class="font-semibold text-xl mb-3">👤 Owner Management</h3>
                <p class="text-gray-400">
                    Assign company owners with secure access.
                </p>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-8">
                <h3 class="font-semibold text-xl mb-3">🔐 Roles & Permissions</h3>
                <p class="text-gray-400">
                    Fine-grained permission control.
                </p>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-8">
                <h3 class="font-semibold text-xl mb-3">⚡ Tenant Databases</h3>
                <p class="text-gray-400">
                    Separate databases for every company.
                </p>
            </div>

        </div>

    </section>

    <!-- How It Works -->
    <section class="px-6 lg:px-24 py-24 bg-white/[0.02]">

        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold">How It Works</h2>
        </div>

        <div class="grid md:grid-cols-4 gap-8">

            <div class="text-center">
                <div class="text-5xl mb-4">1️⃣</div>
                <h3>Create Company</h3>
            </div>

            <div class="text-center">
                <div class="text-5xl mb-4">2️⃣</div>
                <h3>Create Owner</h3>
            </div>

            <div class="text-center">
                <div class="text-5xl mb-4">3️⃣</div>
                <h3>Provision Database</h3>
            </div>

            <div class="text-center">
                <div class="text-5xl mb-4">4️⃣</div>
                <h3>Start Using Platform</h3>
            </div>

        </div>

    </section>

    <!-- Pricing -->
    <section id="pricing" class="px-6 lg:px-24 py-24">

        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold">Simple Pricing</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">

            <div class="bg-white/5 border border-white/10 rounded-3xl p-8">
                <h3 class="text-2xl font-bold mb-4">Starter</h3>
                <p class="text-5xl font-bold mb-6">$9</p>
                <p class="text-gray-400">Small businesses</p>
            </div>

            <div class="bg-cyan-500 text-black rounded-3xl p-8">
                <h3 class="text-2xl font-bold mb-4">Business</h3>
                <p class="text-5xl font-bold mb-6">$29</p>
                <p>Most popular plan</p>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-8">
                <h3 class="text-2xl font-bold mb-4">Enterprise</h3>
                <p class="text-5xl font-bold mb-6">Custom</p>
                <p class="text-gray-400">Large organizations</p>
            </div>

        </div>

    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 px-6 lg:px-24">

        <div class="flex flex-col md:flex-row justify-between items-center gap-4">

            <p class="text-gray-400">
                © {{ date('Y') }} SAASCon. All rights reserved.
            </p>

            <div class="flex gap-6 text-gray-400">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Support</a>
            </div>

        </div>

    </footer>

</body>
</html>
```
