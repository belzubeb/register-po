<?php
$user = Auth::user();
$route = isset($_GET['r']) ? $_GET['r'] : 'dashboard';
$flash = flash_get();
$on = function ($routes) use ($route) { return in_array($route, $routes, true) ? ' class="on" aria-current="page"' : ''; };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - Register PO</title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="manifest" href="<?= e(asset('manifest.webmanifest')) ?>">
    <meta name="theme-color" content="#1F3864">
    <link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Register PO">
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <symbol id="i-home" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-7H9v7H5a2 2 0 0 1-2-2z"/></symbol>
        <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></symbol>
        <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
        <symbol id="i-activity" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></symbol>
        <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
        <symbol id="i-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></symbol>
        <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></symbol>
        <symbol id="i-edit" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></symbol>
        <symbol id="i-alert" viewBox="0 0 24 24"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></symbol>
        <symbol id="i-down" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></symbol>
        <symbol id="i-book" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></symbol>
        <symbol id="i-menu" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></symbol>
    </defs>
</svg>

<a class="skip" href="#main">Lewati ke konten</a>
<div class="shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= e(url('dashboard')) ?>" aria-label="Register PO - ke dashboard">
            <span class="brand-logo"><img src="<?= e(asset('img/logo.png')) ?>" alt="Starion PT WOOIN"></span>
            <span class="brand-sub">Register Proposal dan PO</span>
        </a>
        <nav class="nav" aria-label="Menu utama">
            <a href="<?= e(url('dashboard')) ?>"<?= $on(['dashboard']) ?>><?= icon('home') ?> Dashboard</a>
            <a href="<?= e(url('documents')) ?>"<?= $on(['documents', 'documents/show', 'documents/edit']) ?>><?= icon('file') ?> Dokumen</a>
            <?php if (Policy::create($user)): ?>
                <a href="<?= e(url('documents/create')) ?>"<?= $on(['documents/create']) ?>><?= icon('plus') ?> Dokumen baru</a>
            <?php endif; ?>
            <a href="<?= e(url('panduan')) ?>"<?= $on(['panduan']) ?>><?= icon('book') ?> Panduan</a>
            <?php if (Policy::isDev($user)): ?>
                <div class="sep">Developer</div>
                <a href="<?= e(url('activity')) ?>"<?= $on(['activity']) ?>><?= icon('activity') ?> Aktivitas</a>
                <a href="<?= e(url('users')) ?>"<?= $on(['users']) ?>><?= icon('users') ?> Pengguna</a>
            <?php endif; ?>
        </nav>
        <div class="userbox">
            <b><?= e($user['name']) ?></b>
            <span><?= e(Auth::roleLabel($user)) ?><?= $user['dept_code'] ? ' - ' . e($user['dept_code']) : '' ?></span>
            <form method="POST" action="<?= e(url('logout')) ?>"><?= csrf_field() ?>
                <button type="submit"><?= icon('out') ?> Keluar</button>
            </form>
        </div>
    </aside>
    <div class="scrim" id="scrim"></div>

    <div class="content">
        <header class="topbar">
            <button type="button" class="menu-btn" id="menu-btn" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar"><?= icon('menu') ?></button>
            <img src="<?= e(asset('img/logo.png')) ?>" alt="Starion PT WOOIN">
        </header>
        <main id="main">
            <?php if (!empty($flash['ok'])): foreach ($flash['ok'] as $m): ?>
                <div class="flash ok" role="status"><?= icon('check') ?><div><?= e($m) ?></div></div>
            <?php endforeach; endif; ?>
            <?php if (!empty($flash['errors'])): ?>
                <div class="flash err" role="alert"><?= icon('alert') ?><ul><?php foreach ($flash['errors'] as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script>
    (function () {
        var sb = document.getElementById('sidebar'), mb = document.getElementById('menu-btn'), sc = document.getElementById('scrim');
        function toggle() {
            var o = sb.classList.toggle('open');
            sc.classList.toggle('open', o);
            mb.setAttribute('aria-expanded', o);
            document.body.classList.toggle('no-scroll', o);
        }
        mb.addEventListener('click', toggle);
        sc.addEventListener('click', toggle);
    })();
</script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () { navigator.serviceWorker.register(<?= json_encode(asset('sw.js')) ?>).catch(function () {}); });
    }
</script>
</body>
</html>