<?php
// ============================================================
// configurator.php — Διαμόρφωση & Υπολογισμός Εκτιμώμενης Τιμής
// ============================================================
require_once 'db.php';

$message    = '';
$saved_id   = null;
$presel_car = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

// ---- ΑΠΟΘΗΚΕΥΣΗ ΔΙΑΜΟΡΦΩΣΗΣ ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id        = (int)$_POST['car_id'];
    $customer_name = trim($conn->real_escape_string($_POST['customer_name']));
    $notes         = trim($conn->real_escape_string($_POST['notes']));
    $selected_eq   = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    $total_price   = (float)str_replace(',', '.', $_POST['total_price']);

    if ($car_id > 0 && $total_price > 0) {
        // Αποθήκευση κεφαλίδας
        $stmt = $conn->prepare(
            "INSERT INTO car_configurations (car_id, customer_name, total_price, notes)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isds", $car_id, $customer_name, $total_price, $notes);
        if ($stmt->execute()) {
            $config_id = $conn->insert_id;
            $stmt->close();

            // Αποθήκευση επιλεγμένου εξοπλισμού
            if (!empty($selected_eq)) {
                $stmt2 = $conn->prepare(
                    "INSERT INTO configuration_equipment (config_id, equipment_id) VALUES (?, ?)"
                );
                foreach ($selected_eq as $eq_id) {
                    $eq_id = (int)$eq_id;
                    $stmt2->bind_param("ii", $config_id, $eq_id);
                    $stmt2->execute();
                }
                $stmt2->close();
            }
            $saved_id = $config_id;
            $message  = "success";
        } else {
            $message = "error";
        }
    } else {
        $message = "validation";
    }
}

// ---- ΔΕΔΟΜΕΝΑ ΓΙΑ ΤΗ ΦΟΡΜΑ ----
$cars = $conn->query("SELECT * FROM cars ORDER BY brand, model");

