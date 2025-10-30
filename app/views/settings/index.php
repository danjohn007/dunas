<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Configuraciones del Sistema</h1>
        <p class="text-gray-600">Administre las configuraciones generales del sistema</p>
    </div>
    
    <form method="POST" action="<?php echo BASE_URL; ?>/settings/update" enctype="multipart/form-data">
        
        <!-- Información General -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información General
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Sitio
                    </label>
                    <input type="text" name="site_name" 
                           value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Sistema de Control de Acceso con IoT'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Logotipo del Sitio
                    </label>
                    <?php if (!empty($settings['site_logo'])): ?>
                    <div class="mb-2">
                        <img src="<?php echo BASE_URL . $settings['site_logo']; ?>" 
                             alt="Logo actual" class="h-16">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="site_logo" accept="image/*"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceptados: JPG, PNG. Tamaño máximo: 5MB</p>
                </div>
            </div>
        </div>
        
        <!-- Personalización del Tema -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-palette text-purple-600 mr-2"></i>Personalización del Tema
            </h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Color Primario (Navegación)
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="theme_primary_color" 
                                   value="<?php echo htmlspecialchars($settings['theme_primary_color'] ?? '#2563eb'); ?>"
                                   class="h-10 w-20 rounded border-gray-300">
                            <input type="text" name="theme_primary_color_hex" 
                                   value="<?php echo htmlspecialchars($settings['theme_primary_color'] ?? '#2563eb'); ?>"
                                   placeholder="#2563eb"
                                   class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   onchange="document.querySelector('input[name=theme_primary_color]').value = this.value">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Color Secundario (Hover)
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="theme_secondary_color" 
                                   value="<?php echo htmlspecialchars($settings['theme_secondary_color'] ?? '#1e40af'); ?>"
                                   class="h-10 w-20 rounded border-gray-300">
                            <input type="text" name="theme_secondary_color_hex" 
                                   value="<?php echo htmlspecialchars($settings['theme_secondary_color'] ?? '#1e40af'); ?>"
                                   placeholder="#1e40af"
                                   class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   onchange="document.querySelector('input[name=theme_secondary_color]').value = this.value">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Color de Acento
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="theme_accent_color" 
                                   value="<?php echo htmlspecialchars($settings['theme_accent_color'] ?? '#3b82f6'); ?>"
                                   class="h-10 w-20 rounded border-gray-300">
                            <input type="text" name="theme_accent_color_hex" 
                                   value="<?php echo htmlspecialchars($settings['theme_accent_color'] ?? '#3b82f6'); ?>"
                                   placeholder="#3b82f6"
                                   class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   onchange="document.querySelector('input[name=theme_accent_color]').value = this.value">
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Los colores se aplicarán a la navegación y elementos principales del sistema. 
                    Se recomienda usar colores con buen contraste para mantener la legibilidad.
                </p>
            </div>
        </div>
        
        <!-- Configuración de Email -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-envelope text-green-600 mr-2"></i>Configuración de Email
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correo Electrónico del Sistema
                    </label>
                    <input type="email" name="system_email" 
                           value="<?php echo htmlspecialchars($settings['system_email'] ?? ''); ?>"
                           placeholder="sistema@dunas.com"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Correo desde el cual se enviarán los mensajes del sistema</p>
                </div>
            </div>
        </div>
        
        <!-- Configuración de WhatsApp -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fab fa-whatsapp text-green-500 mr-2"></i>Configuración de WhatsApp
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Número de WhatsApp del Chatbot
                    </label>
                    <input type="text" name="whatsapp_number" 
                           value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>"
                           placeholder="+52 555 123 4567"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Número de WhatsApp para el chatbot del sistema</p>
                </div>
            </div>
        </div>
        
        <!-- Información de Contacto -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-phone text-purple-600 mr-2"></i>Información de Contacto
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono Principal
                    </label>
                    <input type="text" name="contact_phone" 
                           value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>"
                           placeholder="(555) 123-4567"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono Secundario
                    </label>
                    <input type="text" name="contact_phone_secondary" 
                           value="<?php echo htmlspecialchars($settings['contact_phone_secondary'] ?? ''); ?>"
                           placeholder="(555) 987-6543"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Horario de Apertura
                        </label>
                        <input type="time" name="business_hours_open" 
                               value="<?php echo htmlspecialchars($settings['business_hours_open'] ?? '08:00'); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Horario de Cierre
                        </label>
                        <input type="time" name="business_hours_close" 
                               value="<?php echo htmlspecialchars($settings['business_hours_close'] ?? '18:00'); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configuración de Shelly Relay API -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-network-wired text-orange-600 mr-2"></i>Shelly Relay API
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        URL de la API de Shelly
                    </label>
                    <input type="text" name="shelly_api_url" 
                           value="<?php echo htmlspecialchars($settings['shelly_api_url'] ?? 'http://192.168.1.100'); ?>"
                           placeholder="http://192.168.1.100"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Dirección IP del dispositivo Shelly Relay</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Canal para Abrir Barrera
                        </label>
                        <input type="number" name="shelly_relay_open" 
                               value="<?php echo htmlspecialchars($settings['shelly_relay_open'] ?? '0'); ?>"
                               min="0" max="3"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Canal para Cerrar Barrera
                        </label>
                        <input type="number" name="shelly_relay_close" 
                               value="<?php echo htmlspecialchars($settings['shelly_relay_close'] ?? '1'); ?>"
                               min="0" max="3"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configuración de Tickets -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-ticket-alt text-yellow-600 mr-2"></i>Configuración de Tickets
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mensaje en el Pie de Tickets
                    </label>
                    <textarea name="ticket_footer_message" rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Gracias por su preferencia. Para cualquier duda o aclaración contacte a..."><?php echo htmlspecialchars($settings['ticket_footer_message'] ?? ''); ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Este mensaje aparecerá en la parte inferior de todos los tickets impresos</p>
                </div>
            </div>
        </div>
        
        <!-- Botones -->
        <div class="flex justify-end space-x-4 mb-6">
            <a href="<?php echo BASE_URL; ?>/dashboard" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-save mr-2"></i>Guardar Configuraciones
            </button>
        </div>
    </form>
</div>
