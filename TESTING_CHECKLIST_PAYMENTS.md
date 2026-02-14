# Lista de Verificación de Pruebas - Corrección de Pagos por Lote

## Antes de Desplegar

### Prerrequisitos
- [ ] Respaldo de base de datos creado y guardado en ubicación segura
- [ ] Acceso a MySQL con permisos para ejecutar migraciones
- [ ] Acceso al servidor de aplicación
- [ ] Usuario con rol 'admin' o 'supervisor' para pruebas

---

## Durante el Despliegue

### Paso 1: Aplicar Migración
- [ ] Migración `fix_payment_per_batch.sql` ejecutada sin errores
- [ ] Columnas `serie`, `folio_inicio`, `folio_fin` creadas en `voucher_payments`
- [ ] Índice `idx_client_serie_folio` creado
- [ ] Stored procedure `update_voucher_payment_status_per_batch` creado
- [ ] 3 triggers creados (insert, update, delete)

### Paso 2: Actualizar Código
- [ ] Código actualizado desde repositorio
- [ ] Archivos modificados verificados:
  - [ ] app/models/VoucherPayment.php
  - [ ] app/controllers/ReportController.php
  - [ ] app/views/reports/vouchers_summary.php
  - [ ] app/views/reports/financial.php
- [ ] Caché limpiado (si aplica)

---

## Pruebas Post-Despliegue

### Test Suite 1: Funcionalidad Básica

#### Test 1.1: Acceso a Reportes
**Objetivo:** Verificar que los reportes cargan sin errores

- [ ] Acceder a "Reportes → Reporte Financiero"
- [ ] Página carga sin errores PHP
- [ ] Sección "Vales Generados" visible
- [ ] Totales se muestran correctamente
- [ ] Acceder a "Reportes → Resumen de Vales Generados"
- [ ] Tabla de empresas se muestra
- [ ] Columnas incluyen: Empresa, Serie, Rango Folios, Cantidad, etc.

#### Test 1.2: Modal de Registro de Pagos
**Objetivo:** Verificar que el modal muestra información correcta

**Datos de Prueba:**
- Buscar cualquier empresa con al menos un lote pendiente

**Pasos:**
- [ ] Hacer clic en botón "Registrar Pago" de un lote
- [ ] Modal se abre correctamente
- [ ] **Verificar campos visibles:**
  - [ ] Nombre de empresa
  - [ ] **Serie del lote** (ej: "L", "H", "MP")
  - [ ] **Rango de folios** (ej: "1000 - 1039")
  - [ ] Monto pendiente
- [ ] **Verificar campos hidden:**
  - [ ] client_id
  - [ ] serie
  - [ ] folio_inicio
  - [ ] folio_fin
- [ ] Cerrar modal sin registrar pago

---

### Test Suite 2: Registro de Pagos

#### Test 2.1: Pago Parcial en Lote Único
**Objetivo:** Verificar registro de pago parcial

**Datos de Prueba:**
```
Cliente: [Seleccionar uno con 1 lote]
Lote: Serie X, folios A-B
Costo total: $C
```

**Pasos:**
1. [ ] Ir a "Resumen de Vales Generados"
2. [ ] Anotar monto pendiente inicial: $_______
3. [ ] Hacer clic en "Registrar Pago"
4. [ ] Ingresar monto MENOR al pendiente (ej: 50% del total)
   - Monto ingresado: $_______
5. [ ] Seleccionar método de pago
6. [ ] Hacer clic en "Registrar Pago"
7. [ ] **Verificar:**
   - [ ] Mensaje de éxito mostrado
   - [ ] Página recarga automáticamente
   - [ ] Monto pagado actualizado: $_______
   - [ ] Monto pendiente actualizado: $_______
   - [ ] Cálculo correcto: Pendiente inicial - Monto ingresado = Nuevo pendiente
   - [ ] Botón "Registrar Pago" sigue visible (porque aún hay pendiente)

#### Test 2.2: Completar Pago de Lote
**Objetivo:** Verificar que al completar pago el lote se marca como pagado

