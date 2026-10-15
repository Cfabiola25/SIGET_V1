@extends('v1.layouts.public')

@section('title', 'Política de Privacidad y Protección de Datos')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
        <a href="{{ url('/') }}" class="hover:text-[#1e3a8a] dark:hover:text-sky-400 font-medium">Inicio</a>
        <span>&rsaquo;</span>
        <span class="text-slate-800 dark:text-slate-200 font-bold">Privacidad y Protección de Datos</span>
    </nav>

    <!-- Header Section -->
    <div class="border-b border-slate-200 dark:border-[#2a385b] pb-8 mb-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold uppercase tracking-wider mb-4">
            <span class="size-2 rounded-full bg-emerald-500"></span>
            <span>Cumplimiento Legal y Deportivo &middot; siget.online</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black font-display text-slate-900 dark:text-white tracking-tight leading-tight">
            Política de Privacidad, Protección de Datos y Cookies
        </h1>
        <p class="mt-4 text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-3xl leading-relaxed">
            En <b>SIGET (Sistema Integral de Gestión de Torneos Deportivos)</b>, accesible a través de <b>siget.online</b>, la privacidad, confidencialidad e integridad de la información deportiva, médica y personal de nuestros atletas, clubes, árbitros y directores técnicos es un pilar fundamental.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
            <span>Última actualización: <b>Octubre 2026</b></span>
            <span>&middot;</span>
            <span>Versión: <b>1.0 Oficial</b></span>
            <span>&middot;</span>
            <button type="button" onclick="openSigetCookieSettings()" class="text-[#1e3a8a] dark:text-sky-400 font-bold hover:underline">
                Gestionar Preferencias de Cookies
            </button>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="space-y-10 text-slate-700 dark:text-slate-300 text-sm leading-relaxed">
        
        <!-- 1. Responsable -->
        <section class="bg-white dark:bg-[#141d38] p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-[#2a385b] shadow-xs">
            <h2 class="text-lg sm:text-xl font-bold font-display text-slate-900 dark:text-white mb-3 flex items-center gap-2.5">
                <span class="size-7 rounded-lg bg-[#1e3a8a]/10 dark:bg-sky-500/20 text-[#1e3a8a] dark:text-sky-400 flex items-center justify-center font-black text-xs">01</span>
                <span>Responsable del Tratamiento de Datos</span>
            </h2>
            <p>
                El responsable del tratamiento de los datos personales recabados y tratados a través de la plataforma web y los servicios móviles de SIGET es:
            </p>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-slate-50 dark:bg-[#0b132b] p-4 rounded-2xl border border-slate-200 dark:border-[#2a385b]">
                <div><b>Plataforma:</b> SIGET (Sistema Integral de Gestión de Torneos)</div>
                <div><b>Dominio Oficial:</b> siget.online</div>
                <div><b>Soporte y Privacidad:</b> contacto@siget.online</div>
                <div><b>Cobertura:</b> Ligas y Torneos Deportivos Federados / Aficionados</div>
            </div>
        </section>

        <!-- 2. Categorías de Datos -->
        <section class="bg-white dark:bg-[#141d38] p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-[#2a385b] shadow-xs">
            <h2 class="text-lg sm:text-xl font-bold font-display text-slate-900 dark:text-white mb-3 flex items-center gap-2.5">
                <span class="size-7 rounded-lg bg-[#1e3a8a]/10 dark:bg-sky-500/20 text-[#1e3a8a] dark:text-sky-400 flex items-center justify-center font-black text-xs">02</span>
                <span>Datos Personales Tratados en la Plataforma</span>
            </h2>
            <p class="mb-4">
                En función del rol desempeñado en la competencia (Organizador, Administrador, Director Técnico, Árbitro o Jugador), recopilamos y procesamos los siguientes datos:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b] space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">⚽ Datos de Identificación Deportiva</h3>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                        <li>Nombres y apellidos completos.</li>
                        <li>Documento de identidad oficial (Cédula de Ciudadanía, Tarjeta de Identidad, Pasaporte).</li>
                        <li>Fotografía deportiva oficial para Carnet Digital y Cromo de Jugador.</li>
                        <li>Fecha de nacimiento y categoría deportiva habilitada.</li>
                        <li>Posición de juego habitual y número de dorsal.</li>
                    </ul>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b] space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">🩺 Datos Médicos y de Seguridad Deportiva</h3>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                        <li><b>Grupo sanguíneo y factor RH:</b> Dato de salud crítico accesible en cancha para la mesa de control y cuerpos de paramédicos en caso de traumatismo.</li>
                        <li><b>Entidad Promotora de Salud (EPS):</b> Aseguradora médica vigente.</li>
                        <li><b>Certificado médico de aptitud física:</b> Aval obligatorio de práctica deportiva de alta exigencia.</li>
                        <li><b>Exoneración de responsabilidades (Waiver):</b> Firma digital de consentimiento informado de riesgos deportivos.</li>
                    </ul>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b] space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">🏆 Historial Disciplinario y Estadístico</h3>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                        <li>Registro de tarjetas amarillas, tarjetas rojas y sanciones del Tribunal de Penas.</li>
                        <li>Goles, asistencias, minutos jugados y nominaciones al Jugador Más Valioso (MVP).</li>
                        <li>Cierre y firmas digitales tripartitas de actas post-partido (Árbitro, Capitán Local, Capitán Visitante).</li>
                    </ul>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b] space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">🔐 Tokens Criptográficos Antifraude</h3>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                        <li>Tokens UUID secretos asociados al código QR dinámico de cada carnet.</li>
                        <li>Sello de tiempo del reloj en vivo para evitar suplantaciones mediante capturas de pantalla viejas en los ingresos de cancha.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 3. Finalidades -->
        <section class="bg-white dark:bg-[#141d38] p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-[#2a385b] shadow-xs">
            <h2 class="text-lg sm:text-xl font-bold font-display text-slate-900 dark:text-white mb-3 flex items-center gap-2.5">
                <span class="size-7 rounded-lg bg-[#1e3a8a]/10 dark:bg-sky-500/20 text-[#1e3a8a] dark:text-sky-400 flex items-center justify-center font-black text-xs">03</span>
                <span>Finalidades del Tratamiento</span>
            </h2>
            <p class="mb-3">Los datos recopilados se utilizan estrictamente para los siguientes fines legítimos:</p>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="size-5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">✓</div>
                    <div><b>Ejecución del Filtro Tripartito en Cancha:</b> Validar en menos de 1 segundo que el atleta pertenezca al club, cuente con aval médico firmado y no posea sanciones disciplinarias vigentes.</div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="size-5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">✓</div>
                    <div><b>Cálculo Oficial de Tablas de Posiciones:</b> Aplicar de forma inmutable los reglamentos de desempate IFAB (Puntos, Gol Diferencia, Goles a Favor, Historial Particular).</div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="size-5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">✓</div>
                    <div><b>Generación de Actas Arbitrales Oficiales:</b> Preservar la inmutabilidad de los eventos y firmas digitales una vez cerrado el encuentro.</div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="size-5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">✓</div>
                    <div><b>Transmisión Pública y Scouting:</b> Facilitar a los aficionados el seguimiento de marcadores en vivo y a los reclutadores el análisis de rendimiento técnico.</div>
                </div>
            </div>
        </section>

        <!-- 4. Política de Cookies -->
        <section class="bg-white dark:bg-[#141d38] p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-[#2a385b] shadow-xs">
            <h2 class="text-lg sm:text-xl font-bold font-display text-slate-900 dark:text-white mb-3 flex items-center gap-2.5">
                <span class="size-7 rounded-lg bg-[#1e3a8a]/10 dark:bg-sky-500/20 text-[#1e3a8a] dark:text-sky-400 flex items-center justify-center font-black text-xs">04</span>
                <span>Política de Cookies y Tecnologías de Almacenamiento</span>
            </h2>
            <p class="mb-4">
                SIGET utiliza cookies y almacenamiento local (LocalStorage) estrictamente para fines técnicos, de rendimiento y de seguridad:
            </p>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-[#2a385b]">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-[#0b132b] text-slate-800 dark:text-slate-200 uppercase tracking-wider font-bold border-b border-slate-200 dark:border-[#2a385b]">
                        <tr>
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Tipo</th>
                            <th class="p-3">Duración</th>
                            <th class="p-3">Propósito</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#2a385b]/60">
                        <tr>
                            <td class="p-3 font-mono font-bold text-[#1e3a8a] dark:text-sky-400">siget-session</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Obligatoria</span></td>
                            <td class="p-3">2 horas</td>
                            <td class="p-3">Mantiene la sesión de usuario autenticado (Super Admin, DT, Árbitro).</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono font-bold text-[#1e3a8a] dark:text-sky-400">XSRF-TOKEN</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Obligatoria</span></td>
                            <td class="p-3">2 horas</td>
                            <td class="p-3">Protege contra ataques de falsificación de peticiones en sitios cruzados (Cross-Site Request Forgery).</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono font-bold text-[#1e3a8a] dark:text-sky-400">siget_athletix_theme</td>
                            <td class="p-3">Preferencia</td>
                            <td class="p-3">Persistente</td>
                            <td class="p-3">Guarda la preferencia visual del usuario entre Modo Claro y Modo Oscuro OLED.</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono font-bold text-[#1e3a8a] dark:text-sky-400">siget_cookie_consent_v1</td>
                            <td class="p-3">Configuración</td>
                            <td class="p-3">1 año</td>
                            <td class="p-3">Registra el estado del consentimiento de cookies otorgado por el usuario.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-xs text-slate-500">¿Deseas revocar o actualizar tu consentimiento?</span>
                <button type="button" onclick="openSigetCookieSettings()" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#1e3a8a] hover:bg-[#0284c7] text-white transition">
                    Cambiar Preferencias de Cookies
                </button>
            </div>
        </section>

        <!-- 5. Seguridad y Derechos -->
        <section class="bg-white dark:bg-[#141d38] p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-[#2a385b] shadow-xs">
            <h2 class="text-lg sm:text-xl font-bold font-display text-slate-900 dark:text-white mb-3 flex items-center gap-2.5">
                <span class="size-7 rounded-lg bg-[#1e3a8a]/10 dark:bg-sky-500/20 text-[#1e3a8a] dark:text-sky-400 flex items-center justify-center font-black text-xs">05</span>
                <span>Medidas de Seguridad y Derechos del Titular (ARCO)</span>
            </h2>
            <p class="mb-4">
                Implementamos estándares de seguridad multicapa (cifrado bcrypt para contraseñas, tokens criptográficos firmados para acceso QR y aislamiento de base de datos MySQL con respaldos automáticos).
            </p>
            <p class="mb-3">Como titular de los datos, tienes derecho en cualquier momento a:</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b]">
                    <div class="font-bold text-slate-900 dark:text-white">Acceso</div>
                    <div class="text-[11px] text-slate-500 mt-1">Conocer qué datos tenemos</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b]">
                    <div class="font-bold text-slate-900 dark:text-white">Rectificación</div>
                    <div class="text-[11px] text-slate-500 mt-1">Actualizar o corregir datos</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b]">
                    <div class="font-bold text-slate-900 dark:text-white">Cancelación</div>
                    <div class="text-[11px] text-slate-500 mt-1">Solicitar eliminación</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#0b132b] border border-slate-200 dark:border-[#2a385b]">
                    <div class="font-bold text-slate-900 dark:text-white">Oposición</div>
                    <div class="text-[11px] text-slate-500 mt-1">Oponerse a un tratamiento</div>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                Para ejercer cualquiera de tus derechos o solicitar la rectificación de tu ficha deportiva, puedes comunicarte por escrito al correo <b>contacto@siget.online</b> indicando tu número de identificación y torneo correspondiente.
            </p>
        </section>

    </div>

</div>
@endsection
