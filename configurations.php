<?php
// ============================================================
// configurations.php — Προβολή Αποθηκευμένων Quotes / Διαμορφώσεων
// ============================================================
require_once 'db.php';

// ---- ΔΙΑΓΡΑΦΗ ----
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM car_configurations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: configurations.php?deleted=1");
    exit;
}

// ---- ΑΝΑΚΤΗΣΗ ΟΛΩΝ ΤΩΝ CONFIGURATIONS ----
$configs = $conn->query("
    SELECT cc.id, cc.customer_name, cc.total_price, cc.notes, cc.created_at,
           c.brand, c.model, c.year, c.base_price, c.color
    FROM   car_configurations cc
    JOIN   cars c ON c.id = cc.car_id
    ORDER  BY cc.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoDealer — Αποθηκευμένα Quotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#0f1117; color:#e0e0e0; font-family:'Segoe UI',sans-serif; }
        .navbar { background:#1a1d27 !important; border-bottom:2px solid #c0392b; }
        .navbar-brand { color:#c0392b !important; font-weight:700; font-size:1.4rem; }
        .nav-link { color:#ccc !important; }
        .nav-link:hover, .nav-link.active { color:#c0392b !important; }
        .section-title { color:#fff; border-left:4px solid #c0392b; padding-left:12px; margin:2rem 0 1rem; }
        .config-card {
            background:#1a1d27; border:1px solid #2a2d3a; border-radius:12px;
            margin-bottom:1rem; transition: border-color .2s;
        }
        .config-card:hover { border-color:#c0392b; }
        .config-card .header { border-bottom:1px solid #2a2d3a; padding:14px 18px; }
        .config-card .body   { padding:14px 18px; }
        .price-badge { background:#c0392b; font-size:1.1rem; font-weight:700; padding:6px 14px; border-radius:8px; }
        .eq-pill { background:#2a2d3a; border-radius:20px; padding:3px 10px;
                   font-size:.78rem; display:inline-block; margin:2px; }
        .eq-pill.safety   { border-left:3px solid #dc3545; }
        .eq-pill.comfort  { border-left:3px solid #0dcaf0; }
        .eq-pill.entertain{ border-left:3px solid #ffc107; }
        .eq-pill.perform  { border-left:3px solid #198754; }
        .eq-pill.aesthetic{ border-left:3px solid #0d6efd; }
        footer { background:#1a1d27; border-top:1px solid #2a2d3a; color:#888; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-car-front-fill"></i> AutoDealer</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Αρχική</a></li>
        <li class="nav-item"><a class="nav-link" href="cars.php"><i class="bi bi-car-front"></i> Αυτοκίνητα</a></li>
        <li class="nav-item"><a class="nav-link" href="equipment.php"><i class="bi bi-tools"></i> Εξοπλισμός</a></li>
        <li class="nav-item"><a class="nav-link" href="configurator.php"><i class="bi bi-sliders"></i> Διαμόρφωση</a></li>
        <li class="nav-item"><a class="nav-link active" href="configurations.php"><i class="bi bi-journal-check"></i> Αποθηκευμένα</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h2 class="section-title"><i class="bi bi-journal-check me-2"></i>Αποθηκευμένα Quotes
    <span class="badge bg-danger ms-2"><?= $configs->num_rows ?></span>
  </h2>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
      Το quote διαγράφηκε.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($configs->num_rows === 0): ?>
    <div class="text-center py-5 text-secondary">
      <i class="bi bi-journal-x display-4"></i>
      <p class="mt-3">Δεν υπάρχουν αποθηκευμένα quotes ακόμα.</p>
      <a href="configurator.php" class="btn btn-danger mt-2">
        <i class="bi bi-sliders me-1"></i>Δημιουργήστε το πρώτο
      </a>
    </div>
  <?php else: ?>

    <?php while ($cfg = $configs->fetch_assoc()):
        // Ανάκτηση εξοπλισμού για αυτό το configuration
        $eq_items = $conn->query("
            SELECT e.name, ec.name AS cat_name
            FROM   configuration_equipment ce
            JOIN   equipment e  ON e.id  = ce.equipment_id
            LEFT JOIN equipment_categories ec ON ec.id = e.category_id
            WHERE  ce.config_id = {$cfg['id']}
            ORDER  BY ec.name, e.name
        ");
        $eq_extra = $cfg['total_price'] - $cfg['base_price'];
    ?>
    <div class="config-card">
      <div class="header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <span class="text-secondary small me-2">#<?= $cfg['id'] ?></span>
          <strong class="text-white fs-5">
            <i class="bi bi-car-front me-1 text-danger"></i>
            <?= htmlspecialchars($cfg['brand'].' '.$cfg['model'].' ('.$cfg['year'].')') ?>
          </strong>
          <?php if ($cfg['customer_name']): ?>
            <span class="text-secondary ms-2">— <?= htmlspecialchars($cfg['customer_name']) ?></span>
          <?php endif; ?>
          <br>
          <small class="text-secondary">
            <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($cfg['created_at'])) ?>
            <?php if ($cfg['notes']): ?>
              &nbsp;·&nbsp; <i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($cfg['notes']) ?>
            <?php endif; ?>
          </small>
        </div>
        <div class="text-end">
          <div class="price-badge text-white"><?= number_format($cfg['total_price'],2,',','.') ?> €</div>
          <small class="text-secondary d-block mt-1">
            Βάση: <?= number_format($cfg['base_price'],0,',','.') ?> €
            <?php if ($eq_extra > 0): ?>
              + Εξοπλ.: <?= number_format($eq_extra,0,',','.') ?> €
            <?php endif; ?>
          </small>
        </div>
      </div>
      <div class="body">
        <?php if ($eq_items->num_rows > 0): ?>
          <div class="mb-1 text-secondary small"><i class="bi bi-tools me-1"></i>Εξοπλισμός:</div>
          <?php
          $catMap = ['Ασφάλεια'=>'safety','Άνεση'=>'comfort','Ψυχαγωγία'=>'entertain',
                     'Απόδοση'=>'perform','Αισθητική'=>'aesthetic'];
          while ($ei = $eq_items->fetch_assoc()):
              $cls = $catMap[$ei['cat_name']] ?? '';
          ?>
            <span class="eq-pill <?= $cls ?>"><?= htmlspecialchars($ei['name']) ?></span>
          <?php endwhile; ?>
        <?php else: ?>
          <span class="text-secondary small">Χωρίς επιπλέον εξοπλισμό</span>
        <?php endif; ?>

        <div class="mt-2 d-flex gap-2">
          <a href="configurator.php" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-plus-circle me-1"></i>Νέο Quote
          </a>
          <a href="configurations.php?delete=<?= $cfg['id'] ?>"
             class="btn btn-sm btn-outline-danger"
             onclick="return confirm('Διαγραφή quote #<?= $cfg['id'] ?>;')">
            <i class="bi bi-trash me-1"></i>Διαγραφή
          </a>
        </div>
      </div>
    </div>
    <?php endwhile; ?>

    <div class="mt-3">
      <a href="configurator.php" class="btn btn-danger">
        <i class="bi bi-plus-circle me-1"></i>Νέα Διαμόρφωση
      </a>
    </div>
  <?php endif; ?>
</div>

<footer class="py-3 mt-5 text-center">
  <small>AutoDealer &copy; <?= date('Y') ?> — Εργαστηριακό Project | UniWA</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
