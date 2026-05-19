<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/vouchers<?php echo $defaultSerie !== '' ? '?serie=' . urlencode($defaultSerie) : ''; ?>" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Gestión de Vales
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Eliminar Vales</h1>
        <p class="text-gray-600">Seleccione la serie y capture el rango de folios que desea eliminar.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/deleteBulkStore">
            <input type="hidden" name="return_serie" value="<?php echo htmlspecialchars($defaultSerie); ?>">

            <div class="space-y-6">
                <div>
                    <label for="serie" class="block text-sm font-medium text-gray-700 mb-2">
                        Serie <span class="text-red-500">*</span>
                    </label>
                    <select id="serie"
                            name="serie"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione una serie</option>
                        <?php foreach ($series as $serie): ?>
                        <option value="<?php echo htmlspecialchars($serie['serie']); ?>" <?php echo $defaultSerie === $serie['serie'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($serie['serie']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="folio_start" class="block text-sm font-medium text-gray-700 mb-2">
                            Folio Inicial <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="folio_start"
                               name="folio_start"
                               min="1"
                               step="1"
                               required
                               placeholder="1"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="folio_end" class="block text-sm font-medium text-gray-700 mb-2">
                            Folio Final <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="folio_end"
                               name="folio_end"
                               min="1"
                               step="1"
                               required
                               placeholder="100"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    Solo se eliminarán los vales de la serie y rango capturados que no estén usados ni registrados.
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8">
                <a href="<?php echo BASE_URL; ?>/vouchers<?php echo $defaultSerie !== '' ? '?serie=' . urlencode($defaultSerie) : ''; ?>"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        onclick="return confirm('¿Está seguro de eliminar los vales del rango indicado?');"
                        aria-label="Eliminar los vales del rango seleccionado"
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors inline-flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Eliminar Vales
                </button>
            </div>
        </form>
    </div>
</div>
