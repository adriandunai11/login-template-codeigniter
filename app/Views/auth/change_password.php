<?= $this->extend('auth/layout') ?>

<?= $this->section('content') ?>
<div class="login-box">
    <div class="login-logo">
        <b>Jelszócsere</b>
    </div>

    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Első belépés után kötelező</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('auth/password/update') ?>" method="post">
                <?= csrf_field() ?>

                <div class="input-group mb-3">
                    <input type="password" name="current_password" class="form-control" placeholder="Jelenlegi jelszó"
                        required>
                    <span class="input-group-text">
                        <i class="fa-solid fa-unlock-keyhole"></i>
                    </span>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="new_password" class="form-control" placeholder="Új jelszó" required>
                    <span class="input-group-text">
                        <i class="fa-solid fa-key"></i>
                    </span>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="new_password_confirm" class="form-control" placeholder="Új jelszó újra"
                        required>
                    <span class="input-group-text">
                        <i class="fa-solid fa-key"></i>
                    </span>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Mentés
                    </button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <a href="<?= site_url('auth/logout') ?>" class="text-muted">Kilépés</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>