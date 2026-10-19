<?php
// Configuration de la landing Skylon Quest.
// Modifiez ici les listes affichées dans le formulaire.

return [
    'site_title' => 'Skylon Quest — Eight Lines x NSW',
    // Lien du logo (page d'accueil). './' = le dossier du site, quel que soit l'emplacement d'installation.
    'site_url'   => './',

    // Les 8 équipes, chacune menée par un·e créatif·ve (identifiant Instagram).
    'teams' => [
        'Équipe de @mr.kbz',
        'Équipe de @jhamine_',
        'Équipe de @ilyasrmili_',
        'Équipe de @lediegzzz',
        'Équipe de @chey__nese',
        'Équipe de @romguignard',
        'Équipe de @hananalbadri',
        'Équipe de @maemfrt',
    ],

    // Nombre maximum d'inscrit·es par équipe. Une fois toutes les équipes pleines,
    // le formulaire devient une liste d'attente.
    'team_capacity' => 8,

    'sizes'  => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
    'pmr'    => ['Non', 'Oui'],
    'status' => ['Étudiant·e', 'Salarié·e', 'Freelance'],
    'age_min' => 18,
    'age_max' => 60,

    // Fichier où sont stockées les inscriptions (hors web via .htaccess).
    'storage' => __DIR__ . '/data/inscriptions.csv',
    // Fichier de la liste d'attente (même format, sans colonne équipe remplie).
    'storage_waitlist' => __DIR__ . '/data/liste-attente.csv',

    // Panneau d'administration (admin.php) : identifiant et mot de passe (haché).
    // Le mot de passe en clair n'est pas stocké ici : seul son hash l'est.
    // Pour générer un nouveau hash : php -r "echo password_hash('monmotdepasse', PASSWORD_DEFAULT);"
    'admin_user' => 'admin',
    'admin_password_hash' => '$2y$10$5i2XGKw3x3htKypFUgwtOeWCyLE9UctUEccSAC4gKSIQRBTXOGBJ.',

    // Lien vers les conditions d'utilisation (page conditions.php, servie en /conditions).
    'terms_url' => 'conditions',
];
