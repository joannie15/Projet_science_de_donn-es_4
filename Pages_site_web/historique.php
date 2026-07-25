<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Historique — SalaryPredict</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'includes/nav.php'; require_once 'config.php';

$page  = max(1, (int)($_GET['page']??1));
$limit = 15;
$offset= ($page-1)*$limit;
$search= trim($_GET['q']??'');

$rows=[];$total=0;
if($pdo){
  try{
    $where = $search ? "WHERE region LIKE :q OR secteur LIKE :q OR seniorite LIKE :q" : "";
    $params= $search ? [':q'=>"%$search%"] : [];

    $total=(int)$pdo->prepare("SELECT COUNT(*) FROM web_predictions $where")->execute($params) ? 0 : 0;
    $stmt=$pdo->prepare("SELECT COUNT(*) FROM web_predictions $where");
    $stmt->execute($params); $total=(int)$stmt->fetchColumn();

    $stmt=$pdo->prepare("SELECT * FROM web_predictions $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $rows=$stmt->fetchAll();
  }catch(Exception $e){}
}
$pages=max(1,ceil($total/$limit));
?>

<div class="container page-content">
  <div class="section-header fade-up">
    <h1>🕐 Historique des prédictions</h1>
    <p><?= $total ?> prédiction<?= $total>1?'s':'' ?> enregistrée<?= $total>1?'s':'' ?> dans la base.</p>
  </div>

  <!-- SEARCH -->
  <form method="GET" style="margin-bottom:24px;display:flex;gap:10px" class="fade-up">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
           placeholder="Rechercher par région, secteur, séniorité…"
           style="flex:1">
    <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
    <?php if($search): ?><a href="historique.php" class="btn btn-secondary">✕ Effacer</a><?php endif; ?>
  </form>

  <div class="admin-wrap fade-up fade-up-1">
    <div class="admin-header">
      <h2>📋 Prédictions</h2>
      <span style="font-size:0.82rem;opacity:.7"><?= $total ?> résultat<?= $total>1?'s':'' ?></span>
    </div>
    <?php if(empty($rows)): ?>
      <div style="text-align:center;padding:48px;color:var(--text-muted)">
        <?= $search ? "Aucun résultat pour « ".htmlspecialchars($search)." »" : "Aucune prédiction enregistrée pour l'instant." ?>
        <?php if(!$search): ?><br><br><a href="prediction.php" class="btn btn-primary btn-sm">🔮 Faire une prédiction</a><?php endif; ?>
      </div>
    <?php else: ?>
    <div style="overflow-x:auto">
      <table class="data-table">
        <thead><tr>
          <th>#</th>
          <th>Date</th>
          <th>Région</th>
          <th>Secteur</th>
          <th>Séniorité</th>
          <th>Taille</th>
          <th>Score IA</th>
          <th>Score Auto.</th>
          <th>Salaire prédit</th>
        </tr></thead>
        <tbody>
          <?php foreach($rows as $i=>$r): ?>
          <tr>
            <td style="color:var(--text-muted);font-size:0.8rem"><?= $r['id'] ?></td>
            <td style="color:var(--text-muted);font-size:0.82rem;white-space:nowrap"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td><?= htmlspecialchars($r['region']??'—') ?></td>
            <td><?= htmlspecialchars($r['secteur']??'—') ?></td>
            <td><?= htmlspecialchars($r['seniorite']??'—') ?></td>
            <td><?= htmlspecialchars($r['taille_entreprise']??'—') ?></td>
            <td><?= htmlspecialchars($r['intensite_ia']??'—') ?></td>
            <td><?= htmlspecialchars($r['risque_automatisation']??'—') ?></td>
            <td style="font-weight:700;color:var(--accent)">$<?= number_format($r['salaire_predit'],0,',',' ') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <?php if($pages>1): ?>
    <div style="display:flex;justify-content:center;gap:6px;padding:20px">
      <?php if($page>1): ?>
        <a href="?page=<?=$page-1?>&q=<?=urlencode($search)?>" class="btn btn-secondary btn-sm">← Précédent</a>
      <?php endif; ?>
      <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
        <a href="?page=<?=$p?>&q=<?=urlencode($search)?>" class="btn <?= $p===$page?'btn-primary':'btn-secondary' ?> btn-sm"><?= $p ?></a>
      <?php endfor; ?>
      <?php if($page<$pages): ?>
        <a href="?page=<?=$page+1?>&q=<?=urlencode($search)?>" class="btn btn-secondary btn-sm">Suivant →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<footer style="margin-top:60px">
  <strong>SalaryPredict</strong> &nbsp;·&nbsp; <?= date('Y') ?>
</footer>
</body>
</html>
