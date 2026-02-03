<!-- Vista de Detalle de Vale -->
<div class="container mx-auto px-4 py-6">
    
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
            Detalle del Vale
        </h1>
        
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/vouchers/print?ids=<?php echo $voucher['id']; ?>" 
               target="_blank"
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-print mr-2"></i>Imprimir
            </a>
            <a href="<?php echo BASE_URL; ?>/vouchers" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Información del vale -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Datos principales -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Información del Vale</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Código de Vale</label>
                    <p class="text-lg font-bold text-gray-900 font-mono"><?php echo htmlspecialchars($voucher['voucher_code']); ?></p>
                </div>
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Estado</label>
                    <p class="text-lg">
                        <?php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'used' => 'bg-gray-100 text-gray-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        $statusLabels = [
                            'active' => 'Activo',
                            'used' => 'Usado',
                            'cancelled' => 'Cancelado'
                        ];
                        ?>
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $statusColors[$voucher['status']]; ?>">
                            <?php echo $statusLabels[$voucher['status']]; ?>
                        </span>
                    </p>
                </div>
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Serie</label>
                    <p class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($voucher['serie']); ?></p>
                </div>
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Folio</label>
                    <p class="text-lg font-semibold text-gray-900"><?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Capacidad</label>
                    <p class="text-lg font-semibold text-gray-900"><?php echo number_format($voucher['capacity_liters']); ?> Litros</p>
                </div>
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Fecha de Creación</label>
                    <p class="text-lg text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($voucher['created_at'])); ?></p>
                </div>
                
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Creado Por</label>
                    <p class="text-lg text-gray-900"><?php echo htmlspecialchars($voucher['created_by_name'] ?? 'N/A'); ?></p>
                </div>
                
                <?php if ($voucher['status'] === 'used' && !empty($voucher['used_at'])): ?>
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Fecha de Uso</label>
                    <p class="text-lg text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($voucher['used_at'])); ?></p>
                </div>
                
                <?php if (!empty($voucher['used_in_ticket'])): ?>
                <div class="border-b border-gray-200 pb-3">
                    <label class="text-sm text-gray-600">Usado en Ticket</label>
                    <p class="text-lg">
                        <a href="<?php echo BASE_URL; ?>/access/view/<?php echo $voucher['used_by_access_log_id']; ?>" 
                           class="text-blue-600 hover:text-blue-800 font-semibold">
                            <?php echo htmlspecialchars($voucher['used_in_ticket']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
            </div>
            
            <div class="mt-6 border-t border-gray-200 pt-4">
                <label class="text-sm text-gray-600 block mb-2">Código QR</label>
                <p class="text-sm text-gray-700 font-mono bg-gray-100 p-3 rounded break-all">
                    <?php echo htmlspecialchars($voucher['qr_code']); ?>
                </p>
            </div>
            
            <?php if ($voucher['status'] === 'active' && Auth::hasRole(['admin', 'supervisor'])): ?>
            <div class="mt-6 border-t border-gray-200 pt-4">
                <button onclick="cancelVoucher()" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-ban mr-2"></i>Cancelar Vale
                </button>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Código QR -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Código QR</h2>
            <div class="flex justify-center items-center">
                <div id="qrcode" class="border-4 border-gray-200 p-4 rounded-lg bg-white"></div>
            </div>
            <p class="text-center text-sm text-gray-600 mt-4">
                Escanea este código QR con el dispositivo HikVision para validar el vale
            </p>
        </div>
        
    </div>
    
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Generar código QR
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById('qrcode'), {
        text: '<?php echo htmlspecialchars($voucher['qr_code']); ?>',
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
});

function cancelVoucher() {
    if (!confirm('¿Está seguro de que desea cancelar este vale?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/vouchers/cancel/<?php echo $voucher['id']; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar el vale');
    });
}
</script>
