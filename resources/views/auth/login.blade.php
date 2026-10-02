<!DOCTYPE html>
<html lang="id" class="h-full bg-gradient-to-br from-emerald-50 via-slate-50 to-rose-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Arisan PKK KarangKedawung</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-xl shadow-emerald-600/30 mb-4 transform hover:scale-105 transition">
                <i class="fa-solid fa-users-rays text-3xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Arisan PKK KarangKedawung</h1>
            <p class="text-sm text-slate-600 mt-1 font-medium">Sistem Arisan Digital yang Ramah, Guyub & Transparan 🌸</p>
        </div>

        <!-- Card Form -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 p-6 sm:p-8">
            
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5"></i>
                    <div>
                        @foreach($errors->all() as $err)
                            <p>{{ $err }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="login_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor WhatsApp atau Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <input type="text" 
                               name="login_id" 
                               id="login_id" 
                               value="{{ old('login_id', '081234567890') }}" 
                               required 
                               placeholder="Contoh: 081234567890" 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition font-medium text-slate-800 bg-slate-50/50">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi (Password)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               value="password"
                               required 
                               placeholder="Masukkan password Anda" 
                               class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition font-medium text-slate-800 bg-slate-50/50">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-eye" id="togglePassIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" checked>
                        <span class="text-xs text-slate-600 font-medium">Ingat saya di HP ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/40 hover:from-emerald-700 hover:to-teal-700 transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk ke Akun Saya</span>
                </button>
            </form>

            <!-- Quick Demo Login Buttons for convenience -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Akses Cepat (Akun Uji Coba)</p>
                
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillCredentials('081234567890', 'password')" class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 text-left transition flex items-center gap-2 group">
                        <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs flex-shrink-0">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[11px] font-bold text-emerald-950 truncate">Ibu Ketua (Admin)</p>
                            <p class="text-[9px] text-emerald-700">081234567890</p>
                        </div>
                    </button>

                    <button type="button" onclick="fillCredentials('081234567891', 'password')" class="p-2.5 rounded-xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-left transition flex items-center gap-2 group">
                        <div class="w-7 h-7 rounded-lg bg-rose-600 text-white flex items-center justify-center text-xs flex-shrink-0">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[11px] font-bold text-rose-950 truncate">Ibu Endang (Anggota)</p>
                            <p class="text-[9px] text-rose-700">081234567891</p>
                        </div>
                    </button>
                </div>
            </div>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            Pemberdayaan Kesejahteraan Keluarga (PKK) • Desa KarangKedawung
        </p>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('togglePassIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function fillCredentials(phone, pass) {
            document.getElementById('login_id').value = phone;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
