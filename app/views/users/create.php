<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Nuevo Usuario</h1>
        <p class="text-gray-600">Registrar nuevo usuario en el sistema</p>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/users/create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre de Usuario -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre de Usuario <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese nombre de usuario">
                </div>
                
                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese contraseña">
                    <div id="password-strength" class="mt-2 hidden">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="password-strength-bar" class="h-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <span id="password-strength-text" class="text-sm font-medium"></span>
                        </div>
                        <ul id="password-requirements" class="text-xs space-y-1 mt-2">
                            <li id="req-length" class="flex items-center text-gray-500">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                <span>Mínimo 8 caracteres</span>
                            </li>
                            <li id="req-uppercase" class="flex items-center text-gray-500">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                <span>Una letra mayúscula</span>
                            </li>
                            <li id="req-lowercase" class="flex items-center text-gray-500">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                <span>Una letra minúscula</span>
                            </li>
                            <li id="req-number" class="flex items-center text-gray-500">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                <span>Un número</span>
                            </li>
                            <li id="req-special" class="flex items-center text-gray-500">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                <span>Un carácter especial (!@#$%^&*)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Nombre Completo -->
                <div class="md:col-span-2">
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese nombre completo">
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese correo electrónico">
                </div>
                
                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select id="role" name="role" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione rol</option>
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="operator">Operador</option>
                        <option value="viewer">Visualizador</option>
                        <option value="client">Cliente</option>
                    </select>
                </div>
                
                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/users" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthContainer = document.getElementById('password-strength');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number'),
        special: document.getElementById('req-special')
    };
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length === 0) {
            strengthContainer.classList.add('hidden');
            return;
        }
        
        strengthContainer.classList.remove('hidden');
        
        // Check requirements
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };
        
        // Update requirement indicators
        Object.keys(checks).forEach(key => {
            const el = requirements[key];
            const icon = el.querySelector('i');
            const span = el.querySelector('span');
            
            if (checks[key]) {
                el.classList.remove('text-gray-500');
                el.classList.add('text-green-600');
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
            } else {
                el.classList.remove('text-green-600');
                el.classList.add('text-gray-500');
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }
        });
        
        // Calculate strength
        const passedChecks = Object.values(checks).filter(v => v).length;
        let strength = 0;
        let strengthLabel = '';
        let strengthColor = '';
        
        if (passedChecks === 5) {
            strength = 100;
            strengthLabel = 'Muy segura';
            strengthColor = 'bg-green-500';
        } else if (passedChecks === 4) {
            strength = 80;
            strengthLabel = 'Segura';
            strengthColor = 'bg-green-400';
        } else if (passedChecks === 3) {
            strength = 60;
            strengthLabel = 'Media';
            strengthColor = 'bg-yellow-500';
        } else if (passedChecks === 2) {
            strength = 40;
            strengthLabel = 'Débil';
            strengthColor = 'bg-orange-500';
        } else {
            strength = 20;
            strengthLabel = 'Muy débil';
            strengthColor = 'bg-red-500';
        }
        
        strengthBar.style.width = strength + '%';
        strengthBar.className = 'h-full transition-all duration-300 ' + strengthColor;
        strengthText.textContent = strengthLabel;
        strengthText.className = 'text-sm font-medium ' + (passedChecks >= 4 ? 'text-green-600' : passedChecks >= 3 ? 'text-yellow-600' : 'text-red-600');
    });
});
</script>
