<?php
// Panneau d'administration (URL : /admin via .htaccess) : connexion, effectifs, listing, vue par équipe, exports CSV.
session_start();
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/lib/form.php';
require __DIR__ . '/lib/layout.php';

header('Cache-Control: no-store');
$e = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
}
$csrfOk = static fn() => isset($_POST['csrf']) && hash_equals($_SESSION['admin_csrf'], (string)$_POST['csrf']);
$logged = !empty($_SESSION['admin_ok']);

// --- Déconnexion -----------------------------------------------------------
if ($logged && isset($_POST['logout']) && $csrfOk()) {
    unset($_SESSION['admin_ok']);
    session_regenerate_id(true);
    header('Location: admin');
    exit;
}

// --- Connexion -------------------------------------------------------------
$loginError = '';
if (!$logged && isset($_POST['login'])) {
    $tries = (int)($_SESSION['admin_tries'] ?? 0);
    $user  = trim((string)($_POST['login'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $valid = $csrfOk()
        && $tries < 10
        && hash_equals((string)$cfg['admin_user'], $user)
        && password_verify($pass, (string)$cfg['admin_password_hash']);
    if ($valid) {
        session_regenerate_id(true);
        $_SESSION['admin_ok'] = true;
        $_SESSION['admin_tries'] = 0;
        header('Location: admin');
        exit;
    }
    $_SESSION['admin_tries'] = $tries + 1;
    sleep(1); // ralentit les tentatives en boucle
    $loginError = $tries + 1 >= 10
        ? 'Trop de tentatives. Ferme le navigateur et réessaie plus tard.'
        : 'Identifiant ou mot de passe incorrect.';
}

// --- Page de connexion -----------------------------------------------------
if (!$logged) {
    http_response_code($loginError ? 401 : 200);
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php skylon_head($cfg, 'Administration — ' . $cfg['site_title'], true); ?>
</head>
<body class="page-admin">
<?php skylon_header($cfg); ?>
<main class="merci">
  <div class="panel panel--form panel--login">
    <h1 class="panel__title">Administration</h1>
    <?php if ($loginError): ?><p class="alert" role="alert"><?= $e($loginError) ?></p><?php endif; ?>
    <form method="post" action="admin" class="form form--login">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['admin_csrf']) ?>">
      <div class="field">
        <label for="login">Identifiant</label>
        <input id="login" name="login" type="text" autocomplete="username" required autofocus>
      </div>
      <div class="field">
        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <div class="form__actions">
        <button type="submit" class="btn">Se connecter</button>
      </div>
    </form>
  </div>
</main>
<?php skylon_footer($cfg); ?>
</body>
</html>
    <?php
    exit;
}

// --- Données ---------------------------------------------------------------
$capacity = (int)$cfg['team_capacity'];
$rows     = skylon_rows($cfg['storage']);          // inscriptions (col 9 = équipe)
$attente  = skylon_rows($cfg['storage_waitlist']); // liste d'attente
$counts   = skylon_count_rows($cfg, $rows);
$total    = count($rows);
$byTeam   = array_fill_keys($cfg['teams'], []);
foreach ($rows as $r) {
    if (isset($byTeam[$r[9]])) {
        $byTeam[$r[9]][] = $r;
    }
}

// --- Exports CSV -----------------------------------------------------------
$export = (string)($_GET['export'] ?? '');
if ($export !== '') {
    $team = (string)($_GET['equipe'] ?? '');
    if ($export === 'inscriptions') {
        $data = $rows; $name = 'inscriptions';
    } elseif ($export === 'attente') {
        $data = $attente; $name = 'liste-attente';
    } elseif ($export === 'equipe' && isset($byTeam[$team])) {
        $data = $byTeam[$team]; $name = 'equipe-' . preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $team));
    } elseif ($export === 'tout') {
        $data = array_merge($rows, $attente); $name = 'inscriptions-et-attente';
    } else {
        http_response_code(404);
        exit('Export inconnu.');
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="skylon-quest-' . strtolower(trim($name, '-')) . '-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM pour Excel
    fputcsv($out, SKYLON_CSV_HEADER, ';');
    foreach ($data as $r) {
        fputcsv($out, $r, ';');
    }
    fclose($out);
    exit;
}

// --- Vues ------------------------------------------------------------------
$vue = (string)($_GET['vue'] ?? 'equipes');
if (!in_array($vue, ['equipes', 'liste', 'attente'], true)) {
    $vue = 'equipes';
}
$labels = ['Date', 'Nom', 'Prénom', 'Âge', 'Taille', 'PMR', 'Adresse', 'Email', 'Statut', 'Équipe'];

$table = static function (array $data, bool $withTeam = true) use ($e, $labels): void {
    if (!$data) {
        echo '<p class="admin__empty">Aucune inscription pour le moment.</p>';
        return;
    }
    echo '<div class="table-wrap"><table class="table"><thead><tr><th>#</th>';
    foreach ($labels as $i => $l) {
        if ($i === 9 && !$withTeam) continue;
        echo '<th>' . $e($l) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($data as $n => $r) {
        echo '<tr><td>' . ($n + 1) . '</td>';
        foreach ($r as $i => $c) {
            if ($i > 9 || ($i === 9 && !$withTeam)) continue;
            echo '<td>' . $e($c) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php skylon_head($cfg, 'Administration — ' . $cfg['site_title'], true); ?>
</head>
<body class="page-admin">
<?php skylon_header($cfg); ?>

<main class="admin">
  <div class="panel panel--admin">
    <div class="admin__top">
      <h1 class="panel__title">Administration</h1>
      <form method="post" action="admin" class="admin__logout">
        <input type="hidden" name="csrf" value="<?= $e($_SESSION['admin_csrf']) ?>">
        <button type="submit" name="logout" value="1" class="btn btn--small">Se déconnecter</button>
      </form>
    </div>

    <p class="stats__summary">
      <strong><?= $total ?></strong> inscrit·es sur <?= count($cfg['teams']) * $capacity ?> places ·
      <strong><?= count($attente) ?></strong> en liste d’attente
    </p>

    <nav class="tabs" aria-label="Vues">
      <a class="tabs__link<?= $vue === 'equipes' ? ' is-active' : '' ?>" href="admin?vue=equipes">Par équipe</a>
      <a class="tabs__link<?= $vue === 'liste' ? ' is-active' : '' ?>" href="admin?vue=liste">Listing complet (<?= $total ?>)</a>
      <a class="tabs__link<?= $vue === 'attente' ? ' is-active' : '' ?>" href="admin?vue=attente">Liste d’attente (<?= count($attente) ?>)</a>
    </nav>

    <div class="admin__downloads">
      <span>Télécharger :</span>
      <a class="btn btn--small" href="admin?export=inscriptions" download>Inscriptions (CSV)</a>
      <a class="btn btn--small" href="admin?export=attente" download>Liste d’attente (CSV)</a>
      <a class="btn btn--small" href="admin?export=tout" download>Tout (CSV)</a>
    </div>

    <?php if ($vue === 'equipes'): ?>
      <ul class="teams teams--large">
        <?php foreach ($cfg['teams'] as $t): $n = $counts[$t]; ?>
          <li class="teams__item<?= $n >= $capacity ? ' is-full' : '' ?>">
            <a class="teams__name" href="#equipe-<?= $e(md5($t)) ?>"><?= $e($t) ?></a>
            <span class="teams__bar"><span style="width: <?= (int)round(100 * min($n, $capacity) / $capacity) ?>%"></span></span>
            <span class="teams__count"><?= $n ?>/<?= $capacity ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php foreach ($cfg['teams'] as $t): $n = $counts[$t]; ?>
        <section class="team" id="equipe-<?= $e(md5($t)) ?>">
          <div class="team__head">
            <h2 class="team__title"><?= $e($t) ?> <span class="team__count"><?= $n ?>/<?= $capacity ?><?= $n >= $capacity ? ' · complète' : '' ?></span></h2>
            <?php if ($n): ?><a class="btn btn--small" href="admin?export=equipe&equipe=<?= urlencode($t) ?>" download>CSV de l’équipe</a><?php endif; ?>
          </div>
          <?php $table($byTeam[$t], false); ?>
        </section>
      <?php endforeach; ?>

    <?php elseif ($vue === 'liste'): ?>
      <?php $table($rows, true); ?>

    <?php else: ?>
      <?php $table($attente, false); ?>
    <?php endif; ?>

    <p class="stats__files">Fichiers : <code>data/inscriptions.csv</code> · <code>data/liste-attente.csv</code> (séparateur <code>;</code>, UTF-8)</p>
  </div>
</main>

<?php skylon_footer($cfg); ?>
</body>
</html>
