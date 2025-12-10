<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Generar Pase de Visita</h1>
            <p class="text-gray-600">Complete el formulario para generar un pase con vigencia</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/visitors" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/visitors/createPass">
            
            <!-- Información del Visitante -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user text-blue-600 mr-2"></i>Información del Visitante
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre Completo -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="visitor_name" required
                               placeholder="Nombre del visitante"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <!-- Identificación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Identificación
                        </label>
                        <input type="text" name="identification"
                               placeholder="INE, Pasaporte, etc."
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <!-- Teléfono/WhatsApp -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono/WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" required
                               placeholder="4421234567"
                               pattern="[0-9]{10}"
                               title="10 dígitos sin espacios ni guiones"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">10 dígitos sin espacios ni guiones</p>
                    </div>
                    
                    <!-- Placa del Vehículo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Placa del Vehículo
                        </label>
                        <input type="text" name="plate_number"
                               placeholder="ABC-123-D"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                               oninput="this.value = this.value.toUpperCase()">
                    </div>
                    
                    <!-- Tipo de Visita -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Visita
                        </label>
                        <select name="visit_type" 
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="personal">Personal</option>
                            <option value="servicio">Servicio</option>
                            <option value="proveedor">Proveedor</option>
                            <option value="mensajeria">Mensajería</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Vigencia del Pase -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Vigencia del Pase
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Válido Desde -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Válido Desde <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="valid_from" required
                               value="<?php echo date('Y-m-d\TH:i'); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <!-- Válido Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Válido Hasta <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="valid_until" required
                               value="<?php echo date('Y-m-d\TH:i', strtotime('+4 hours')); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Notas Adicionales -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notas Adicionales
                </label>
                <textarea name="notes" rows="3"
                          placeholder="Información adicional sobre la visita..."
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/visitors" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-6 rounded-lg">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                    <i class="fas fa-qrcode mr-2"></i>Generar Pase QR
                </button>
            </div>
            
        </form>
    </div>
</div>
