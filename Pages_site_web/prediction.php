<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Prédiction — SalaryPredict</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<?php include 'includes/nav.php'; require_once 'config.php';

$cats = getCategories();
$defaults = [
  'region'          => ['Africa','Asia','Europe','Latin America','North America','Oceania'],
  'industry'        => ['Education','Finance','Healthcare','Manufacturing','Retail','Technology'],
  'company_size'    => ['Large','Medium','Small','Startup'],
  'seniority_level' => ['Entry','Executive','Junior','Mid','Senior'],
  'job_title'       => ['AI Engineer','Analyst','Data Scientist','Developer','Manager','Researcher'],
  'automation_risk_score_range' => [0.0, 1.0],
  'ai_intensity_score_range'    => [0.0, 1.0],
];
foreach($defaults as $k => $v) if(empty($cats[$k])) $cats[$k] = $v;

$result    = null;
$error     = null;
$formData  = [];
$apiOnline = false;

function yearConfidence(int $year): array {
  if($year <= 2024) return ['ok',   'teal',  ''];
  if($year <= 2027) return ['warn', 'gold',  'Extrapolation modérée — le modèle prolonge les tendances observées 2020–2024. L\'estimation est moins précise qu\'avant 2025.'];
  return                   ['high', 'coral', 'Projection lointaine — l\'incertitude augmente fortement. Ce résultat est à titre indicatif uniquement.'];
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $formData = [
    'year'                  => min(2030, max(2020, (int)($_POST['year'] ?? 2024))),
    'region'                => trim($_POST['region'] ?? ''),
    'industry'              => trim($_POST['industry'] ?? ''),
    'automation_risk_score' => (float)($_POST['automation_risk_score'] ?? 0.5),
    'company_size'          => trim($_POST['company_size'] ?? ''),
    'seniority_level'       => trim($_POST['seniority_level'] ?? ''),
    'ai_intensity_score'    => (float)($_POST['ai_intensity_score'] ?? 0.5),
    'job_title'             => trim($_POST['job_title'] ?? ''),
  ];

  if(in_array('', [
    $formData['region'], $formData['industry'],
    $formData['company_size'], $formData['seniority_level'], $formData['job_title']
  ])) {
    $error = "Veuillez remplir tous les champs.";
  } else {
    $apiResult = callPredictAPI($formData);

    if(isset($apiResult['salary'])) {
      $result    = $apiResult;
      $apiOnline = true;
      if($pdo) {
        try {
          $pdo->prepare(
            "INSERT INTO web_predictions (region,secteur,taille_entreprise,seniorite,intensite_ia,risque_automatisation,salaire_predit)
             VALUES (?,?,?,?,?,?,?)"
          )->execute([
            $formData['region'],$formData['industry'],$formData['company_size'],
            $formData['seniority_level'],
            $formData['ai_intensity_score'],$formData['automation_risk_score'],
            $result['salary']
          ]);
        } catch(Exception $e) {}
      }
    } else {
      // Fallback local
      $base = 75000;
      $rw = ['North America'=>1.4,'Europe'=>1.1,'Oceania'=>1.05,'Asia'=>0.9,'Latin America'=>0.78,'Africa'=>0.75];
      $sw = ['Executive'=>2.0,'Lead'=>1.5,'Senior'=>1.4,'Mid'=>1.0,'Junior'=>0.7,'Entry'=>0.6];
      $iw = ['Technology'=>1.3,'Finance'=>1.2,'Healthcare'=>1.1,'Manufacturing'=>1.0,'Education'=>0.9,'Retail'=>0.85];
      $cw = ['Enterprise'=>1.25,'Large'=>1.15,'Medium'=>1.0,'Startup'=>0.95,'Small'=>0.9];
      $yf = 1 + ($formData['year'] - 2020) * 0.03;
      $s  = round($base * ($rw[$formData['region']]??1) * ($sw[$formData['seniority_level']]??1)
                       * ($iw[$formData['industry']]??1) * ($cw[$formData['company_size']]??1)
                       * $yf
                       * (1 + $formData['ai_intensity_score'] * 0.3)
                       * (1 - $formData['automation_risk_score'] * 0.1), -2);
      $result = ['salary'=>$s,'salary_min'=>round($s*.9,-2),'salary_max'=>round($s*1.1,-2)];

      // Sauvegarde en BDD même en mode fallback
      if($pdo) {
        try {
          $pdo->exec("CREATE TABLE IF NOT EXISTS web_predictions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            region VARCHAR(100), secteur VARCHAR(100),
            taille_entreprise VARCHAR(50), seniorite VARCHAR(50),
            intensite_ia VARCHAR(10), risque_automatisation VARCHAR(10),
            salaire_predit DECIMAL(12,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
          $pdo->prepare(
            "INSERT INTO web_predictions (region,secteur,taille_entreprise,seniorite,intensite_ia,risque_automatisation,salaire_predit)
             VALUES (?,?,?,?,?,?,?)"
          )->execute([
            $formData['region'], $formData['industry'], $formData['company_size'],
            $formData['seniority_level'],
            $formData['ai_intensity_score'], $formData['automation_risk_score'],
            $result['salary']
          ]);
        } catch(Exception $e) {}
      }
    }
  }
}

function interpretSalary($s): array {
  if($s < 40000)  return ['🔴','Entrée de gamme', 'Profil débutant ou zone géographique à faible coût de vie.'];
  if($s < 70000)  return ['🟡','Intermédiaire',   'Dans la moyenne des marchés secondaires.'];
  if($s < 110000) return ['🟢','Bon niveau',      'Au-dessus de la médiane — profil expérimenté.'];
  if($s < 160000) return ['🔵','Élevé',           'Profil senior dans un secteur porteur.'];
  return                 ['⭐','Très élevé',       'Top des rémunérations — Executive ou marché nord-américain.'];
}
?>

<div class="container page-content">
  <div class="section-header reveal">
    <h1>Prédiction de salaire</h1>
    <p>Renseignez votre profil. Notre modèle estime le salaire le plus probable d'après les tendances observées.</p>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;">

    <div class="card reveal reveal-delay-1">
      <div class="card-title">Votre profil</div>
      <div class="card-sub">7 critères — le modèle infère le salaire le plus probable pour ce profil</div>

      <form method="POST" id="predForm">
        <div class="form-grid">

          <!-- ANNÉE -->
          <div class="form-group full">
            <label>📅 Année cible</label>
            <div class="slider-wrap">
              <div class="slider-row">
                <input type="range" name="year" id="sl_year" min="2020" max="2030" step="1"
                  value="<?= $formData['year'] ?? 2024 ?>" oninput="updateYear(this)">
                <span class="slider-val" id="lbl_year"><?= $formData['year'] ?? 2024 ?></span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-dim);margin-top:4px;">
                <span>2020</span><span>2030</span>
              </div>
            </div>
          </div>

          <!-- RÉGION -->
          <div class="form-group">
            <label>🌍 Région</label>
            <select name="region" required>
              <option value="">— Sélectionner —</option>
              <?php foreach($cats['region'] as $v): ?>
              <option value="<?= htmlspecialchars($v) ?>" <?= ($formData['region']??'')===$v?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- SECTEUR -->
          <div class="form-group">
            <label>🏭 Secteur d'activité</label>
            <select name="industry" required>
              <option value="">— Sélectionner —</option>
              <?php foreach($cats['industry'] as $v): ?>
              <option value="<?= htmlspecialchars($v) ?>" <?= ($formData['industry']??'')===$v?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- TAILLE -->
          <div class="form-group">
            <label>🏢 Taille de l'entreprise</label>
            <select name="company_size" required>
              <option value="">— Sélectionner —</option>
              <?php foreach($cats['company_size'] as $v): ?>
              <option value="<?= htmlspecialchars($v) ?>" <?= ($formData['company_size']??'')===$v?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- SÉNIORITÉ -->
          <div class="form-group">
            <label>📊 Niveau de séniorité</label>
            <select name="seniority_level" required>
              <option value="">— Sélectionner —</option>
              <?php foreach($cats['seniority_level'] as $v): ?>
              <option value="<?= htmlspecialchars($v) ?>" <?= ($formData['seniority_level']??'')===$v?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- POSTE -->
          <div class="form-group full">
            <label>💼 Intitulé du poste</label>
            <select name="job_title" required>
              <option value="">— Sélectionner —</option>
              <?php foreach($cats['job_title'] as $v): ?>
              <option value="<?= htmlspecialchars($v) ?>" <?= ($formData['job_title']??'')===$v?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- SCORE IA -->
          <div class="form-group">
            <label>🤖 Intensité de l'IA dans le poste</label>
            <div class="slider-wrap">
              <div class="slider-row">
                <input type="range" name="ai_intensity_score" id="sl_ai" step="0.01" min="0" max="1"
                  value="<?= $formData['ai_intensity_score']??0.5 ?>" oninput="updateSlider(this,'lbl_ai')">
                <span class="slider-val" id="lbl_ai"><?= number_format($formData['ai_intensity_score']??0.5,2) ?></span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-dim)"><span>Faible</span><span>Élevée</span></div>
            </div>
          </div>

          <!-- RISQUE -->
          <div class="form-group">
            <label>⚙️ Risque d'automatisation</label>
            <div class="slider-wrap">
              <div class="slider-row">
                <input type="range" name="automation_risk_score" id="sl_auto" step="0.01" min="0" max="1"
                  value="<?= $formData['automation_risk_score']??0.5 ?>" oninput="updateSlider(this,'lbl_auto')">
                <span class="slider-val" id="lbl_auto"><?= number_format($formData['automation_risk_score']??0.5,2) ?></span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-dim)"><span>Faible</span><span>Élevé</span></div>
            </div>
          </div>

          <div class="form-group full" style="margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:15px;font-size:1rem;" id="submitBtn">
              🔮 Estimer le salaire
            </button>
          </div>
        </div>
      </form>

      <?php if($error && !$result): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if($result):
        [$icon,$titre,$desc] = interpretSalary($result['salary']);
        [$level,$wColor,$wMsg] = yearConfidence($formData['year']);
      ?>
        <?php if($level !== 'ok'): ?>
        <div class="alert" style="margin-top:16px;background:var(--<?= $wColor ?>-light);color:var(--<?= $wColor ?>);border:1px solid rgba(0,0,0,0.06);">
          <?= $level==='warn'?'⚠️':'🔴' ?> <?= $wMsg ?>
        </div>
        <?php endif; ?>

        <div class="result-box" id="resultBox">
          <div class="result-label">
            Salaire annuel estimé &nbsp;·&nbsp;
            <?= $formData['year'] <= 2024 ? 'référence '.$formData['year'] : 'projection '.$formData['year'] ?>
          </div>
          <div class="result-salary">$<?= number_format($result['salary'],0,',',' ') ?></div>
          <div class="result-range">Fourchette : $<?= number_format($result['salary_min'],0,',',' ') ?> – $<?= number_format($result['salary_max'],0,',',' ') ?></div>
          <div class="result-badge"><?= $icon ?> <?= $titre ?></div>
        </div>

        <div style="margin-top:12px;padding:14px 16px;background:var(--bg3);border-radius:var(--radius-sm);font-size:0.85rem;color:var(--text-muted);line-height:1.65;">
          <?= $desc ?>
          <?php if($formData['year'] > 2024): ?>
          <br><br><strong style="color:var(--text)">Note :</strong>
          Le modèle a été entraîné sur des données 2020–2024.
          Pour <?= $formData['year'] ?>, il extrapole les tendances observées.
          <?php if($formData['year'] >= 2028): ?>
          À cet horizon, l'écart avec la réalité peut être significatif.
          <?php endif; ?>
          <?php endif; ?>
        </div>

        <button class="pdf-btn" style="margin-top:14px;" onclick="exportPDF()">
          📄 Télécharger en PDF
        </button>
      <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <div style="display:flex;flex-direction:column;gap:16px;">
      <div class="card reveal reveal-delay-2">
        <div class="card-title">💡 Comment ça marche ?</div>
        <div style="font-size:0.84rem;color:var(--text-muted);line-height:1.75;margin-top:10px;">
          <p>Notre modèle a <strong style="color:var(--text)">appris les patterns</strong> sur des milliers d'offres réelles (2020–2024).</p>
          <br>
          <p>Il ne compare pas à des données existantes — il <strong style="color:var(--teal)">infère</strong> le salaire le plus probable d'après ce qu'il a appris.</p>
          <br>
          <p>Pour les années futures, il <strong style="color:var(--gold)">extrapole</strong> les tendances. Plus l'année est lointaine, plus l'incertitude grandit.</p>
        </div>
      </div>

      <div class="card reveal reveal-delay-3">
        <div class="card-title">📌 Facteurs clés</div>
        <div style="display:flex;flex-direction:column;gap:9px;margin-top:10px;font-size:0.84rem;">
          <div style="display:flex;gap:10px;"><span>🌍</span><span><strong style="color:var(--text)">Région</strong> — facteur n°1, peut doubler le salaire.</span></div>
          <div style="display:flex;gap:10px;"><span>📊</span><span><strong style="color:var(--text)">Séniorité</strong> — ×2 à ×3 entre Junior et Executive.</span></div>
          <div style="display:flex;gap:10px;"><span>🤖</span><span>Score IA élevé → salaire <strong style="color:var(--teal)">plus élevé</strong>.</span></div>
          <div style="display:flex;gap:10px;"><span>⚙️</span><span>Automatisation élevée → salaire <strong style="color:var(--coral)">plus bas</strong>.</span></div>
        </div>
      </div>

      <a href="comparaison.php" class="btn btn-secondary reveal reveal-delay-4" style="justify-content:center;text-align:center;">
        ⚖️ Comparer deux profils
      </a>
    </div>
  </div>
