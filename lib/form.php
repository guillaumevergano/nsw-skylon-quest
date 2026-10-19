<?php
// Inscriptions : comptage par équipe, plafond, liste d'attente et enregistrement CSV.

const SKYLON_CSV_HEADER = ['date', 'nom', 'prenom', 'age', 'taille', 'pmr', 'adresse', 'email', 'statut', 'equipe'];

/** Lit les lignes d'un CSV déjà ouvert (le curseur est remis au début). */
function skylon_read_rows($fh): array
{
    rewind($fh);
    $rows = [];
    $first = true;
    while (($row = fgetcsv($fh, 0, ';')) !== false) {
        if ($first) { $first = false; continue; } // en-tête
        if (count($row) >= 10) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/** Nombre d'inscrit·es par équipe, à partir d'une liste de lignes CSV. */
function skylon_count_rows(array $cfg, array $rows): array
{
    $counts = array_fill_keys($cfg['teams'], 0);
    foreach ($rows as $row) {
        $team = $row[9] ?? '';
        if (isset($counts[$team])) {
            $counts[$team]++;
        }
    }
    return $counts;
}

/** Toutes les lignes d'un fichier CSV (lecture seule, tableau vide si absent). */
function skylon_rows(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $fh = fopen($file, 'r');
    if ($fh === false) {
        return [];
    }
    flock($fh, LOCK_SH);
    $rows = skylon_read_rows($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $rows;
}

/** Nombre d'inscrit·es par équipe (lecture seule). */
function skylon_counts(array $cfg): array
{
    if (!file_exists($cfg['storage'])) {
        return array_fill_keys($cfg['teams'], 0);
    }
    $fh = fopen($cfg['storage'], 'r');
    if ($fh === false) {
        return array_fill_keys($cfg['teams'], 0);
    }
    flock($fh, LOCK_SH);
    $counts = skylon_count_rows($cfg, skylon_read_rows($fh));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $counts;
}

/** Nombre de personnes en liste d'attente. */
function skylon_waitlist_count(array $cfg): int
{
    if (!file_exists($cfg['storage_waitlist'])) {
        return 0;
    }
    $fh = fopen($cfg['storage_waitlist'], 'r');
    if ($fh === false) {
        return 0;
    }
    flock($fh, LOCK_SH);
    $n = count(skylon_read_rows($fh));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $n;
}

/** Vrai si toutes les équipes ont atteint la capacité. */
function skylon_all_full(array $cfg, array $counts): bool
{
    foreach ($cfg['teams'] as $team) {
        if (($counts[$team] ?? 0) < $cfg['team_capacity']) {
            return false;
        }
    }
    return true;
}

/**
 * Traite le formulaire (inscription ou liste d'attente).
 * $simulation : force le mode liste d'attente et n'enregistre rien (aperçu via ?simuler=complet).
 * Retourne ['ok' => bool, 'waitlist' => bool, 'errors' => [...], 'values' => [...]].
 */
function skylon_handle_inscription(array $cfg, array $post, bool $simulation = false): array
{
    $v = static function (string $k) use ($post): string {
        return trim((string)($post[$k] ?? ''));
    };

    $values = [
        'nom'     => $v('nom'),
        'prenom'  => $v('prenom'),
        'age'     => $v('age'),
        'taille'  => $v('taille'),
        'pmr'     => $v('pmr'),
        'adresse' => $v('adresse'),
        'email'   => $v('email'),
        'statut'  => $v('statut'),
        'equipe'  => $v('equipe'),
        'cgu'     => isset($post['cgu']),
    ];
    $errors = [];
    $waitlist = $simulation || skylon_all_full($cfg, skylon_counts($cfg));

    // Honeypot : un humain ne remplit pas ce champ.
    if ($v('website') !== '') {
        return ['ok' => true, 'waitlist' => $waitlist, 'errors' => [], 'values' => $values];
    }

    // Jeton anti-CSRF.
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $v('csrf'))) {
        $errors['global'] = 'Session expirée, merci de réessayer.';
    }

    if (mb_strlen($values['nom']) < 2 || mb_strlen($values['nom']) > 80) {
        $errors['nom'] = 'Indique ton nom.';
    }
    if (mb_strlen($values['prenom']) < 2 || mb_strlen($values['prenom']) > 80) {
        $errors['prenom'] = 'Indique ton prénom.';
    }
    $age = (int)$values['age'];
    if ($values['age'] === '' || $age < $cfg['age_min'] || $age > $cfg['age_max']) {
        $errors['age'] = 'Choisis ton âge.';
    }
    if (!in_array($values['taille'], $cfg['sizes'], true)) {
        $errors['taille'] = 'Choisis ta taille.';
    }
    if (!in_array($values['pmr'], $cfg['pmr'], true)) {
        $errors['pmr'] = 'Indique si tu es PMR.';
    }
    if (mb_strlen($values['adresse']) < 8 || mb_strlen($values['adresse']) > 250) {
        $errors['adresse'] = 'Indique ton adresse postale.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 150) {
        $errors['email'] = 'Indique une adresse mail valide.';
    }
    if (!in_array($values['statut'], $cfg['status'], true)) {
        $errors['statut'] = 'Choisis ton statut.';
    }
    if (!$waitlist && !in_array($values['equipe'], $cfg['teams'], true)) {
        $errors['equipe'] = 'Choisis ton équipe.';
    }
    if (!$values['cgu']) {
        $errors['cgu'] = 'Tu dois accepter les conditions d’utilisation.';
    }

    if ($errors) {
        return ['ok' => false, 'waitlist' => $waitlist, 'errors' => $errors, 'values' => $values];
    }
    if ($simulation) {
        // Aperçu de la liste d'attente : rien n'est enregistré.
        return ['ok' => true, 'waitlist' => true, 'errors' => [], 'values' => $values];
    }

    $file = $waitlist ? $cfg['storage_waitlist'] : $cfg['storage'];
    $fh = fopen($file, 'c+');
    if ($fh === false) {
        return ['ok' => false, 'waitlist' => $waitlist, 'errors' => ['global' => 'Impossible d’enregistrer l’inscription. Réessaie plus tard.'], 'values' => $values];
    }
    flock($fh, LOCK_EX);

    if (!$waitlist) {
        // Re-vérification sous verrou : l'équipe peut s'être remplie entre l'affichage et l'envoi.
        $counts = skylon_count_rows($cfg, skylon_read_rows($fh));
        if ($counts[$values['equipe']] >= $cfg['team_capacity']) {
            flock($fh, LOCK_UN);
            fclose($fh);
            if (skylon_all_full($cfg, $counts)) {
                // Tout vient de se remplir : on bascule la demande en liste d'attente.
                $values['equipe'] = '';
                return skylon_handle_inscription($cfg, array_merge($post, ['equipe' => '']));
            }
            return ['ok' => false, 'waitlist' => false, 'errors' => ['equipe' => 'Cette équipe vient de se remplir, choisis-en une autre.'], 'values' => $values];
        }
    }

    fseek($fh, 0, SEEK_END);
    if (ftell($fh) === 0) {
        fputcsv($fh, SKYLON_CSV_HEADER, ';');
    }
    fputcsv($fh, [
        date('Y-m-d H:i:s'),
        $values['nom'], $values['prenom'], $age, $values['taille'], $values['pmr'],
        $values['adresse'], $values['email'], $values['statut'], $waitlist ? '' : $values['equipe'],
    ], ';');
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);

    return ['ok' => true, 'waitlist' => $waitlist, 'errors' => [], 'values' => $values];
}
