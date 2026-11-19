<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error <?= (int) $code ?> - Register PO</title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('img/favicon.png')) ?>">
</head>
<body class="login">
<div class="login-card">
    <img class="login-logo" src="<?= e(asset('img/logo.png')) ?>" alt="Starion PT WOOIN">
    <h1><?= (int) $code ?></h1>
    <p style="margin:.5rem 0 1.25rem"><?= e($msg ?: 'Terjadi kesalahan.') ?></p>
    <a class="btn p" href="<?= e(url('dashboard')) ?>">Kembali ke dashboard</a>
</div>
</body>
</html>