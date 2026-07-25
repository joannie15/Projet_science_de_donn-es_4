<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SalaryPredict — Estimez votre salaire</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-orb hero-orb-3"></div>
  <div class="container">
    <div class="hero-badge reveal">
      <span class="dot-live"></span>
      L3 MIASHS · Université de Montpellier Paul-Valéry 
    </div>
    <h1 class="reveal reveal-delay-1">
      Estimez votre salaire<br>
      avec <span class="gradient-text">Salary Predict</span>
    </h1>
    <p class="hero-sub reveal reveal-delay-2">
      Renseignez votre profil — région, secteur, séniorité, exposition à l'IA —
      et obtenez une estimation du salaire annuel en USD, avec projection jusqu'en 2030.
    </p>
    <div class="hero-ctas reveal reveal-delay-3">
      <a href="prediction.php" class="btn btn-primary btn-lg">🔮 Estimer mon salaire</a>
      <a href="comparaison.php" class="btn btn-secondary btn-lg">⚖️ Comparer deux profils</a>
    </div>
  </div>
</section>

<div class="container">

  <!-- ── STATS UTILES POUR L'UTILISATEUR ── -->
  <div class="stats-grid reveal" style="margin-top:52px;">
    <div class="stat-item">
      <div class="stat-val">30 sec</div>
      <div class="stat-label">Pour obtenir une estimation</div>
    </div>
    <div class="stat-item">
      <div class="stat-val">7</div>
      <div class="stat-label">Variables prises en compte</div>
    </div>
    <div class="stat-item">
      <div class="stat-val">2030</div>
      <div class="stat-label">Projection possible jusqu'en</div>
    </div>
  </div>

  <!-- ── CARTES CLIQUABLES ── -->
  <div class="features" style="margin-top:48px;grid-template-columns:repeat(3,1fr);">

    <a href="prediction.php" class="feat-card" style="text-decoration:none;color:inherit;">
      <div class="feat-icon">🔮</div>
      <h3>Estimer un salaire</h3>
      <p>Renseignez votre profil et obtenez une estimation instantanée — avec projection possible jusqu'en 2030.</p>
      <div style="margin-top:14px;font-size:0.8rem;color:var(--teal);font-weight:600;">Accéder →</div>
    </a>

    <a href="comparaison.php" class="feat-card" style="text-decoration:none;color:inherit;">
      <div class="feat-icon">⚖️</div>
      <h3>Comparer deux profils</h3>
      <p>Simulez deux scénarios de carrière côte à côte et mesurez l'impact de chaque variable sur le salaire.</p>
      <div style="margin-top:14px;font-size:0.8rem;color:var(--teal);font-weight:600;">Comparer →</div>
    </a>

    <a href="exploration.php" class="feat-card" style="text-decoration:none;color:inherit;">
      <div class="feat-icon">📊</div>
      <h3>Explorer les tendances</h3>
      <p>Visualisez les salaires par région, secteur et séniorité à travers des graphiques interactifs.</p>
      <div style="margin-top:14px;font-size:0.8rem;color:var(--teal);font-weight:600;">Explorer →</div>
    </a>

    <a href="prediction.php" class="feat-card" style="text-decoration:none;color:inherit;">
      <div class="feat-icon">📄</div>
      <h3>Télécharger en PDF</h3>
      <p>Après votre estimation, exportez le résultat en PDF pour le conserver ou le partager facilement.</p>
      <div style="margin-top:14px;font-size:0.8rem;color:var(--teal);font-weight:600;">Faire une estimation →</div>
    </a>

    <a href="apropos.php" class="feat-card" style="text-decoration:none;color:inherit;">
      <div class="feat-icon">🎓</div>
      <h3>À propos du projet</h3>
      <p>Découvrez l'équipe de 4 étudiants en L3 MIASHS à l'origine de ce projet et notre démarche.</p>
      <div style="margin-top:14px;font-size:0.8rem;color:var(--teal);font-weight:600;">En savoir plus →</div>
    </a>

  </div>

  <!-- ── COMMENT ÇA MARCHE ── -->
  <div style="margin:64px 0 0;" class="reveal">
    <h2 style="font-family:'Instrument Serif',serif;font-size:1.8rem;font-weight:400;margin-bottom:32px;">
      Comment ça fonctionne ?
    </h2>
    <div class="timeline">
      <?php
      $steps = [
        ['🖊️', 'Remplissez le formulaire',
         'Sélectionnez votre région, secteur, niveau de séniorité, taille d\'entreprise, poste, et ajustez les scores IA et automatisation.'],
        ['⚙️', 'Le modèle analyse votre profil',
         'Notre algorithme applique ce qu\'il a appris sur des milliers d\'offres réelles pour trouver le salaire le plus probable pour votre profil.'],
        ['💰', 'Vous recevez une estimation',
         'Le salaire estimé s\'affiche avec une fourchette de confiance. Pour les années futures, un indicateur signale le niveau de certitude.'],
        ['📄', 'Exportez si vous le souhaitez',
         'Téléchargez votre résultat en PDF pour le conserver ou le comparer avec un autre profil.'],
      ];
      $last = count($steps) - 1;
      foreach($steps as $k => [$ico, $t, $d]):
      ?>
      <div class="tl-item reveal reveal-delay-<?= $k+1 ?>">
        <div class="tl-line">
          <div class="tl-dot"></div>
          <?php if($k < $last): ?><div class="tl-connector"></div><?php endif; ?>
        </div>
        <div class="tl-content">
          <h4><?= $ico ?> <?= $t ?></h4>
          <p><?= $d ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── CTA FINAL ── -->
  <div class="reveal" style="text-align:center;margin:64px 0 0;padding:48px 32px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
    <h3 style="font-family:'Instrument Serif',serif;font-size:1.8rem;font-weight:400;margin-bottom:10px;">
      Prêt à estimer votre salaire ?
    </h3>
    <p style="color:var(--text-muted);margin-bottom:28px;max-width:440px;margin-left:auto;margin-right:auto;">
      Moins d'une minute suffit. Aucune inscription requise.
    </p>
    <a href="prediction.php" class="btn btn-primary btn-lg">🔮 Commencer maintenant</a>
  </div>

</div>

<footer style="margin-top:80px;">
  <strong>SalaryPredict</strong> &nbsp;·&nbsp;
  L3 MIASHS · Université de Montpellier Paul-Valéry &nbsp;·&nbsp;
  <a href="admin/index.php" style="color:var(--text-muted);">Admin</a> &nbsp;·&nbsp;
  <?= date('Y') ?>
</footer>

<script>
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if(e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
</script>
</body>
</html>
