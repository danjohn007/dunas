<?php
/**
 * Helper TextUtils
 * Utilidades para procesamiento de texto
 */
class TextUtils {
    
    /**
     * Normaliza una placa vehicular para comparación
     * - Convierte a mayúsculas
     * - Remueve caracteres no alfanuméricos
     * - Elimina espacios, guiones, etc.
     * 
     * @param string|null $plate Placa a normalizar
     * @return string Placa normalizada
     */
    public static function normalizePlate($plate) {
        if ($plate === null || $plate === '') {
            return '';
        }
        
        // Convertir a mayúsculas usando mb_strtoupper para soporte UTF-8
        $plate = mb_strtoupper($plate, 'UTF-8');
        
        // Remover todos los caracteres que no sean letras (A-Z) o números (0-9)
        $plate = preg_replace('/[^A-Z0-9]/u', '', $plate);
        
        return $plate;
    }
    
    /**
     * Compara dos placas normalizadas
     * 
     * @param string|null $plate1 Primera placa
     * @param string|null $plate2 Segunda placa
     * @return bool true si las placas coinciden después de normalizar
     */
    public static function platesMatch($plate1, $plate2) {
        $normalized1 = self::normalizePlate($plate1);
        $normalized2 = self::normalizePlate($plate2);
        
        // Ambas deben tener contenido para considerar match
        if (empty($normalized1) || empty($normalized2)) {
            return false;
        }
        
        return $normalized1 === $normalized2;
    }
    
    /**
     * Limpia y normaliza texto general
     * 
     * @param string|null $text Texto a limpiar
     * @return string Texto limpio
     */
    public static function cleanText($text) {
        if ($text === null || $text === '') {
            return '';
        }
        
        // Remover espacios extra y caracteres de control
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        return $text;
    }
}
