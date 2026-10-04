<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Posiciones | {{ $tournament->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --font: 'Plus Jakarta Sans', -apple-system, sans-serif;
            --mono: 'JetBrains Mono', monospace;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            font-size: 13px;
            background: {{ $theme === 'dark' ? '#0f172a' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#f1f5f9' : '#1e293b' }};
            padding: 12px;
        }
        .widget-card {
            border: 1px solid {{ $theme === 'dark' ? '#334155' : '#e2e8f0' }};
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .widget-header {
            padding: 12px 16px;
            background: {{ $theme === 'dark' ? '#1e293b' : '#f8fafc' }};
            border-bottom: 1px solid {{ $theme === 'dark' ? '#334155' : '#e2e8f0' }};
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .widget-title {
            font-size: 14px;
            font-weight: 700;
            color: {{ $theme === 'dark' ? '#ffffff' : '#0f172a' }};
        }
        .widget-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(5, 122, 85, 0.15);
            color: var(--primary);
            padding: 3px 8px;
            border-radius: 9999px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th {
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: {{ $theme === 'dark' ? '#94a3b8' : '#64748b' }};
            border-bottom: 1px solid {{ $theme === 'dark' ? '#334155' : '#e2e8f0' }};
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid {{ $theme === 'dark' ? '#1e293b' : '#f1f5f9' }};
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td {
            background: {{ $theme === 'dark' ? 'rgba(255,255,255,0.02)' : 'rgba(0,0,0,0.01)' }};
        }
        .col-pos { width: 36px; text-align: center; font-weight: 800; font-family: var(--mono); }
        .col-pts { text-align: center; font-weight: 800; font-family: var(--mono); color: var(--primary); }
        .col-num { text-align: center; font-family: var(--mono); font-size: 12px; }
        .team-name { font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .top-1 { color: #f59e0b; }
        .top-3 { color: var(--primary); }
        .footer-tag {
            text-align: right;
            padding: 8px 12px;
            font-size: 10px;
            color: {{ $theme === 'dark' ? '#64748b' : '#94a3b8' }};
            background: {{ $theme === 'dark' ? '#1e293b' : '#f8fafc' }};
            border-top: 1px solid {{ $theme === 'dark' ? '#334155' : '#e2e8f0' }};
        }
        .footer-tag a { color: inherit; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="widget-card">
        @if ($showTitle)
            <div class="widget-header">
                <span class="widget-title">{{ $tournament->name }}</span>
                <span class="widget-badge">Posiciones Oficiales</span>
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th class="col-pos">#</th>
                    <th>Club</th>
                    <th class="col-num">PJ</th>
                    <th class="col-num">G</th>
                    <th class="col-num">E</th>
                    <th class="col-num">P</th>
                    <th class="col-num">DIF</th>
                    <th class="col-pts">PTS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($standings as $index => $standing)
                    <tr>
                        <td class="col-pos {{ $index === 0 ? 'top-1' : ($index < 4 ? 'top-3' : '') }}">
                            {{ $index + 1 }}
                        </td>
                        <td class="team-name">
                            {{ $standing->team?->name ?? 'Equipo' }}
                        </td>
                        <td class="col-num">{{ $standing->played }}</td>
                        <td class="col-num">{{ $standing->won }}</td>
                        <td class="col-num">{{ $standing->drawn }}</td>
                        <td class="col-num">{{ $standing->lost }}</td>
                        <td class="col-num" style="color: {{ $standing->goal_difference > 0 ? '#10b981' : ($standing->goal_difference < 0 ? '#ef4444' : 'inherit') }}">
                            {{ $standing->goal_difference > 0 ? '+' : '' }}{{ $standing->goal_difference }}
                        </td>
                        <td class="col-pts">{{ $standing->points }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 24px; color: #94a3b8;">
                            Sin partidos jugados aún en este torneo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-tag">
            Powered by <a href="{{ url('/') }}" target="_blank">SIGET Torneos</a>
        </div>
    </div>

    <script>
        // Auto-resizing del iframe en la web del club vía postMessage
        window.addEventListener('load', () => {
            const height = document.body.scrollHeight;
            window.parent.postMessage({ type: 'siget-widget-resize', height: height }, '*');
        });
    </script>
</body>
</html>
