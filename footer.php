    <?php wp_footer(); ?>
    <script>
        // Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/sw.js')
                    .then(function(registration) {
                        console.log('VICOBA ServiceWorker registration successful');
                    }, function(err) {
                        console.log('VICOBA ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</body>
</html>
