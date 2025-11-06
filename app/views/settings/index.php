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
    
    <!-- Configuración de Dispositivos Shelly Cloud -->
    <form method="POST" action="<?php echo BASE_URL; ?>/settings/saveShellyDevices" id="shellyDevicesForm">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-cloud text-orange-600 mr-2"></i>Dispositivos Shelly Cloud
                </h2>
                <button type="button" onclick="addShellyDevice()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Nuevo dispositivo +
                </button>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">
                Configure múltiples dispositivos Shelly para control de acceso. Cada dispositivo puede tener canales independientes y acciones configurables.
            </p>
            
            <!-- Contenedor de dispositivos -->
            <div id="shellyDevicesContainer" class="space-y-4">
                <?php if (empty($shellyDevices)): ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-500" data-no-devices>
                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                        <p>No hay dispositivos Shelly configurados. Haga clic en "Nuevo dispositivo +" para agregar uno.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($shellyDevices as $index => $device): ?>
                        <?php 
                            $action = !empty($device['actions']) ? $device['actions'][0] : null;
                            $actionCode = $action ? $action['code'] : 'abrir_cerrar';
                        ?>
                        <div class="shelly-device-card bg-gray-50 border border-gray-300 rounded-lg p-6 relative">
                            <!-- Botón eliminar -->
                            <button type="button" onclick="removeShellyDevice(this)" 
                                    class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <input type="hidden" name="devices[<?php echo $index; ?>][id]" value="<?php echo $device['id']; ?>">
                            <input type="hidden" name="devices[<?php echo $index; ?>][sort_order]" value="<?php echo $index; ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Token de Autenticación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Token de Autenticación
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="devices[<?php echo $index; ?>][auth_token]" 
                                               value="<?php echo htmlspecialchars($device['auth_token']); ?>"
                                               placeholder="Token de autenticación"
                                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm pr-10"
                                               required>
                                        <button type="button" onclick="togglePasswordVisibility(this)" 
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Device ID -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Device ID
                                    </label>
                                    <input type="text" name="devices[<?php echo $index; ?>][device_id]" 
                                           value="<?php echo htmlspecialchars($device['device_id']); ?>"
                                           placeholder="34987A67DA6C"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono"
                                           required>
                                </div>
                                
                                <!-- Servidor Cloud -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Servidor Cloud
                                    </label>
                                    <input type="text" name="devices[<?php echo $index; ?>][server_host]" 
                                           value="<?php echo htmlspecialchars($device['server_host']); ?>"
                                           placeholder="shelly-208-eu.shelly.cloud"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">Sin https:// ni puerto</p>
                                </div>
                                
                                <!-- Acción -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Acción
                                    </label>
                                    <select name="devices[<?php echo $index; ?>][action_code]" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="abrir_cerrar" <?php echo $actionCode === 'abrir_cerrar' ? 'selected' : ''; ?>>Abrir/Cerrar</option>
                                        <option value="vacio" <?php echo $actionCode === 'vacio' ? 'selected' : ''; ?>>Vacío</option>
                                    </select>
                                </div>
                                
                                <!-- Área -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Área
                                    </label>
                                    <input type="text" name="devices[<?php echo $index; ?>][area]" 
                                           value="<?php echo htmlspecialchars($device['area'] ?? ''); ?>"
                                           placeholder="Ej: Entrada principal"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <!-- Canales de Entrada y Salida -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Canal de Entrada (Apertura)
                                    </label>
                                    <select name="devices[<?php echo $index; ?>][entry_channel]" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <?php for ($ch = 0; $ch < 4; $ch++): ?>
                                            <option value="<?php echo $ch; ?>" 
                                                    <?php echo (isset($device['entry_channel']) && $device['entry_channel'] == $ch) ? 'selected' : ''; ?>>
                                                Canal <?php echo $ch; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Pulso de 5 segundos al entrar</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Canal de Salida (Cierre)
                                    </label>
                                    <select name="devices[<?php echo $index; ?>][exit_channel]" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <?php for ($ch = 0; $ch < 4; $ch++): ?>
                                            <option value="<?php echo $ch; ?>" 
                                                    <?php echo (isset($device['exit_channel']) && $device['exit_channel'] == $ch) ? 'selected' : ''; ?>>
                                                Canal <?php echo $ch; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Activación al salir</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Duración Pulso (ms)
                                    </label>
                                    <input type="number" name="devices[<?php echo $index; ?>][pulse_duration_ms]" 
                                           value="<?php echo isset($device['pulse_duration_ms']) ? $device['pulse_duration_ms'] : 5000; ?>"
                                           min="100" max="10000" step="100"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Por defecto: 5000 ms. Máximo: 10 seg</p>
                                </div>
                            </div>
                            
                            <!-- Puerto Activo (legacy - oculto) -->
                            <input type="hidden" name="devices[<?php echo $index; ?>][active_channel]" 
                                   value="<?php echo $device['active_channel'] ?? 0; ?>">
                            
                            <!-- Habilitado -->
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="devices[<?php echo $index; ?>][is_enabled]" 
                                           value="1" <?php echo $device['is_enabled'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="text-sm text-gray-700">Dispositivo habilitado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="devices[<?php echo $index; ?>][invert_sequence]" 
                                           value="1" <?php echo isset($device['invert_sequence']) && $device['invert_sequence'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2">
                                    <span class="text-sm text-gray-700">Invertido (off → on)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="devices[<?php echo $index; ?>][is_simultaneous]" 
                                           value="1" <?php echo isset($device['is_simultaneous']) && $device['is_simultaneous'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-green-600 focus:ring-green-500 mr-2">
                                    <span class="text-sm text-gray-700">Dispositivo simultáneo</span>
                                </label>
                            </div>
                            
                            <input type="hidden" name="devices[<?php echo $index; ?>][channel_count]" value="4">
                            <input type="hidden" name="devices[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($device['name']); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Botones para dispositivos Shelly -->
        <div class="flex justify-end space-x-4 mb-6">
            <a href="<?php echo BASE_URL; ?>/dashboard" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-save mr-2"></i>Guardar Dispositivos Shelly
            </button>
        </div>
    </form>
    
    <!-- Template para nuevos dispositivos (oculto) -->
    <template id="shellyDeviceTemplate">
        <div class="shelly-device-card bg-gray-50 border border-gray-300 rounded-lg p-6 relative">
            <button type="button" onclick="removeShellyDevice(this)" 
                    class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
            
            <input type="hidden" name="devices[INDEX][id]" value="">
            <input type="hidden" name="devices[INDEX][sort_order]" value="INDEX">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Token de Autenticación
                    </label>
                    <div class="relative">
                        <input type="password" name="devices[INDEX][auth_token]" 
                               placeholder="Token de autenticación"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm pr-10"
                               required>
                        <button type="button" onclick="togglePasswordVisibility(this)" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Device ID
                    </label>
                    <input type="text" name="devices[INDEX][device_id]" 
                           placeholder="34987A67DA6C"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Servidor Cloud
                    </label>
                    <input type="text" name="devices[INDEX][server_host]" 
                           placeholder="shelly-208-eu.shelly.cloud"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Sin https:// ni puerto</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Acción
                    </label>
                    <select name="devices[INDEX][action_code]" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="abrir_cerrar" selected>Abrir/Cerrar</option>
                        <option value="vacio">Vacío</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Área
                    </label>
                    <input type="text" name="devices[INDEX][area]" 
                           value=""
                           placeholder="Ej: Entrada principal"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Canal de Entrada (Apertura)
                    </label>
                    <select name="devices[INDEX][entry_channel]" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="0" selected>Canal 0</option>
                        <option value="1">Canal 1</option>
                        <option value="2">Canal 2</option>
                        <option value="3">Canal 3</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Pulso de 5 segundos al entrar</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Canal de Salida (Cierre)
                    </label>
                    <select name="devices[INDEX][exit_channel]" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="0">Canal 0</option>
                        <option value="1" selected>Canal 1</option>
                        <option value="2">Canal 2</option>
                        <option value="3">Canal 3</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Activación al salir</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Duración Pulso (ms)
                    </label>
                    <input type="number" name="devices[INDEX][pulse_duration_ms]" 
                           value="5000"
                           min="100" max="10000" step="100"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Por defecto: 5000 ms. Máximo: 10 seg</p>
                </div>
            </div>
            
            <input type="hidden" name="devices[INDEX][active_channel]" value="0">
            
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" name="devices[INDEX][is_enabled]" value="1" checked
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                    <span class="text-sm text-gray-700">Dispositivo habilitado</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="devices[INDEX][invert_sequence]" value="1" checked
                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2">
                    <span class="text-sm text-gray-700">Invertido (off → on)</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="devices[INDEX][is_simultaneous]" value="1"
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500 mr-2">
                    <span class="text-sm text-gray-700">Dispositivo simultáneo</span>
                </label>
            </div>
            
            <input type="hidden" name="devices[INDEX][channel_count]" value="4">
            <input type="hidden" name="devices[INDEX][name]" value="Abrir/Cerrar">
        </div>
    </template>
    
    <!-- Configuración de Dispositivos HikVision -->
    <form method="POST" action="<?php echo BASE_URL; ?>/settings/saveHikvisionDevices" id="hikvisionDevicesForm">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-camera text-indigo-600 mr-2"></i>Dispositivos HikVision
                </h2>
                <button type="button" onclick="addHikvisionDevice()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Nuevo dispositivo +
                </button>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">
                Configure dispositivos HikVision para lectura de placas (LPR) y lectores de código de barras. Los dispositivos se utilizarán para registro automático y control de acceso.
            </p>
            
            <!-- Contenedor de dispositivos -->
            <div id="hikvisionDevicesContainer" class="space-y-4">
                <?php if (empty($hikvisionDevices)): ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-500" data-no-devices>
                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                        <p>No hay dispositivos HikVision configurados. Haga clic en "Nuevo dispositivo +" para agregar uno.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($hikvisionDevices as $index => $device): ?>
                        <div class="hikvision-device-card bg-gray-50 border border-gray-300 rounded-lg p-6 relative">
                            <!-- Botón eliminar -->
                            <button type="button" onclick="removeHikvisionDevice(this)" 
                                    class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <input type="hidden" name="hikvision_devices[<?php echo $index; ?>][id]" value="<?php echo $device['id']; ?>">
                            <input type="hidden" name="hikvision_devices[<?php echo $index; ?>][sort_order]" value="<?php echo $index; ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Nombre -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre del Dispositivo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="hikvision_devices[<?php echo $index; ?>][name]" 
                                           value="<?php echo htmlspecialchars($device['name']); ?>"
                                           placeholder="Cámara Placas"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           required>
                                </div>
                                
                                <!-- Tipo de Dispositivo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Dispositivo <span class="text-red-500">*</span>
                                    </label>
                                    <select name="hikvision_devices[<?php echo $index; ?>][device_type]" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="camera_lpr" <?php echo $device['device_type'] === 'camera_lpr' ? 'selected' : ''; ?>>
                                            Cámara LPR (Lectura de Placas)
                                        </option>
                                        <option value="barcode_reader" <?php echo $device['device_type'] === 'barcode_reader' ? 'selected' : ''; ?>>
                                            Lector de Códigos
                                        </option>
                                    </select>
                                </div>
                                
                                <!-- Api Key -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Api Key
                                    </label>
                                    <input type="text" name="hikvision_devices[<?php echo $index; ?>][api_key]" 
                                           value="<?php echo htmlspecialchars($device['api_key'] ?? ''); ?>"
                                           placeholder="ErfVjgzq0y"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                                </div>
                                
                                <!-- Api Secret -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Api Secret
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="hikvision_devices[<?php echo $index; ?>][api_secret]" 
                                               value="<?php echo htmlspecialchars($device['api_secret'] ?? ''); ?>"
                                               placeholder="••••••••••"
                                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm pr-10">
                                        <button type="button" onclick="togglePasswordVisibility(this)" 
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Endpoint -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Endpoint (Token)
                                    </label>
                                    <input type="text" name="hikvision_devices[<?php echo $index; ?>][token_endpoint]" 
                                           value="<?php echo htmlspecialchars($device['token_endpoint'] ?? ''); ?>"
                                           placeholder="https://isaapi.hik-partner.com/api/hpcgw/v1/token/get"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                                    <p class="mt-1 text-xs text-gray-500">URL para obtener token de autenticación</p>
                                </div>
                                
                                <!-- Area Domain -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Area Domain
                                    </label>
                                    <input type="text" name="hikvision_devices[<?php echo $index; ?>][area_domain]" 
                                           value="<?php echo htmlspecialchars($device['area_domain'] ?? ''); ?>"
                                           placeholder="https://iusapi.hik-partner.com"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Dominio del área para consultas API</p>
                                </div>
                                
                                <!-- Device Index Code / Serial -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Device Index Code / Serial
                                    </label>
                                    <input type="text" name="hikvision_devices[<?php echo $index; ?>][device_index_code]" 
                                           value="<?php echo htmlspecialchars($device['device_index_code'] ?? ''); ?>"
                                           placeholder="GA8817570"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono">
                                </div>
                                
                                <!-- Área / Ubicación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Área / Ubicación
                                    </label>
                                    <input type="text" name="hikvision_devices[<?php echo $index; ?>][area_label]" 
                                           value="<?php echo htmlspecialchars($device['area_label'] ?? ''); ?>"
                                           placeholder="Entrada Principal"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                
                                <!-- Access Token (read-only) -->
                                <?php if (!empty($device['access_token'])): ?>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Access Token (automático)
                                    </label>
                                    <input type="text" 
                                           value="<?php echo substr($device['access_token'], 0, 50) . '...'; ?>"
                                           class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-600 font-mono text-xs"
                                           readonly>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Expira: <?php echo $device['token_expires_at'] ?? 'N/A'; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Separador para campos ISAPI legacy -->
                            <div class="border-t border-gray-300 my-4 pt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-network-wired text-gray-500 mr-2"></i>Configuración ISAPI Local (Opcional)
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- URL de API ISAPI -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            URL de API (ISAPI)
                                        </label>
                                        <input type="text" name="hikvision_devices[<?php echo $index; ?>][api_url]" 
                                               value="<?php echo htmlspecialchars($device['api_url'] ?? ''); ?>"
                                               placeholder="http://192.168.1.100"
                                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Solo para modo ISAPI local (no usar con Cloud)</p>
                                    </div>
                                    
                                    <!-- Usuario ISAPI -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Usuario (ISAPI)
                                        </label>
                                        <input type="text" name="hikvision_devices[<?php echo $index; ?>][username]" 
                                               value="<?php echo htmlspecialchars($device['username'] ?? ''); ?>"
                                               placeholder="admin"
                                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    
                                    <!-- Contraseña ISAPI -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Contraseña (ISAPI)
                                        </label>
                                        <div class="relative">
                                            <input type="password" name="hikvision_devices[<?php echo $index; ?>][password]" 
                                                   value="<?php echo htmlspecialchars($device['password'] ?? ''); ?>"
                                                   placeholder="••••••••"
                                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 pr-10">
                                            <button type="button" onclick="togglePasswordVisibility(this)" 
                                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Área legacy -->
                                    <input type="hidden" name="hikvision_devices[<?php echo $index; ?>][area]" 
                                           value="<?php echo htmlspecialchars($device['area'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Opciones -->
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="hikvision_devices[<?php echo $index; ?>][is_enabled]" 
                                           value="1" <?php echo $device['is_enabled'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                                    <span class="text-sm text-gray-700">Dispositivo habilitado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="hikvision_devices[<?php echo $index; ?>][verify_ssl]" 
                                           value="1" <?php echo isset($device['verify_ssl']) && $device['verify_ssl'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                                    <span class="text-sm text-gray-700">Verificar certificado SSL</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Botones para dispositivos HikVision -->
        <div class="flex justify-end space-x-4 mb-6">
            <a href="<?php echo BASE_URL; ?>/dashboard" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-save mr-2"></i>Guardar Dispositivos HikVision
            </button>
        </div>
    </form>
    
    <!-- Template para nuevos dispositivos HikVision (oculto) -->
    <template id="hikvisionDeviceTemplate">
        <div class="hikvision-device-card bg-gray-50 border border-gray-300 rounded-lg p-6 relative">
            <button type="button" onclick="removeHikvisionDevice(this)" 
                    class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
            
            <input type="hidden" name="hikvision_devices[HIK_INDEX][id]" value="">
            <input type="hidden" name="hikvision_devices[HIK_INDEX][sort_order]" value="HIK_INDEX">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Dispositivo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="hikvision_devices[HIK_INDEX][name]" 
                           placeholder="Cámara Placas"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Dispositivo <span class="text-red-500">*</span>
                    </label>
                    <select name="hikvision_devices[HIK_INDEX][device_type]" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="camera_lpr" selected>Cámara LPR (Lectura de Placas)</option>
                        <option value="barcode_reader">Lector de Códigos</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Api Key
                    </label>
                    <input type="text" name="hikvision_devices[HIK_INDEX][api_key]" 
                           placeholder="ErfVjgzq0y"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Api Secret
                    </label>
                    <div class="relative">
                        <input type="password" name="hikvision_devices[HIK_INDEX][api_secret]" 
                               placeholder="••••••••••"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm pr-10">
                        <button type="button" onclick="togglePasswordVisibility(this)" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Endpoint (Token)
                    </label>
                    <input type="text" name="hikvision_devices[HIK_INDEX][token_endpoint]" 
                           value="https://isaapi.hik-partner.com/api/hpcgw/v1/token/get"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">URL para obtener token de autenticación</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Area Domain
                    </label>
                    <input type="text" name="hikvision_devices[HIK_INDEX][area_domain]" 
                           value="https://iusapi.hik-partner.com"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">Dominio del área para consultas API</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Device Index Code / Serial
                    </label>
                    <input type="text" name="hikvision_devices[HIK_INDEX][device_index_code]" 
                           placeholder="GA8817570"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Área / Ubicación
                    </label>
                    <input type="text" name="hikvision_devices[HIK_INDEX][area_label]" 
                           placeholder="Entrada Principal"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="border-t border-gray-300 my-4 pt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-network-wired text-gray-500 mr-2"></i>Configuración ISAPI Local (Opcional)
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL de API (ISAPI)
                        </label>
                        <input type="text" name="hikvision_devices[HIK_INDEX][api_url]" 
                               placeholder="http://192.168.1.100"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Solo para modo ISAPI local (no usar con Cloud)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario (ISAPI)
                        </label>
                        <input type="text" name="hikvision_devices[HIK_INDEX][username]" 
                               placeholder="admin"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña (ISAPI)
                        </label>
                        <div class="relative">
                            <input type="password" name="hikvision_devices[HIK_INDEX][password]" 
                                   placeholder="••••••••"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 pr-10">
                            <button type="button" onclick="togglePasswordVisibility(this)" 
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <input type="hidden" name="hikvision_devices[HIK_INDEX][area]" value="">
                </div>
            </div>
            
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" name="hikvision_devices[HIK_INDEX][is_enabled]" 
                           value="1" checked
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                    <span class="text-sm text-gray-700">Dispositivo habilitado</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="hikvision_devices[HIK_INDEX][verify_ssl]" 
                           value="1" checked
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                    <span class="text-sm text-gray-700">Verificar certificado SSL</span>
                </label>
            </div>
        </div>
    </template>
    
    <script>
        let deviceIndex = <?php echo (int)count($shellyDevices); ?>;
        let hikvisionIndex = <?php echo (int)count($hikvisionDevices); ?>;
        
        function addShellyDevice() {
            const container = document.getElementById('shellyDevicesContainer');
            const template = document.getElementById('shellyDeviceTemplate');
            
            // Remover mensaje de "no hay dispositivos" si existe
            const noDevicesMsg = container.querySelector('[data-no-devices]');
            if (noDevicesMsg) {
                noDevicesMsg.remove();
            }
            
            // Clonar template
            const clone = template.content.cloneNode(true);
            const html = clone.querySelector('.shelly-device-card').outerHTML;
            
            // Reemplazar INDEX con el índice actual
            const newHtml = html.replace(/INDEX/g, deviceIndex);
            
            // Insertar al final
            container.insertAdjacentHTML('beforeend', newHtml);
            deviceIndex++;
        }
        
        function removeShellyDevice(btn) {
            if (confirm('¿Está seguro de eliminar este dispositivo?')) {
                const card = btn.closest('.shelly-device-card');
                card.remove();
                
                // Si no quedan dispositivos, mostrar mensaje
                const container = document.getElementById('shellyDevicesContainer');
                if (container.querySelectorAll('.shelly-device-card').length === 0) {
                    container.innerHTML = `
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-500" data-no-devices>
                            <i class="fas fa-info-circle text-2xl mb-2"></i>
                            <p>No hay dispositivos Shelly configurados. Haga clic en "Nuevo dispositivo +" para agregar uno.</p>
                        </div>
                    `;
                }
            }
        }
        
        function addHikvisionDevice() {
            const container = document.getElementById('hikvisionDevicesContainer');
            const template = document.getElementById('hikvisionDeviceTemplate');
            
            // Remover mensaje de "no hay dispositivos" si existe
            const noDevicesMsg = container.querySelector('[data-no-devices]');
            if (noDevicesMsg) {
                noDevicesMsg.remove();
            }
            
            // Clonar template
            const clone = template.content.cloneNode(true);
            const html = clone.querySelector('.hikvision-device-card').outerHTML;
            
            // Reemplazar HIK_INDEX con el índice actual
            const newHtml = html.replace(/HIK_INDEX/g, hikvisionIndex);
            
            // Insertar al final
            container.insertAdjacentHTML('beforeend', newHtml);
            hikvisionIndex++;
        }
        
        function removeHikvisionDevice(btn) {
            if (confirm('¿Está seguro de eliminar este dispositivo?')) {
                const card = btn.closest('.hikvision-device-card');
                card.remove();
                
                // Si no quedan dispositivos, mostrar mensaje
                const container = document.getElementById('hikvisionDevicesContainer');
                if (container.querySelectorAll('.hikvision-device-card').length === 0) {
                    container.innerHTML = `
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-500" data-no-devices>
                            <i class="fas fa-info-circle text-2xl mb-2"></i>
                            <p>No hay dispositivos HikVision configurados. Haga clic en "Nuevo dispositivo +" para agregar uno.</p>
                        </div>
                    `;
                }
            }
        }
        
        function togglePasswordVisibility(btn) {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</div>
