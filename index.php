<?php
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/lib/layout.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php skylon_head($cfg, $cfg['site_title']); ?>
</head>
<body class="page-welcome">
<?php skylon_header($cfg); ?>

<main class="hero">
  <h1 class="hero__title">
    <img src="assets/img/welcome.webp" alt="Welcome" width="1600" height="572">
  </h1>
  <a href="inscription" class="hero__next" aria-label="Accéder au formulaire d’inscription">
    <svg viewBox="0 0 48 24" width="48" height="24" aria-hidden="true"><path d="M2 22 24 2l22 20" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <span>Formulaire d’inscription</span>
  </a>
</main>

<?php skylon_footer($cfg); ?>
</body>
</html>
