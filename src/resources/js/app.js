// SIGET & Athletix Unified Theme and Energy-Saving Engine
(function initSigetTheme() {
    const THEME_KEY = 'siget_athletix_theme';
    const MANUAL_KEY = 'siget_athletix_manual_override';

    function applyTheme(theme, reason = 'manual') {
        const root = document.documentElement;
        const isDark = (theme === 'dark');

        if (isDark) {
            root.classList.add('dark');
            root.classList.remove('light');
            root.setAttribute('data-theme', 'dark');
        } else {
            root.classList.add('light');
            root.classList.remove('dark');
            root.setAttribute('data-theme', 'light');
        }

        // Sync all switch checkboxes on the page
        const checkboxes = document.querySelectorAll('.theme-switch-checkbox');
        checkboxes.forEach((cb) => {
            cb.checked = isDark;
            const label = cb.closest('.switch');
            if (label) {
                if (reason === 'battery_saver') {
                    label.setAttribute('title', 'Modo Oscuro (Activado automáticamente por Ahorro de Batería)');
                } else if (reason === 'data_saver') {
                    label.setAttribute('title', 'Modo Oscuro (Activado por Ahorro de Datos)');
                } else {
                    label.setAttribute('title', isDark ? 'Modo Oscuro (Clic para cambiar a Modo Claro)' : 'Modo Claro (Clic para cambiar a Modo Oscuro)');
                }
            }
        });
    }

    async function checkBatteryAndEnergySaving() {
        const isManual = localStorage.getItem(MANUAL_KEY) === 'true';
        if (isManual) return; // User manually set their preference, respect it!

        // 1. Data-saver mode
        if (navigator.connection && navigator.connection.saveData) {
            applyTheme('dark', 'data_saver');
            return;
        }

        // 2. Battery Status API
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                const evaluateBattery = () => {
                    if (localStorage.getItem(MANUAL_KEY) === 'true') return;
                    // Low battery & discharging -> switch to dark mode to save energy on screens
                    if (!battery.charging && battery.level <= 0.20) {
                        applyTheme('dark', 'battery_saver');
                    }
                };

                evaluateBattery();
                battery.addEventListener('levelchange', evaluateBattery);
                battery.addEventListener('chargingchange', evaluateBattery);
            } catch (e) {
                // Ignore if blocked
            }
        }
    }

    function setupThemeListeners() {
        const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        applyTheme(currentTheme, 'init');

        document.querySelectorAll('.theme-switch-checkbox').forEach((cb) => {
            cb.addEventListener('change', (e) => {
                const newTheme = e.target.checked ? 'dark' : 'light';
                localStorage.setItem(THEME_KEY, newTheme);
                localStorage.setItem(MANUAL_KEY, 'true');
                applyTheme(newTheme, 'manual');
            });
        });

        checkBatteryAndEnergySaving();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupThemeListeners);
    } else {
        setupThemeListeners();
    }
})();
