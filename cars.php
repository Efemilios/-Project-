<?php
// ============================================================
// cars.php — Διαχείριση Αυτοκινήτων (Εισαγωγή / Προβολή / Διαγραφή)
// ============================================================
require_once 'db.php';

$message = '';
$error   = '';

// ---- ΔΙΑΓΡΑΦΗ ----
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Το αυτοκίνητο διαγράφηκε επιτυχώς.";
    } else {
        $error = "Σφάλμα διαγραφής. Ίσως υπάρχουν αποθηκευμένες διαμορφώσεις για αυτό το αυτοκίνητο.";
    }
    $stmt->close();
}

// ---- ΕΙΣΑΓΩΓΗ ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand      = trim($conn->real_escape_string($_POST['brand']));
    $model      = trim($conn->real_escape_string($_POST['model']));
    $year       = (int)$_POST['year'];
    $color      = trim($conn->real_escape_string($_POST['color']));
    $base_price = (float)str_replace(',', '.', $_POST['base_price']);

    if ($brand && $model && $year >= 1990 && $base_price > 0) {
        $stmt = $conn->prepare(
            "INSERT INTO cars (brand, model, year, color, base_price) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssiss", $brand, $model, $year, $color, $base_price);
        if ($stmt->execute()) {
            $message = "✅ Το αυτοκίνητο <strong>$brand $model</strong> προστέθηκε επιτυχώς!";
        } else {
            $error = "Σφάλμα κατά την αποθήκευση: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Παρακαλώ συμπληρώστε σωστά όλα τα απαιτούμενα πεδία.";
    }
}

// ---- ΑΝΑΚΤΗΣΗ ΟΛΩΝ ΤΩΝ ΑΥΤΟΚΙΝΗΤΩΝ ----
$cars = $conn->query("SELECT * FROM cars ORDER BY brand, model");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoDealer — Αυτοκίνητα</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="theme.css">
    <script>
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
        .section-title { border-left:4px solid #1a73e8; padding-left:12px; margin:2rem 0 1rem; }
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
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Αρχική</a></li>
        <li class="nav-item"><a class="nav-link active" href="cars.php"><i class="bi bi-car-front"></i> Αυτοκίνητα</a></li>
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

<div class="container py-4">
  <h2 class="section-title"><i class="bi bi-car-front me-2"></i>Διαχείριση Αυτοκινήτων</h2>

  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $error ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- ΦΟΡΜΑ ΕΙΣΑΓΩΓΗΣ -->
  <div class="card-form p-4 mb-4">
    <h5 class="mb-3"><i class="bi bi-plus-circle-fill text-danger me-2"></i>Προσθήκη Νέου Αυτοκινήτου</h5>
    <form method="POST" novalidate>
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Μάρκα <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="brand" placeholder="π.χ. Toyota" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Μοντέλο <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="model" placeholder="π.χ. Corolla" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Έτος <span class="text-danger">*</span></label>
          <input type="number" class="form-control" name="year"
                 min="1990" max="<?= date('Y')+1 ?>" placeholder="<?= date('Y') ?>" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Χρώμα</label>
          <input type="text" class="form-control" name="color" placeholder="π.χ. Λευκό">
        </div>
        <div class="col-md-2">
          <label class="form-label">Βασική Τιμή (€) <span class="text-danger">*</span></label>
          <input type="number" class="form-control" name="base_price"
                 min="1000" step="100" placeholder="25000" required>
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-floppy-fill me-1"></i>Αποθήκευση
        </button>
      </div>
    </form>
  </div>

  <!-- ΠΙΝΑΚΑΣ ΑΥΤΟΚΙΝΗΤΩΝ -->
  <h5 class="mb-3">
    <i class="bi bi-list-ul me-2"></i>Καταχωρημένα Αυτοκίνητα
    <span class="badge bg-danger ms-2"><?= $cars->num_rows ?></span>
  </h5>

  <?php if ($cars->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead class="table-danger">
        <tr>
          <th>ID</th><th>Μάρκα</th><th>Μοντέλο</th><th>Έτος</th>
          <th>Χρώμα</th><th>Βασική Τιμή</th><th>Ενέργεια</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($car = $cars->fetch_assoc()): ?>
        <tr>
          <td class="text-secondary">#<?= $car['id'] ?></td>
          <td class="fw-bold"><?= htmlspecialchars($car['brand']) ?></td>
          <td><?= htmlspecialchars($car['model']) ?></td>
          <td><?= $car['year'] ?></td>
          <td><?= htmlspecialchars($car['color'] ?: '—') ?></td>
          <td class="text-danger fw-bold"><?= number_format($car['base_price'],2,',','.') ?> €</td>
          <td>
            <a href="configurator.php?car_id=<?= $car['id'] ?>"
               class="btn btn-sm btn-outline-warning me-1" title="Διαμόρφωση">
              <i class="bi bi-sliders"></i>
            </a>
            <a href="cars.php?delete=<?= $car['id'] ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Διαγραφή αυτοκινήτου <?= addslashes($car['brand'].' '.$car['model']) ?>;')"
               title="Διαγραφή">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p class="text-secondary">Δεν υπάρχουν αυτοκίνητα. Προσθέστε το πρώτο παραπάνω.</p>
  <?php endif; ?>
</div>

<footer class="py-3 mt-5 text-center">
  <small>AutoDealer &copy; <?= date('Y') ?> — Εργαστηριακό Project | UniWA</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="theme.js"></script>
</body>
</html>
