<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Reportes del Sistema</h1>
        <p class="text-gray-600">Acceda a los diferentes tipos de reportes disponibles</p>
    </div>
    
    <!-- Tarjetas de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Reporte de Accesos -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-door-open text-blue-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Reporte de Accesos</h3>
            <p class="text-gray-600 text-center mb-4">Entradas y salidas por período, accesos por cliente/unidad/chofer, horarios pico</p>
            <a href="<?php echo BASE_URL; ?>/reports/access" 
               class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte Financiero -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-dollar-sign text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Reporte Financiero</h3>
            <p class="text-gray-600 text-center mb-4">Litros suministrados, ingresos por método de pago, clientes con mayor consumo</p>
            <a href="<?php echo BASE_URL; ?>/reports/financial" 
               class="block w-full bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte Operativo -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-chart-line text-purple-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Reporte Operativo</h3>
            <p class="text-gray-600 text-center mb-4">Eficiencia de unidades, rendimiento de choferes, consumo por tipo de cliente</p>
            <a href="<?php echo BASE_URL; ?>/reports/operational" 
               class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
    </div>
    
    <!-- Información adicional -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información sobre Reportes
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Formatos de Exportación</h4>
                <ul class="text-gray-600 space-y-1">
                    <li><i class="fas fa-file-excel text-green-600 mr-2"></i>Excel (.xlsx) - Para análisis de datos</li>
                    <li><i class="fas fa-file-pdf text-red-600 mr-2"></i>PDF - Para impresión y archivo</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Filtros Disponibles</h4>
                <ul class="text-gray-600 space-y-1">
                    <li><i class="fas fa-calendar text-blue-600 mr-2"></i>Rango de fechas personalizado</li>
                    <li><i class="fas fa-filter text-blue-600 mr-2"></i>Filtros por estado, tipo, método de pago</li>
                    <li><i class="fas fa-search text-blue-600 mr-2"></i>Búsqueda específica por cliente/unidad/chofer</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Accesos rápidos -->
    <div class="mt-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-bolt mr-2"></i>Reportes Rápidos
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="<?php echo BASE_URL; ?>/reports/access?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Accesos Hoy</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Ingresos del Mes</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/operational?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Rendimiento del Mes</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo date('Y-01-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Ingresos Anuales</p>
            </a>
        </div>
    </div>
</div>
