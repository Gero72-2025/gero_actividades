    </div> 
    </main> 
        <footer class="bg-dark text-white text-center py-3 mt-auto"> 
            <p class="m-0">&copy; <?php echo date('Y'); ?> AGPT</p>
        </footer>
        
    </div>
    <!-- jQuery Local -->
    <script src="<?php echo URLROOT; ?>/public/lib/jquery/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap Bundle JS Local -->
    <script src="<?php echo URLROOT; ?>/public/lib/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        const APP_URL_ROOT = "<?php echo URLROOT; ?>";
        // Nota: Usamos un nombre diferente (APP_URL_ROOT) para evitar conflictos si ya definiste URLROOT en otro lado.
    </script>
    
    <!-- Validación de Sesión: Detectar navegación hacia atrás y validar sesión -->
    <script>
        // Este evento se dispara cuando vuelves a una página en el historial (incluso si está cacheada)
        window.addEventListener('pageshow', function(event) {
            // Si la página viene del caché del navegador (bfcache), valida la sesión
            if (event.persisted) {
                // Verifica si hay sesión activa llamando al servidor
                fetch('<?php echo URLROOT; ?>/users/checksession', {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    // Si no hay sesión activa, redirige al login
                    if (!data.logged_in) {
                        window.location.href = '<?php echo URLROOT; ?>/users/login';
                    }
                })
                .catch(error => {
                    // En caso de error, redirige al login por seguridad
                    console.error('Error validando sesión:', error);
                    window.location.href = '<?php echo URLROOT; ?>/users/login';
                });
            }
        });
    </script>
    <!-- Custom JS -->
    <script src="<?php echo URLROOT; ?>/public/js/main.js"></script>
</body>
</html>