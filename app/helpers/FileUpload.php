<?php
/**
 * Clase FileUpload - Manejo de subida de archivos
 */
class FileUpload {
    
    public static function upload($file, $destination, $allowedTypes = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Parámetros inválidos.'];
        }
        
        // Verificar errores
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir el archivo.'];
        }
        
        // Verificar tamaño
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'El archivo es demasiado grande.'];
        }
        
        // Verificar tipo
        $allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido.'];
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $destination . '/' . $filename;
        
        // Crear directorio si no existe
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'No se pudo guardar el archivo.'];
        }
        
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    public static function delete($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}
