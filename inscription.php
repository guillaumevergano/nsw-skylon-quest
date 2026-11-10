<?php
session_start();
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/lib/form.php';
require __DIR__ . '/lib/layout.php';

$errors = [];
$values = [];

// ?simuler=complet : aperçu du formulaire en mode liste d'attente (rien n'est enregistré).
$simulation = (($_GET['simuler'] ?? $_POST['simuler'] ?? '') === 'complet');
$ajax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');

$capacity = (int)$cfg['team_capacity'];
$remaining = static function (array $counts) use ($cfg, $capacity): array {
    $r = [];
    foreach ($cfg['teams'] as $t) {
        $r[$t] = max(0, $capacity - ($counts[$t] ?? 0));
    }
    return $r;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = skylon_handle_inscription($cfg, $_POST, $simulation);
    if ($ajax) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        if ($result['ok']) {
            unset($_SESSION['csrf']);
            echo json_encode(['ok' => true, 'waitlist' => $result['waitlist']]);
        } else {
            $c = skylon_counts($cfg);
            echo json_encode([
                'ok'        => false,
                'waitlist'  => $result['waitlist'],
                'errors'    => $result['errors'],
                'remaining' => $remaining($c),
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    if ($result['ok']) {
        unset($_SESSION['csrf']);
        $_SESSION['inscrit'] = $result['waitlist'] ? 'attente' : 'equipe';
        header('Location: merci.php');
        exit;
    }
    $errors = $result['errors'];
    $values = $result['values'];
}

$counts   = skylon_counts($cfg);
$waitlist = $simulation || skylon_all_full($cfg, $counts);

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$e = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$old = static fn(string $k) => $e($values[$k] ?? '');
$sel = static fn(string $k, string $opt) => (($values[$k] ?? '') === $opt) ? ' selected' : '';
$err = static fn(string $k) => isset($errors[$k]) ? '<p class="field__error" id="err-' . $k . '">' . $e($errors[$k]) . '</p>' : '';
$inv = static fn(string $k) => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php skylon_head($cfg, 'Inscription — ' . $cfg['site_title']); ?>
</head>
<body class="page-inscription">
<?php skylon_header($cfg); ?>

<main class="quest">
  <div class="quest__grid">

    <div class="quest__left">
      <figure class="shoe">
        <img src="assets/img/paire.webp" alt="Nike Skylon 11, coloris rouge et argent" width="1800" height="813">
      </figure>
      <div class="panel panel--text">
        <h2 class="panel--text__title">Skylon Quest : la chasse au trésor signée Eight Lines x Nike Sportswear</h2>
        <p>À l’occasion du lancement de la Skylon 11, Nike Sportswear s’associe à Eight Lines pour t’embarquer dans une chasse au trésor à travers Paris, inspirée des années 2010. Cinq challenges dans la ville, huit équipes en compétition, toutes menées par des créateurs de contenus. Une seule team gagnante, avec à la clé une année de Pass Navigo pour chaque membre de l’équipe. Inscris-toi et prépare-toi à parcourir la ville&nbsp;!</p>
      </div>
    </div>

    <div class="quest__right">
      <div class="panel panel--form" id="inscription">
        <?php if ($waitlist): ?>
          <h1 class="panel__title">Liste d’attente</h1>
          <p class="panel__intro">Toutes les équipes sont complètes. Laisse tes infos pour être recontacté·e si une place se libère.</p>
        <?php else: ?>
          <h1 class="panel__title">Formulaire d’inscription</h1>
        <?php endif; ?>

        <?php if (isset($errors['global'])): ?>
          <p class="alert" role="alert"><?= $e($errors['global']) ?></p>
        <?php elseif ($errors): ?>
          <p class="alert" role="alert">Quelques champs sont à corriger ci-dessous.</p>
        <?php endif; ?>

        <form method="post" action="inscription<?= $simulation ? '?simuler=complet' : '' ?>#inscription" class="form" novalidate>
          <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
          <?php if ($simulation): ?><input type="hidden" name="simuler" value="complet"><?php endif; ?>
          <div class="hp" aria-hidden="true">
            <label>Site web <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="form__row form__row--2">
            <div class="field">
              <label for="nom">Nom</label>
              <input id="nom" name="nom" type="text" autocomplete="family-name" required maxlength="80" value="<?= $old('nom') ?>"<?= $inv('nom') ?>>
              <?= $err('nom') ?>
            </div>
            <div class="field">
              <label for="prenom">Prénom</label>
              <input id="prenom" name="prenom" type="text" autocomplete="given-name" required maxlength="80" value="<?= $old('prenom') ?>"<?= $inv('prenom') ?>>
              <?= $err('prenom') ?>
            </div>
          </div>

          <div class="form__row form__row--age">
            <div class="field">
              <label for="age">Âge</label>
              <select id="age" name="age" required<?= $inv('age') ?>>
                <option value=""></option>
                <?php for ($a = $cfg['age_min']; $a <= $cfg['age_max']; $a++): ?>
                  <option value="<?= $a ?>"<?= $sel('age', (string)$a) ?>><?= $a ?></option>
                <?php endfor; ?>
              </select>
              <?= $err('age') ?>
            </div>
            <div class="field">
              <label for="taille">Taille (tee)</label>
              <select id="taille" name="taille" required<?= $inv('taille') ?>>
                <option value=""></option>
                <?php foreach ($cfg['sizes'] as $s): ?>
                  <option value="<?= $e($s) ?>"<?= $sel('taille', $s) ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
              </select>
              <?= $err('taille') ?>
            </div>
            <div class="field">
              <label for="pmr">PMR</label>
              <select id="pmr" name="pmr" required<?= $inv('pmr') ?>>
                <option value=""></option>
                <?php foreach ($cfg['pmr'] as $s): ?>
                  <option value="<?= $e($s) ?>"<?= $sel('pmr', $s) ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
              </select>
              <?= $err('pmr') ?>
            </div>
            <div class="field field--grow">
              <label for="adresse">Adresse postale</label>
              <input id="adresse" name="adresse" type="text" autocomplete="street-address" required maxlength="250" value="<?= $old('adresse') ?>"<?= $inv('adresse') ?>>
              <?= $err('adresse') ?>
            </div>
          </div>

          <div class="form__row form__row--2">
            <div class="field">
              <label for="email">Adresse mail</label>
              <input id="email" name="email" type="email" autocomplete="email" inputmode="email" required maxlength="150" value="<?= $old('email') ?>"<?= $inv('email') ?>>
              <?= $err('email') ?>
            </div>
            <div class="field">
              <label for="statut">Statut (Étudiant·e/Salarié·e/Freelance)</label>
              <select id="statut" name="statut" required<?= $inv('statut') ?>>
                <option value=""></option>
                <?php foreach ($cfg['status'] as $s): ?>
                  <option value="<?= $e($s) ?>"<?= $sel('statut', $s) ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
              </select>
              <?= $err('statut') ?>
            </div>
          </div>

          <?php if (!$waitlist): ?>
          <div class="form__row form__row--2">
            <div class="field">
              <label for="equipe">Choisis ton équipe</label>
              <select id="equipe" name="equipe" required<?= $inv('equipe') ?>>
                <option value=""></option>
                <?php foreach ($cfg['teams'] as $t): $n = $counts[$t]; $full = $n >= $capacity; ?>
                  <option value="<?= $e($t) ?>"<?= $sel('equipe', $t) ?><?= $full ? ' disabled' : '' ?>><?= $e($t) ?> — <?= $full ? 'complète' : ($capacity - $n) . ' place' . ($capacity - $n > 1 ? 's' : '') ?></option>
                <?php endforeach; ?>
              </select>
              <?= $err('equipe') ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="field field--check">
            <label>
              <input type="checkbox" name="cgu" value="1" required<?= !empty($values['cgu']) ? ' checked' : '' ?><?= $inv('cgu') ?>>
              <span>J’accepte les <a href="<?= $e($cfg['terms_url']) ?>" target="_blank" rel="noopener">Conditions d’utilisation</a></span>
            </label>
            <?= $err('cgu') ?>
          </div>

          <div class="form__actions">
            <button type="submit" class="btn btn--submit"><?= $waitlist ? 'Rejoindre la liste d’attente' : 'Valider l’inscription' ?></button>
          </div>
        </form>
      </div>
    </div>

  </div>
</main>

<!-- Vue « merci » injectée dans la page après validation (sans rechargement) -->
<template id="tpl-merci">
  <main class="merci">
    <h1 class="merci__title">
      <img src="assets/img/merci.webp" alt="Merci beaucoup pour ton inscription !" width="1600" height="178">
    </h1>
    <div class="panel panel--merci">
      <p data-merci="attente" hidden>Toutes les équipes sont complètes pour le moment : tu es sur la liste d’attente. On te recontacte par mail si une place se libère&nbsp;!</p>
      <p data-merci="equipe" hidden>Un mail de confirmation avec les infos utiles vous sera envoyé prochainement si vous êtes sélectionné·es&nbsp;!</p>
      <p>À très vite,<br>Les équipes Eight Lines x NSW x Yard</p>
    </div>
  </main>
</template>

<?php skylon_footer($cfg); ?>
</body>
</html>
