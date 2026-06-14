<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work4Village - Login</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/App.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body style="margin: 0; padding: 0;">
    <div style="display: flex; min-height: 100vh; background: linear-gradient(135deg, #0f766e 0%, #0d645e 100%); align-items: center; justify-content: center;">
        <div class="glass-panel animate-fade-in" style="width: 100%; max-width: 400px; padding: 2.5rem; background: var(--surface);">
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: var(--primary-light); color: var(--primary); margin-bottom: 1rem;">
                    <i data-lucide="leaf" style="width: 32px; height: 32px;"></i>
                </div>
                <h2 style="font-size: 1.5rem;">Work4Village</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Masuk untuk melanjutkan</p>
            </div>

            @if ($errors->any())
                <div style="background: #fef2f2; color: var(--danger); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input 
                        type="email" 
                        name="email"
                        class="form-input" 
                        placeholder="Masukkan email"
                        value="{{ old('email') }}"
                        required
                    />
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Password</label>
                    <input 
                        type="password" 
                        name="password"
                        class="form-input" 
                        placeholder="Masukkan password"
                        required
                    />
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Masuk Sistem
                </button>
            </form>
            
            <div style="margin-top: 2rem; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                <p>Admin: admin@village.com / admin123</p>
                <p>Pengawas: pengawas@village.com / pengawas123</p>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <span style="color: var(--text-muted);">Belum punya akun? </span>
                <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                    Daftar di sini
                </a>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
