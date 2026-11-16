<?php /** Connexion et inscription ($page vaut « login » ou « register »). */
$isLogin = ($page === 'login');
$title = ($isLogin ? 'Connexion' : 'Créer un compte') . ' — DEUS COM NOS';
?>
<div class="panel auth-panel">
    <h1><?= $isLogin ? 'Connexion' : 'Créer un compte' ?></h1>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $isLogin ? 'login' : 'register' ?>">
        <?php if (!$isLogin): ?>
            <label>Nom complet<input class="field" name="name" required minlength="2" maxlength="120" autocomplete="name"></label>
        <?php endif ?>
        <label>E-mail<input class="field" type="email" name="email" required maxlength="190" autocomplete="email"></label>
        <label>Mot de passe<input class="field" type="password" name="password" required minlength="8" maxlength="72" autocomplete="<?= $isLogin ? 'current-password' : 'new-password' ?>"></label>
        <?php if (!$isLogin): ?><p class="muted">8 caractères minimum.</p><?php endif ?>
        <button><?= $isLogin ? 'Se connecter' : "S'inscrire" ?></button>
    </form>
    <p><a href="?page=<?= $isLogin ? 'register' : 'login' ?>"><?= $isLogin ? 'Pas encore de compte ? Créer un compte' : 'Déjà inscrit ? Se connecter' ?></a></p>
</div>
