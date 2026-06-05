// ============================================================
// THEME: Light / Dark mode toggle
// Εφαρμόζει αποθηκευμένη προτίμηση, ακολουθεί τη ρύθμιση
// του λειτουργικού αν δεν υπάρχει, και επιτρέπει εναλλαγή.
// ============================================================
(function () {
    'use strict';

    var STORAGE_KEY = 'autodealer-theme';

    function getInitialTheme() {
        var stored = null;
        try { stored = localStorage.getItem(STORAGE_KEY); } catch (e) {}
        if (stored === 'light' || stored === 'dark') return stored;
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            return 'light';
        }
        return 'dark';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
        try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) {}

        var icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = theme === 'dark'
                ? 'bi bi-moon-stars-fill'
                : 'bi bi-sun-fill';
        }
        var btn = document.getElementById('themeToggle');
        if (btn) {
            btn.setAttribute('title', theme === 'dark' ? 'Φωτεινό θέμα' : 'Σκοτεινό θέμα');
            btn.setAttribute('aria-label', theme === 'dark'
                ? 'Μετάβαση σε φωτεινό θέμα'
                : 'Μετάβαση σε σκοτεινό θέμα');
        }
    }

    // Πρώτο "paint" — το inline script στο <head> έχει ήδη ορίσει
    // data-theme για να μην υπάρξει flash. Εδώ απλά συγχρονίζουμε
    // το εικονίδιο και δένουμε το κουμπί.
    document.addEventListener('DOMContentLoaded', function () {
        var current = document.documentElement.getAttribute('data-theme') || getInitialTheme();
        applyTheme(current);

        var btn = document.getElementById('themeToggle');
        if (btn) {
            btn.addEventListener('click', function () {
                var cur = document.documentElement.getAttribute('data-theme') || 'dark';
                applyTheme(cur === 'dark' ? 'light' : 'dark');
            });
        }
    });
})();
