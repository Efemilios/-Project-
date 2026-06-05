<?php
// ============================================================
// index.php — Dashboard / Αρχική Σελίδα
// ============================================================
require_once 'db.php';

// Στατιστικά για το dashboard
$total_cars     = $conn->query("SELECT COUNT(*) AS n FROM cars")->fetch_assoc()['n'];
$total_equip    = $conn->query("SELECT COUNT(*) AS n FROM equipment")->fetch_assoc()['n'];
$total_configs  = $conn->query("SELECT COUNT(*) AS n FROM car_configurations")->fetch_assoc()['n'];
$avg_config     = $conn->query("SELECT AVG(total_price) AS avg FROM car_configurations")->fetch_assoc()['avg'];

// Τελευταίες 5 διαμορφώσεις
$recent = $conn->query("
    SELECT cc.id, cc.customer_name, cc.total_price, cc.created_at,
           c.brand, c.model
    FROM   car_configurations cc
    JOIN   cars c ON c.id = cc.car_id
    ORDER  BY cc.created_at DESC
    LIMIT  5
");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoDealer — Αρχική</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="theme.css">
    <script>
        // Inline pre-paint: εφαρμογή θέματος πριν την εμφάνιση για αποφυγή flash.
        (function () {
            try {
                var t = localStorage.getItem('autodealer-theme');
                if (t !== 'light' && t !== 'dark') {
                    t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        })();
    </script>
    <style>
        .navbar { border-bottom:2px solid #1a73e8; }
        .navbar-brand { color:#1a73e8 !important; font-weight:700; font-size:1.4rem; }
        .nav-link:hover, .nav-link.active { color:#1a73e8 !important; }
        .card-stat .icon { font-size:2.5rem; }
        .stat-value { font-size:2rem; font-weight:700; color:#1a73e8; }
        .section-title { border-left:4px solid #1a73e8; padding-left:12px; margin:2rem 0 1rem; }
        .badge-brand { background:#1a73e8; color:#fff; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-car-front-fill"></i> AutoDealer</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-house"></i> Αρχική</a></li>
        <li class="nav-item"><a class="nav-link" href="cars.php"><i class="bi bi-car-front"></i> Αυτοκίνητα</a></li>
        <li class="nav-item"><a class="nav-link" href="equipment.php"><i class="bi bi-tools"></i> Εξοπλισμός</a></li>
        <li class="nav-item"><a class="nav-link" href="configurator.php"><i class="bi bi-sliders"></i> Διαμόρφωση</a></li>
        <li class="nav-item"><a class="nav-link" href="configurations.php"><i class="bi bi-journal-check"></i> Αποθηκευμένα</a></li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
          <button type="button" id="themeToggle" class="btn btn-sm btn-outline-secondary theme-toggle" title="Εναλλαγή θέματος" aria-label="Εναλλαγή θέματος">
            <i id="themeIcon" class="bi bi-moon-stars-fill"></i>
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="py-5 hero-banner">
  <div class="container text-center">
    <h1 class="display-5 fw-bold">Σύστημα Διαχείρισης Εξοπλισμού</h1>
    <p class="lead text-secondary">Αντιπροσωπεία Αυτοκινήτων — Διαμόρφωση & Εκτιμώμενη Τιμή</p>
  </div>
</div>

<!-- STATS -->
<div class="container py-4">
  <div class="row g-4">
    <div class="col-md-3">
      <div class="card-stat p-4 text-center">
        <div class="icon text-danger mb-2"><i class="bi bi-car-front-fill"></i></div>
        <div class="stat-value"><?= $total_cars ?></div>
        <div class="text-secondary">Αυτοκίνητα</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-stat p-4 text-center">
        <div class="icon text-warning mb-2"><i class="bi bi-tools"></i></div>
        <div class="stat-value"><?= $total_equip ?></div>
        <div class="text-secondary">Επιλογές Εξοπλισμού</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-stat p-4 text-center">
        <div class="icon text-info mb-2"><i class="bi bi-journal-check"></i></div>
        <div class="stat-value"><?= $total_configs ?></div>
        <div class="text-secondary">Αποθηκευμένα Quotes</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-stat p-4 text-center">
        <div class="icon text-success mb-2"><i class="bi bi-currency-euro"></i></div>
        <div class="stat-value"><?= $avg_config ? number_format($avg_config,0,',','.') : '—' ?></div>
        <div class="text-secondary">Μέση Τιμή Quote (€)</div>
      </div>
    </div>
  </div>

  <!-- SHORTCUTS -->
  <h4 class="section-title mt-4">Γρήγορες Ενέργειες</h4>
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <a href="cars.php" class="btn btn-outline-danger w-100 py-3">
        <i class="bi bi-plus-circle me-2"></i>Προσθήκη Αυτοκινήτου
      </a>
    </div>
    <div class="col-md-4">
      <a href="equipment.php" class="btn btn-outline-warning w-100 py-3">
        <i class="bi bi-plus-circle me-2"></i>Προσθήκη Εξοπλισμού
      </a>
    </div>
    <div class="col-md-4">
      <a href="configurator.php" class="btn btn-danger w-100 py-3">
        <i class="bi bi-sliders me-2"></i>Νέα Διαμόρφωση / Quote
      </a>
    </div>
  </div>

  <!-- RECENT CONFIGURATIONS -->
  <h4 class="section-title">Τελευταίες Διαμορφώσεις</h4>
  <?php if ($recent->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead>
        <tr>
          <th>#</th><th>Πελάτης</th><th>Αυτοκίνητο</th><th>Συνολική Τιμή</th><th>Ημερομηνία</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($r = $recent->fetch_assoc()): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['customer_name'] ?: '—') ?></td>
          <td><span class="badge badge-brand"><?= htmlspecialchars($r['brand'].' '.$r['model']) ?></span></td>
          <td class="fw-bold text-danger"><?= number_format($r['total_price'],2,',','.') ?> €</td>
          <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <a href="configurations.php" class="btn btn-sm btn-outline-secondary">Δείτε όλα →</a>
  <?php else: ?>
    <p class="text-secondary">Δεν υπάρχουν αποθηκευμένες διαμορφώσεις ακόμα. 
       <a href="configurator.php" class="text-danger">Δημιουργήστε την πρώτη!</a></p>
  <?php endif; ?>
</div>

<!-- FOOTER -->
<footer class="py-3 mt-5 text-center">
  <small>AutoDealer &copy; <?= date('Y') ?> — Εργαστηριακό Project | Τεχνολογία Διαδικτύου στην Ψηφιακή Βιομηχανία | UniWA</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="theme.js"></script>
</body>
</html>
