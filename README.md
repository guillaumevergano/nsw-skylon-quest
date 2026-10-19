# Skylon Quest — landing Eight Lines x NSW

Landing d'inscription à la chasse au trésor Skylon Quest (lancement Nike Skylon 11), conforme aux maquettes « LANDING PAGE » (16:9 et 9:16).

## Lancer en local
MAMP → `http://localhost:8888/nsw-skylon-quest/` (PHP ≥ 8.0).

## Mise en ligne
Serveur `bltzr@bb0084.aquaray.com`, dossier `web/skylon-quest/` → https://bltzr.fr/skylon-quest/
```
rsync -az --delete --exclude .git --exclude .DS_Store --exclude 'data/*.csv' ./ bltzr@bb0084.aquaray.com:web/skylon-quest/
```
(`--exclude 'data/*.csv'` préserve les inscriptions déjà enregistrées sur le serveur.)

## Pages
1. `index.php` — Welcome (logo, partenaires, WELCOME, chevron vers le formulaire)
2. `/inscription` (fichier `inscription.php`) — chaussure + texte de présentation + formulaire. L'envoi se fait en AJAX (fetch) : la vue « merci » remplace le formulaire sans recharger la page, pendant que la pluie de baskets continue. Les erreurs serveur s'affichent sous les champs.
3. `merci.php` — confirmation, utilisée seulement en repli (JavaScript désactivé ou échec réseau)
4. `/conditions` (fichier `conditions.php`) — conditions d'utilisation, texte par défaut à faire valider (participation, dotation, droit à l'image, RGPD)
5. `/admin` (fichier `admin.php`) — panneau d'administration protégé par identifiant + mot de passe
Les URL `/inscription`, `/conditions` et `/admin` sont réécrites par `.htaccess` (`Options -MultiViews` obligatoire).

## Administration (`/admin`)
- Identifiants dans `config.php` : `admin_user` (défaut `admin`) et `admin_password_hash` (le mot de passe en clair a été transmis au client). Pour le changer :
  `php -r "echo password_hash('nouveau-mot-de-passe', PASSWORD_DEFAULT);"` puis coller le résultat dans `admin_password_hash`.
- Vues : par équipe (barres + listing de chaque équipe), listing complet, liste d'attente.
- Téléchargements CSV (UTF-8 avec BOM, séparateur `;`, lisible dans Excel) : inscriptions, liste d'attente, tout, ou une équipe seule.
- 10 tentatives de connexion ratées bloquent la session ; déconnexion par bouton.

## Aperçu de la liste d'attente
`/inscription?simuler=complet` affiche le formulaire comme si toutes les équipes étaient complètes (titre, texte, bouton, plus de choix d'équipe). En mode aperçu, la validation fonctionne mais **rien n'est enregistré**.

## Équipes, plafond et liste d'attente
- Chaque équipe accepte `team_capacity` inscrit·es (8 par défaut, dans `config.php`).
- Le sélecteur affiche les places restantes et désactive les équipes complètes.
- Le plafond est re-vérifié côté serveur sous verrou fichier au moment de l'enregistrement.
- Quand toutes les équipes sont complètes, le formulaire devient une liste d'attente : plus de choix d'équipe, enregistrement dans `data/liste-attente.csv`.

## Fichiers
- `lib/form.php` — validation et enregistrement CSV
- `lib/layout.php` — head, header (logo + NSW + Eight Lines) et footer communs
- `config.php` — équipes, tailles, statuts, âge min/max, lien CGU
- `data/inscriptions.csv` — inscriptions, `data/liste-attente.csv` — liste d'attente (séparateur `;`), bloqués côté web par `.htaccess`
- `assets/` — CSS, JS, images WebP, logos SVG

## Effets
- Curseur en forme de basket sur tout le site (`assets/img/cursor-shoe.png` et `@2x`, défini dans `assets/css/style.css`).
- Pluie de baskets au clic sur « Valider » : elle dure environ 5,5 s pendant que la vue merci remplace le formulaire (même page, sans rechargement). Désactivée si l'utilisateur a demandé moins d'animations (`prefers-reduced-motion`).

## Polices
La maquette utilise Elevon FourG (Adobe Fonts). Les titres WELCOME et MERCI sont les PNG fournis ; le titre du formulaire et le chevron utilisent Michroma (Google Fonts). Pour Elevon : ajouter le kit Adobe Fonts dans `lib/layout.php` et remplacer `--font-display` dans `assets/css/style.css`.

## À compléter
- Le texte des conditions d'utilisation (`conditions.php`) : à relire et valider par l'organisation (adresse de contact, durées, responsable du traitement).