**Continuar desde Test 2.1:**

**Pasos:**
1. [ ] Anotar nuevo monto pendiente: $_______
2. [ ] Hacer clic en "Registrar Pago" nuevamente
3. [ ] Ingresar el monto restante exacto
   - Monto ingresado: $_______
4. [ ] Hacer clic en "Registrar Pago"
5. [ ] **Verificar:**
   - [ ] Mensaje de éxito
   - [ ] Monto pagado = Costo total del lote
   - [ ] Monto pendiente = $0.00
   - [ ] **Botón "Registrar Pago" ya NO visible**
   - [ ] Estado del lote: PAGADO COMPLETO

---

### Test Suite 3: Múltiples Lotes (CRÍTICO)

#### Test 3.1: Cliente con Múltiples Lotes - Pago a Lote Específico
**Objetivo:** Verificar independencia entre lotes

**Datos de Prueba:**
```
Cliente: [Seleccionar uno con 3+ lotes]
Lote A: Serie X1, folios A1-B1, Costo $C1
Lote B: Serie X2, folios A2-B2, Costo $C2
Lote C: Serie X3, folios A3-B3, Costo $C3
```

**Estado Inicial:**
- [ ] Anotar estado de cada lote:
  ```
  Lote A: Pagado $______, Pendiente $______
  Lote B: Pagado $______, Pendiente $______
  Lote C: Pagado $______, Pendiente $______
  ```

**Acción: Pagar 100% del Lote B**

**Pasos:**
1. [ ] Buscar el Lote B en la tabla
2. [ ] Hacer clic en "Registrar Pago" del **Lote B específicamente**
3. [ ] Verificar en modal:
   - [ ] Serie mostrada = Serie de Lote B
   - [ ] Rango folios = Folios de Lote B
   - [ ] Monto pendiente = Costo de Lote B
4. [ ] Ingresar monto completo de Lote B
5. [ ] Registrar pago
6. [ ] **Verificar INDEPENDENCIA:**
   - [ ] **Lote A NO cambió** (mismo monto pagado y pendiente)
   - [ ] **Lote B ahora PAGADO** (pendiente = $0)
   - [ ] **Lote C NO cambió** (mismo monto pagado y pendiente)

**✅ Este es el test MÁS IMPORTANTE - si pasa, la corrección funciona correctamente**

#### Test 3.2: Pagos Parciales en Múltiples Lotes
**Objetivo:** Verificar pagos parciales independientes

**Continuar desde Test 3.1:**

**Acción: Registrar pagos parciales en Lote A y Lote C**

**Pasos:**
1. [ ] Registrar pago parcial (50%) en Lote A
2. [ ] **Verificar:**
   - [ ] Lote A actualizado correctamente
   - [ ] Lote B sigue PAGADO (sin cambios)
   - [ ] Lote C sin cambios
3. [ ] Registrar pago parcial (30%) en Lote C
4. [ ] **Verificar:**
   - [ ] Lote A sin cambios desde paso anterior
   - [ ] Lote B sigue PAGADO
   - [ ] Lote C actualizado correctamente

**Estado Final Esperado:**
```
Lote A: Pagado ~50%, Pendiente ~50% (PARCIAL)
Lote B: Pagado 100%, Pendiente $0 (COMPLETO)
Lote C: Pagado ~30%, Pendiente ~70% (PARCIAL)
```

---

### Test Suite 4: Totales en Reporte Financiero

#### Test 4.1: Pagos Parciales en Totales
**Objetivo:** Verificar que pagos parciales se incluyen en totales

**Prerequisito:** Haber completado Test Suite 3 (múltiples lotes con pagos parciales)

**Pasos:**
1. [ ] Ir a "Reportes → Reporte Financiero"
2. [ ] Seleccionar rango de fechas que incluya los pagos de prueba
3. [ ] En sección "Vales Generados" verificar:
   - [ ] **"Pagados"**: Muestra vales completamente pagados
   - [ ] **"Pagos Parciales"**: Sección VISIBLE ← **NUEVO**
   - [ ] **"Pagos Parciales"**: Monto > $0 (suma de pagos parciales de Lote A y C)
   - [ ] **"Pendientes"**: Muestra vales aún pendientes
   - [ ] **"Total"**: Suma correcta
