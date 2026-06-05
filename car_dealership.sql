-- ============================================================
-- car_dealership.sql
-- Βάση δεδομένων για Σύστημα Διαχείρισης Εξοπλισμού Αυτοκινήτου
-- Πανεπιστήμιο Δυτικής Αττικής — Εργαστηριακό Project
-- ============================================================

CREATE DATABASE IF NOT EXISTS car_dealership
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE car_dealership;

-- ----------------------------------------------------------
-- Πίνακας: equipment_categories (Κατηγορίες εξοπλισμού)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipment_categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------
-- Πίνακας: cars (Αυτοκίνητα)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS cars (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    brand       VARCHAR(50)    NOT NULL,
    model       VARCHAR(50)    NOT NULL,
    year        INT            NOT NULL,
    color       VARCHAR(30),
    base_price  DECIMAL(10,2)  NOT NULL,
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------
-- Πίνακας: equipment (Εξοπλισμός)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipment (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)   NOT NULL,
    category_id INT,
    price       DECIMAL(10,2)  NOT NULL,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------
-- Πίνακας: car_configurations (Αποθηκευμένες διαμορφώσεις)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS car_configurations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    car_id        INT           NOT NULL,
    customer_name VARCHAR(100),
    total_price   DECIMAL(10,2) NOT NULL,
    notes         TEXT,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------
-- Πίνακας: configuration_equipment (Εξοπλισμός ανά διαμόρφωση)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuration_equipment (
    config_id    INT NOT NULL,
    equipment_id INT NOT NULL,
    PRIMARY KEY (config_id, equipment_id),
    FOREIGN KEY (config_id)    REFERENCES car_configurations(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- ΔΕΙΓΜΑΤΙΚΑ ΔΕΔΟΜΕΝΑ (Sample Data)
-- ============================================================

INSERT INTO equipment_categories (name) VALUES
    ('Ασφάλεια'),
    ('Άνεση'),
    ('Ψυχαγωγία'),
    ('Απόδοση'),
    ('Αισθητική');

INSERT INTO cars (brand, model, year, color, base_price) VALUES
    ('Toyota',     'Corolla',   2023, 'Λευκό',  22000.00),
    ('BMW',        '3 Series',  2023, 'Μαύρο',  45000.00),
    ('Volkswagen', 'Golf',      2022, 'Γκρι',   28000.00),
    ('Mercedes',   'A-Class',   2024, 'Κόκκινο',38000.00),
    ('Hyundai',    'i30',       2023, 'Μπλε',   21000.00);

INSERT INTO equipment (name, category_id, price, description) VALUES
    ('ABS',                         1,  500.00, 'Σύστημα αντιμπλοκαρίσματος τροχών'),
    ('ESP',                         1,  700.00, 'Ηλεκτρονικό πρόγραμμα ευστάθειας'),
    ('Αερόσακοι x6',                1, 1200.00, 'Σύστημα προστασίας 6 αερόσακων'),
    ('Κάμερα όπισθεν',              1,  450.00, 'Κάμερα 180° για παρκάρισμα'),
    ('Δερμάτινα καθίσματα',         2, 1500.00, 'Καθίσματα από γνήσιο δέρμα'),
    ('Ηλεκτρικά καθίσματα',         2,  800.00, 'Καθίσματα με ηλεκτρική ρύθμιση 8 θέσεων'),
    ('Κλιματισμός Dual Zone',       2, 1100.00, 'Διζωνικός αυτόματος κλιματισμός'),
    ('Θερμαινόμενα καθίσματα',      2,  600.00, 'Θέρμανση καθισμάτων οδηγού & συνοδηγού'),
    ('Σύστημα Navigation',          3, 1300.00, 'Ενσωματωμένο GPS με χάρτες Ευρώπης'),
    ('Apple CarPlay / Android Auto',3,  600.00, 'Ασύρματη σύνδεση smartphone'),
    ('Ηχοσύστημα Premium',          3,  900.00, 'Σύστημα ήχου 12 ηχείων υψηλής πιστότητας'),
    ('Sport Suspension',            4, 2000.00, 'Αθλητική ανάρτηση χαμηλωμένη -30mm'),
    ('Turbo Upgrade',               4, 3500.00, 'Αναβάθμιση κινητήρα +50HP'),
    ('Πανοραμική Οροφή',            5, 2200.00, 'Ηλεκτρική συρόμενη πανοραμική οροφή'),
    ('Μεταλλικό Χρώμα',            5,  500.00, 'Ειδικό μεταλλικό βαφή 2 στρώσεων'),
    ('Ζάντες 19"',                  5, 1800.00, 'Αλουμινένιες ζάντες sport 19 ιντσών');
