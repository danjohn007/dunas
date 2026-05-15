-- Soporte para selector de capacidad en Registro Rápido
-- MySQL 5.7 compatible - inserciones idempotentes

INSERT INTO capacity_costs (capacity_liters, cost, description, is_active)
SELECT 5000, 0.00, 'Capacidad predeterminada', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM capacity_costs WHERE capacity_liters = 5000
);

INSERT INTO capacity_costs (capacity_liters, cost, description, is_active)
SELECT 10000, 0.00, 'Capacidad predeterminada', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM capacity_costs WHERE capacity_liters = 10000
);

INSERT INTO capacity_costs (capacity_liters, cost, description, is_active)
SELECT 12000, 0.00, 'Capacidad predeterminada', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM capacity_costs WHERE capacity_liters = 12000
);

INSERT INTO capacity_costs (capacity_liters, cost, description, is_active)
SELECT 15000, 0.00, 'Capacidad predeterminada', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM capacity_costs WHERE capacity_liters = 15000
);

INSERT INTO capacity_costs (capacity_liters, cost, description, is_active)
SELECT 20000, 0.00, 'Capacidad predeterminada', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM capacity_costs WHERE capacity_liters = 20000
);
