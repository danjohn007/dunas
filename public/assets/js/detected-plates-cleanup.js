/**
 * detected-plates-cleanup.js
 * 
 * Script para limpieza periódica de registros de placas detectadas.
 * Este script se ejecuta automáticamente en las vistas /access y /access/quickRegistration
 */

(function() {
    // La configuración viene del PHP que incluye este script
    if (typeof window.CLEANUP_CONFIG === 'undefined') {
        console.error('Cleanup - Configuración no encontrada (CLEANUP_CONFIG)');
        return;
    }
    
    const cleanupIntervalMinutes = window.CLEANUP_CONFIG.intervalMinutes || 15;
    const cleanupUrl = window.CLEANUP_CONFIG.url;
    const viewName = window.CLEANUP_CONFIG.viewName || 'Unknown';
    
    if (!cleanupUrl) {
        console.error('Cleanup - URL de limpieza no configurada');
        return;
    }
    
    // Convertir a milisegundos
    const cleanupIntervalMs = cleanupIntervalMinutes * 60 * 1000;
    
    console.log(viewName + ' - Limpieza automática configurada cada', cleanupIntervalMinutes, 'minutos');
    
    // Función para ejecutar la limpieza
    async function executeCleanup() {
        try {
            console.log(viewName + ' - Ejecutando limpieza de registros...');
            
            const response = await fetch(cleanupUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            
            const data = await response.json();
            
            if (data.success) {
                console.log(viewName + ' - Limpieza completada:', data.deleted_count, 'registros eliminados');
            } else {
                console.error(viewName + ' - Error en limpieza:', data.error);
            }
            
        } catch (error) {
            console.error(viewName + ' - Error al ejecutar limpieza:', error);
        }
    }
    
    // Ejecutar la primera limpieza al cargar la página
    executeCleanup();
    
    // Configurar el intervalo periódico
    setInterval(executeCleanup, cleanupIntervalMs);
    
    console.log(viewName + ' - Próxima limpieza en', cleanupIntervalMinutes, 'minutos');
})();
