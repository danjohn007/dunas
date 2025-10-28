<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        <div class="text-center">
            <i class="fas fa-water text-blue-600 text-6xl mb-4"></i>
            <h2 class="text-3xl font-extrabold text-gray-900">
                Iniciar Sesión
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Ingrese sus credenciales para acceder al sistema
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="<?php echo BASE_URL; ?>/login" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Usuario
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input id="username" 
                               name="username" 
                               type="text" 
                               required 
                               class="appearance-none rounded-lg relative block w-full pl-10 px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Nombre de usuario">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               class="appearance-none rounded-lg relative block w-full pl-10 px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Contraseña">
                    </div>
                </div>
            </div>
            
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Ingresar al Sistema
                </button>
            </div>
            
            <div class="text-center">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-600 mb-2">Credenciales de prueba:</p>
                    <p class="text-xs text-gray-800"><strong>Usuario:</strong> admin / supervisor / operator / cliente1</p>
                    <p class="text-xs text-gray-800"><strong>Contraseña:</strong> admin123</p>
                </div>
            </div>
        </form>
    </div>
</div>
