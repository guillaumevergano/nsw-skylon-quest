<?php
session_start();
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/lib/layout.php';
if (empty($_SESSION['inscrit'])) {
    header('Location: inscription');
    exit;
}
$attente = $_SESSION['inscrit'] === 'attente';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php skylon_head($cfg, 'Merci — ' . $cfg['site_title'], true); ?>
</head>
<body class="page-merci">
<?php skylon_header($cfg); ?>

<main class="merci">
  <h1 class="merci__title">
    <img src="assets/img/merci.webp" alt="Merci beaucoup pour ton inscription !" width="1600" height="178">
  </h1>
  <div class="panel panel--merci">
    <?php if ($attente): ?>
      <p>Toutes les équipes sont complètes pour le moment : tu es sur la liste d’attente. On te recontacte par mail si une place se libère&nbsp;!</p>
    <?php else: ?>
      <p>Un mail de confirmation avec les infos utiles vous sera envoyé prochainement si vous êtes sélectionné·es&nbsp;!</p>
    <?php endif; ?>
    <p>À très vite,<br>Les équipes Eight Lines x NSW x Yard</p>
  </div>
</main>

<?php skylon_footer($cfg); ?>
</body>
</html>
