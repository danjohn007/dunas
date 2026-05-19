-- Actualización para gestión de estados de vales desde la interfaz de Gestión de Vales
-- Compatible con MySQL 5.7
-- Asegura que el ENUM de status contemple todos los estados usados por el sistema

ALTER TABLE `vouchers`
MODIFY COLUMN `status` ENUM('active','used','cancelled','registered','pending_assignment')
NOT NULL DEFAULT 'active'
COMMENT 'Estado del vale';
