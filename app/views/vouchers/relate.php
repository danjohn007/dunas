<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/vouchers" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Vales
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Relacionar Vales de Imprenta</h1>
        <p class="text-gray-600">Asigne rangos de vales a una empresa para activarlos</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/relateStore">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Empresa <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="client_search"
                               autocomplete="off"
                               required
                               placeholder="-- Seleccione una empresa --"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <input type="hidden" name="client_id" id="client_id">
                        <div id="client_results"
                             class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden"
                             style="max-height:200px;overflow-y:auto;"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Serie <span class="text-red-500">*</span>
                    </label>
                    <select name="serie"
                            id="serie"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Seleccione la serie --</option>
                        <?php foreach ($series as $serie): ?>
                        <option value="<?php echo htmlspecialchars($serie['serie']); ?>">
                            <?php echo htmlspecialchars($serie['serie']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Capacidad (Litros) <span class="text-red-500">*</span>
                    </label>
                    <select name="capacity"
                            id="capacity"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Seleccione la capacidad --</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            PIN Inicial <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="folio_start"
                               required
                               min="0"
                               max="9999"
                               placeholder="Ejemplo: 1000"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            PIN Final <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="folio_end"
                               required
                               min="0"
                               max="9999"
                               placeholder="Ejemplo: 1099"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8">
                <a href="<?php echo BASE_URL; ?>/vouchers"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors inline-flex items-center">
                    <i class="fas fa-link mr-2"></i>
                    RELACIONAR VALES
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var clientsData = <?php echo json_encode(array_values($clients), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var seriesCapacities = <?php echo json_encode(array_values($seriesCapacities)); ?>;

    var clientSearchInput = document.getElementById('client_search');
    var clientIdInput     = document.getElementById('client_id');
    var clientResultsBox  = document.getElementById('client_results');
    var serieSelect    = document.getElementById('serie');
    var capacitySelect = document.getElementById('capacity');

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderClientResults(matches) {
        if (matches.length === 0) {
            clientResultsBox.innerHTML = '<div class="p-3 text-gray-500 text-sm">No se encontraron empresas</div>';
        } else {
            clientResultsBox.innerHTML = matches.map(function (client) {
                var name = escapeHtml(client.business_name || '');
                var id = Number(client.id) || 0;
                return '<div class="p-3 cursor-pointer hover:bg-blue-50 text-sm border-b border-gray-100 last:border-0" data-id="' + id + '" data-name="' + name + '">' + name + '</div>';
            }).join('');

            clientResultsBox.querySelectorAll('div[data-id]').forEach(function (item) {
                item.addEventListener('click', function () {
                    clientIdInput.value = this.getAttribute('data-id');
                    clientSearchInput.value = this.getAttribute('data-name');
                    clientResultsBox.classList.add('hidden');
                });
            });
        }

        clientResultsBox.classList.remove('hidden');
    }

    clientSearchInput.addEventListener('input', function () {
        var query = this.value.toLowerCase().trim();
        clientIdInput.value = '';
        clientSearchInput.classList.remove('border-red-500');

        if (query.length === 0) {
            clientResultsBox.classList.add('hidden');
            return;
        }

        var matches = clientsData.filter(function (client) {
            return (client.business_name || '').toLowerCase().indexOf(query) !== -1;
        });

        renderClientResults(matches);
    });

    clientSearchInput.addEventListener('focus', function () {
        if (this.value.trim().length > 0 && !clientIdInput.value) {
            clientSearchInput.dispatchEvent(new Event('input'));
        }
    });

    document.addEventListener('click', function (event) {
        if (!clientSearchInput.contains(event.target) && !clientResultsBox.contains(event.target)) {
            clientResultsBox.classList.add('hidden');
        }
    });

    clientSearchInput.closest('form').addEventListener('submit', function (event) {
        if (!clientIdInput.value) {
            event.preventDefault();
            clientSearchInput.focus();
            clientSearchInput.classList.add('border-red-500');
            alert('Por favor seleccione una empresa de la lista.');
        }
    });

    serieSelect.addEventListener('change', function () {
        var selectedSerie = this.value;
        var placeholder   = capacitySelect.options[0];

        while (capacitySelect.options.length > 1) {
            capacitySelect.remove(1);
        }

        capacitySelect.value = '';

        seriesCapacities.forEach(function (row) {
            if (row.serie === selectedSerie) {
                var opt   = document.createElement('option');
                opt.value = row.capacity;
                opt.text  = Number(row.capacity).toLocaleString() + ' L';
                capacitySelect.appendChild(opt);
            }
        });
    });
}());
</script>
