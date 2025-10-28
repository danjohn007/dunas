<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        <div class="text-center">
            <i class="fas fa-water text-blue-600 text-6xl mb-4"></i>
            <h2 class="text-3xl font-extrabold text-gray-900">
                Sistema de Control de Acceso
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Gestión integral de pipas de agua con IoT
            </p>
        </div>
        
        <div class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm space-y-4">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">
                        <i class="fas fa-check-circle mr-2"></i>Características del Sistema
                    </h3>
                    <ul class="text-sm text-blue-700 space-y-2">
                        <li><i class="fas fa-angle-right mr-2"></i>Control de acceso con IoT</li>
                        <li><i class="fas fa-angle-right mr-2"></i>Gestión de clientes y unidades</li>
                        <li><i class="fas fa-angle-right mr-2"></i>Seguimiento de transacciones</li>
                        <li><i class="fas fa-angle-right mr-2"></i>Reportes detallados</li>
                        <li><i class="fas fa-angle-right mr-2"></i>Integración con Shelly Relay</li>
                    </ul>
                </div>
            </div>
            
            <div>
                <a href="<?php echo BASE_URL; ?>/login" 
                   class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Iniciar Sesión
                </a>
            </div>
            
            <div class="text-center text-sm text-gray-600">
                <p>Versión <?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </div>
</div>
