-- Sistema de Control de Acceso con IoT
-- Base de datos: dunas_access_control
-- MySQL 5.7+

CREATE DATABASE IF NOT EXISTS dunas_access_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dunas_access_control;

-- Tabla de usuarios del sistema
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'supervisor', 'operator', 'viewer', 'client') NOT NULL DEFAULT 'operator',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clientes
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    business_name VARCHAR(150) NOT NULL,
    rfc_curp VARCHAR(18) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    client_type ENUM('residential', 'commercial', 'industrial') NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_rfc_curp (rfc_curp),
    INDEX idx_client_type (client_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de unidades (pipas)
CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    capacity_liters INT NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    photo VARCHAR(255) NULL,
    status ENUM('active', 'maintenance', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plate_number (plate_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de mantenimiento
CREATE TABLE maintenance_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    description TEXT NOT NULL,
    cost DECIMAL(10, 2) NULL,
    performed_by VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    INDEX idx_unit_id (unit_id),
    INDEX idx_maintenance_date (maintenance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de choferes
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    license_expiry DATE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    photo VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_license_number (license_number),
    INDEX idx_status (status),
    INDEX idx_license_expiry (license_expiry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de asignación de unidades a choferes
CREATE TABLE driver_unit_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    unit_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    INDEX idx_driver_id (driver_id),
    INDEX idx_unit_id (unit_id),
    INDEX idx_assigned_date (assigned_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de control de acceso
CREATE TABLE access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_datetime DATETIME NOT NULL,
    exit_datetime DATETIME NULL,
    driver_id INT NOT NULL,
    unit_id INT NOT NULL,
    client_id INT NOT NULL,
    liters_supplied INT NULL,
    ticket_code VARCHAR(50) UNIQUE NOT NULL,
    qr_code VARCHAR(255) NULL,
    barcode VARCHAR(255) NULL,
    status ENUM('in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_entry_datetime (entry_datetime),
    INDEX idx_exit_datetime (exit_datetime),
    INDEX idx_ticket_code (ticket_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de transacciones
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_log_id INT NOT NULL,
    client_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    liters_supplied INT NOT NULL,
    price_per_liter DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'voucher', 'bank_transfer') NOT NULL,
    payment_status ENUM('paid', 'pending', 'cancelled') NOT NULL DEFAULT 'pending',
    transaction_date DATETIME NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (access_log_id) REFERENCES access_logs(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_client_id (client_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo

-- Usuarios (contraseña para todos: admin123)
INSERT INTO users (username, password, full_name, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez Administrador', 'admin@dunas.com', 'admin', 'active'),
('supervisor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González Supervisor', 'supervisor@dunas.com', 'supervisor', 'active'),
('operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos López Operador', 'operator@dunas.com', 'operator', 'active'),
('cliente1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Roberto Martínez Cliente', 'cliente1@email.com', 'client', 'active');

-- Clientes
INSERT INTO clients (user_id, business_name, rfc_curp, address, phone, email, client_type, status) VALUES
(4, 'Constructora ABC S.A. de C.V.', 'CAB950101ABC', 'Av. Principal 123, Col. Centro, Ciudad', '5551234567', 'cliente1@email.com', 'commercial', 'active'),
(NULL, 'Industrias XYZ S.A.', 'IXY940615XYZ', 'Calle Industrial 456, Parque Industrial', '5559876543', 'contacto@xyz.com', 'industrial', 'active'),
(NULL, 'Juan Ramírez', 'RAJM850320HDF', 'Calle Residencial 789, Col. Jardines', '5556543210', 'juan.ramirez@email.com', 'residential', 'active'),
(NULL, 'Hotel Gran Plaza', 'HGP980520HPL', 'Blvd. Turístico 321, Zona Hotelera', '5557896541', 'info@hotelgranplaza.com', 'commercial', 'active');

-- Unidades (pipas)
INSERT INTO units (plate_number, capacity_liters, brand, model, year, serial_number, status) VALUES
('ABC-123-X', 10000, 'Kenworth', 'T800', 2020, 'KW2020T800001', 'active'),
('DEF-456-Y', 15000, 'Freightliner', 'M2', 2019, 'FL2019M2002', 'active'),
('GHI-789-Z', 12000, 'International', '4300', 2021, 'INT20214300003', 'active'),
('JKL-012-W', 10000, 'Kenworth', 'T370', 2018, 'KW2018T370004', 'maintenance');

-- Choferes
INSERT INTO drivers (full_name, license_number, license_expiry, phone, status) VALUES
('Pedro Sánchez García', 'LIC123456789', '2026-12-31', '5551111111', 'active'),
('Luis Hernández Torres', 'LIC987654321', '2025-06-30', '5552222222', 'active'),
('Miguel Ángel Flores', 'LIC456789123', '2027-03-15', '5553333333', 'active'),
('José Antonio Ruiz', 'LIC789123456', '2024-12-31', '5554444444', 'active');

-- Asignación de unidades a choferes
INSERT INTO driver_unit_assignments (driver_id, unit_id, assigned_date, end_date) VALUES
(1, 1, '2024-01-01', NULL),
(2, 2, '2024-01-01', NULL),
(3, 3, '2024-01-01', NULL),
(4, 4, '2024-01-01', NULL);

-- Registros de acceso
INSERT INTO access_logs (entry_datetime, exit_datetime, driver_id, unit_id, client_id, liters_supplied, ticket_code, status) VALUES
('2024-10-27 08:00:00', '2024-10-27 09:30:00', 1, 1, 1, 10000, 'TKT20241027001', 'completed'),
('2024-10-27 10:00:00', '2024-10-27 11:45:00', 2, 2, 2, 15000, 'TKT20241027002', 'completed'),
('2024-10-27 13:00:00', '2024-10-27 14:20:00', 3, 3, 3, 12000, 'TKT20241027003', 'completed'),
('2024-10-28 08:30:00', NULL, 1, 1, 4, NULL, 'TKT20241028004', 'in_progress');

-- Transacciones
INSERT INTO transactions (access_log_id, client_id, total_amount, liters_supplied, price_per_liter, payment_method, payment_status, transaction_date) VALUES
(1, 1, 50000.00, 10000, 5.00, 'bank_transfer', 'paid', '2024-10-27 09:30:00'),
(2, 2, 67500.00, 15000, 4.50, 'voucher', 'paid', '2024-10-27 11:45:00'),
(3, 3, 72000.00, 12000, 6.00, 'cash', 'paid', '2024-10-27 14:20:00');

-- Historial de mantenimiento
INSERT INTO maintenance_history (unit_id, maintenance_date, description, cost, performed_by) VALUES
(4, '2024-10-25', 'Cambio de aceite y filtros', 2500.00, 'Taller Mecánico Central'),
(1, '2024-10-20', 'Revisión general de frenos', 3500.00, 'Taller Mecánico Central'),
(2, '2024-10-15', 'Alineación y balanceo', 1800.00, 'Taller de Llantas Express');