// Εξοπλισμός ομαδοποιημένος κατά κατηγορία
$eq_result = $conn->query("
    SELECT e.*, ec.name AS cat_name
    FROM   equipment e
    LEFT JOIN equipment_categories ec ON ec.id = e.category_id
    ORDER  BY ec.name, e.name
");

$equipment_by_cat = [];
while ($eq = $eq_result->fetch_assoc()) {
    $cat = $eq['cat_name'] ?: 'Άλλα';
    $equipment_by_cat[$cat][] = $eq;
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoDealer — Διαμόρφωση</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#0f1117; color:#e0e0e0; font-family:'Segoe UI',sans-serif; }
        .navbar { background:#1a1d27 !important; border-bottom:2px solid #c0392b; }
        .navbar-brand { color:#c0392b !important; font-weight:700; font-size:1.4rem; }
        .nav-link { color:#ccc !important; }
        .nav-link:hover, .nav-link.active { color:#c0392b !important; }
        .card-section { background:#1a1d27; border:1px solid #2a2d3a; border-radius:12px; }
        .form-control, .form-select {
            background:#0f1117; border:1px solid #3a3d4a; color:#e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            background:#0f1117; color:#fff; border-color:#c0392b;
            box-shadow:0 0 0 .2rem rgba(192,57,43,.25);
        }
        .section-title { color:#fff; border-left:4px solid #c0392b; padding-left:12px; margin:2rem 0 1rem; }

        /* Price sidebar */
        .price-box { background:#1a1d27; border:2px solid #c0392b; border-radius:12px; position:sticky; top:80px; }
        .price-total { font-size:2rem; font-weight:700; color:#c0392b; }

        /* Equipment checkboxes */
        .eq-item {
            background:#0f1117; border:1px solid #2a2d3a; border-radius:8px;
            padding:10px 14px; margin-bottom:8px; cursor:pointer;
            transition:border-color .2s, background .2s;
        }
        .eq-item:hover { border-color:#c0392b; }
        .eq-item input[type=checkbox]:checked ~ label { color:#c0392b; }
        .eq-item.selected { border-color:#c0392b; background:#1e0f0d; }
        .eq-price { color:#f39c12; font-weight:600; }

        .cat-header { color:#aaa; font-size:.8rem; text-transform:uppercase; letter-spacing:1px;
                      border-bottom:1px solid #2a2d3a; padding-bottom:6px; margin:16px 0 8px; }
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
        <li class="nav-item"><a class="nav-link active" href="configurator.php"><i class="bi bi-sliders"></i> Διαμόρφωση</a></li>
        <li class="nav-item"><a class="nav-link" href="configurations.php"><i class="bi bi-journal-check"></i> Αποθηκευμένα</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h2 class="section-title"><i class="bi bi-sliders me-2"></i>Διαμόρφωση Αυτοκινήτου & Εκτιμώμενη Τιμή</h2>

  <?php if ($message === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show">
      ✅ Το quote αποθηκεύτηκε επιτυχώς! (ID: #<?= $saved_id ?>)
      <a href="configurations.php" class="alert-link ms-2">Δείτε όλα →</a>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php elseif ($message === 'error'): ?>
    <div class="alert alert-danger">Σφάλμα αποθήκευσης. Δοκιμάστε ξανά.</div>
  <?php elseif ($message === 'validation'): ?>
    <div class="alert alert-warning">Επιλέξτε αυτοκίνητο για να συνεχίσετε.</div>
  <?php endif; ?>

  <form method="POST" id="configForm">
    <div class="row g-4">

      <!-- ΑΡΙΣΤΕΡΑ: Φόρμα διαμόρφωσης -->
      <div class="col-lg-8">

        <!-- Βήμα 1: Επιλογή Αυτοκινήτου -->
        <div class="card-section p-4 mb-4">
          <h5 class="text-white mb-3"><span class="badge bg-danger me-2">1</span>Επιλογή Αυτοκινήτου</h5>
          <select class="form-select form-select-lg" name="car_id" id="car_select" required>
            <option value="">— Επιλέξτε αυτοκίνητο —</option>
            <?php
            $cars->data_seek(0);
            while ($car = $cars->fetch_assoc()):
                $sel = ($presel_car === (int)$car['id']) ? 'selected' : '';
            ?>
              <option value="<?= $car['id'] ?>"
                      data-price="<?= $car['base_price'] ?>"
                      <?= $sel ?>>
                <?= htmlspecialchars($car['brand'].' '.$car['model'].' ('.$car['year'].')') ?>
                — Βασική: <?= number_format($car['base_price'],0,',','.') ?> €
              </option>
            <?php endwhile; ?>
          </select>
          <div class="mt-2 text-secondary small">
            Βασική τιμή: <span id="base_price_display" class="text-white fw-bold">—</span>
          </div>
        </div>

        <!-- Βήμα 2: Επιλογή Εξοπλισμού -->
        <div class="card-section p-4 mb-4">
          <h5 class="text-white mb-3"><span class="badge bg-danger me-2">2</span>Επιλογή Εξοπλισμού</h5>
          <p class="text-secondary small mb-3">Κλικ σε κάθε επιλογή για να την ενεργοποιήσετε. Η τιμή ενημερώνεται αυτόματα.</p>

          <?php foreach ($equipment_by_cat as $cat_name => $items): ?>
            <div class="cat-header"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($cat_name) ?></div>
            <?php foreach ($items as $eq): ?>
              <div class="eq-item" id="item_<?= $eq['id'] ?>" onclick="toggleEq(<?= $eq['id'] ?>, <?= $eq['price'] ?>)">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <input type="checkbox" name="equipment[]" value="<?= $eq['id'] ?>"
                           id="eq_<?= $eq['id'] ?>" class="eq-check me-2"
                           style="accent-color:#c0392b;" onclick="event.stopPropagation()">
                    <label for="eq_<?= $eq['id'] ?>" class="mb-0" style="cursor:pointer;">
                      <strong><?= htmlspecialchars($eq['name']) ?></strong>
                      <?php if ($eq['description']): ?>
                        <br><small class="text-secondary"><?= htmlspecialchars($eq['description']) ?></small>
                      <?php endif; ?>
                    </label>
                  </div>
                  <span class="eq-price ms-3 text-nowrap">+<?= number_format($eq['price'],0,',','.') ?> €</span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>

        <!-- Βήμα 3: Στοιχεία Πελάτη -->
        <div class="card-section p-4">
          <h5 class="text-white mb-3"><span class="badge bg-danger me-2">3</span>Στοιχεία Πελάτη (Προαιρετικά)</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Όνομα Πελάτη</label>
              <input type="text" class="form-control" name="customer_name" placeholder="π.χ. Γιώργος Παπαδόπουλος">
            </div>
            <div class="col-md-6">
              <label class="form-label">Σημειώσεις</label>
              <input type="text" class="form-control" name="notes" placeholder="π.χ. Delivery Μάρτιος 2025">
            </div>
          </div>
        </div>
      </div>

      <!-- ΔΕΞΙΑ: Σύνοψη Τιμής -->
      <div class="col-lg-4">
        <div class="price-box p-4">
          <h5 class="text-white mb-3"><i class="bi bi-receipt me-2"></i>Σύνοψη Quote</h5>

          <div class="mb-2 d-flex justify-content-between text-secondary">
            <span>Βασική τιμή:</span>
            <span id="s_base">—</span>
          </div>
          <div class="mb-2 d-flex justify-content-between text-secondary">
            <span>Εξοπλισμός (<span id="s_count">0</span> επιλογές):</span>
            <span id="s_eq">+0 €</span>
          </div>
          <hr style="border-color:#c0392b;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-white fw-bold">ΣΥΝΟΛΟ:</span>
            <span class="price-total" id="s_total">—</span>
          </div>

          <!-- Hidden field για αποθήκευση τιμής -->
          <input type="hidden" name="total_price" id="total_price_input" value="0">

          <button type="submit" class="btn btn-danger w-100 btn-lg">
            <i class="bi bi-floppy-fill me-1"></i>Αποθήκευση Quote
          </button>
          <a href="configurations.php" class="btn btn-outline-secondary w-100 mt-2">
            <i class="bi bi-journal-check me-1"></i>Δείτε Αποθηκευμένα
          </a>
        </div>
      </div>

    </div>
  </form>
</div>

<footer class="py-3 mt-5 text-center">
  <small>AutoDealer &copy; <?= date('Y') ?> — Εργαστηριακό Project | UniWA</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// JavaScript — Live υπολογισμός τιμής
// ============================================================
let basePrice    = 0;
let equipTotal   = 0;
const fmt = n => new Intl.NumberFormat('el-GR', {minimumFractionDigits:2}).format(n);

// Ανανέωση τιμής σύνοψης
function updatePrice() {
    const total = basePrice + equipTotal;
    const count = document.querySelectorAll('.eq-check:checked').length;

    document.getElementById('s_base').textContent  = basePrice > 0 ? fmt(basePrice)+' €' : '—';
    document.getElementById('s_eq').textContent    = '+'+fmt(equipTotal)+' €';
    document.getElementById('s_count').textContent = count;
    document.getElementById('s_total').textContent = basePrice > 0 ? fmt(total)+' €' : '—';
    document.getElementById('total_price_input').value = total.toFixed(2);
}

// Αλλαγή αυτοκινήτου
document.getElementById('car_select').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    basePrice  = parseFloat(opt.getAttribute('data-price')) || 0;
    document.getElementById('base_price_display').textContent =
        basePrice > 0 ? fmt(basePrice)+' €' : '—';
    updatePrice();
});

// Toggle εξοπλισμός (κλικ στο div)
function toggleEq(id, price) {
    const cb   = document.getElementById('eq_'+id);
    const item = document.getElementById('item_'+id);
    cb.checked = !cb.checked;
    item.classList.toggle('selected', cb.checked);
    equipTotal += cb.checked ? price : -price;
    if (equipTotal < 0) equipTotal = 0;
    updatePrice();
}

// Ξεχωριστό handling για το ίδιο το checkbox (για να μην κάνει double toggle)
document.querySelectorAll('.eq-check').forEach(cb => {
    cb.addEventListener('change', function(e) {
        const price = parseFloat(
            this.closest('.eq-item').querySelector('.eq-price').textContent.replace(/[^\d,]/g,'').replace(',','.')
        );
        const item  = document.getElementById('item_'+this.value);
        item.classList.toggle('selected', this.checked);
        equipTotal += this.checked ? price : -price;
        if (equipTotal < 0) equipTotal = 0;
        updatePrice();
    });
});

// Αρχικοποίηση αν είναι pre-selected αυτοκίνητο
window.addEventListener('load', function() {
    const sel = document.getElementById('car_select');
    if (sel.value) {
        const opt = sel.options[sel.selectedIndex];
        basePrice = parseFloat(opt.getAttribute('data-price')) || 0;
        document.getElementById('base_price_display').textContent =
            basePrice > 0 ? fmt(basePrice)+' €' : '—';
        updatePrice();
    }
});
</script>
</body>
</html>