4. [ ] Verificar "Total Ingresos" en card superior:
   - [ ] Incluye transacciones
   - [ ] Incluye vales pagados
   - [ ] **Incluye pagos parciales** ← Verificar manualmente

**Cálculo Manual de Verificación:**
```
Total Ingresos Esperado = 
    [Transacciones efectivo/transferencia]
  + [Vales marcados como 'paid']
  + [Suma de pagos parciales registrados]
  
Comparar con Total Ingresos mostrado en reporte
¿Coincide? [ ] Sí [ ] No
```

#### Test 4.2: Detalle por Empresa
**Objetivo:** Verificar que cada lote muestra montos correctos

**Pasos:**
1. [ ] En Reporte Financiero, scroll a "Resumen de Vales por Empresa"
2. [ ] Buscar el cliente usado en Test Suite 3
3. [ ] **Verificar cada lote muestra:**
   - [ ] Serie correcta
   - [ ] Rango de folios correcto
   - [ ] Monto pagado correcto (principal + registrado)
   - [ ] Monto pendiente correcto
   - [ ] Si hay pago registrado, muestra: "+ $XX.XX registrado"
   - [ ] Si hay pago registrado, pendiente original tachado

---

### Test Suite 5: Exportación de Reportes

#### Test 5.1: Exportar a Excel
- [ ] En "Resumen de Vales Generados" hacer clic en "Excel"
- [ ] Archivo CSV se descarga
- [ ] Abrir en Excel/LibreOffice
- [ ] **Verificar columnas incluyen:**
  - [ ] Empresa
  - [ ] Serie
  - [ ] Rango Folios
  - [ ] Montos pagado y pendiente correctos

#### Test 5.2: Exportar a PDF
- [ ] En "Resumen de Vales Generados" hacer clic en "PDF"
- [ ] Archivo PDF se abre/descarga
- [ ] Formato es legible
- [ ] Datos coinciden con la vista en pantalla

---

### Test Suite 6: Validaciones y Seguridad

#### Test 6.1: Validación de Monto
**Objetivo:** Verificar que no se puede pagar más del pendiente

**Pasos:**
1. [ ] Abrir modal de pago de un lote
2. [ ] Intentar ingresar monto MAYOR al pendiente
3. [ ] Hacer clic en "Registrar Pago"
4. [ ] **Verificar:**
   - [ ] Mensaje de error mostrado
   - [ ] Pago NO se registra
   - [ ] Modal sigue abierto

#### Test 6.2: Validación de Monto Negativo/Cero
**Pasos:**
1. [ ] Intentar ingresar $0
2. [ ] Hacer clic en "Registrar Pago"
3. [ ] **Verificar:** Error mostrado
4. [ ] Intentar ingresar monto negativo (-$100)
5. [ ] **Verificar:** No permite o muestra error

#### Test 6.3: Permisos de Usuario
**Objetivo:** Solo admin/supervisor pueden registrar pagos

**Pasos:**
1. [ ] Con usuario 'admin': Botón "Registrar Pago" visible
2. [ ] Con usuario 'supervisor': Botón "Registrar Pago" visible
3. [ ] Con usuario 'operator' (si existe): Botón NO visible o muestra error
4. [ ] Sin autenticación: Redirecciona a login

---

### Test Suite 7: Casos Extremos

#### Test 7.1: Lote Sin Pendiente
**Objetivo:** No mostrar botón de pago si lote ya pagado

**Pasos:**
1. [ ] Buscar lote completamente pagado (pendiente = $0)
2. [ ] **Verificar:**
   - [ ] Botón "Registrar Pago" NO visible
   - [ ] Solo botón "Ver Detalle" visible

#### Test 7.2: Múltiples Pagos Seguidos
**Objetivo:** Verificar que se pueden registrar múltiples pagos seguidos

