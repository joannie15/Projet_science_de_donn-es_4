<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Exploration — SalaryPredict</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>
<?php include 'includes/nav.php'; require_once 'config.php';

// ── Requêtes BDD réelles ──────────────────────────────────────
$data_region=$data_industry=$data_senior=$data_ia=$data_years=$models=[];

if($pdo){try{
  // Salaire moyen par région
  $data_region=$pdo->query("
    SELECT r.region_name, ROUND(AVG(o.salary_usd)) avg_sal, COUNT(*) nb
    FROM offre o JOIN regions r ON o.id_region=r.id_region
    WHERE o.salary_usd>0 GROUP BY r.region_name ORDER BY avg_sal DESC LIMIT 8
  ")->fetchAll();

  // Salaire moyen par secteur
  $data_industry=$pdo->query("
    SELECT i.industry_name, ROUND(AVG(o.salary_usd)) avg_sal, COUNT(*) nb
    FROM offre o JOIN industry i ON o.id_industry=i.id_industry
    WHERE o.salary_usd>0 GROUP BY i.industry_name ORDER BY avg_sal DESC LIMIT 8
  ")->fetchAll();

  // Par séniorité
  $data_senior=$pdo->query("
    SELECT sl.label_seniority, ROUND(AVG(o.salary_usd)) avg_sal
    FROM offre o JOIN senior_level sl ON o.id_senior_level=sl.id_senior_level
    WHERE o.salary_usd>0 GROUP BY sl.label_seniority ORDER BY avg_sal DESC
  ")->fetchAll();

  // Évolution par année — uniquement les années du dataset (2020-2024)
  $data_years=$pdo->query("
    SELECT year, ROUND(AVG(salary_usd)) avg_sal, COUNT(*) nb
    FROM offre
    WHERE salary_usd>0 AND year IS NOT NULL AND year BETWEEN 2020 AND 2024
    GROUP BY year ORDER BY year
  ")->fetchAll();

  // Corrélation IA vs salaire
  $data_ia=$pdo->query("
    SELECT ROUND(ai_intensity_score,1) AS ai_score, ROUND(AVG(salary_usd)) avg_sal
    FROM offre WHERE salary_usd>0 AND ai_intensity_score IS NOT NULL
    GROUP BY ROUND(ai_intensity_score,1) ORDER BY ai_score
  ")->fetchAll();

  // Stats globales
  $global=$pdo->query("SELECT COUNT(*) nb, ROUND(AVG(salary_usd)) avg, MIN(salary_usd) mn, MAX(salary_usd) mx, ROUND(STDDEV(salary_usd)) std FROM offre WHERE salary_usd>0")->fetch();

}catch(Exception $e){ $global=['nb'=>0,'avg'=>0,'mn'=>0,'mx'=>0,'std'=>0]; }}

$models=[
  ['Gradient Boosting','R² ~0.87','~14 200 $','~10 100 $','selected'],
  ['Random Forest',    'R² ~0.83','~16 800 $','~12 300 $',''],
  ['KNN',              'R² ~0.73','~19 600 $','~14 700 $',''],
  ['Régression linéaire','R² ~0.61','~24 100 $','~18 900 $',''],
];

// Préparer JSON pour Chart.js
$js_region   = json_encode(array_column($data_region,   'region_name'));
$js_reg_sal  = json_encode(array_map('intval', array_column($data_region,   'avg_sal')));
$js_ind      = json_encode(array_column($data_industry, 'industry_name'));
$js_ind_sal  = json_encode(array_map('intval', array_column($data_industry, 'avg_sal')));
$js_sen      = json_encode(array_column($data_senior,   'label_seniority'));
$js_sen_sal  = json_encode(array_map('intval', array_column($data_senior,   'avg_sal')));
$js_yr       = json_encode(array_column($data_years,    'year'));
$js_yr_sal   = json_encode(array_map('intval', array_column($data_years,    'avg_sal')));
$js_ia       = json_encode(array_column($data_ia,       'ai_score'));
$js_ia_sal   = json_encode(array_map('intval', array_column($data_ia,       'avg_sal')));
?>

<div class="container page-content">
  <div class="section-header reveal">
    <h1>Exploration des données</h1>
    <p>Statistiques calculées en temps réel depuis la base Projet_AI (2020–2024).</p>
  </div>

  <!-- STATS GLOBALES -->
  <div class="stats-grid reveal" style="margin-bottom:40px">
    <div class="stat-item"><div class="stat-val"><?= number_format($global['nb']??0,0,',',' ') ?></div><div class="stat-label">Offres analysées</div></div>
    <div class="stat-item"><div class="stat-val">$<?= number_format($global['avg']??0,0,',',' ') ?></div><div class="stat-label">Salaire moyen</div></div>
    <div class="stat-item"><div class="stat-val">$<?= number_format($global['mx']??0,0,',',' ') ?></div><div class="stat-label">Salaire maximum</div></div>
    <div class="stat-item"><div class="stat-val">±$<?= number_format($global['std']??0,0,',',' ') ?></div><div class="stat-label">Écart-type</div></div>
  </div>

  <!-- CHARTS LIGNE 1 -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    <div class="card reveal">
      <div class="card-title">💰 Salaire moyen par région</div>
      <canvas id="chartRegion" height="220"></canvas>
    </div>

    <div class="card reveal reveal-delay-1">
      <div class="card-title">📈 Évolution salariale 2020–2024</div>
      <div class="card-sub" style="font-size:0.8rem;color:var(--text-muted);margin-bottom:12px;">Données observées dans le dataset — années réelles uniquement</div>
      <canvas id="chartYears" height="220"></canvas>
    </div>

  </div>

  <!-- CHARTS LIGNE 2 -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    <div class="card reveal">
      <div class="card-title">🏭 Salaire moyen par secteur</div>
      <canvas id="chartIndustry" height="240"></canvas>
    </div>

    <div class="card reveal reveal-delay-1">
      <div class="card-title">📊 Salaire moyen par séniorité</div>
      <canvas id="chartSeniority" height="240"></canvas>
    </div>

  </div>

  <!-- CHART IA pleine largeur -->
  <div class="card reveal" style="margin-bottom:32px">
    <div class="card-title">🤖 Corrélation score IA → Salaire</div>
    <div class="card-sub">Plus l'intensité IA est élevée, plus le salaire tend à augmenter</div>
    <canvas id="chartIA" height="110"></canvas>
  </div>

  <!-- COMPARAISON MODÈLES -->
  <div class="reveal">
    <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.5rem;letter-spacing:-.02em;margin-bottom:20px">🔬 Comparaison des modèles testés</h2>
    <div class="admin-wrap">
      <table class="data-table">
        <thead><tr>
          <th>Modèle</th><th>R²</th><th>RMSE</th><th>MAE</th><th>Statut</th>
        </tr></thead>
        <tbody>
          <?php foreach($models as [$name,$r2,$rmse,$mae,$sel]): ?>
          <tr class="<?= $sel?'highlight':'' ?>">
            <td><strong><?= $sel?'⭐ ':'' ?><?= $name ?></strong></td>
            <td><span class="tag <?= $sel?'tag-green':'tag-gray' ?>"><?= $r2 ?></span></td>
            <td><?= $rmse ?></td><td><?= $mae ?></td>
            <td><?= $sel?'<span class="tag tag-green">✅ Retenu</span>':'<span class="tag tag-gray">Non retenu</span>' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const ACCENT='#1B4FD8', GOLD='#D4832A', GREEN='#1A7A55';
const defaults={responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{grid:{color:'#E0DDD8'},ticks:{callback:v=>'$'+v.toLocaleString()}}}};

// Région
new Chart('chartRegion',{type:'bar',data:{
  labels:<?= $js_region ?>,
  datasets:[{data:<?= $js_reg_sal ?>,backgroundColor:ACCENT+'CC',borderRadius:6,borderSkipped:false}]
},options:{...defaults,indexAxis:'y',scales:{...defaults.scales,x:{...defaults.scales.x,ticks:{callback:v=>'$'+v.toLocaleString()}},y:{grid:{display:false}}}}});

// Années
new Chart('chartYears',{type:'line',data:{
  labels:<?= $js_yr ?>,
  datasets:[{data:<?= $js_yr_sal ?>,borderColor:ACCENT,backgroundColor:ACCENT+'18',fill:true,tension:0.4,pointBackgroundColor:ACCENT,pointRadius:4}]
},options:{...defaults}});

// Industrie
new Chart('chartIndustry',{type:'bar',data:{
  labels:<?= $js_ind ?>,
  datasets:[{data:<?= $js_ind_sal ?>,backgroundColor:GOLD+'CC',borderRadius:6,borderSkipped:false}]
},options:{...defaults,indexAxis:'y',scales:{...defaults.scales,x:{...defaults.scales.x,ticks:{callback:v=>'$'+v.toLocaleString()}},y:{grid:{display:false}}}}});

// Séniorité
new Chart('chartSeniority',{type:'bar',data:{
  labels:<?= $js_sen ?>,
  datasets:[{data:<?= $js_sen_sal ?>,backgroundColor:GREEN+'CC',borderRadius:6,borderSkipped:false}]
},options:{...defaults}});

// IA corrélation
new Chart('chartIA',{type:'line',data:{
  labels:<?= $js_ia ?>,
  datasets:[{label:'Salaire moyen',data:<?= $js_ia_sal ?>,borderColor:ACCENT,backgroundColor:ACCENT+'15',fill:true,tension:0.4,pointBackgroundColor:ACCENT,pointRadius:5}]
},options:{responsive:true,plugins:{legend:{display:true}},scales:{x:{title:{display:true,text:'Score IA'},grid:{display:false}},y:{ticks:{callback:v=>'$'+v.toLocaleString()},grid:{color:'#E0DDD8'}}}}});

// Scroll reveal
const obs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});
},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>

<footer style="margin-top:60px">
  <strong>SalaryPredict</strong> &nbsp;·&nbsp; L3 MIASHS · Université de Montpellier Paul-Valéry &nbsp;·&nbsp; <?= date('Y') ?>
</footer>
</body>
</html>
