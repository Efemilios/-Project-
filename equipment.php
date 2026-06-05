<?php
// ============================================================
// equipment.php — Διαχείριση Εξοπλισμού (Εισαγωγή / Προβολή / Διαγραφή)
// ============================================================
require_once 'db.php';

$message = '';
$error   = '';

// ---- ΔΙΑΓΡΑΦΗ ----
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute() ? $message = "Ο εξοπλισμός διαγράφηκε." : $error = "Σφάλμα διαγραφής.";
    $stmt->close();
}

// ---- ΕΙΣΑΓΩΓΗ ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($conn->real_escape_string($_POST['name']));
    $category_id = (int)$_POST['category_id'];
    $price       = (float)str_replace(',', '.', $_POST['price']);
    $description = trim($conn->real_escape_string($_POST['description']));

    if ($name && $price > 0) {
        $stmt = $conn->prepare(
            "INSERT INTO equipment (name, category_id, price, description) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sids", $name, $category_id, $price, $description);
        $stmt->execute()
            ? $message = "✅ Ο εξοπλισμός <strong>$name</strong> προστέθηκε επιτυχώς!"
            : $error   = "Σφάλμα: " . $conn->error;
        $stmt->close();
    } else {
        $error = "Συμπληρώστε Όνομα και Τιμή.";
    }
}

// ---- ΔΕΔΟΜΕΝΑ ----
$categories = $conn->query("SELECT * FROM equipment_categories ORDER BY name");
$equipment  = $conn->query("
    SELECT e.*, ec.name AS cat_name
    FROM   equipment e
    LEFT JOIN equipment_categories ec ON ec.id = e.category_id
    ORDER  BY ec.name, e.name
");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoDealer — Εξοπλισμός</title>
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
        .navbar { border-bottom:2px solid #c0392b; }
        .navbar-brand { color:#c0392b !important; font-weight:700; font-size:1.4rem; }
        .nav-link:hover, .nav-link.active { color:#c0392b !important; }
        .section-title { border-left:4px solid #c0392b; padding-left:12px; margin:2rem 0 1rem; }
        .cat-badge { font-size:.75rem; }
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
        <li class="nav-item"><a class="nav-link" href="cars.php"><i class="bi bi-car-front"></i> Αυτοκίνητα</a></li>
        <li class="nav-item"><a class="nav-link active" href="equipment.php"><i class="bi bi-tools"></i> Εξοπλισμός</a></li>
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
  <h2 class="section-title"><i class="bi bi-tools me-2"></i>Διαχείριση Εξοπλισμού</h2>

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

  <!-- ΦΟΡΜΑ -->
  <div class="card-form p-4 mb-4">
    <h5 class="mb-3"><i class="bi bi-plus-circle-fill text-danger me-2"></i>Προσθήκη Νέου Εξοπλισμού</h5>
    <form method="POST" novalidate>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Όνομα <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="name" placeholder="π.χ. Κάμερα 360°" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Κατηγορία</label>
          <select class="form-select" name="category_id">
            <option value="">— Χωρίς κατηγορία —</option>
            <?php
            $categories->data_seek(0);
            while ($cat = $categories->fetch_assoc()):
            ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Τιμή (€) <span class="text-danger">*</span></label>
          <input type="number" class="form-control" name="price" min="0" step="50" placeholder="500" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Περιγραφή</label>
          <input type="text" class="form-control" name="description" placeholder="Προαιρετική περιγραφή">
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-floppy-fill me-1"></i>Αποθήκευση
        </button>
      </div>
    </form>
  </div>

  <!-- ΠΙΝΑΚΑΣ -->
  <h5 class="mb-3">
    <i class="bi bi-list-ul me-2"></i>Διαθέσιμος Εξοπλισμός
    <span class="badge bg-danger ms-2"><?= $equipment->num_rows ?></span>
  </h5>

  <?php
  // Χρωματοδοτία κατηγοριών
  $catColors = ['Ασφάλεια'=>'danger','Άνεση'=>'info','Ψυχαγωγία'=>'warning',
                'Απόδοση'=>'success','Αισθητική'=>'primary'];
  ?>

  <?php if ($equipment->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead class="table-danger">
        <tr>
          <th>ID</th><th>Όνομα</th><th>Κατηγορία</th><th>Τιμή</th><th>Περιγραφή</th><th>Ενέργεια</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($eq = $equipment->fetch_assoc()):
              $bc = $catColors[$eq['cat_name']] ?? 'secondary';
        ?>
        <tr>
          <td class="text-secondary">#<?= $eq['id'] ?></td>
          <td class="fw-bold"><?= htmlspecialchars($eq['name']) ?></td>
          <td>
            <?php if ($eq['cat_name']): ?>
              <span class="badge bg-<?= $bc ?> cat-badge"><?= htmlspecialchars($eq['cat_name']) ?></span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="text-warning fw-bold">+<?= number_format($eq['price'],2,',','.') ?> €</td>
          <td class="text-secondary small"><?= htmlspecialchars($eq['description'] ?: '—') ?></td>
          <td>
            <a href="equipment.php?delete=<?= $eq['id'] ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Διαγραφή «<?= addslashes($eq['name']) ?>»?')"
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
    <p class="text-secondary">Δεν υπάρχει εξοπλισμός. Προσθέστε παραπάνω.</p>
  <?php endif; ?>
</div>

<footer class="py-3 mt-5 text-center">
  <small>AutoDealer &copy; <?= date('Y') ?> — Εργαστηριακό Project | UniWA</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="theme.js"></script>
</body>
</html>
