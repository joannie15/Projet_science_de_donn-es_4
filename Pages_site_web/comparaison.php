<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Comparaison — SalaryPredict</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'includes/nav.php'; require_once 'config.php';

$cats = getCategories();
$defaults=['region'=>['Africa','Asia','Europe','Latin America','North America','Oceania'],'industry'=>['Education','Finance','Healthcare','Manufacturing','Retail','Technology'],'company_size'=>['Large','Medium','Small','Startup'],'seniority_level'=>['Entry','Executive','Junior','Mid','Senior'],'job_title'=>['AI Engineer','Analyst','Data Scientist','Developer','Manager','Researcher'],'year_range'=>[2020,2030],'automation_risk_score_range'=>[0.0,1.0],'ai_intensity_score_range'=>[0.0,1.0]];
foreach($defaults as $k=>$v) if(empty($cats[$k])) $cats[$k]=$v;

$results=[null,null];$error=null;$profiles=[[],[]];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $fields=['year','region','industry','automation_risk_score','company_size','seniority_level','ai_intensity_score','job_title'];
  $valid=true;
  for($i=1;$i<=2;$i++){
    $p=[];
    foreach($fields as $f){
      $v=trim($_POST["p{$i}_{$f}"]??'');
      if($v===''){$valid=false;break 2;}
      $p[$f]=$f==='year'?(int)$v:(in_array($f,['automation_risk_score','ai_intensity_score'])?(float)$v:$v);
    }
    $profiles[$i-1]=$p;
  }
  if(!$valid){$error="Remplissez tous les champs des deux profils.";}
  else{
    // Fonction fallback locale identique à prediction.php
    function estimateSalary($p){
      $r=callPredictAPI($p);
      if(isset($r['salary'])) return $r;
      $base=75000;
      $rw=['North America'=>1.4,'Europe'=>1.1,'Oceania'=>1.05,'Asia'=>0.9,'Latin America'=>0.78,'Africa'=>0.75];
      $sw=['Executive'=>2.0,'Lead'=>1.5,'Senior'=>1.4,'Mid'=>1.0,'Junior'=>0.7,'Entry'=>0.6];
      $iw=['Technology'=>1.3,'Finance'=>1.2,'Healthcare'=>1.1,'Manufacturing'=>1.0,'Education'=>0.9,'Retail'=>0.85];
      $cw=['Enterprise'=>1.25,'Large'=>1.15,'Medium'=>1.0,'Startup'=>0.95,'Small'=>0.9];
      $yf=1+((int)$p['year']-2020)*0.03;
      $s=round($base*($rw[$p['region']]??1)*($sw[$p['seniority_level']]??1)*($iw[$p['industry']]??1)*($cw[$p['company_size']]??1)*$yf*(1+$p['ai_intensity_score']*0.3)*(1-$p['automation_risk_score']*0.1),-2);
      return ['salary'=>$s,'salary_min'=>round($s*.9,-2),'salary_max'=>round($s*1.1,-2)];
    }
    $results[0]=estimateSalary($profiles[0]);
    $results[1]=estimateSalary($profiles[1]);
  }
}

function mkSelect($cats,$key,$name,$selected=''){
  $h='<option value="">— Sélectionner —</option>';
  foreach($cats[$key] as $v) $h.='<option value="'.htmlspecialchars($v).'"'.($selected===$v?' selected':'').'>'.htmlspecialchars($v).'</option>';
  return "<select name=\"$name\" required>$h</select>";
}
?>