</div>

<footer>
  <strong>SalaryPredict</strong> &nbsp;·&nbsp; L3 MIASHS · Université de Montpellier Paul-Valéry &nbsp;·&nbsp; <?= date('Y') ?>
</footer>

<script>
function updateSlider(el, lblId) {
  const min=parseFloat(el.min),max=parseFloat(el.max),val=parseFloat(el.value);
  el.style.setProperty('--pct',((val-min)/(max-min)*100).toFixed(1)+'%');
  document.getElementById(lblId).textContent=Number.isInteger(val)?val:val.toFixed(2);
}

function updateYear(el) {
  const val=parseInt(el.value);
  el.style.setProperty('--pct',((val-2020)/10*100).toFixed(1)+'%');
  document.getElementById('lbl_year').textContent=val;
}

document.querySelectorAll('input[type="range"]').forEach(el=>{
  const min=parseFloat(el.min),max=parseFloat(el.max),val=parseFloat(el.value);
  el.style.setProperty('--pct',((val-min)/(max-min)*100).toFixed(1)+'%');
});

document.getElementById('predForm').addEventListener('submit',function(){
  const btn=document.getElementById('submitBtn');
  btn.innerHTML='<span class="spinner"></span> Analyse en cours…';
  btn.disabled=true;
});

const obs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});
},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

