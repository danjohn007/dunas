<?php
/**
 * Clase Validator - Validación de datos
 */
class Validator {
    
    private $errors = [];
    
    public function validate($data, $rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            $value = $data[$field] ?? null;
            
            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule($field, $value, $rule, $data) {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $param) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "El campo es requerido.";
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "Debe ser un email válido.";
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $param) {
                    $this->errors[$field][] = "Debe tener al menos {$param} caracteres.";
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $param) {
                    $this->errors[$field][] = "No debe exceder {$param} caracteres.";
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "Debe ser un número.";
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = "Debe ser un número entero.";
                }
                break;
                
            case 'unique':
                list($table, $column) = explode(',', $param);
                if (!empty($value) && $this->checkUnique($table, $column, $value)) {
                    $this->errors[$field][] = "Este valor ya está en uso.";
                }
                break;
                
            case 'match':
                if (!empty($value) && $value !== ($data[$param] ?? null)) {
                    $this->errors[$field][] = "Los campos no coinciden.";
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->errors[$field][] = "Debe ser una fecha válida.";
                }
                break;
                
            case 'phone':
                if (!empty($value)) {
                    // Remove any non-digit characters
                    $cleaned = preg_replace('/\D/', '', $value);
                    if (strlen($cleaned) !== 10) {
                        $this->errors[$field][] = "El número telefónico debe tener exactamente 10 dígitos.";
                    }
                }
                break;
        }
    }
    
    private function checkUnique($table, $column, $value) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $db->fetchOne($sql, [$value]);
        return $result['count'] > 0;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getError($field) {
        return $this->errors[$field] ?? [];
    }
    
    public function hasError($field) {
        return isset($this->errors[$field]);
    }
}
