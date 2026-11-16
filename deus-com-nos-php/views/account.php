<?php $title = 'Mon compte — DEUS COM NOS'; ?>
<h1>Bonjour, <?= e(user()['name']) ?></h1>
<p><?= e(user()['email']) ?></p>
<p>
    <a class="btn" href="?page=orders">Mes commandes</a>
    <?php if (is_admin()): ?><a class="btn" href="?page=admin">Accéder à l'administration</a><?php endif ?>
</p>
<form method="post" class="account-logout">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="logout">
    <button class="outline">Déconnexion</button>
</form>
