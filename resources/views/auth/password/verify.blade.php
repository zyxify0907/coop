<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengesahan Akaun - CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --red:#C62828; --blue:#2453A6; --text:#0F172A; --muted:#475569; --line:#E2E8F0; }
        * { box-sizing:border-box; } body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:#F7F8FA; color:var(--text); font-family:Inter,system-ui,sans-serif; }
        main { width:min(100%,520px); padding:32px; border:1px solid var(--line); border-radius:10px; background:#fff; }
        .brand { display:flex; align-items:center; gap:10px; margin-bottom:28px; color:var(--text); text-decoration:none; font-weight:700; }
        .mark { width:36px; height:36px; display:grid; place-items:center; border-radius:7px; background:var(--red); color:#fff; font-size:15px; }
        h1 { margin:0; font-size:24px; letter-spacing:-.02em; } p { margin:10px 0 24px; color:var(--muted); line-height:1.6; }
        a:last-child { display:inline-flex; min-height:40px; align-items:center; padding:0 14px; border:1px solid #BFDBFE; border-radius:7px; color:var(--blue); text-decoration:none; font-size:14px; font-weight:600; }
    </style>
</head>
<body>
    <main>
        <a class="brand" href="{{ url('/') }}"><span class="mark">CB</span><span>CoopBest</span></a>
        <h1>Pengesahan Akaun</h1>
        <p>Akaun disahkan melalui rekod No. KP dan No Matrik / staff.</p>
        <a href="{{ route('login') }}">Kembali ke login</a>
    </main>
</body>
</html>
