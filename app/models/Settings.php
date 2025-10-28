<?php
/**
 * Modelo Settings
 */
class Settings {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM settings ORDER BY setting_key";
        $settings = $this->db->fetchAll($sql);
        
        // Convertir a array asociativo
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    public function get($key, $default = null) {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
        $result = $this->db->fetchOne($sql, [$key]);
        
        return $result ? $result['setting_value'] : $default;
    }
    
    public function set($key, $value) {
        $sql = "INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?";
        
        return $this->db->execute($sql, [$key, $value, $value]);
    }
    
    public function updateMultiple($settings) {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
        return true;
    }
    
    public function delete($key) {
        $sql = "DELETE FROM settings WHERE setting_key = ?";
        return $this->db->execute($sql, [$key]);
    }
}
