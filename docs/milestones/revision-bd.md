# Revisión del modelo relacional propuesto vs modelo de dominio v2

Este documento registra las observaciones hechas sobre el primer borrador de la BD propuesta y las decisiones tomadas para alinearlo con el modelo de dominio v2 acordado.

## Contexto

El modelo relacional inicial fue propuesto por un compañero del equipo. Presenta un buen punto de partida: cubre las entidades core (USUARIOS, FINCAS, ANIMALES, PESAJES, FOTOS) con las cardinalidades correctas y marca como UNIQUE los identificadores que corresponden (`AreteSENASA`, `Correo`).

Sin embargo, contrastándolo contra el modelo de dominio v2 que consolida todas las historias de usuario aprobadas, se identifican vacíos que conviene corregir antes de iniciar las migraciones, porque arrancar con una BD incompleta y migrarla en sprints posteriores tiene un costo mucho mayor.

## Observaciones

### 1. Faltan entidades del dominio

El modelo de dominio define las siguientes entidades que no aparecen en el borrador:

- **`TransferenciaAnimal`** — flujo de venta entre ganaderos (estados PENDIENTE / ACEPTADA / RECHAZADA). Es funcionalidad core del Sprint 3.
- **`RecordatorioPesaje`** — historia de usuario contemplada para alertar al ganadero cuándo volver a pesar.
- **`Raza`** — necesaria para parametrizar la fórmula zootécnica por raza.
- **`CorreccionPeso`** — auditoría de las correcciones manuales sobre estimaciones de IA.

### 2. Pesaje incompleto

La tabla PESAJES del borrador solo tiene `Fecha`, `PesoEstimado`, `PesoCorregido` y `Metodo`. El modelo de dominio v2 requiere:

- `RangoConfianzaKg` — intervalo +/- que devuelve el servicio de IA
- `FueCorregido` — boolean explícito
- `ModeloIAVersion`, `TiempoProcesamientoSeg`, `EstadoProcesamiento` — trazabilidad para defensa académica
- `FormulaZootecnica`, `EsOffline` — campos del PesajeManual
- `PerimetroToracicoCm`, `LargoCuerpoCm`, `FechaMedicion` — `MedidaCorporal` (ValueObject)

El modelo de dominio define `Pesaje` como clase abstracta con dos subclases (`PesajeIA`, `PesajeManual`). Se implementa con herencia de tipo `single-table` usando un campo discriminator `tipo`.

### 3. Estados de Animal incompletos

El borrador define `Estado: Activo, Vendido, Fallecido`. El modelo de dominio v2 define cinco estados:

- `ACTIVO`
- `INACTIVO_VENDIDO`
- `INACTIVO_MUERTO`
- `INACTIVO_TRASLADO`
- `INACTIVO_TRANSFERIDO`

Además se requieren `motivo_inactivacion` y `fecha_inactivacion` para auditoría.

### 4. Solapamiento entre `COMPARTIR_ANIMALES` y `VETERINARIO_FINCA`

El borrador tiene dos tablas para acceso compartido:

- `VETERINARIO_FINCA` (acceso a nivel de finca)
- `COMPARTIR_ANIMALES` (acceso a nivel de animal individual)

El modelo de dominio v2 define un único concepto: `AccesoCompartido` a nivel de finca, con campos `fechaInicio`, `fechaFin`, `tipoAcceso` (lectura / edición), `activo`. La granularidad por animal individual no estaba contemplada en las historias de usuario y agrega complejidad innecesaria.

**Decisión:** se mantiene una sola tabla `acceso_compartido` a nivel finca y se elimina `compartir_animales`.

### 5. FOTOS sin validación

El borrador solo guarda `UrlImagen` y FK a Pesaje. El modelo de dominio v2 incluye:

- `FechaCaptura` — para auditoría temporal
- `Resolucion` — para validación de calidad
- `EsValida` — boolean que indica si pasó la validación de nitidez y detección de bovino
- `MotivoInvalidez` — texto explicativo cuando `EsValida = false`

### 6. REPORTES sin información de almacenamiento

El borrador tiene `Tipo` y `FechaGeneracion`. Falta:

- `RutaArchivo` — URL en DigitalOcean Spaces donde queda el PDF/Excel generado
- `Parametros` — JSON con los filtros aplicados (rango fechas, finca, etc.)
- `FincaId` — FK opcional para reportes filtrados por finca

### 7. Falta auditoría de Usuario

El modelo de dominio v2 requiere `fechaRegistro` y `ultimoAcceso` en User. El borrador no los tiene. También falta `estado` (`activo`, `inactivo`, `bloqueado`).

### 8. Detalles de Laravel

- Falta `created_at` / `updated_at` en todas las tablas (Laravel los agrega con `$table->timestamps()`)
- Falta tabla `personal_access_tokens` requerida por Sanctum
- Faltan tablas `cache`, `jobs`, `sessions` que Laravel 11 espera por defecto

## Resolución

Las migraciones del Sprint 1 (`database/migrations/`) implementan la versión corregida que contempla todas las observaciones anteriores. Se conservan los aciertos del borrador original (cardinalidades, UNIQUEs en identificadores) y se completan los faltantes.

El esquema final tiene 13 tablas de dominio + 4 tablas de soporte (sessions, cache, jobs, personal_access_tokens):

| # | Tabla | Propósito |
|---|---|---|
| 1 | `users` | Usuarios del sistema (propietarios, veterinarios, administradores) |
| 2 | `fincas` | Fincas registradas por propietarios |
| 3 | `rebanos` | Agrupaciones de animales dentro de una finca |
| 4 | `razas` | Catálogo de razas con parámetros morfológicos |
| 5 | `animales` | Bovinos individuales identificados por arete SENASA |
| 6 | `pesajes` | Pesajes (IA o manuales) con campos discriminador `tipo` |
| 7 | `fotografias` | Fotos asociadas a un pesaje IA |
| 8 | `correcciones_peso` | Historial de correcciones manuales |
| 9 | `acceso_compartido` | Veterinarios autorizados a consultar fincas |
| 10 | `transferencias_animal` | Solicitudes de transferencia de animales |
| 11 | `recordatorios_pesaje` | Recordatorios programados |
| 12 | `reportes` | Metadatos de reportes generados |
| 13 | `notificaciones` | Notificaciones in-app |
