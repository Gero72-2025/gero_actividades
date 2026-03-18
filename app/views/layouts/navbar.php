<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container">
        <span class="navbar-brand"><?php echo SITENAME; ?></span>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/pages/index') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/pages/index">Inicio</a>
                </li>
                
                <?php if(isLoggedIn()): 
                    // Validar que el usuario tenga acceso a al menos un item del menú GERO
                    $tieneAccesoGERO = tieneAcceso('divisions', 'ver') || 
                                      tieneAcceso('personal', 'ver') || 
                                      tieneAcceso('contratos', 'ver') || 
                                      tieneAcceso('alcances', 'ver') || 
                                      tieneAcceso('actividades', 'ver') ||
                                      tieneAcceso('tablero', 'ver');
                    
                    // Validar que el usuario tenga acceso a al menos un item del menú Configuraciones
                    $tieneAccesoConfig = tieneAcceso('usuarios', 'ver') || 
                                        tieneAcceso('roles', 'ver') || 
                                        tieneAcceso('permisos', 'ver');
                ?>
                    <!-- GERO Menu Dropdown -->
                    <?php if($tieneAccesoGERO): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarGERO" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-folder-check"></i> GERO
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarGERO">
                                <!-- Divisiones -->
                                <?php if(tieneAcceso('divisions', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/divisions') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/divisions/index">
                                        <i class="bi bi-diagram-3"></i> Divisiones
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Personal -->
                                <?php if(tieneAcceso('personal', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/personal') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/personal/index">
                                        <i class="bi bi-people"></i> Personal
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Contratos -->
                                <?php if(tieneAcceso('contratos', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/contratos') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/contratos/index">
                                        <i class="bi bi-file-earmark-text"></i> Contratos
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Alcances -->
                                <?php if(tieneAcceso('alcances', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/alcances') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/alcances/index">
                                        <i class="bi bi-bullseye"></i> Alcances
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Separador -->
                                <?php if((tieneAcceso('actividades', 'ver') || tieneAcceso('tablero', 'ver'))): ?>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>
                                
                                <!-- Actividades -->
                                <?php if(tieneAcceso('actividades', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/actividades') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/actividades/index">
                                        <i class="bi bi-check-square"></i> Actividades
                                    </a>
                                <?php endif; ?>

                                <!-- Tablero de Actividades -->
                                <?php if(tieneAcceso('tablero', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/tablero') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/tablero/index">
                                        <i class="bi bi-kanban"></i> Tablero
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Configuraciones Menu Dropdown -->
                    <?php if($tieneAccesoConfig): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarConfig" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-gear"></i> Configuraciones
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarConfig">
                                <!-- Usuarios -->
                                <?php if(tieneAcceso('usuarios', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/usuarios') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/usuarios/index">
                                        <i class="bi bi-person-badge"></i> Usuarios
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Roles -->
                                <?php if(tieneAcceso('roles', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/roles') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/roles/index">
                                        <i class="bi bi-shield-lock"></i> Roles
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Permisos -->
                                <?php if(tieneAcceso('permisos', 'ver')): ?>
                                    <a class="dropdown-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/permisos') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/permisos/index">
                                        <i class="bi bi-key"></i> Permisos
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ml-auto">
                <?php if(isLoggedIn()): ?>
                    <?php $userRole = getRolUsuarioActual(); ?>
                    <li class="nav-item">
                        <span class="nav-link text-white d-flex flex-column align-items-start" style="line-height: 1.2;">
                            <span><i class="bi bi-person"></i> Bienvenido, <?php echo getUserDisplayName(); ?></span>
                            <?php if($userRole): ?>
                                <small class="text-white-50">Rol: <?php echo htmlspecialchars($userRole->Nombre, ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white ml-2" href="<?php echo URLROOT; ?>/users/logout" style="border-radius: 0.25rem; padding: 0.375rem 0.75rem;">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URLROOT; ?>/users/login">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>