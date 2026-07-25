<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>À propos — SalaryPredict</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="container page-content">
  <div class="section-header reveal">
    <h1>À propos</h1>
    <p>Un projet né d'une question simple : qu'est-ce qui détermine vraiment un salaire ?</p>
  </div>

  <!-- QUI SOMMES-NOUS -->
  <div class="card reveal" style="margin-bottom:28px; background:linear-gradient(135deg, rgba(0,212,170,0.06) 0%, rgba(167,139,250,0.04) 100%); border-color:rgba(0,212,170,0.2);">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center;">
      <div>
        <span class="tag tag-teal" style="margin-bottom:16px; display:inline-block;">🎓 Université Paul-Valéry Montpellier 3</span>
        <h2 style="font-family:'Instrument Serif',serif; font-size:2rem; font-weight:400; line-height:1.25; margin-bottom:16px;">
          4 étudiants en <em style="color:var(--teal)">Licence 3 MIASHS</em>
        </h2>
        <p style="color:var(--text-muted); line-height:1.8; font-size:0.95rem;">
          <strong style="color:var(--text)">MIASHS</strong> — Mathématiques et Informatique Appliquées aux Sciences Humaines et Sociales.
          Une formation à l'interface entre la data science, les mathématiques appliquées et les sciences sociales,
          qui nous a donné les outils pour analyser des données du monde réel.
        </p>
        <p style="color:var(--text-muted); line-height:1.8; font-size:0.95rem; margin-top:12px;">
          Ce projet a été réalisé dans le cadre de notre cursus universitaire, avec pour ambition de
          construire un outil <strong style="color:var(--text)">concret et utilisable.</strong>
        </p>
      </div>
      <div style="text-align:center;">
        <div style="font-size:5rem; margin-bottom:12px; animation:float 6s ease-in-out infinite;">🎓</div>
        <div style="color:var(--text-muted); font-size:0.85rem;">Montpellier, 2025–2026</div>
        <div style="margin-top:8px;">
          <span class="tag tag-teal">Machine Learning</span>&nbsp;
          <span class="tag tag-violet">Data Science</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ÉQUIPE -->
  <h2 class="reveal" style="font-family:'Instrument Serif',serif; font-size:1.8rem; font-weight:400; margin-bottom:24px;">Notre équipe</h2>
  <div class="team-grid reveal reveal-delay-1">
    <?php
    $members = ['Diella Joannie Gateka', 'Kardiatou Ba', 'Naim Bouhadjadj', 'Lydia Ait'];
    foreach($members as $name):
    ?>
    <div class="team-card">
      <div class="team-avatar">🎓</div>
      <div class="team-name"><?= $name ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- LE PROJET -->
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:28px;">

    <div class="card reveal reveal-delay-1">
      <div class="feat-icon">🎯</div>
      <div class="card-title">Notre objectif</div>
      <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.75;">
        Développer un outil accessible permettant à n'importe qui d'estimer
        le salaire d'un poste à partir de caractéristiques clés du marché du travail.
        Une réponse concrète à une question que beaucoup se posent.
      </p>
    </div>

    <div class="card reveal reveal-delay-2">
      <div class="feat-icon">🤖</div>
      <div class="card-title">Notre approche</div>
      <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.75;">
        Après avoir testé plusieurs algorithmes de machine learning, nous avons sélectionné
        le modèle le plus précis sur nos données. Le résultat : un estimateur capable
        d'expliquer une large part de la variabilité des salaires observés.
      </p>
    </div>

    <div class="card reveal reveal-delay-3">
      <div class="feat-icon">🌍</div>
      <div class="card-title">Les données</div>
      <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.75;">
        Notre dataset couvre des milliers d'offres d'emploi dans le monde entier,
        intégrant des variables sur la région, le secteur, la taille d'entreprise,
        le niveau d'exposition à l'IA et le risque d'automatisation.
      </p>
    </div>

    <div class="card reveal reveal-delay-4">
      <div class="feat-icon">📈</div>
      <div class="card-title">Ce que ça apporte</div>
      <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.75;">
        Au-delà de la prédiction, cet outil permet de comprendre quels facteurs
        pèsent le plus sur la rémunération — et comment des variables comme
        l'intensité IA redessinent les écarts de salaire.
      </p>
    </div>
  </div>

  <!-- CTA -->
  <div class="reveal" style="text-align:center; margin-top:56px; padding:48px; background:var(--surface); border-radius:var(--radius-lg); border:1px solid var(--border);">
    <h3 style="font-family:'Instrument Serif',serif; font-size:1.8rem; font-weight:400; margin-bottom:12px;">Envie de tester ?</h3>
    <p style="color:var(--text-muted); margin-bottom:28px;">Renseignez votre profil et découvrez le salaire estimé par notre modèle.</p>
    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
      <a href="prediction.php" class="btn btn-primary btn-lg">🔮 Prédire mon salaire</a>
      <a href="contact.php" class="btn btn-secondary btn-lg">📩 Nous contacter</a>
    </div>
  </div>
</div>

<footer>
  <strong>SalaryPredict</strong> &nbsp;·&nbsp; L3 MIASHS · Université de Montpellier Paul-Valéry &nbsp;·&nbsp; <?= date('Y') ?>
</footer>

<script>
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }});
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
</script>
</body>
</html>
