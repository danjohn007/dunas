<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    $primaryColor = $systemSettings['theme_primary_color'] ?? '#2563eb';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        :root {
            --color-primary: <?php echo $primaryColor; ?>;
        }
        .bg-primary { background-color: var(--color-primary) !important; }
        .text-primary { color: var(--color-primary) !important; }
        
        .success-animation {
            animation: successPulse 2s ease-in-out;
        }
        
        @keyframes successPulse {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    
    <!-- Header -->
    <header class="bg-primary shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-center">
                <?php if (!empty($systemSettings['site_logo'])): ?>
                    <img src="<?php echo BASE_URL . $systemSettings['site_logo']; ?>" alt="Logo" class="h-10 mr-3">
                <?php else: ?>
                    <i class="fas fa-check-circle text-white text-2xl mr-3"></i>
                <?php endif; ?>
                <h1 class="text-white text-xl font-bold"><?php echo $systemSettings['site_name'] ?? 'Registro de Visitantes'; ?></h1>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center max-w-lg w-full">
            
            <!-- Success Icon -->
            <div class="success-animation mb-6">
                <div class="w-24 h-24 bg-green-100 rounded-full mx-auto flex items-center justify-center">
                    <i class="fas fa-check text-green-500 text-5xl"></i>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-4">¡Registro Exitoso!</h2>
            
            <p class="text-gray-600 mb-6">Su visita ha sido registrada correctamente.</p>
            
            <!-- Información del Visitante -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Datos del Registro</h3>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID de Registro:</span>
                        <span class="font-semibold text-gray-800">#<?php echo $visitor['id']; ?></span>
                    </div>
                    
                    <?php if (!empty($visitor['visitor_name'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nombre:</span>
                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($visitor['visitor_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($visitor['plate_number'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Placa:</span>
                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($visitor['plate_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha/Hora:</span>
                        <span class="font-semibold text-gray-800">
                            <?php echo date('d/m/Y H:i', strtotime($visitor['entry_datetime'])); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Botón para ver pase -->
            <a href="<?php echo BASE_URL; ?>/visitors/pass/<?php echo $visitor['id']; ?>" 
               target="_blank"
               class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all mr-3 mb-3">
                <i class="fas fa-qrcode mr-2"></i>
                Ver Pase de Visita
            </a>
            
            <!-- Botón para nuevo registro -->
            <a href="<?php echo BASE_URL; ?>/visitors/register" 
               class="inline-block bg-primary hover:opacity-90 text-white font-semibold py-3 px-6 rounded-lg transition-all">
                <i class="fas fa-plus-circle mr-2"></i>
                Nuevo Registro
            </a>
            
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="py-4 text-center text-gray-500 text-sm">
        <p>© <?php echo date('Y'); ?> <?php echo $systemSettings['site_name'] ?? APP_NAME; ?></p>
    </footer>
    
</body>
</html>
