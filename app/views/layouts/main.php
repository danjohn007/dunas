<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? APP_NAME; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php
    // Obtener colores del tema (valores por defecto si no están configurados)
    $primaryColor = $systemSettings['theme_primary_color'] ?? '#2563eb';
    $secondaryColor = $systemSettings['theme_secondary_color'] ?? '#1e40af';
    $accentColor = $systemSettings['theme_accent_color'] ?? '#3b82f6';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        :root {
            --color-primary: <?php echo $primaryColor; ?>;
            --color-secondary: <?php echo $secondaryColor; ?>;
            --color-accent: <?php echo $accentColor; ?>;
            --sidebar-width: 260px;
        }
        .bg-primary { background-color: var(--color-primary) !important; }
        .bg-secondary { background-color: var(--color-secondary) !important; }
        .bg-accent { background-color: var(--color-accent) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .text-secondary { color: var(--color-secondary) !important; }
        .text-accent { color: var(--color-accent) !important; }
        .border-primary { border-color: var(--color-primary) !important; }
        .hover\:bg-primary:hover { background-color: var(--color-secondary) !important; }
        
        /* Primary button theme styles */
        .btn-primary {
            background-color: var(--color-primary) !important;
        }
        .btn-primary:hover {
            background-color: var(--color-secondary) !important;
        }
        
        /* Override Tailwind blue buttons with theme colors */
        .bg-blue-600 {
            background-color: var(--color-primary) !important;
        }
        .hover\:bg-blue-700:hover {
            background-color: var(--color-secondary) !important;
        }
        
        /* Plate comparison styles */
        #plate-compare-box.match-ok  { border-color: #16a34a !important; }
        #plate-compare-box.match-bad { border-color: #9ca3af !important; }
        
        /* Sidebar styles */
        .sidebar {
            width: var(--sidebar-width);
            transition: transform 0.3s ease-in-out;
        }
        
        .sidebar-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 0.3s ease-in-out;
        }
        
        /* Mobile: sidebar oculta por defecto */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Desktop: sidebar siempre visible */
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
            .main-content {
                margin-left: var(--sidebar-width);
            }
            .sidebar-overlay {
                display: none !important;
            }
        }
        
        /* Sidebar nav item hover */
        .sidebar-nav-item {
            transition: all 0.2s ease;
        }
        .sidebar-nav-item:hover {
            background-color: var(--color-secondary);
        }
        .sidebar-nav-item.active {
            background-color: var(--color-secondary);
            border-left: 4px solid white;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php if (isset($showNav) && $showNav): ?>
    
    <!-- Mobile Header Bar -->
    <header class="lg:hidden fixed top-0 left-0 right-0 bg-primary shadow-lg z-40 h-16">
        <div class="flex items-center justify-between h-full px-4">
            <button id="sidebarToggle" class="text-white p-2 focus:outline-none" onclick="toggleSidebar()">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="flex items-center">
                <?php if (!empty($systemSettings['site_logo'])): ?>
                    <img src="<?php echo BASE_URL . $systemSettings['site_logo']; ?>" 
                         alt="<?php echo $systemSettings['site_name'] ?? APP_NAME; ?>" 
                         class="h-8 mr-2">
                <?php else: ?>
                    <i class="fas fa-water text-white text-xl mr-2"></i>
                <?php endif; ?>
                <span class="text-white font-bold text-lg"><?php echo $systemSettings['site_name'] ?? 'DUNAS'; ?></span>
            </a>
            <div class="w-10"></div> <!-- Spacer para centrar el logo -->
        </div>
    </header>
    
    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed top-0 left-0 h-full bg-primary shadow-xl z-50 overflow-y-auto">
        <!-- Logo/Header -->
        <div class="p-4 border-b border-white/20">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="flex items-center">
                <?php if (!empty($systemSettings['site_logo'])): ?>
                    <img src="<?php echo BASE_URL . $systemSettings['site_logo']; ?>" 
                         alt="<?php echo $systemSettings['site_name'] ?? APP_NAME; ?>" 
                         class="h-10 mr-3">
                <?php else: ?>
                    <i class="fas fa-water text-white text-2xl mr-3"></i>
                <?php endif; ?>
                <span class="text-white font-bold text-xl"><?php echo $systemSettings['site_name'] ?? 'DUNAS'; ?></span>
            </a>
            <!-- Close button for mobile -->
            <button class="lg:hidden absolute top-4 right-4 text-white/70 hover:text-white" onclick="closeSidebar()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- User Info -->
        <div class="p-4 border-b border-white/20">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-3">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div>
                    <p class="text-white font-medium text-sm truncate" style="max-width: 160px;">
                        <?php echo Auth::user()['full_name']; ?>
                    </p>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded">
                        <?php echo strtoupper(Auth::user()['role']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="py-4">
            <ul class="space-y-1">
                <li>
                    <a href="<?php echo BASE_URL; ?>/dashboard" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-home w-6 mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/clients" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-users w-6 mr-3"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/units" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-truck w-6 mr-3"></i>
                        <span>Unidades</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/drivers" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-id-card w-6 mr-3"></i>
                        <span>Choferes</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/vouchers" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-ticket-alt w-6 mr-3"></i>
                        <span>Vales</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/access" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-door-open w-6 mr-3"></i>
                        <span>Accesos</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/transactions" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-dollar-sign w-6 mr-3"></i>
                        <span>Transacciones</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/visitors" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-user-plus w-6 mr-3"></i>
                        <span>Visitantes</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/reports" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-chart-bar w-6 mr-3"></i>
                        <span>Reportes</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::hasRole(['admin'])): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/users" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-user-cog w-6 mr-3"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="border-t border-white/20 mt-4 pt-4">
                    <a href="<?php echo BASE_URL; ?>/profile" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-user-circle w-6 mr-3"></i>
                        <span>Mi Perfil</span>
                    </a>
                </li>
                
                <?php if (Auth::hasRole(['admin'])): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/devices" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-microchip w-6 mr-3"></i>
                        <span>Dispositivos</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/settings" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-cog w-6 mr-3"></i>
                        <span>Configuraciones</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>/logout" 
                       class="sidebar-nav-item flex items-center text-white px-4 py-3">
                        <i class="fas fa-sign-out-alt w-6 mr-3"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    
    <?php endif; ?>
    
    <!-- Main Content Wrapper -->
    <div class="<?php echo (isset($showNav) && $showNav) ? 'main-content' : ''; ?>">
        
        <?php if (isset($showNav) && $showNav): ?>
        <!-- Spacer for mobile header -->
        <div class="h-16 lg:h-0"></div>
        <?php endif; ?>
    
    <!-- Alertas Flash (only show when showNav is true) -->
    <?php if (isset($showNav) && $showNav): ?>
    <?php
    $successMsg = Session::getFlash('success');
    $errorMsg = Session::getFlash('error');
    $warningMsg = Session::getFlash('warning');
    $infoMsg = Session::getFlash('info');
    ?>
    
    <?php if ($successMsg): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($successMsg); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($errorMsg): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($errorMsg); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($warningMsg): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($warningMsg); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($infoMsg): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($infoMsg); ?></span>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Contenido Principal -->
    <main class="py-6">
        <?php echo $content; ?>
    </main>
    
    <!-- Botón Fijo: Registrar Acceso -->
    <?php if (isset($showNav) && $showNav && Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
    <a href="<?php echo BASE_URL; ?>/access/quickRegistration" 
       class="fixed bottom-6 right-6 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center space-x-2 z-50 group"
       title="Registrar Acceso Rápido">
        <i class="fas fa-door-open text-lg group-hover:scale-110 transition-transform"></i>
        <span class="hidden sm:inline">Registrar Acceso</span>
    </a>
    <?php endif; ?>
    
    <!-- Footer (only show when showNav is true) -->
    <?php if (isset($showNav) && $showNav): ?>
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                © <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Versión <?php echo APP_VERSION; ?>. Todos los derechos reservados.
            </p>
            <p class="text-center text-gray-400 text-xs mt-2">
                Sistema desarrollado por ID Industrial <a href="https://www.idindustrial.com.mx" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700">www.idindustrial.com.mx</a>
            </p>
        </div>
    </footer>
    <?php endif; ?>
    
    </div><!-- End main-content wrapper -->
    
    <script>
        // Sidebar toggle functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                sidebar.classList.add('open');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // Close sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
        
        // Close sidebar when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });
        
        // Objeto para controlar el Shelly
        window.shellyControl = {
            async openBarrier() {
                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/access/openBarrier', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error al abrir barrera:', error);
                    return { success: false, error: error.message };
                }
            },
            
            async closeBarrier() {
                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/access/closeBarrier', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error al cerrar barrera:', error);
                    return { success: false, error: error.message };
                }
            }
        };
        
        // Cerrar alertas automáticamente después de 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
