<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akaun - CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#C62828;--secondary:#2453A6;--secondary-soft:#EFF6FF;--danger:#B91C1C;--danger-soft:#FEE2E2;--text:#0F172A;--muted:#475569;--line:#E2E8F0;--bg:#F7F8FA}
        *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .register-page{min-height:100vh;display:grid;place-items:center;padding:34px 18px}
        .register-shell{width:min(980px,100%);display:grid;grid-template-columns:.85fr 1.15fr;border:1px solid var(--line);border-radius:12px;background:#fff;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.08)}
        .register-copy{padding:36px;background:linear-gradient(135deg,#fff 0%,#f8fbff 100%);border-right:1px solid var(--line)}
        .brand{display:flex;align-items:center;gap:8px;color:var(--text);font-size:24px;font-weight:900;text-decoration:none}.brand img{width:52px;height:52px}.brand b{color:var(--primary)}.brand span span{color:var(--secondary)}
        .register-copy h1{margin:34px 0 12px;font-size:34px;line-height:1.12;letter-spacing:0}.register-copy p{margin:0;color:var(--muted);line-height:1.65;font-weight:600}
        .register-card{padding:32px}.register-card h2{margin:0;font-size:24px}.register-card>p{margin:7px 0 22px;color:var(--muted);font-size:14px}
        .alert{margin-bottom:16px;padding:12px 14px;border:1px solid var(--danger-soft);border-radius:8px;background:var(--danger-soft);color:var(--danger);font-weight:800}
        .role-tabs{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px;padding:6px;border:1px solid var(--line);border-radius:10px;background:#F8FAFC}.role-tabs label{display:grid;place-items:center;min-height:40px;border-radius:8px;font-weight:900;color:var(--muted);cursor:pointer}.role-tabs input{position:absolute;opacity:0;pointer-events:none}.role-tabs label.is-active{background:var(--secondary);color:#fff}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.field{display:grid;gap:6px}.field.full{grid-column:1/-1}.field label{color:var(--muted);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.03em}
        input,select{width:100%;min-height:42px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:var(--text);padding:0 12px;font:inherit;font-weight:700;outline:0}input:focus,select:focus{border-color:var(--secondary);box-shadow:0 0 0 3px rgba(36,83,166,.13)}
        .staff-only{display:none}.is-staff .student-only{display:none}.is-staff .staff-only{display:grid}
        .actions{display:flex;align-items:center;gap:12px;margin-top:20px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;border:1px solid var(--secondary);border-radius:9px;background:var(--secondary);color:#fff;padding:0 18px;font-weight:900;text-decoration:none;cursor:pointer}.button.secondary{background:#fff;color:var(--secondary)}.actions .button:first-child{flex:1}
        @media(max-width:820px){.register-shell{grid-template-columns:1fr}.register-copy{display:none}.form-grid{grid-template-columns:1fr}.actions{align-items:stretch;flex-direction:column}.button{width:100%}}
    </style>
</head>
<body>
    <main class="register-page">
        <section class="register-shell">
            <aside class="register-copy">
                <a class="brand" href="{{ url('/') }}"><img src="{{ asset('images/koperasi-logo.svg?v=2') }}" alt=""><span><b>Coop</b><span>Best</span></span></a>
                <h1>Daftar akaun portal koperasi.</h1>
                <p>Pelajar dan staff boleh mencipta akaun sendiri untuk akses permohonan, saham, tempahan dan modul berkaitan.</p>
            </aside>

            <section class="register-card">
                <h2>Daftar Akaun</h2>
                <p>Pilih jenis akaun dan lengkapkan maklumat asas.</p>

                @if ($errors->any())
                    <div class="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                    @csrf
                    <div class="role-tabs">
                        <label data-role-tab="student"><input type="radio" name="role" value="student" @checked(old('role', 'student') === 'student')> Pelajar</label>
                        <label data-role-tab="staff"><input type="radio" name="role" value="staff" @checked(old('role') === 'staff')> Staff</label>
                    </div>

                    <div class="form-grid">
                        <div class="field student-only"><label>No Matrik</label><input name="no_matrik" value="{{ old('no_matrik') }}"></div>
                        <div class="field staff-only"><label>Jenis Staff</label><select name="staff_type"><option value="lecturer_member" @selected(old('staff_type') === 'lecturer_member')>Pensyarah / Staf Akademik</option><option value="coop_staff" @selected(old('staff_type') === 'coop_staff')>Pekerja Koperasi</option><option value="clothing_staff" @selected(old('staff_type') === 'clothing_staff')>Staff Pengurusan Baju</option></select></div>
                        <div class="field"><label>Nama Penuh</label><input name="nama" value="{{ old('nama') }}" required></div>
                        <div class="field"><label>No KP</label><input name="nric" value="{{ old('nric') }}" required></div>
                        <div class="field student-only"><label>Kelas</label><select name="kelas"><option value="">Pilih kelas</option>@foreach($studentClasses as $class)<option value="{{ $class }}" @selected(old('kelas') === $class)>{{ $class }}</option>@endforeach</select></div>
                        <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}"></div>
                        <div class="field"><label>No Telefon</label><input name="no_tel" value="{{ old('no_tel') }}"></div>
                        <div class="field"><label>Password</label><input type="password" name="password" required></div>
                        <div class="field"><label>Sahkan Password</label><input type="password" name="password_confirmation" required></div>
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">Daftar Akaun</button>
                        <a class="button secondary" href="{{ route('login') }}">Log Masuk</a>
                    </div>
                </form>
            </section>
        </section>
    </main>
    <script>
        const form = document.getElementById('registerForm');
        const roleTabs = form.querySelectorAll('[data-role-tab]');
        function syncRole(){
            const role = form.querySelector('[name="role"]:checked')?.value || 'student';
            form.classList.toggle('is-staff', role === 'staff');
            roleTabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.roleTab === role));
        }
        roleTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const input = tab.querySelector('[name="role"]');
                input.checked = true;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        form.querySelectorAll('[name="role"]').forEach((input)=>input.addEventListener('change', syncRole));
        syncRole();
    </script>
</body>
</html>
