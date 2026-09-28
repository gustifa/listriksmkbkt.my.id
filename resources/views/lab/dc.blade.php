<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, shrink-to-fit=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>PhET Circuit Construction Kit: DC (Mobile Responsive)</title>

    <!-- Tailwind CSS for UI Controls and Overlay styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS Reset & Fullscreen Fit */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
        }

        html, body {
            width: 100vw;
            height: 100vh;
            height: -webkit-fill-available;
            overflow: hidden;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #000;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        /* Container for PhET Simulation */
        #sim-container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            background: #1e293b;
            overflow: hidden;
        }

        #phet-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* Orientation Alert Overlay for Portrait Mode on Smartphones */
        @media screen and (orientation: portrait) and (max-width: 768px) {
            #orientation-warning {
                display: flex !important;
            }
        }

        /* Smooth UI Buttons */
        .touch-btn {
            touch-action: manipulation;
            transition: all 0.2s ease;
        }

        .touch-btn:active {
            transform: scale(0.92);
        }
    </style>
</head>
<body>

    <!-- Main Simulation Container -->
    <div id="sim-container">
        <!-- PhET Interactive Simulation Embed with Full Touch Accessibility -->
        <iframe
            id="phet-frame"
            src="https://phet.colorado.edu/sims/html/circuit-construction-kit-dc/latest/circuit-construction-kit-dc_all.html"
            allow="fullscreen; autoplay"
            allowfullscreen
            loading="lazy"
            title="PhET Circuit Construction Kit: DC">
        </iframe>

        <!-- Mobile Floating Control Bar -->
        <div class="absolute top-3 right-3 z-30 flex items-center space-x-2 bg-slate-900/80 backdrop-blur-md p-1.5 rounded-full shadow-lg border border-slate-700/50">
            <button id="fullscreen-btn" onclick="toggleFullScreen()" class="touch-btn text-white p-2.5 rounded-full hover:bg-slate-700/60 focus:outline-none flex items-center justify-center text-sm" title="Toggle Fullscreen">
                <i class="fas fa-expand text-lg"></i>
            </button>
            <button id="reload-btn" onclick="reloadSim()" class="touch-btn text-white p-2.5 rounded-full hover:bg-slate-700/60 focus:outline-none flex items-center justify-center text-sm" title="Reset Simulation">
                <i class="fas fa-rotate-right text-lg"></i>
            </button>
        </div>

        <!-- Portrait Orientation Recommendation Overlay -->
        <div id="orientation-warning" class="hidden absolute inset-0 bg-slate-950/90 z-50 flex-col items-center justify-center p-6 text-center backdrop-blur-sm transition-all duration-300">
            <div class="bg-indigo-600/20 p-5 rounded-full mb-4 border border-indigo-500/30 animate-pulse">
                <i class="fas fa-mobile-screen-button text-4xl text-indigo-400 rotate-90"></i>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Rotasi Layar Ke Landscape</h2>
            <p class="text-slate-300 text-sm max-w-xs mb-6 leading-relaxed">
                Untuk pengalaman terbaik merangkai sirkuit listrik di smartphone Android, silakan putar HP Anda ke mode horizontal (Landscape).
            </p>
            <button onclick="dismissOrientationWarning()" class="touch-btn bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-5 py-2.5 rounded-xl shadow-lg border border-indigo-400/30 text-sm">
                Lanjutkan Dalam Mode Portrait
            </button>
        </div>
    </div>

    <script>
        /* Window resizing handler to prevent viewport layout breaks on dynamic toolbars */
        function adjustViewportHeight() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        window.addEventListener('resize', adjustViewportHeight);
        window.addEventListener('orientationchange', adjustViewportHeight);
        adjustViewportHeight();

        /* Dismiss Landscape Recommendation Overlay */
        function dismissOrientationWarning() {
            const warningEl = document.getElementById('orientation-warning');
            if (warningEl) {
                warningEl.style.display = 'none';
            }
        }

        /* Reload PhET Simulation Frame */
        function reloadSim() {
            const iframe = document.getElementById('phet-frame');
            if (iframe) {
                iframe.src = iframe.src;
            }
        }

        /* Toggle Fullscreen API for Android Chrome/Firefox Browsers */
        function toggleFullScreen() {
            const container = document.documentElement;
            const btnIcon = document.querySelector('#fullscreen-btn i');

            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (container.requestFullscreen) {
                    container.requestFullscreen();
                } else if (container.webkitRequestFullscreen) {
                    container.webkitRequestFullscreen();
                }
                if (btnIcon) {
                    btnIcon.className = 'fas fa-compress text-lg';
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                if (btnIcon) {
                    btnIcon.className = 'fas fa-expand text-lg';
                }
            }
        }

        /* Listen for fullscreen changes to update button icon correctly */
        document.addEventListener('fullscreenchange', updateFullscreenIcon);
        document.addEventListener('webkitfullscreenchange', updateFullscreenIcon);

        function updateFullscreenIcon() {
            const btnIcon = document.querySelector('#fullscreen-btn i');
            if (!btnIcon) return;

            if (document.fullscreenElement || document.webkitFullscreenElement) {
                btnIcon.className = 'fas fa-compress text-lg';
            } else {
                btnIcon.className = 'fas fa-expand text-lg';
            }
        }
    </script>
</body>
</html>
