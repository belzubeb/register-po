<?php $flash = flash_get(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Register PO</title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="manifest" href="<?= e(asset('manifest.webmanifest')) ?>">
    <meta name="theme-color" content="#1F3864">
    <link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Register PO">
</head>
<body class="login">
<form method="POST" action="<?= e(url('login')) ?>" class="login-card">
    <?= csrf_field() ?>
    <img class="login-logo" src="<?= e(asset('img/logo.png')) ?>" alt="Starion PT WOOIN">
    <h1>Register Proposal dan PO</h1>
    <p class="mut" style="margin:.4rem 0 0">Masuk dengan akun lu.</p>
    <?php if (!empty($flash['errors'])): ?><div class="flash err" role="alert" style="margin-top:1rem"><?= e($flash['errors'][0]) ?></div><?php endif; ?>
    <div class="field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= e(old('email')) ?>" required autofocus autocomplete="username">
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
    </div>
    <div class="field"><button type="submit" class="btn p block">Masuk</button></div>
</form>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () { navigator.serviceWorker.register(<?= json_encode(asset('sw.js')) ?>).catch(function () {}); });
    }
</script>
</body>
</html>