<div class="container page-content">
  <div class="section-header reveal">
    <h1>Comparaison de profils</h1>
    <p>Simulez deux scénarios de carrière et mesurez l'impact de chaque variable sur le salaire.</p>
  </div>

  <form method="POST">
    <div class="compare-grid reveal">
      <?php for($i=1;$i<=2;$i++): $p=$profiles[$i-1]; $color=$i===1?'var(--teal)':'var(--coral)'; ?>
      <div class="card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
          <div style="width:12px;height:12px;border-radius:50%;background:<?= $color ?>"></div>
          <div class="card-title" style="margin:0">Profil <?= $i ?></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:13px">

          <div class="form-group">
            <label>📅 Année cible</label>
            <div class="slider-row">
              <input type="range" name="p<?=$i?>_year" id="sl<?=$i?>_yr"
                min="2020" max="2030" step="1"
                value="<?=$p['year']??2024?>" oninput="updateSlider(this,'lbl<?=$i?>yr')">
              <span class="slider-val" id="lbl<?=$i?>yr"><?=$p['year']??2024?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--text-dim);margin-top:2px"><span>2020</span><span>→ 2030</span></div>
          </div>

          <div class="form-group"><label>🌍 Région</label><?= mkSelect($cats,'region',"p{$i}_region",$p['region']??'') ?></div>
          <div class="form-group"><label>🏭 Secteur</label><?= mkSelect($cats,'industry',"p{$i}_industry",$p['industry']??'') ?></div>
          <div class="form-group"><label>🏢 Taille entreprise</label><?= mkSelect($cats,'company_size',"p{$i}_company_size",$p['company_size']??'') ?></div>
          <div class="form-group"><label>📊 Séniorité</label><?= mkSelect($cats,'seniority_level',"p{$i}_seniority_level",$p['seniority_level']??'') ?></div>
          <div class="form-group"><label>💼 Poste</label><?= mkSelect($cats,'job_title',"p{$i}_job_title",$p['job_title']??'') ?></div>

          <div class="form-group">
            <label>🤖 Score IA</label>
            <div class="slider-row">
              <input type="range" name="p<?=$i?>_ai_intensity_score" id="sl<?=$i?>_ai" step="0.01"
                min="0" max="1"
                value="<?=$p['ai_intensity_score']??0.5?>" oninput="updateSlider(this,'lbl<?=$i?>ai')">
              <span class="slider-val" id="lbl<?=$i?>ai"><?= number_format($p['ai_intensity_score']??0.5,2) ?></span>
            </div>
          </div>

          <div class="form-group">
            <label>⚙️ Score automatisation</label>
            <div class="slider-row">
              <input type="range" name="p<?=$i?>_automation_risk_score" id="sl<?=$i?>_auto" step="0.01"
                min="0" max="1"
                value="<?=$p['automation_risk_score']??0.5?>" oninput="updateSlider(this,'lbl<?=$i?>auto')">
              <span class="slider-val" id="lbl<?=$i?>auto"><?= number_format($p['automation_risk_score']??0.5,2) ?></span>
            </div>
          </div>
        </div>

        <?php if($results[$i-1]): $s=$results[$i-1]['salary']; ?>
        <div class="compare-result-box p<?=$i?>" style="margin-top:20px">
          <div style="font-size:0.75rem;opacity:.8;text-transform:uppercase;letter-spacing:.08em">
            Salaire estimé · <?= ($p['year']??2024) <= 2024 ? 'référence' : 'projection' ?> <?= $p['year']??2024 ?>
          </div>
          <div class="compare-sal">$<?= number_format($s,0,',',' ') ?></div>
          <div style="font-size:0.8rem;opacity:.75">$<?= number_format($results[$i-1]['salary_min'],0,',',' ') ?> – $<?= number_format($results[$i-1]['salary_max'],0,',',' ') ?></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endfor; ?>
    </div>

    <?php if($error): ?><div class="alert alert-error fade-up" style="margin-top:16px">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="text-align:center;margin-top:24px" class="fade-up">
      <button type="submit" class="btn btn-primary btn-lg">⚖️ Comparer les deux profils</button>
    </div>
  </form>

  <!-- RÉSULTAT DIFFÉRENCE -->
  <?php if($results[0]&&$results[1]):
    $s0=$results[0]['salary']; $s1=$results[1]['salary'];
    $diff=$s0-$s1; $pct=$s1>0?round(abs($diff)/$s1*100):0;
    $winner=$diff>0?1:($diff<0?2:0);
    $maxS=max($s0,$s1);
  ?>
  <div class="diff-box reveal" style="margin-top:28px">
    <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Différence de salaire</div>
    <div class="diff-val <?= $diff>=0?'diff-positive':'diff-negative' ?>">
      <?= $diff>=0?'+':'' ?>$<?= number_format(abs($diff),0,',',' ') ?>
      <span style="font-size:1rem;font-family:'Outfit',sans-serif;font-weight:400">(<?= $pct ?>%)</span>
    </div>
    <?php if($winner>0): ?>
    <p style="margin-top:10px;color:var(--text-muted);font-size:0.9rem">
      Le <strong style="color:var(--text)">Profil <?= $winner ?></strong> est mieux rémunéré de
      <strong style="color:var(--<?= $winner===1?'teal':'coral' ?>)">$<?= number_format(abs($diff),0,',',' ') ?>/an</strong>.
    </p>
    <?php endif; ?>

    <div style="margin-top:24px;display:flex;flex-direction:column;gap:10px;max-width:500px;margin-left:auto;margin-right:auto">
      <?php foreach([[$s0,'Profil 1','fill-teal'],[$s1,'Profil 2','fill-coral']] as [$s,$lbl,$cls]): ?>
      <div class="chart-bar-row">
        <div class="chart-bar-label"><?= $lbl ?></div>
        <div class="chart-bar-track">
          <div class="chart-bar-fill <?= $cls ?>" style="width:<?= round($s/$maxS*100) ?>%">
            $<?= number_format($s,0,',',' ') ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<footer style="margin-top:60px">
  <strong>SalaryPredict</strong> &nbsp;·&nbsp; L3 MIASHS · Université de Montpellier Paul-Valéry &nbsp;·&nbsp; <?= date('Y') ?>
</footer>

<script>
function updateSlider(el,lblId){
  const min=parseFloat(el.min),max=parseFloat(el.max),val=parseFloat(el.value);
  el.style.setProperty('--pct',((val-min)/(max-min)*100).toFixed(1)+'%');
  document.getElementById(lblId).textContent=Number.isInteger(val)?val:val.toFixed(2);
}
document.querySelectorAll('input[type="range"]').forEach(el=>{
  const min=parseFloat(el.min),max=parseFloat(el.max),val=parseFloat(el.value);
  el.style.setProperty('--pct',((val-min)/(max-min)*100).toFixed(1)+'%');
});
const obs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});
},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>
</body>
</html>
