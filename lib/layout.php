<?php
// Fragments communs : <head>, header et footer.

// URL d'un asset avec un paramètre de version (date de modification) : évite que le
// navigateur garde une vieille copie du CSS/JS après une mise à jour.
function skylon_asset(string $path): string
{
    $file = __DIR__ . '/../' . $path;
    $v = is_file($file) ? filemtime($file) : 0;
    return htmlspecialchars($path . '?v=' . $v, ENT_QUOTES, 'UTF-8');
}

function skylon_head(array $cfg, string $title, bool $noindex = false): void
{
    $e = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($title) ?></title>
  <meta name="description" content="Skylon Quest : la chasse au trésor signée Eight Lines x NSW dans Paris pour le lancement de la Nike Skylon 11. Cinq challenges, huit équipes, une crew gagnante. Inscris-toi.">
  <meta property="og:title" content="<?= $e($title) ?>">
  <meta property="og:description" content="Chasse au trésor dans Paris pour le lancement de la Skylon 11. Cinq challenges, huit équipes, une année de Navigo Pass à gagner.">
  <meta property="og:image" content="assets/img/skylon-logo.webp">
  <?php if ($noindex): ?><meta name="robots" content="noindex"><?php endif; ?>
  <meta name="theme-color" content="#0b0203">
  <link rel="icon" href="assets/img/nsw.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Michroma&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="preload" as="image" href="assets/img/bg-wide.webp" media="(min-aspect-ratio: 1/1)">
  <link rel="preload" as="image" href="assets/img/bg-tall.webp" media="(max-aspect-ratio: 1/1)">
  <link rel="stylesheet" href="<?= skylon_asset('assets/css/style.css') ?>">
    <?php
}

function skylon_header(array $cfg): void
{
    $e = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    ?>
<div class="bg" aria-hidden="true"></div>
<header class="site-header">
  <a class="brand" href="<?= $e($cfg['site_url']) ?>" aria-label="Skylon Quest — accueil">
    <img src="assets/img/skylon-logo.svg" alt="Skylon Quest x NSW" width="1861" height="378">
  </a>
  <div class="partners">
    <img src="assets/img/nsw.svg" alt="NSW" width="348" height="147">
    <img src="assets/img/eightlines.svg" alt="Eight Lines" width="305" height="147">
  </div>
</header>
    <?php
}

function skylon_footer(array $cfg): void
{
    ?>
<footer class="site-footer">
  <p>Skylon Quest — événement organisé par Eight Lines x NSW à Paris. Les données collectées servent uniquement à la gestion des inscriptions et de l’événement. <a href="<?= htmlspecialchars((string)$cfg['terms_url'], ENT_QUOTES, 'UTF-8') ?>">Conditions d’utilisation</a></p>
</footer>
<script src="<?= skylon_asset('assets/js/main.js') ?>" defer></script>
    <?php
}
