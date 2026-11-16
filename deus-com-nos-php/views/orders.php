<?php
$title = 'Mes commandes — DEUS COM NOS';
$s = $db->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC');
$s->execute([user()['id']]);
$orders = $s->fetchAll();
?>
<h1>Mes commandes</h1>
<?php if (!$orders): ?>
    <div class="catalog-empty"><h3>Aucune commande pour le moment</h3><p>Vos commandes apparaîtront ici.</p><a class="btn" href="?page=shop">Voir la collection</a></div>
<?php else: ?>
<div class="panel table-wrap">
    <table>
        <tr><th>Commande</th><th>Date</th><th>Total</th><th>Statut</th></tr>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="?page=order&amp;id=<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?> · Détails</a></td>
                <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                <td><?= money($o['total']) ?></td>
                <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
<?php endif ?>
