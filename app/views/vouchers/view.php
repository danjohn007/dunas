<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/vouchers" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Vales
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Detalle de Vale</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
                Información del Vale
            </h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Serie - Folio</label>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($voucher['serie']); ?>-<?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Estado</label>
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
                    $colorClass = $statusColors[$voucher['status']] ?? 'bg-gray-100 text-gray-800';
                    $label = $statusLabels[$voucher['status']] ?? $voucher['status'];
                    ?>
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $colorClass; ?>">
                        <?php echo $label; ?>
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Capacidad</label>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo number_format($voucher['capacity']); ?> Litros
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Código QR</label>
                    <p class="text-sm font-mono text-gray-900 break-all">
                        <?php echo htmlspecialchars($voucher['qr_code']); ?>
                    </p>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Creación</h3>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Creado Por</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($voucher['created_by_name']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Creación</label>
                        <p class="text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($voucher['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            
            <?php if ($voucher['status'] === 'used'): ?>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Uso</h3>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Fecha de Uso</label>
                        <p class="text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($voucher['used_at'])); ?></p>
                    </div>
                    
                    <?php if (!empty($voucher['used_ticket_code'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Ticket de Acceso</label>
                        <a href="<?php echo BASE_URL; ?>/access/view/<?php echo $voucher['used_by_access_log_id']; ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($voucher['used_ticket_code']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- QR Code Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 text-center">
                Código QR
            </h2>
            
            <div id="qrcode" class="flex justify-center mb-4"></div>
            
            <div class="text-center text-sm text-gray-600 mb-4">
                <p class="font-mono break-all">
                    <?php echo htmlspecialchars($voucher['qr_code']); ?>
                </p>
            </div>
            
            <?php if ($voucher['status'] === 'active' && Auth::hasRole(['admin', 'supervisor'])): ?>
            <div class="space-y-2">
                <button onclick="printVoucher()" 
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-print mr-2"></i>Imprimir Vale
                </button>
                
                <button onclick="confirmCancel()" 
                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-ban mr-2"></i>Cancelar Vale
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Generate QR code
document.addEventListener('DOMContentLoaded', function() {
    try {
        new QRCode(document.getElementById('qrcode'), {
            text: '<?php echo htmlspecialchars($voucher['qr_code']); ?>',
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    } catch (error) {
        console.error('Error generating QR code:', error);
        document.getElementById('qrcode').innerHTML = '<p class="text-red-500">Error al generar código QR</p>';
    }
});

function printVoucher() {
    // Redirect to print view for single voucher
    window.open('<?php echo BASE_URL; ?>/vouchers/printSingle/<?php echo $voucher['id']; ?>', '_blank');
}

function confirmCancel() {
    if (confirm('¿Está seguro de que desea cancelar este vale? Esta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/vouchers/cancel/<?php echo $voucher['id']; ?>';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
