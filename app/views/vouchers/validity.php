<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Tiempo de vales</h1>
        <p class="text-gray-600">Define cuánto tiempo será válido el código numérico del ticket generado al registrar un vale para abrir la pluma de salida.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <?php if ($validityMinutes !== null): ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <p class="text-blue-800">
                Vigencia actual: <strong><?php echo number_format($validityMinutes); ?> minutos</strong>.
                El tiempo se guarda en cada vale asociado a su ticket al registrarlo; los cambios aplican a los vales nuevos.
            </p>
        </div>
        <?php else: ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
            <p class="text-yellow-800">Aún no hay una vigencia configurada. Agrega un tiempo para habilitar el uso de vales.</p>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/saveValidity" class="space-y-4">
            <div>
                <label for="validity_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                    Tiempo de validez (minutos)
                </label>
                <input type="number" id="validity_minutes" name="validity_minutes" min="1" step="1" required
                       value="<?php echo $validityMinutes !== null ? (int)$validityMinutes : ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Ej. 60">
                <p class="mt-1 text-xs text-gray-500">La cuenta inicia cuando el vale se registra en un acceso.</p>
            </div>
            <div class="flex flex-wrap gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>/vouchers"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg">
                    Volver a vales
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i><?php echo $validityMinutes === null ? 'Agregar vigencia' : 'Guardar cambios'; ?>
                </button>
            </div>
        </form>

        <?php if ($validityMinutes !== null): ?>
        <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/deleteValidity" class="mt-6 pt-6 border-t border-gray-200"
              onsubmit="return confirm('¿Eliminar la vigencia configurada? Los vales nuevos no podrán registrarse hasta agregar otra.');">
            <button type="submit" class="text-red-700 hover:text-red-900 font-medium">
                <i class="fas fa-trash-alt mr-2"></i>Eliminar vigencia
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
