<!-- BANNER Y GESTIÓN DE COOKIES (SIGET / siget.online) -->
<div id="sigetCookieBanner" class="fixed bottom-0 inset-x-0 z-50 p-4 sm:p-6 transition-all duration-300 transform translate-y-full opacity-0 pointer-events-none">
    <div class="max-w-5xl mx-auto rounded-2xl bg-white/95 dark:bg-[#0b132b]/95 backdrop-blur-md p-5 sm:p-6 shadow-2xl border border-slate-200 dark:border-[#2a385b] flex flex-col md:flex-row items-start md:items-center justify-between gap-5 text-slate-800 dark:text-slate-100">
        
        <!-- Info Column -->
        <div class="space-y-2 max-w-3xl">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center size-6 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs border border-emerald-500/20">
                    🍪
                </span>
                <span class="text-xs font-black uppercase tracking-wider text-[#1e3a8a] dark:text-sky-400 font-display">
                    Control de Privacidad y Cookies &middot; siget.online
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                Utilizamos cookies técnicas obligatorias para el funcionamiento seguro de la plataforma (autenticación de árbitros, directores técnicos y seguridad anti-CSRF), así como cookies de rendimiento y preferencias deportivas. Puedes aceptar todas, mantener solo las necesarias o configurar tus preferencias. Conoce más en nuestra 
                <a href="{{ route('public.privacy') }}" class="font-bold text-[#1e3a8a] dark:text-sky-400 hover:underline">Política de Privacidad y Protección de Datos</a>.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full md:w-auto shrink-0">
            <button type="button" 
                    onclick="openCookieSettingsModal()" 
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#141d38] text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-[#1c274a] border border-slate-200 dark:border-[#2a385b] transition">
                Configurar
            </button>
            <button type="button" 
                    onclick="acceptEssentialCookies()" 
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#141d38] text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-[#1c274a] border border-slate-200 dark:border-[#2a385b] transition">
                Solo Necesarias
            </button>
            <button type="button" 
                    onclick="acceptAllCookies()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-extrabold bg-[#1e3a8a] hover:bg-[#0284c7] text-white shadow-md shadow-sky-950/20 transition transform active:scale-95">
                Aceptar Todas
            </button>
        </div>

    </div>
</div>

