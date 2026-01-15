<?= $this->extend('auth/layout') ?>

<?= $this->section('content') ?>
<div class="login-box">

    <!-- LOGO -->
    <div class="text-center mb-4">
        <img src="<?= base_url('assets/adminlte/img/logo.png') ?>" alt="Miell Group"
            style="max-width: 220px; height: auto;">
    </div>

    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">
                Jelentkezz be a nyilatkozatok kitöltéséhez
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('auth/login/authenticate') ?>">
                <?= csrf_field() ?>

                <div class="input-group mb-3">
                    <input type="text" name="antra_id" class="form-control" placeholder="ANTRA azonosító"
                        autocomplete="username" required>
                    <span class="input-group-text">
                        <i class="fa-solid fa-id-badge"></i>
                    </span>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Jelszó"
                        autocomplete="current-password" required>
                    <span class="input-group-text">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                </div>

                <div class="form-check mb-3">
                    <?php $checked = !empty($_COOKIE[remember_cookie_name()] ?? null); ?>
                    <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="remember_me"
                        <?= $checked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="remember_me">
                        Emlékezz rám (30 nap)
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Belépés
                    </button>
                </div>
            </form>

            <p class="mt-4 mb-0 text-center text-muted" style="font-size: 0.85rem;">
                © <?= date('Y') ?> Miell Group
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>