**Pasos:**
1. [ ] Registrar pago parcial 1 de $X
2. [ ] Inmediatamente registrar pago parcial 2 de $Y
3. [ ] Inmediatamente registrar pago parcial 3 de $Z
4. [ ] **Verificar:**
   - [ ] Los 3 pagos se registraron
   - [ ] Monto pagado = X + Y + Z
   - [ ] Pendiente se actualizó correctamente

#### Test 7.3: Pago Exacto del Pendiente
**Objetivo:** Pago que completa exactamente el pendiente

**Pasos:**
1. [ ] Lote con pendiente de $123.45
2. [ ] Registrar pago exacto de $123.45
3. [ ] **Verificar:**
   - [ ] Pendiente = $0.00
   - [ ] Lote marcado como PAGADO
   - [ ] Botón de pago desaparece

---

## Verificación Final

### Checklist de Aceptación

**Funcionalidad Core:**
- [ ] Pagos se registran correctamente
- [ ] Cada lote mantiene seguimiento independiente
- [ ] Pagos parciales funcionan
- [ ] Pagos completos marcan lote como pagado

**Interfaz de Usuario:**
- [ ] Modal muestra información de lote claramente
- [ ] Mensajes de éxito/error son claros
- [ ] Botones aparecen/desaparecen según estado

**Reportes:**
- [ ] Reporte Financiero muestra pagos parciales
- [ ] Totales incluyen pagos parciales
- [ ] Resumen por empresa es preciso
- [ ] Exportaciones funcionan

**Seguridad y Validaciones:**
- [ ] No se puede pagar más del pendiente
- [ ] Solo usuarios autorizados pueden pagar
- [ ] Montos negativos/cero rechazados

**Datos e Integridad:**
- [ ] No se mezclan pagos entre lotes
- [ ] Cálculos son precisos
- [ ] Sin errores en logs de PHP
- [ ] Sin errores en logs de MySQL

---

## Registro de Pruebas

**Fecha de Prueba:** _______________  
**Probado por:** _______________  
**Ambiente:** [ ] Desarrollo [ ] Staging [ ] Producción

**Resultado General:**
- [ ] ✅ Todas las pruebas pasaron
- [ ] ⚠️ Algunas pruebas fallaron (detallar abajo)
- [ ] ❌ Pruebas fallaron críticamente (revertir)

**Pruebas Fallidas (si aplica):**
```
Test ID: __________
Descripción: ____________________________________
Resultado Esperado: _____________________________
Resultado Obtenido: _____________________________
Severidad: [ ] Crítica [ ] Alta [ ] Media [ ] Baja
```

**Observaciones Adicionales:**
```
_________________________________________________
_________________________________________________
_________________________________________________
```

**Aprobado para Producción:**
- [ ] Sí (todas las pruebas pasaron)
- [ ] No (requiere correcciones)

**Firma:** _______________  
**Fecha:** _______________

---

## Anexo: Datos de Prueba Recomendados

### Escenario Ideal para Pruebas Completas

**Cliente: "Hotel Gran Plaza"**
```
Lote 1: Serie "L", folios 1000-1039, 40 vales × $2000 = $80,000
Lote 2: Serie "H", folios 0010-0109, 100 vales × $2000 = $200,000
Lote 3: Serie "M", folios 1-50, 50 vales × $1500 = $75,000
```

**Secuencia de Pagos para Probar:**
1. Pago parcial Lote 1: $40,000 (50%)
2. Pago parcial Lote 2: $100,000 (50%)
3. Pago completo Lote 3: $75,000 (100%)
4. Pago parcial Lote 1: $40,000 (completar)

**Resultado Final Esperado:**
```
Lote 1: PAGADO ($80,000)
Lote 2: PARCIAL ($100,000 pagado, $100,000 pendiente)
Lote 3: PAGADO ($75,000)

Total Pagado: $255,000
Total Pendiente: $100,000
```

---

**FIN DE LA LISTA DE VERIFICACIÓN**
