<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Arisan PKK KarangKedawung') — Guyub, Rukun & Transparan</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        pkk: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        rosepkk: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                        },
                        amberpkk: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome & Lucide Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Canvas Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
        }
        .btn-pkk {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-pkk:hover {
            background: linear-gradient(135deg, #14532d 0%, #15803d 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }
        .btn-rose {
            background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-rose:hover {
            background: linear-gradient(135deg, #be123c 0%, #e11d48 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
        }
        .badge-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-pkk-200 selection:text-pkk-900 pb-20 md:pb-6">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 glass-header border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand Logo & Title -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pkk-600 to-pkk-700 flex items-center justify-center text-white shadow-md shadow-pkk-600/30 group-hover:scale-105 transition">
                        <i class="fa-solid fa-users-rays text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-lg text-slate-800 tracking-tight leading-tight">Arisan PKK</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-pkk-100 text-pkk-800 rounded-full border border-pkk-200">KarangKedawung</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">Guyub, Rukun & Transparan</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('dashboard*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('groups.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('groups*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Kelompok</span>
                        </a>
                        <a href="{{ route('members.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('members*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                            <i class="fa-solid fa-users"></i>
                            <span>Anggota</span>
                        </a>
                        <a href="{{ route('payments.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('payments.index*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Iuran & Verifikasi</span>
                            @php
                                $pendingVerifCount = \App\Models\Payment::where('payment_status', 'pending_verification')->count();
                            @endphp
                            @if($pendingVerifCount > 0)
                                <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full animate-pulse">{{ $pendingVerifCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('notifications.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('notifications*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-emerald-600 text-base"></i>
                            <span>Pengingat WA</span>
                        </a>
                        <a href="{{ route('reports.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('reports*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span>Laporan Kas</span>
                        </a>
                    @else
                        <a href="{{ route('payments.member') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('payments.member*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                            <i class="fa-solid fa-wallet"></i>
                            <span>Tagihan Saya</span>
                        </a>
                    @endif

                    <a href="{{ route('calendar') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('calendar*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2">
                        <i class="fa-regular fa-calendar-days"></i>
                        <span>Kalender</span>
                    </a>
                    <a href="{{ route('transparency') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('transparency*') ? 'bg-pkk-50 text-pkk-700 border border-pkk-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition flex items-center gap-2 text-rose-700">
                        <i class="fa-solid fa-eye text-rose-500"></i>
                        <span>Transparansi</span>
                    </a>
                </nav>

                <!-- User Profile & Action Dropdown -->
                <div class="flex items-center gap-3">
                    @auth
                        <div class="relative" id="userMenuContainer">
                            <button id="userMenuBtn" onclick="toggleUserMenu()" class="flex items-center gap-2.5 p-1.5 rounded-full hover:bg-slate-100 border border-slate-200 transition focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm border border-emerald-300">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="hidden lg:block text-left pr-2">
                                    <p class="text-xs font-bold text-slate-800 leading-none truncate max-w-[130px]">{{ auth()->user()->name }}</p>
                                    <span class="text-[10px] font-semibold {{ auth()->user()->isAdmin() ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ auth()->user()->isAdmin() ? 'Pengurus / Admin' : 'Anggota PKK' }}
                                    </span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs hidden lg:block"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                                <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/70 rounded-t-xl">
                                    <p class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ auth()->user()->phone }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ auth()->user()->isAdmin() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                        {{ auth()->user()->isAdmin() ? 'Ketua / Pengurus' : 'Anggota' }}
                                    </span>
                                </div>

                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    <i class="fa-solid fa-user-gear text-slate-400 w-4"></i>
                                    <span>Profil Saya</span>
                                </a>

                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('activity_logs.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-clock-rotate-left text-slate-400 w-4"></i>
                                        <span>Log Aktivitas Admin</span>
                                    </a>
                                @endif

                                <div class="border-t border-slate-100 my-1"></div>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-rose-500 w-4"></i>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn-pkk px-4 py-2 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>Masuk</span>
                        </a>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Flash Messages Alerts -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div class="w-7 h-7 rounded-full bg-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="flex-grow">
                    <h4 class="text-sm font-bold">Berhasil!</h4>
                    <p class="text-xs text-emerald-800 mt-0.5 leading-relaxed">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-sm p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div class="w-7 h-7 rounded-full bg-rose-200 text-rose-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-grow">
                    <h4 class="text-sm font-bold">Perhatian!</h4>
                    <p class="text-xs text-rose-800 mt-0.5 leading-relaxed">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-sm p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 flex items-start gap-3 shadow-sm">
                <div class="w-7 h-7 rounded-full bg-sky-200 text-sky-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-xs text-sky-800 leading-relaxed font-medium">{{ session('info') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-sky-600 hover:text-sky-900 text-sm p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-sm">
                <div class="w-7 h-7 rounded-full bg-rose-200 text-rose-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="flex-grow">
                    <h4 class="text-sm font-bold">Mohon Periksa Kembali:</h4>
                    <ul class="list-disc list-inside text-xs text-rose-800 mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar (Very Friendly for Ibu-Ibu PKK!) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2 px-3 shadow-lg">
        <div class="flex items-center justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard*') ? 'text-pkk-700 font-bold' : 'text-slate-500' }}">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px]">Beranda</span>
            </a>

            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('groups.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('groups*') ? 'text-pkk-700 font-bold' : 'text-slate-500' }}">
                    <i class="fa-solid fa-layer-group text-lg"></i>
                    <span class="text-[10px]">Kelompok</span>
                </a>
                <a href="{{ route('payments.index') }}" class="flex flex-col items-center gap-1 relative {{ request()->routeIs('payments.index*') ? 'text-pkk-700 font-bold' : 'text-slate-500' }}">
                    <i class="fa-solid fa-money-bill-wave text-lg"></i>
                    <span class="text-[10px]">Iuran</span>
                    @if(isset($pendingVerifCount) && $pendingVerifCount > 0)
                        <span class="absolute -top-1 -right-2 bg-rose-500 text-white text-[9px] font-bold px-1.5 rounded-full">{{ $pendingVerifCount }}</span>
                    @endif
                </a>
                <a href="{{ route('members.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('members*') ? 'text-pkk-700 font-bold' : 'text-slate-500' }}">
                    <i class="fa-solid fa-users text-lg"></i>
                    <span class="text-[10px]">Anggota</span>
                </a>
            @else
                <a href="{{ route('payments.member') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('payments.member*') ? 'text-pkk-700 font-bold' : 'text-slate-500' }}">
                    <i class="fa-solid fa-wallet text-lg"></i>
                    <span class="text-[10px]">Tagihan</span>
                </a>
                <a href="{{ route('calendar') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('calendar*') ? 'text-pkk-700 font-bold' : 'text-slate-500' }}">
                    <i class="fa-regular fa-calendar-days text-lg"></i>
                    <span class="text-[10px]">Jadwal</span>
                </a>
            @endif

            <a href="{{ route('transparency') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('transparency*') ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                <i class="fa-solid fa-eye text-lg"></i>
                <span class="text-[10px]">Transparansi</span>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700">PKK Desa KarangKedawung</span>
                <span>•</span>
                <span>Sistem Arisan Digital & Guyub Rukun</span>
            </div>
            <p class="text-slate-400 flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-emerald-600"></i> Sistem Informasi PKK KarangKedawung</p>
        </div>
    </footer>

    <!-- Dropdown Toggle Script -->
    <script>
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const container = document.getElementById('userMenuContainer');
            const dropdown = document.getElementById('userDropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
