<?php
declare(strict_types=1);

// Point d’entrée Apache : l’interface PHP est servie depuis le dossier frontend.
header('Location: frontend/index.php', true, 302);
exit;
