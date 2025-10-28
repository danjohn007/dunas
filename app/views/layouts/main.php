<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? APP_NAME; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php if (isset($showNav) && $showNav): ?>
    <!-- Navegación -->
    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="flex items-center">
                        <i class="fas fa-water text-white text-2xl mr-2"></i>
                        <span class="text-white font-bold text-xl">DUNAS</span>
                    </a>
                    
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="<?php echo BASE_URL; ?>/dashboard" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        
                        <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
                        <a href="<?php echo BASE_URL; ?>/clients" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-users mr-1"></i> Clientes
                        </a>
                        <a href="<?php echo BASE_URL; ?>/units" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-truck mr-1"></i> Unidades
                        </a>
                        <a href="<?php echo BASE_URL; ?>/drivers" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-id-card mr-1"></i> Choferes
                        </a>
                        <a href="<?php echo BASE_URL; ?>/access" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-door-open mr-1"></i> Accesos
                        </a>
                        <a href="<?php echo BASE_URL; ?>/transactions" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-dollar-sign mr-1"></i> Transacciones
                        </a>
                        <?php endif; ?>
                        
                        <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
                        <a href="<?php echo BASE_URL; ?>/reports" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-bar mr-1"></i> Reportes
                        </a>
                        <?php endif; ?>
                        
                        <?php if (Auth::hasRole(['admin'])): ?>
                        <a href="<?php echo BASE_URL; ?>/users" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user-cog mr-1"></i> Usuarios
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="text-white mr-4">
                        <span class="text-sm"><?php echo Auth::user()['full_name']; ?></span>
                        <span class="text-xs bg-blue-800 px-2 py-1 rounded ml-2"><?php echo strtoupper(Auth::user()['role']); ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/logout" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Alertas Flash -->
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
    
    <!-- Contenido Principal -->
    <main class="py-6">
        <?php echo $content; ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                © <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Versión <?php echo APP_VERSION; ?>. Todos los derechos reservados.
            </p>
        </div>
    </footer>
    
    <script>
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