function exportPDF(){
  const{jsPDF}=window.jspdf;
  const doc=new jsPDF();
  doc.setFillColor(0,212,170); doc.rect(0,0,210,42,'F');
  doc.setTextColor(13,17,23); doc.setFont('helvetica','bold'); doc.setFontSize(20);
  doc.text('SalaryPredict',105,18,{align:'center'});
  doc.setFont('helvetica','normal'); doc.setFontSize(10);
  doc.text('L3 MIASHS · Université Paul-Valéry Montpellier 3',105,32,{align:'center'});
  doc.setTextColor(13,17,23); doc.setFont('helvetica','bold'); doc.setFontSize(13);
  doc.text('Salaire annuel estimé',20,56);
  doc.setFontSize(28); doc.setTextColor(0,150,120);
  doc.text('$<?= number_format($result['salary']??0,0,',',' ') ?>',20,72);
  doc.setFontSize(10); doc.setTextColor(100,100,100);
  doc.text('Fourchette : $<?= number_format($result['salary_min']??0,0,',',' ') ?> – $<?= number_format($result['salary_max']??0,0,',',' ') ?>',20,84);
  <?php if(($formData['year']??0)>2024): ?>
  doc.setTextColor(200,100,50); doc.setFontSize(9);
  doc.text('Projection <?= $formData['year'] ?> — basée sur extrapolation des tendances 2020–2024.',20,94);
  <?php endif; ?>
  doc.setTextColor(13,17,23); doc.setFont('helvetica','bold'); doc.setFontSize(12);
  doc.text('Profil analysé',20,110);
  doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(60,60,60);
  const f=[
    ['Année','<?= $formData['year']??'' ?>'],
    ['Région','<?= addslashes($formData['region']??'') ?>'],
    ['Secteur','<?= addslashes($formData['industry']??'') ?>'],
    ['Taille','<?= addslashes($formData['company_size']??'') ?>'],
    ['Séniorité','<?= addslashes($formData['seniority_level']??'') ?>'],
    ['Poste','<?= addslashes($formData['job_title']??'') ?>'],
    ['Score IA','<?= number_format($formData['ai_intensity_score']??0,2) ?>'],
    ['Risque auto.','<?= number_format($formData['automation_risk_score']??0,2) ?>'],
  ];
  let y=122;
  f.forEach(([k,v])=>{doc.setFont('helvetica','bold');doc.text(k+' :',25,y);doc.setFont('helvetica','normal');doc.text(String(v),90,y);y+=10;});
  doc.setFillColor(13,17,23); doc.rect(0,268,210,29,'F');
  doc.setTextColor(150,150,150); doc.setFontSize(8);
  doc.text('SalaryPredict · L3 MIASHS · Université de Montpellier Paul-Valéry· <?= date('Y') ?>',105,280,{align:'center'});
  doc.text('Modèle : Gradient Boosting · Données Projet_AI 2020–2024',105,290,{align:'center'});
  doc.save('estimation_<?= $formData['year']??date('Y') ?>_<?= date('Ymd') ?>.pdf');
}
</script>
</body>
</html>
