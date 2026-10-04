<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Oficial de Contingencia | {{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
            padding: 8px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 8px;
            text-align: center;
            color: #444;
        }
        .match-badge {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            background: #eee;
        }
        .teams-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .team-box {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }
        .team-header {
            background: #ddd;
            font-weight: bold;
            font-size: 11px;
            padding: 4px;
            text-align: center;
            border: 1px solid #000;
            border-bottom: none;
            text-transform: uppercase;
        }
        .roster-table {
            width: 100%;
            border-collapse: collapse;
        }
        .roster-table th, .roster-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 9px;
        }
        .roster-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .events-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .events-table th, .events-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 9px;
            text-align: center;
        }
        .events-table th {
            background: #e5e5e5;
            font-weight: bold;
        }
        .obs-box {
            border: 1px solid #000;
            height: 60px;
            padding: 4px;
            margin-bottom: 8px;
            font-size: 9px;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signatures-table td {
            width: 33.33%;
            border: 1px solid #000;
            height: 55px;
            vertical-align: bottom;
            text-align: center;
            padding-bottom: 4px;
            font-size: 8px;
        }
        .watermark {
            position: fixed;
            bottom: 5px;
            right: 10px;
            font-size: 8px;
            color: #888;
        }
        .btn-print {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #057a55;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print">🖨️ Imprimir Planilla de Contingencia</button>

    <!-- Header General -->
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: center;">
                <strong>SIGET</strong><br>
                <span class="subtitle">Planilla Federativa</span>
            </td>
            <td style="width: 60%; text-align: center;">
                <div class="title">{{ $match->tournament->name ?? 'TORNEO OFICIAL' }}</div>
                <div class="subtitle">PLANILLA OFICIAL DE CONTINGENCIA EN CANCHA &bull; SISTEMA DE RESPALDO MANUAL</div>
            </td>
            <td style="width: 20%; text-align: center;">
                <strong>MATCH #{{ $match->id }}</strong><br>
                <span class="subtitle">Fecha {{ $match->round_number }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <strong>Fecha/Hora:</strong> {{ $match->match_date?->format('d/m/Y H:i') ?? 'A definir' }} &bull;
                <strong>Sede:</strong> {{ $match->venue?->name ?? 'N/A' }} (Cancha {{ $match->field_number ?? 1 }}) &bull;
                <strong>Árbitro Principal:</strong> {{ $match->referee?->name ?? 'Designación en campo' }}
            </td>
        </tr>
    </table>

    <!-- Marcador y Nóminas de Equipos -->
    <table class="teams-grid">
        <tr>
            <!-- Equipo Local -->
            <td class="team-box">
                <div class="team-header">LOCAL: {{ $match->homeTeam->name }}</div>
                <table class="roster-table">
                    <thead>
                        <tr>
                            <th style="width: 25px;">#</th>
                            <th>Jugador / Apellido y Nombre</th>
                            <th style="width: 50px;">DNI/Cédula</th>
                            <th style="width: 25px;">Méd.</th>
                            <th style="width: 45px;">Firma Pre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($match->homeTeam->players as $p)
                            <tr>
                                <td style="text-align: center; font-weight: bold;">{{ $p->jersey_number ?? '-' }}</td>
                                <td>{{ $p->name }}</td>
                                <td style="text-align: center; font-size: 8px;">{{ $p->profile?->document_number ?? 'OK' }}</td>
                                <td style="text-align: center;">✓</td>
                                <td></td>
                            </tr>
                        @empty
                            @for ($i = 1; $i <= 11; $i++)
                                <tr>
                                    <td></td><td></td><td></td><td></td><td></td>
                                </tr>
                            @endfor
                        @endforelse
                    </tbody>
                </table>
            </td>

            <!-- Equipo Visitante -->
            <td class="team-box">
                <div class="team-header">VISITANTE: {{ $match->awayTeam->name }}</div>
                <table class="roster-table">
                    <thead>
                        <tr>
                            <th style="width: 25px;">#</th>
                            <th>Jugador / Apellido y Nombre</th>
                            <th style="width: 50px;">DNI/Cédula</th>
                            <th style="width: 25px;">Méd.</th>
                            <th style="width: 45px;">Firma Pre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($match->awayTeam->players as $p)
                            <tr>
                                <td style="text-align: center; font-weight: bold;">{{ $p->jersey_number ?? '-' }}</td>
                                <td>{{ $p->name }}</td>
                                <td style="text-align: center; font-size: 8px;">{{ $p->profile?->document_number ?? 'OK' }}</td>
                                <td style="text-align: center;">✓</td>
                                <td></td>
                            </tr>
                        @empty
                            @for ($i = 1; $i <= 11; $i++)
                                <tr>
                                    <td></td><td></td><td></td><td></td><td></td>
                                </tr>
                            @endfor
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Grilla de Registro de Incidencias en Vivo -->
    <div style="font-weight: bold; font-size: 10px; margin-bottom: 3px; text-transform: uppercase;">
        Control de Incidencias en Cancha (Goles y Tarjetas IFAB)
    </div>
    <table class="events-table">
        <thead>
            <tr>
                <th style="width: 50px;">Minuto</th>
                <th style="width: 120px;">Equipo</th>
                <th style="width: 40px;">Dorsal</th>
                <th>Nombre del Jugador</th>
                <th style="width: 90px;">Incidencia</th>
                <th>Observaciones / Causa IFAB</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= 8; $i++)
                <tr>
                    <td style="height: 16px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-size: 8px; color: #555;">[ ] GOL &nbsp; [ ] 🟨 &nbsp; [ ] 🟥</td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Marcador Final & Observaciones -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
        <tr>
            <td style="width: 40%; border: 1px solid #000; padding: 6px; text-align: center;">
                <span style="font-size: 10px; font-weight: bold;">RESULTADO FINAL EN CANCHA</span><br>
                <span style="font-size: 14px; font-weight: bold;">
                    {{ $match->homeTeam->name }} [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ] - [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ] {{ $match->awayTeam->name }}
                </span>
            </td>
            <td style="width: 60%; border: 1px solid #000; padding: 4px; vertical-align: top;">
                <strong>Observaciones Disciplinarias / Incidentes:</strong>
                <div style="height: 30px;"></div>
            </td>
        </tr>
    </table>

    <!-- Firmas Oficiales de Cierre -->
    <table class="signatures-table">
        <tr>
            <td>
                ___________________________________<br>
                <strong>Firma y Aclaración Capitán / DT Local</strong><br>
                {{ $match->homeTeam->coach?->name ?? 'DT Local' }}
            </td>
            <td>
                ___________________________________<br>
                <strong>Firma Árbitro Principal</strong><br>
                {{ $match->referee?->name ?? 'Árbitro Oficial' }}
            </td>
            <td>
                ___________________________________<br>
                <strong>Firma y Aclaración Capitán / DT Visitante</strong><br>
                {{ $match->awayTeam->coach?->name ?? 'DT Visitante' }}
            </td>
        </tr>
    </table>

    <div class="watermark">
        SIGET Planilla de Contingencia &bull; Generado el {{ now()->format('d/m/Y H:i:s') }} &bull; Código de Validación: SHA-{{ substr(hash('sha256', (string) $match->id), 0, 10) }}
    </div>

</body>
</html>
