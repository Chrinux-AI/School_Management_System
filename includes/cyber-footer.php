    </div> <!-- .cyber-layout -->

    <!-- PWA Install Prompt -->
    <script src="../assets/js/pwa-manager.js"></script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('../sw.js')
                .then(reg => console.log('Service Worker registered'))
                .catch(err => console.log('Service Worker registration failed'));
        }
    </script>
    </body>

    </html>