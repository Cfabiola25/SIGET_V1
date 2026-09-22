import './bootstrap';
import LiveScoreboard from './components/LiveScoreboard';

const app = document.getElementById('app');

if (app) {
    app.innerHTML = '<div id="live-scoreboard-root"></div>';
    // React mount point for future UI integration
}
