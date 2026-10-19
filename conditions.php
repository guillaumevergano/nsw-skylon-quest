<?php
// Conditions d'utilisation (URL : /conditions via .htaccess). Texte par défaut à valider par l'organisation.
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/lib/layout.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php skylon_head($cfg, 'Conditions d’utilisation — ' . $cfg['site_title']); ?>
</head>
<body class="page-conditions">
<?php skylon_header($cfg); ?>

<main class="legal">
  <article class="panel panel--legal">
    <h1 class="panel__title">Conditions d’utilisation</h1>
    <p class="legal__meta">Skylon Quest — chasse au trésor organisée par Eight Lines x NSW à Paris, à l’occasion du lancement de la Nike Skylon 11. Dernière mise à jour : <?= date('d/m/Y') ?>.</p>

    <h2>1. Objet</h2>
    <p>Les présentes conditions encadrent l’inscription et la participation à la Skylon Quest (ci-après « l’Événement »), une chasse au trésor en équipes se déroulant dans Paris. En validant le formulaire d’inscription, vous confirmez les avoir lues et acceptées sans réserve.</p>

    <h2>2. Conditions de participation</h2>
    <ul>
      <li>L’Événement est ouvert à toute personne âgée de <?= (int)$cfg['age_min'] ?> à <?= (int)$cfg['age_max'] ?> ans à la date de l’Événement.</li>
      <li>Une seule inscription par personne. Toute inscription incomplète, inexacte ou en doublon pourra être écartée.</li>
      <li>Chaque équipe est limitée à <?= (int)$cfg['team_capacity'] ?> participant·es. Lorsque toutes les équipes sont complètes, les demandes sont placées en liste d’attente, sans garantie de place.</li>
      <li>L’inscription ne vaut pas sélection définitive : les participant·es retenu·es recevront un e-mail de confirmation avec les informations pratiques.</li>
      <li>Les personnes à mobilité réduite sont les bienvenues ; l’indication donnée dans le formulaire sert à adapter le parcours et l’accueil.</li>
    </ul>

    <h2>3. Déroulement de l’Événement</h2>
    <ul>
      <li>L’Événement se compose de cinq challenges répartis dans Paris, à réaliser en équipe sous la conduite d’un·e capitaine créatif·ve.</li>
      <li>Les participant·es se déplacent par leurs propres moyens, dans le respect du Code de la route, des lieux traversés et des autres usagers.</li>
      <li>L’organisation peut modifier le parcours, les horaires ou le contenu des challenges, voire reporter ou annuler l’Événement, en cas de force majeure, d’intempéries ou d’impératif de sécurité. Les participant·es en seront informé·es par e-mail.</li>
      <li>Tout comportement dangereux, frauduleux ou contraire à l’esprit de l’Événement pourra entraîner l’exclusion immédiate de la personne ou de l’équipe concernée.</li>
    </ul>

    <h2>4. Dotation</h2>
    <p>L’équipe gagnante est celle qui valide l’ensemble des challenges selon les règles annoncées le jour de l’Événement. Chaque membre de l’équipe gagnante reçoit une année de Navigo Pass (ou une dotation de valeur équivalente). La dotation est nominative, non cessible et ne peut être échangée contre sa valeur en espèces. Les décisions de l’organisation concernant le classement sont sans appel.</p>

    <h2>5. Équipement et tee-shirt</h2>
    <p>La taille indiquée dans le formulaire sert à préparer le tee-shirt remis aux participant·es. Elle est donnée à titre indicatif et ne constitue pas un engagement de disponibilité.</p>

    <h2>6. Droit à l’image</h2>
    <p>Des photographies et vidéos pourront être réalisées pendant l’Événement. En participant, vous autorisez Eight Lines et NSW à utiliser votre image sur leurs supports de communication (sites, réseaux sociaux, presse) en lien avec l’Événement, pour une durée de cinq ans, sans contrepartie financière. Vous pouvez vous y opposer en l’indiquant à l’organisation avant le début de l’Événement ou à tout moment par e-mail.</p>

    <h2>7. Données personnelles</h2>
    <p>Les informations collectées via le formulaire (nom, prénom, âge, taille, situation de mobilité, adresse postale, adresse e-mail, statut, équipe choisie) sont destinées uniquement à la gestion des inscriptions, à l’organisation de l’Événement et à la remise des dotations. Elles sont traitées par Eight Lines, responsable du traitement, et ne sont ni vendues ni transmises à des tiers en dehors des prestataires nécessaires à l’Événement. Elles sont conservées jusqu’à trois mois après la fin de l’Événement, puis supprimées.</p>
    <p>Conformément au Règlement général sur la protection des données (RGPD) et à la loi Informatique et Libertés, vous disposez d’un droit d’accès, de rectification, d’effacement, d’opposition et de limitation du traitement de vos données. Pour l’exercer, écrivez à l’organisation à l’adresse de contact communiquée dans l’e-mail de confirmation.</p>

    <h2>8. Responsabilité</h2>
    <p>Chaque participant·e prend part à l’Événement sous sa propre responsabilité et s’engage à être en condition physique adaptée à une activité de marche en ville. L’organisation ne saurait être tenue responsable des dommages, pertes ou vols survenant pendant l’Événement, ni des incidents résultant du non-respect des présentes conditions.</p>

    <h2>9. Modification des conditions</h2>
    <p>L’organisation se réserve le droit de modifier les présentes conditions. La version applicable est celle publiée sur cette page à la date de l’inscription.</p>

    <p class="legal__back"><a class="btn" href="inscription#inscription">Retour au formulaire</a></p>
  </article>
</main>

<?php skylon_footer($cfg); ?>
</body>
</html>
