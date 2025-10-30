/**
 * Control del Shelly Pro 4PM desde el navegador del cliente
 * Esta solución funciona porque el navegador del usuario está en la misma red que el Shelly
 */

class ShellyControl {
    constructor() {
        // Configuración del Shelly usando las URLs que ya te funcionan
        this.shellyIP = '192.168.1.95';
        this.credentials = 'admin:67da6c';
        
        // URLs exactas que ya te funcionan
        this.openURL = `http://${this.credentials}@${this.shellyIP}/rpc/Switch.Set?id=0&on=false`;  // Abrir
        this.closeURL = `http://${this.credentials}@${this.shellyIP}/rpc/Switch.Set?id=0&on=true`;   // Cerrar
    }

    async makeRequest(url) {
        try {
            const response = await fetch(url, {
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Authorization': `Basic ${btoa(this.credentials)}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return { success: true, data: data };
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('Error en petición Shelly:', error);
            return { success: false, error: error.message };
        }
    }

    async openBarrier() {
        console.log('🔓 Abriendo barrera...');
        const result = await this.makeRequest(this.openURL);
        
        if (result.success) {
            console.log('✅ Barrera abierta exitosamente');
            this.showNotification('Barrera abierta', 'success');
        } else {
            console.error('❌ Error abriendo barrera:', result.error);
            this.showNotification('Error abriendo barrera: ' + result.error, 'error');
        }
        
        return result;
    }

    async closeBarrier() {
        console.log('🔒 Cerrando barrera...');
        const result = await this.makeRequest(this.closeURL);
        
        if (result.success) {
            console.log('✅ Barrera cerrada exitosamente');
            this.showNotification('Barrera cerrada', 'success');
        } else {
            console.error('❌ Error cerrando barrera:', result.error);
            this.showNotification('Error cerrando barrera: ' + result.error, 'error');
        }
        
        return result;
    }

    showNotification(message, type) {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} shelly-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Inicializar control global
window.shellyControl = new ShellyControl();

// Funciones globales para usar en el sistema
window.openBarrier = () => window.shellyControl.openBarrier();
window.closeBarrier = () => window.shellyControl.closeBarrier();

// Estilos CSS para las notificaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .shelly-notification {
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-weight: 500;
    }
`;
document.head.appendChild(style);

console.log('🔌 Shelly Control inicializado - Usando IP:', window.shellyControl.shellyIP);