<!-- MODAL DE CONFIGURACIÓN GRANULAR DE COOKIES -->
<dialog id="sigetCookieSettingsModal" class="fixed inset-0 m-auto rounded-3xl p-0 backdrop:bg-slate-950/70 border-0 shadow-2xl max-w-xl w-[min(94vw,36rem)] bg-white dark:bg-[#0b132b] text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-[#2a385b]">
    <div class="p-6 sm:p-8 space-y-6">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-[#2a385b]">
            <div class="flex items-center gap-2.5 font-display font-black text-lg text-slate-900 dark:text-white">
                <span class="text-xl">🍪</span>
                <span>Preferencias de Cookies &middot; SIGET</span>
            </div>
            <button type="button" 
                    onclick="document.getElementById('sigetCookieSettingsModal')?.close()" 
                    class="size-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#141d38] text-xl font-bold transition">
                &times;
            </button>
        </div>

        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
            Administra qué categorías de cookies permites en tu navegador. Puedes modificar estas preferencias en cualquier momento desde el enlace en el pie de página de la plataforma.
        </p>

        <!-- Options list -->
        <div class="space-y-4">
            
            <!-- 1. Cookies Técnicas -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#141d38] border border-slate-200 dark:border-[#2a385b] flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Cookies Técnicas y de Seguridad</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300">Obligatorias</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Esenciales para iniciar sesión, validar tokens criptográficos de carnets QR, protección anti-CSRF y mantener la sesión federativa activa. No se pueden desactivar.
                    </p>
                </div>
                <input type="checkbox" checked disabled class="mt-1 size-4 accent-[#1e3a8a] rounded opacity-60 cursor-not-allowed">
            </div>

            <!-- 2. Cookies de Preferencias -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#141d38] border border-slate-200 dark:border-[#2a385b] flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Preferencias de Interfaz</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Guarda tu modo visual (tema oscuro/claro OLED), torneos predeterminados favoritos y configuraciones de accesibilidad.
                    </p>
                </div>
                <input type="checkbox" id="cookiePrefToggle" checked class="mt-1 size-4 accent-[#1e3a8a] rounded cursor-pointer">
            </div>

            <!-- 3. Cookies de Rendimiento -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#141d38] border border-slate-200 dark:border-[#2a385b] flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Rendimiento y Telemetría</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Permiten medir de forma 100% anónima los tiempos de carga en streaming SSE de partidos en vivo y participación en votaciones MVP.
                    </p>
                </div>
                <input type="checkbox" id="cookieAnalyticsToggle" checked class="mt-1 size-4 accent-[#1e3a8a] rounded cursor-pointer">
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="pt-4 border-t border-slate-200 dark:border-[#2a385b] flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('public.privacy') }}" class="text-xs font-bold text-[#1e3a8a] dark:text-sky-400 hover:underline">
                Consultar Política Completa
            </a>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" 
                        onclick="saveCustomCookieSettings()" 
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-extrabold bg-[#1e3a8a] hover:bg-[#0284c7] text-white shadow-md transition">
                    Guardar Preferencias
                </button>
            </div>
        </div>

    </div>
</dialog>

<!-- SCRIPT DE GESTIÓN Y PERSISTENCIA -->
<script>
    const SIGET_COOKIE_KEY = 'siget_cookie_consent_v1';

    function getCookieConsent() {
        try {
            const raw = localStorage.getItem(SIGET_COOKIE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch(e) {
            return null;
        }
    }

    function setCookieConsent(preferences) {
        try {
            localStorage.setItem(SIGET_COOKIE_KEY, JSON.stringify({
                ...preferences,
                date: new Date().toISOString()
            }));
        } catch(e) {}
        hideCookieBanner();
    }

    function showCookieBanner() {
        const banner = document.getElementById('sigetCookieBanner');
        if (banner) {
            banner.classList.remove('translate-y-full', 'opacity-0', 'pointer-events-none');
            banner.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
        }
    }

    function hideCookieBanner() {
        const banner = document.getElementById('sigetCookieBanner');
        if (banner) {
            banner.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
            banner.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
        }
    }

    function acceptAllCookies() {
        setCookieConsent({ essential: true, preferences: true, analytics: true });
    }

    function acceptEssentialCookies() {
        setCookieConsent({ essential: true, preferences: false, analytics: false });
    }

    function openCookieSettingsModal() {
        const modal = document.getElementById('sigetCookieSettingsModal');
        if (modal) {
            const current = getCookieConsent();
            const prefToggle = document.getElementById('cookiePrefToggle');
            const anaToggle = document.getElementById('cookieAnalyticsToggle');
            if (prefToggle && current) prefToggle.checked = !!current.preferences;
            if (anaToggle && current) anaToggle.checked = !!current.analytics;
            modal.showModal();
        }
    }

    function saveCustomCookieSettings() {
        const prefToggle = document.getElementById('cookiePrefToggle');
        const anaToggle = document.getElementById('cookieAnalyticsToggle');
        setCookieConsent({
            essential: true,
            preferences: prefToggle ? prefToggle.checked : true,
            analytics: anaToggle ? anaToggle.checked : true
        });
        document.getElementById('sigetCookieSettingsModal')?.close();
    }

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const consent = getCookieConsent();
        if (!consent) {
            // Mostrar tras 700ms para entrada suave
            setTimeout(showCookieBanner, 700);
        }
    });

    // Exponer función global para abrir modal desde cualquier enlace del footer
    window.openSigetCookieSettings = openCookieSettingsModal;
</script>
