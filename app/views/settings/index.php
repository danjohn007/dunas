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
        
        <!-- Configuración de Códigos QR -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start mb-4">
                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 mr-4">
                    <i class="fas fa-qrcode text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Configuración de Códigos QR</h2>
                    <p class="text-gray-600 text-sm">Para generación de códigos QR en eventos</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        API para Generación de QR
                    </label>
                    <select name="qr_api_provider" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="qrserver" <?php echo ($settings['qr_api_provider'] ?? 'qrserver') === 'qrserver' ? 'selected' : ''; ?>>QR Server API</option>
                        <option value="goqr" <?php echo ($settings['qr_api_provider'] ?? '') === 'goqr' ? 'selected' : ''; ?>>GoQR.me API</option>
                        <option value="local" <?php echo ($settings['qr_api_provider'] ?? '') === 'local' ? 'selected' : ''; ?>>Generación Local (JavaScript)</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Seleccione el proveedor de API para generar códigos QR</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tamaño de QR (píxeles)
                    </label>
                    <input type="number" name="qr_size" min="100" max="1000" step="50"
                           value="<?php echo htmlspecialchars($settings['qr_size'] ?? '350'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="350">
                    <p class="mt-1 text-xs text-gray-500">Tamaño del código QR para impresión (recomendado: 400px)</p>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Nota:</strong> La configuración de API de QR permite cambiar el proveedor de generación de códigos QR. Un tamaño mayor mejora la calidad de la impresión.
                </p>
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
        
        <!-- Parque Acuático - Precios de Boletos -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-swimming-pool text-blue-600 mr-2"></i>Parque Acuático — Costo de Boletos
            </h2>

            <!-- Tiempo de restablecimiento automático en Validación QR -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tiempo de restablecimiento automático en Validación QR (segundos)
                </label>
                <div class="flex items-center gap-3" style="max-width: 220px;">
                    <input type="number" name="aquapark_validate_reset_seconds" step="1" min="1" max="60"
                           value="<?php echo (int)($settings['aquapark_validate_reset_seconds'] ?? 3); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <span class="text-sm text-gray-500 whitespace-nowrap">seg</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">Segundos que espera la pantalla de validación antes de regresar automáticamente al modo Manual tras mostrar el resultado. Valor predeterminado: 3.</p>
            </div>

            <!-- Tipo de impresión de códigos QR -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Tipo de impresión de los códigos QR
                </label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <label class="flex items-start gap-3 cursor-pointer p-3 border rounded-lg hover:bg-blue-50 transition <?php echo (($settings['aquapark_qr_print_type'] ?? 'pulsera') === 'pulsera') ? 'border-blue-500 bg-blue-50' : 'border-gray-300'; ?>">
                        <input type="radio" name="aquapark_qr_print_type" value="pulsera"
                               <?php echo (($settings['aquapark_qr_print_type'] ?? 'pulsera') === 'pulsera') ? 'checked' : ''; ?>
                               class="mt-0.5 text-blue-600">
                        <span>
                            <span class="block font-medium text-gray-800">Etiqueta de pulsera</span>
                            <span class="block text-xs text-gray-500">11 pulseras por hoja carta (formato actual)</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer p-3 border rounded-lg hover:bg-blue-50 transition <?php echo (($settings['aquapark_qr_print_type'] ?? 'pulsera') === 'adhesiva') ? 'border-blue-500 bg-blue-50' : 'border-gray-300'; ?>">
                        <input type="radio" name="aquapark_qr_print_type" value="adhesiva"
                               <?php echo (($settings['aquapark_qr_print_type'] ?? 'pulsera') === 'adhesiva') ? 'checked' : ''; ?>
                               class="mt-0.5 text-blue-600">
                        <span>
                            <span class="block font-medium text-gray-800">Etiquetas adhesivas A11</span>
                            <span class="block text-xs text-gray-500">Modelo Tuk-Stik A11 (38 × 13 mm) — <?php echo (int)($settings['aquapark_qr_cols_a11'] ?? 5); ?> columnas × <?php echo (int)($settings['aquapark_qr_rows_a11'] ?? 9); ?> renglones por hoja</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer p-3 border rounded-lg hover:bg-blue-50 transition <?php echo (($settings['aquapark_qr_print_type'] ?? 'pulsera') === 'adhesiva_a14') ? 'border-blue-500 bg-blue-50' : 'border-gray-300'; ?>">
                        <input type="radio" name="aquapark_qr_print_type" value="adhesiva_a14"
                               <?php echo (($settings['aquapark_qr_print_type'] ?? 'pulsera') === 'adhesiva_a14') ? 'checked' : ''; ?>
                               class="mt-0.5 text-blue-600">
                        <span>
                            <span class="block font-medium text-gray-800">Etiquetas adhesivas A14</span>
                            <span class="block text-xs text-gray-500">Modelo Tuk-Stik A14 (19 × 50 mm) — <?php echo (int)($settings['aquapark_qr_cols_a14'] ?? 4); ?> columnas × <?php echo (int)($settings['aquapark_qr_rows_a14'] ?? 6); ?> renglones por hoja</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Número de columnas por tipo de etiqueta adhesiva -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Columnas por hoja — Tuk-Stik A11
                    </label>
                    <div class="flex items-center gap-3" style="max-width: 220px;">
                        <input type="number" name="aquapark_qr_cols_a11" step="1" min="1" max="20"
                               value="<?php echo (int)($settings['aquapark_qr_cols_a11'] ?? 5); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="text-sm text-gray-500 whitespace-nowrap">cols</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Número de columnas por hoja al imprimir etiquetas adhesivas A11. Valor predeterminado: 5.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Columnas por hoja — Tuk-Stik A14
                    </label>
                    <div class="flex items-center gap-3" style="max-width: 220px;">
                        <input type="number" name="aquapark_qr_cols_a14" step="1" min="1" max="20"
                               value="<?php echo (int)($settings['aquapark_qr_cols_a14'] ?? 4); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="text-sm text-gray-500 whitespace-nowrap">cols</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Número de columnas por hoja al imprimir etiquetas adhesivas A14. Valor predeterminado: 4.</p>
                </div>
            </div>

            <!-- Número de renglones por tipo de etiqueta adhesiva -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Renglones por hoja — Tuk-Stik A11
                    </label>
                    <div class="flex items-center gap-3" style="max-width: 220px;">
                        <input type="number" name="aquapark_qr_rows_a11" step="1" min="1" max="30"
                               value="<?php echo (int)($settings['aquapark_qr_rows_a11'] ?? 9); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="text-sm text-gray-500 whitespace-nowrap">rows</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Número de renglones por hoja al imprimir etiquetas adhesivas A11. Valor predeterminado: 9.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Renglones por hoja — Tuk-Stik A14
                    </label>
                    <div class="flex items-center gap-3" style="max-width: 220px;">
                        <input type="number" name="aquapark_qr_rows_a14" step="1" min="1" max="30"
                               value="<?php echo (int)($settings['aquapark_qr_rows_a14'] ?? 6); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="text-sm text-gray-500 whitespace-nowrap">rows</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Número de renglones por hoja al imprimir etiquetas adhesivas A14. Valor predeterminado: 6.</p>
                </div>
            </div>

            <!-- Distancia entre renglones (solo etiquetas adhesivas) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Distancia entre renglones de impresión (mm)
                </label>
                <div class="flex items-center gap-3" style="max-width: 220px;">
                    <input type="number" name="aquapark_qr_row_gap" step="any" min="0" max="50"
                           value="<?php echo htmlspecialchars($settings['aquapark_qr_row_gap'] ?? '1'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <span class="text-sm text-gray-500 whitespace-nowrap">mm</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">Espacio en milímetros entre cada renglón al imprimir etiquetas adhesivas A11 y A14. Valor predeterminado: 1 mm.</p>
            </div>

            <!-- Distancia entre columnas (solo etiquetas adhesivas) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Distancia entre columnas de impresión (mm)
                </label>
                <div class="flex items-center gap-3" style="max-width: 220px;">
                    <input type="number" name="aquapark_qr_col_gap" step="any" min="0" max="50"
                           value="<?php echo htmlspecialchars($settings['aquapark_qr_col_gap'] ?? '0'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <span class="text-sm text-gray-500 whitespace-nowrap">mm</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">Espacio en milímetros entre cada columna al imprimir etiquetas adhesivas A11 y A14. Valor predeterminado: 0 mm.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por pulsera Normal (código de serie)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" name="aquapark_ticket_price_series" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($settings['aquapark_ticket_price_series'] ?? '0.00'); ?>"
                               class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Precio unitario — pulseras QR tipo Normal generadas por serie</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por pulsera Niño
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" name="aquapark_ticket_price_nino" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($settings['aquapark_ticket_price_nino'] ?? '0.00'); ?>"
                               class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Precio unitario — pulseras QR tipo Niño</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por pulsera Adulto Mayor
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" name="aquapark_ticket_price_adulto_mayor" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($settings['aquapark_ticket_price_adulto_mayor'] ?? '0.00'); ?>"
                               class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Precio unitario — pulseras QR tipo Adulto Mayor</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por pulsera Capacidades Diferentes
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" name="aquapark_ticket_price_capacidades" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($settings['aquapark_ticket_price_capacidades'] ?? '0.00'); ?>"
                               class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Precio unitario — pulseras QR tipo Capacidades Diferentes</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por boleto (registro manual)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" name="aquapark_ticket_price_manual" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($settings['aquapark_ticket_price_manual'] ?? '0.00'); ?>"
                               class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Precio unitario para calcular el monto al registrar visitantes manualmente</p>
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
    
    <!-- Configuración de Costos por Capacidad -->
    <form method="POST" action="<?php echo BASE_URL; ?>/settings/saveCapacityCosts" id="capacityCostsForm">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-start">
                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 mr-4">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Costos por Capacidad de Pipa</h2>
                        <p class="text-gray-600 text-sm">Configure los precios para cada capacidad disponible en el sistema</p>
                    </div>
                </div>
                <button type="button" onclick="addCapacityCost()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Agregar Capacidad
                </button>
            </div>
            
            <div id="capacityCostsContainer" class="space-y-4">
                <?php 
                // Get capacity costs from database
                require_once APP_PATH . '/models/CapacityCost.php';
                $capacityCostModel = new CapacityCost();
                $capacityCosts = $capacityCostModel->getAll(false);
                
                if (empty($capacityCosts)): 
                ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-4xl mb-4"></i>
                        <p>No hay costos configurados. Haga clic en "Agregar Capacidad" para comenzar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($capacityCosts as $index => $cost): ?>
                        <div class="capacity-cost-item border border-gray-200 rounded-lg p-4 bg-gray-50" id="capacity-cost-<?php echo $index; ?>">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                                <input type="hidden" name="capacity_costs[<?php echo $index; ?>][id]" value="<?php echo $cost['id']; ?>">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad (Litros)</label>
                                    <input type="number" name="capacity_costs[<?php echo $index; ?>][capacity_liters]" 
                                           value="<?php echo $cost['capacity_liters']; ?>" required min="1"
                                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                           placeholder="Ej: 10000">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo ($)</label>
                                    <input type="number" name="capacity_costs[<?php echo $index; ?>][cost]" 
                                           value="<?php echo $cost['cost']; ?>" required min="0" step="0.01"
                                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                           placeholder="Ej: 50000.00">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                    <input type="text" name="capacity_costs[<?php echo $index; ?>][description]" 
                                           value="<?php echo htmlspecialchars($cost['description'] ?? ''); ?>"
                                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                           placeholder="Ej: Pipa estándar">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select name="capacity_costs[<?php echo $index; ?>][is_active]"
                                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                                        <option value="1" <?php echo $cost['is_active'] ? 'selected' : ''; ?>>Activo</option>
                                        <option value="0" <?php echo !$cost['is_active'] ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <button type="button" onclick="removeCapacityCost(this)"
                                            class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg w-full">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Importante:</strong> Los costos configurados aquí se utilizarán automáticamente en los registros de entrada y se reflejarán en el Reporte Financiero.
                </p>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Costos por Capacidad
                </button>
            </div>
        </div>
    </form>
    
    <!-- Configuración FTP -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-folder text-cyan-600 mr-2"></i>Configuración FTP
        </h2>
        
        <p class="text-sm text-gray-600 mb-4">
            Configure las rutas de las carpetas para el movimiento automático de imágenes de placas detectadas.
        </p>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/settings/update" id="ftpConfigForm">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Carpeta Origen (FTP)
                    </label>
                    <input type="text" name="ftp_source_dir" 
                           value="<?php echo htmlspecialchars($settings['ftp_source_dir'] ?? '/home2/residencial/placas/'); ?>"
                           placeholder="/home2/residencial/placas/"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">
                        Ruta completa de la carpeta donde la cámara HikVision guarda las imágenes de placas detectadas.
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Carpeta Destino (Público)
                    </label>
                    <input type="text" name="ftp_destination_dir" 
                           value="<?php echo htmlspecialchars($settings['ftp_destination_dir'] ?? '/home2/residencial/public_html/dunas/imagenes/'); ?>"
                           placeholder="/home2/residencial/public_html/dunas/imagenes/"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">
                        Ruta completa de la carpeta pública donde se moverán las imágenes para su procesamiento.
                    </p>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Nota:</strong> Estas rutas son utilizadas por el script <code class="bg-blue-100 px-1 rounded">move_ftp_images.php</code> 
                    que se ejecuta automáticamente cada 10 segundos en las vistas de Control de Acceso y Registro Rápido. 
                    Asegúrese de que ambas carpetas existan y tengan los permisos correctos.
                </p>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Configuración FTP
                </button>
            </div>
        </form>
    </div>
    
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
                                    
                                    <!-- Área legacy - mantener para compatibilidad con código anterior -->
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
    
    <!-- Configuración del Lector HikVision (Control de Acceso con PIN) -->
    <form method="POST" action="<?php echo BASE_URL; ?>/settings/saveHikvisionBridge" id="hikvisionBridgeForm">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-fingerprint text-purple-600 mr-2"></i>Lector HikVision (Control de Acceso)
                </h2>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">
                Configure el puente (bridge) para comunicación con el dispositivo lector HikVision DS-K1T502DBWX. 
                Este sistema permite crear usuarios temporales con PIN en el dispositivo cuando se generan tickets de acceso.
            </p>
            
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- URL del Bridge -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL del Bridge <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="bridge_url" 
                               value="<?php echo defined('HIKVISION_BRIDGE_URL') ? HIKVISION_BRIDGE_URL : 'http://187.145.46.170:8080'; ?>"
                               placeholder="http://187.145.46.170:8080"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            URL del servidor puente que conecta con el dispositivo HikVision. Incluir http:// y puerto.
                        </p>
                    </div>
                    
                    <!-- Timeout -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Timeout (segundos) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="bridge_timeout" 
                               value="<?php echo defined('HIKVISION_BRIDGE_TIMEOUT') ? HIKVISION_BRIDGE_TIMEOUT : 10; ?>"
                               min="5" max="60" step="1"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Tiempo máximo de espera para conexión (5-60 segundos)</p>
                    </div>
                    
                    <!-- Horas de Validez -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Validez del PIN (horas) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="user_validity_hours" 
                               value="<?php echo defined('HIKVISION_USER_VALIDITY_HOURS') ? HIKVISION_USER_VALIDITY_HOURS : 1; ?>"
                               min="1" max="24" step="1"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Duración de usuarios temporales creados (1-24 horas)</p>
                    </div>
                </div>
                
                <!-- Modo Local -->
                <div class="md:col-span-2 mt-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" name="bridge_local_mode" 
                                   value="1" <?php echo (defined('HIKVISION_BRIDGE_LOCAL_MODE') && HIKVISION_BRIDGE_LOCAL_MODE) ? 'checked' : ''; ?>
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3 mt-1"
                                   id="bridgeLocalModeCheckbox"
                                   onchange="toggleLocalUrlField()">
                            <div>
                                <span class="text-sm font-medium text-blue-900">🏠 Modo Local (PC Puente)</span>
                                <p class="text-xs text-blue-700 mt-1">
                                    Habilitar cuando se accede al sistema desde el navegador en la PC puente. 
                                    Las peticiones se harán client-side (JavaScript) a localhost en lugar de server-side (PHP).
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- URL Local (se muestra solo si modo local está activado) -->
                <div class="md:col-span-2" id="localUrlField" style="display: <?php echo (defined('HIKVISION_BRIDGE_LOCAL_MODE') && HIKVISION_BRIDGE_LOCAL_MODE) ? 'block' : 'none'; ?>;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-laptop-house text-blue-600 mr-1"></i>URL Local del Bridge
                    </label>
                    <input type="text" name="bridge_local_url" 
                           value="<?php echo defined('HIKVISION_BRIDGE_LOCAL_URL') ? HIKVISION_BRIDGE_LOCAL_URL : 'http://127.0.0.1:8080'; ?>"
                           placeholder="http://127.0.0.1:8080"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                        URL local para peticiones client-side (usualmente 127.0.0.1 o localhost).
                    </p>
                </div>
                
                <!-- Habilitado -->
                <div class="md:col-span-2 space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="bridge_enabled" 
                               value="1" <?php echo (defined('HIKVISION_ENABLED') && HIKVISION_ENABLED) ? 'checked' : ''; ?>
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-2">
                        <span class="text-sm text-gray-700">Sistema habilitado (crear usuarios automáticamente en tickets)</span>
                    </label>
                </div>
                
                <!-- Info del dispositivo actual -->
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>Información del Dispositivo
                    </h4>
                    <div class="text-xs text-blue-800 space-y-1">
                        <p><strong>Modelo:</strong> HikVision DS-K1T502DBWX-CQR</p>
                        <p><strong>IP Local:</strong> 192.168.16.59 (red IENTC)</p>
                        <p><strong>Bridge PC:</strong> localhost:8080 (modo local) / 192.168.1.50:8080 (modo remoto)</p>
                        <p><strong>Endpoint:</strong> POST /create-ticket-user</p>
                        <p class="text-xs text-blue-600 mt-2">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Nota:</strong> En modo local no se requiere IP fija de la PC puente. 
                            Las peticiones se hacen directamente a localhost (127.0.0.1).
                        </p>
                    </div>
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
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-save mr-2"></i>Guardar Configuración del Lector
            </button>
        </div>
    </form>
    
    <script>
        // Debug para el formulario de HikVision Bridge
        document.getElementById('hikvisionBridgeForm').addEventListener('submit', function(e) {
            console.log('=== Enviando formulario HikVision Bridge ===');
            const formData = new FormData(this);
            console.log('Datos del formulario:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            console.log('Action URL:', this.action);
            console.log('Method:', this.method);
        });
        
        // Función para mostrar/ocultar el campo URL Local
        function toggleLocalUrlField() {
            const checkbox = document.querySelector('input[name="bridge_local_mode"]');
            const urlField = document.getElementById('localUrlField');
            if (checkbox && urlField) {
                urlField.style.display = checkbox.checked ? 'block' : 'none';
            }
        }
        
        // Agregar listener al checkbox de modo local
        const localModeCheckbox = document.querySelector('input[name="bridge_local_mode"]');
        if (localModeCheckbox) {
            localModeCheckbox.addEventListener('change', toggleLocalUrlField);
        }
    </script>
    
    <!-- Optimización del Sistema -->
    <form method="POST" action="<?php echo BASE_URL; ?>/settings/optimizeSystem" id="optimizeSystemForm">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start mb-4">
                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 mr-4">
                    <i class="fas fa-database text-red-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Optimización del Sistema</h2>
                    <p class="text-gray-600 text-sm">Gestión de historial y limpieza de registros antiguos</p>
                </div>
            </div>
            
            <!-- Autoborrado de registros -->
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-6 mb-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clock text-gray-600 mr-2"></i>Autoborrado de Registros
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Borrar automáticamente después de
                        </label>
                        <div class="flex items-center space-x-2">
                            <select name="auto_delete_days" id="autoDeleteDays"
                                    class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="0" <?php echo ($settings['auto_delete_days'] ?? '0') === '0' ? 'selected' : ''; ?>>No borrar historial</option>
                                <option value="30" <?php echo ($settings['auto_delete_days'] ?? '') === '30' ? 'selected' : ''; ?>>30 días</option>
                                <option value="60" <?php echo ($settings['auto_delete_days'] ?? '') === '60' ? 'selected' : ''; ?>>60 días</option>
                                <option value="90" <?php echo ($settings['auto_delete_days'] ?? '') === '90' ? 'selected' : ''; ?>>90 días</option>
                                <option value="180" <?php echo ($settings['auto_delete_days'] ?? '') === '180' ? 'selected' : ''; ?>>180 días (6 meses)</option>
                                <option value="365" <?php echo ($settings['auto_delete_days'] ?? '') === '365' ? 'selected' : ''; ?>>365 días (1 año)</option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Los registros de entrada de unidad más antiguos que el período seleccionado serán eliminados automáticamente.
                        </p>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="auto_delete_enabled" value="1" 
                                   <?php echo ($settings['auto_delete_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                            <span class="text-sm text-gray-700">Habilitar autoborrado</span>
                        </label>
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i>
                            El autoborrado se ejecutará diariamente a las 3:00 AM (requiere cron job configurado).
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Borrado manual por fecha -->
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6 mb-4">
                <h3 class="text-lg font-semibold text-yellow-800 mb-4">
                    <i class="fas fa-trash-alt text-yellow-600 mr-2"></i>Borrado Manual de Registros
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Borrar registros anteriores a
                        </label>
                        <input type="date" name="delete_before_date" id="deleteBeforeDate"
                               class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500"
                               max="<?php echo date('Y-m-d'); ?>">
                        <p class="mt-1 text-xs text-gray-500">
                            Seleccione una fecha. Se eliminarán todos los registros de entrada de unidad anteriores a esta fecha.
                        </p>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="button" onclick="confirmManualDelete()"
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg w-full">
                            <i class="fas fa-trash mr-2"></i>Borrar Registros Antiguos
                        </button>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>¡Advertencia!</strong> Esta acción es irreversible. Los registros borrados no podrán recuperarse.
                        Asegúrese de tener un respaldo antes de proceder.
                    </p>
                </div>
            </div>
            
            <!-- Respaldo de Base de Datos -->
            <div class="bg-green-50 border border-green-300 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-800 mb-4">
                    <i class="fas fa-download text-green-600 mr-2"></i>Respaldo de Base de Datos
                </h3>
                
                <p class="text-sm text-gray-600 mb-4">
                    Descargue un respaldo completo de la base de datos en formato SQL. Este archivo contiene todas las tablas y datos del sistema.
                </p>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            El respaldo incluirá todas las transacciones, registros de acceso, visitantes, usuarios y configuraciones.
                        </p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/settings/backupDatabase" 
                       class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex-shrink-0">
                        <i class="fas fa-download mr-2"></i>Descargar Respaldo SQL
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Botones para optimización -->
        <div class="flex justify-end space-x-4 mb-6">
            <a href="<?php echo BASE_URL; ?>/dashboard" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-save mr-2"></i>Guardar Configuración de Optimización
            </button>
        </div>
    </form>
    
    <script>
        function confirmManualDelete() {
            const dateInput = document.getElementById('deleteBeforeDate');
            if (!dateInput.value) {
                alert('Por favor seleccione una fecha');
                return;
            }
            
            const confirmMsg = `¿Está seguro de que desea borrar TODOS los registros de entrada de unidad anteriores al ${dateInput.value}?\n\nEsta acción es IRREVERSIBLE.`;
            
            if (confirm(confirmMsg)) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo BASE_URL; ?>/settings/deleteOldRecords';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_before_date';
                input.value = dateInput.value;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    
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
        
        // Capacity Cost functions
        let capacityCostIndex = <?php 
            require_once APP_PATH . '/models/CapacityCost.php';
            $ccModel = new CapacityCost();
            $ccCount = $ccModel->count(false);
            echo (int)$ccCount; 
        ?>;
        
        function addCapacityCost() {
            const container = document.getElementById('capacityCostsContainer');
            
            // Remove "no costs" message if present
            const noDataMsg = container.querySelector('.text-center');
            if (noDataMsg && noDataMsg.textContent.includes('No hay costos')) {
                noDataMsg.remove();
            }
            
            const html = `
                <div class="capacity-cost-item border border-gray-200 rounded-lg p-4 bg-gray-50" id="capacity-cost-${capacityCostIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <input type="hidden" name="capacity_costs[${capacityCostIndex}][id]" value="">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad (Litros)</label>
                            <input type="number" name="capacity_costs[${capacityCostIndex}][capacity_liters]" 
                                   required min="1"
                                   class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                   placeholder="Ej: 10000">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Costo ($)</label>
                            <input type="number" name="capacity_costs[${capacityCostIndex}][cost]" 
                                   required min="0" step="0.01"
                                   class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                   placeholder="Ej: 50000.00">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <input type="text" name="capacity_costs[${capacityCostIndex}][description]" 
                                   class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                                   placeholder="Ej: Pipa estándar">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="capacity_costs[${capacityCostIndex}][is_active]"
                                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        
                        <div>
                            <button type="button" onclick="removeCapacityCost(this)"
                                    class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg w-full">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            capacityCostIndex++;
        }
        
        function removeCapacityCost(btn) {
            if (confirm('¿Está seguro de eliminar este costo de capacidad?')) {
                const item = btn.closest('.capacity-cost-item');
                item.remove();
                
                // Check if container is empty
                const container = document.getElementById('capacityCostsContainer');
                if (container.querySelectorAll('.capacity-cost-item').length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-info-circle text-4xl mb-4"></i>
                            <p>No hay costos configurados. Haga clic en "Agregar Capacidad" para comenzar.</p>
                        </div>
                    `;
                }
            }
        }
    </script>
</div>
