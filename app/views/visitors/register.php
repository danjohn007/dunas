<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Visitante - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    $primaryColor = $systemSettings['theme_primary_color'] ?? '#2563eb';
    $secondaryColor = $systemSettings['theme_secondary_color'] ?? '#1e40af';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        :root {
            --color-primary: <?php echo $primaryColor; ?>;
            --color-secondary: <?php echo $secondaryColor; ?>;
        }
        .bg-primary { background-color: var(--color-primary) !important; }
        .text-primary { color: var(--color-primary) !important; }
        
        .capture-box {
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        .capture-box:hover {
            border-color: var(--color-primary);
            background: #f0f9ff;
        }
        .capture-box.has-image {
            border: 2px solid #22c55e;
            background: #f0fdf4;
        }
        .capture-preview {
            max-height: 200px;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Header -->
    <header class="bg-primary shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-center">
                <?php if (!empty($systemSettings['site_logo'])): ?>
                    <img src="<?php echo BASE_URL . $systemSettings['site_logo']; ?>" alt="Logo" class="h-10 mr-3">
                <?php else: ?>
                    <i class="fas fa-user-plus text-white text-2xl mr-3"></i>
                <?php endif; ?>
                <h1 class="text-white text-xl font-bold"><?php echo $systemSettings['site_name'] ?? 'Registro de Visitantes'; ?></h1>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <?php $errorMsg = Session::getFlash('error'); ?>
    <?php if ($errorMsg): ?>
    <div class="max-w-4xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($errorMsg); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-id-card text-primary mr-2"></i>
                    Registro de Visitante
                </h2>
                <p class="text-gray-600">Complete el formulario para registrar su visita</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="visitorForm">
                
                <!-- Sección de Fotos Requeridas -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-camera text-primary mr-2"></i>
                        Fotografías <span class="text-red-500 ml-1">*</span>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Foto de Identificación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Foto de Identificación <span class="text-red-500">*</span>
                            </label>
                            <div class="capture-box rounded-lg p-4 text-center cursor-pointer" id="idPhotoBox" onclick="document.getElementById('id_photo').click()">
                                <input type="file" name="id_photo" id="id_photo" accept="image/*" capture="environment" class="hidden" required>
                                <div id="idPhotoPreview" class="hidden">
                                    <img id="idPhotoImg" src="" alt="Preview" class="capture-preview mx-auto rounded-lg mb-2">
                                    <p class="text-sm text-green-600"><i class="fas fa-check-circle mr-1"></i>Foto capturada</p>
                                </div>
                                <div id="idPhotoPlaceholder">
                                    <i class="fas fa-id-card text-gray-400 text-4xl mb-2"></i>
                                    <p class="text-gray-500">Toque para capturar foto de su identificación</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Foto de Placas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Foto de Placas del Vehículo <span class="text-red-500">*</span>
                            </label>
                            <div class="capture-box rounded-lg p-4 text-center cursor-pointer" id="platePhotoBox" onclick="document.getElementById('plate_photo').click()">
                                <input type="file" name="plate_photo" id="plate_photo" accept="image/*" capture="environment" class="hidden" required>
                                <div id="platePhotoPreview" class="hidden">
                                    <img id="platePhotoImg" src="" alt="Preview" class="capture-preview mx-auto rounded-lg mb-2">
                                    <p class="text-sm text-green-600"><i class="fas fa-check-circle mr-1"></i>Foto capturada</p>
                                </div>
                                <div id="platePhotoPlaceholder">
                                    <i class="fas fa-car text-gray-400 text-4xl mb-2"></i>
                                    <p class="text-gray-500">Toque para capturar foto de las placas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Datos Opcionales -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-primary mr-2"></i>
                        Datos Adicionales <span class="text-gray-400 text-sm font-normal ml-2">(Opcional)</span>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre Completo
                            </label>
                            <input type="text" name="visitor_name" 
                                   placeholder="Ingrese su nombre completo"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <!-- Placa -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Número de Placa
                            </label>
                            <input type="text" name="plate_number" 
                                   placeholder="Ej: ABC-123"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase">
                        </div>
                        
                        <!-- Teléfono -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input type="tel" name="phone" 
                                   placeholder="Número de teléfono"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <!-- Foto de Gafete -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Foto de Gafete <span class="text-gray-400">(Opcional)</span>
                            </label>
                            <div class="capture-box rounded-lg p-4 text-center cursor-pointer" id="badgePhotoBox" onclick="document.getElementById('badge_photo').click()" style="min-height: 120px;">
                                <input type="file" name="badge_photo" id="badge_photo" accept="image/*" capture="environment" class="hidden">
                                <div id="badgePhotoPreview" class="hidden">
                                    <img id="badgePhotoImg" src="" alt="Preview" class="capture-preview mx-auto rounded-lg mb-2" style="max-height: 80px;">
                                    <p class="text-sm text-green-600"><i class="fas fa-check-circle mr-1"></i>Foto capturada</p>
                                </div>
                                <div id="badgePhotoPlaceholder">
                                    <i class="fas fa-address-card text-gray-400 text-2xl mb-1"></i>
                                    <p class="text-gray-500 text-sm">Toque para agregar foto de gafete</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botón de Envío -->
                <div class="text-center">
                    <button type="submit" id="submitBtn"
                            class="bg-primary hover:opacity-90 text-white font-semibold py-4 px-8 rounded-lg text-lg shadow-lg transition-all duration-300 w-full md:w-auto">
                        <i class="fas fa-check-circle mr-2"></i>
                        Registrar Visita
                    </button>
                </div>
                
            </form>
        </div>
        
        <!-- Información de Contacto -->
        <?php if (!empty($systemSettings['contact_phone'])): ?>
        <div class="mt-6 text-center text-gray-500 text-sm">
            <p>¿Necesita ayuda? Comuníquese al: <?php echo htmlspecialchars($systemSettings['contact_phone']); ?></p>
        </div>
        <?php endif; ?>
    </main>
    
    <script>
        // Preview de imágenes
        function setupImagePreview(inputId, boxId, previewId, placeholderId, imgId) {
            const input = document.getElementById(inputId);
            const box = document.getElementById(boxId);
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);
            const img = document.getElementById(imgId);
            
            input.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                        box.classList.add('has-image');
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
        
        // Configurar previews
        setupImagePreview('id_photo', 'idPhotoBox', 'idPhotoPreview', 'idPhotoPlaceholder', 'idPhotoImg');
        setupImagePreview('plate_photo', 'platePhotoBox', 'platePhotoPreview', 'platePhotoPlaceholder', 'platePhotoImg');
        setupImagePreview('badge_photo', 'badgePhotoBox', 'badgePhotoPreview', 'badgePhotoPlaceholder', 'badgePhotoImg');
        
        // Convertir placa a mayúsculas
        document.querySelector('input[name="plate_number"]').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Prevenir doble envío
        document.getElementById('visitorForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Registrando...';
        });
    </script>
    
</body>
</html>
