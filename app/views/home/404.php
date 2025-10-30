<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center py-16">
        <i class="fas fa-search text-gray-400 text-9xl mb-8"></i>
        <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
        <h2 class="text-3xl font-semibold text-gray-700 mb-4">Página No Encontrada</h2>
        <p class="text-gray-600 mb-8">La página que está buscando no existe o ha sido movida.</p>
        <a href="<?php echo BASE_URL; ?>/<?php echo Auth::isLoggedIn() ? 'dashboard' : ''; ?>" 
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
            <i class="fas fa-home mr-2"></i>Volver al Inicio
        </a>
    </div>
</div>
