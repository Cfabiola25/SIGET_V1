<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixture de Partidos | {{ $tournament->name }}</title>
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
        .matches-list {
            display: flex;
            flex-direction: column;
        }
        .match-row {
            padding: 12px 16px;
            border-bottom: 1px solid {{ $theme === 'dark' ? '#1e293b' : '#f1f5f9' }};
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: background 0.15s ease;
        }
        .match-row:last-child {
            border-bottom: none;
        }
        .match-row:hover {
            background: {{ $theme === 'dark' ? 'rgba(255,255,255,0.02)' : 'rgba(0,0,0,0.01)' }};
        }
        .match-meta {
            font-size: 11px;
            color: {{ $theme === 'dark' ? '#94a3b8' : '#64748b' }};
            min-width: 90px;
        }
        .match-date {
            font-weight: 700;
            font-family: var(--mono);
        }
        .match-venue {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 110px;
        }
        .match-teams {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .team {
            font-weight: 600;
            font-size: 13px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .team-home {
            text-align: right;
        }
        .team-away {
            text-align: left;
        }
        .match-score {
            font-family: var(--mono);
            font-weight: 800;
            padding: 4px 10px;
            background: {{ $theme === 'dark' ? '#1e293b' : '#f1f5f9' }};
            border-radius: 6px;
            font-size: 13px;
            white-space: nowrap;
            color: {{ $theme === 'dark' ? '#f8fafc' : '#0f172a' }};
            border: 1px solid {{ $theme === 'dark' ? '#334155' : '#e2e8f0' }};
        }
        .match-status {
            min-width: 80px;
            text-align: right;
        }
        .status-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 2px 7px;
            border-radius: 9999px;
            display: inline-block;
        }
        .status-live {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .status-scheduled {
            background: #e0f2fe;
            color: #0369a1;
        }
        .status-completed {
            background: #f1f5f9;
            color: #475569;
        }
        .footer-tag {
            text-align: right;
            padding: 8px 12px;
            font-size: 10px;
            color: {{ $theme === 'dark' ? '#64748b' : '#94a3b8' }};
            background: {{ $theme === 'dark' ? '#1e293b' : '#f8fafc' }};
            border-top: 1px solid {{ $theme === 'dark' ? '#334155' : '#e2e8f0' }};
        }
        .footer-tag a { color: inherit; text-decoration: none; font-weight: 600; }
        .empty-state {
            text-align: center;
            padding: 32px 16px;
            color: #94a3b8;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="widget-card">
        <div class="widget-header">
            <span class="widget-title">{{ $tournament->name }}</span>
            <span class="widget-badge">Fixture Oficial</span>
        </div>

        <div class="matches-list">
            @forelse ($matches as $match)
                <div class="match-row">
                    <div class="match-meta">
                        <div class="match-date">{{ $match->match_date ? $match->match_date->format('d/m H:i') : 'Por definir' }}</div>
                        <div class="match-venue" title="{{ $match->venue?->name ?? 'Sede por asignar' }}">{{ $match->venue?->name ?? 'Sede por asignar' }}</div>
                    </div>

                    <div class="match-teams">
                        <span class="team team-home">{{ $match->homeTeam?->name ?? 'Equipo Local' }}</span>
                        <div class="match-score">
                            @if(in_array($match->status, ['in_progress', 'completed', 'disputed'], true))
                                {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
                            @else
                                VS
                            @endif
                        </div>
                        <span class="team team-away">{{ $match->awayTeam?->name ?? 'Equipo Visitante' }}</span>
                    </div>

                    <div class="match-status">
                        @if($match->status === 'in_progress')
                            <span class="status-badge status-live">En Vivo</span>
                        @elseif($match->status === 'completed')
                            <span class="status-badge status-completed">Final</span>
                        @else
                            <span class="status-badge status-scheduled">Prog.</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    No hay partidos programados actualmente en este torneo.
                </div>
            @endforelse
        </div>

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
