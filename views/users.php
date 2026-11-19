<div class="page-h"><div><h1>Pengguna</h1><p class="sub">Atur siapa yang bisa masuk dan perannya.</p></div></div>
<div class="cols wide">
    <section class="card flush">
        <div class="card-h"><h2>Daftar pengguna</h2><span class="mut small"><?= count($users) ?> akun</span></div>
        <div class="card-b"><div class="tw"><table>
            <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Dept</th></tr></thead>
            <tbody>
            <?php foreach ($users as $x): ?>
                <tr><td><?= e($x['name']) ?></td><td><?= e($x['email']) ?></td><td><?= e(Auth::roleLabel($x)) ?></td><td><?= e($x['dept_code'] ?: '-') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </section>

    <form method="POST" action="<?= e(url('users/store')) ?>" class="card">
        <?= csrf_field() ?>
        <div class="card-h"><h2>Tambah pengguna</h2></div>
        <div class="card-b stack">
            <div><label for="name">Nama</label><input id="name" name="name" value="<?= e(old('name')) ?>" required></div>
            <div><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e(old('email')) ?>" required></div>
            <div><label for="password">Password (min. 8 karakter)</label><input id="password" type="password" name="password" required autocomplete="new-password"></div>
            <div><label for="role">Peran</label>
                <select id="role" name="role">
                    <option value="admin">Admin dept</option><option value="manager">Manager lokal</option><option value="dev">Developer</option>
                </select></div>
            <div><label for="department_id">Dept (kosongkan untuk Developer)</label>
                <select id="department_id" name="department_id"><option value="">-</option>
                    <?php foreach ($departments as $dp): ?><option value="<?= (int) $dp['id'] ?>"><?= e($dp['code']) ?> - <?= e($dp['name']) ?></option><?php endforeach; ?>
                </select></div>
            <button type="submit" class="btn p">Simpan pengguna</button>
        </div>
    </form>
</div>
