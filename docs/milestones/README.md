# Milestones del proyecto BovWeight CR — API

Este documento describe el alcance de cada sprint del proyecto. Es la versión narrativa de los milestones que aparecen en GitHub Issues.

## Sprint 1 — Fundamentos y autenticación

**Duración:** 3 semanas (mayo 2026)
**Objetivo:** Tener un esqueleto de API funcional con persistencia, autenticación y la estructura del dominio lista para que los siguientes sprints solo agreguen casos de uso.

### Entregables

- Proyecto Laravel 11 inicializado con conexión a MySQL local
- Migraciones de las 13 tablas del modelo de dominio v2
- Autenticación con Sanctum (login, logout, me)
- Estructura de carpetas: Controllers, Models, Requests, Resources, Services, Enums
- Configuración de CORS para Ionic
- Endpoint `GET /api/health` con verificación de BD
- README con instrucciones de setup
- `.env.example` documentado
- Templates de issues y conventional commits
- Código pusheado a `dev`

### Criterio de aceptación del sprint

- Cualquier integrante del equipo puede clonar el repo y correr el proyecto siguiendo el README
- Los 3 endpoints de auth están funcionando contra la BD local
- El health-check responde `200 ok` cuando la BD está arriba y `503 degraded` cuando no

---

## Sprint 2 — Estimación de peso por IA

**Duración:** 3 semanas (junio 2026)
**Objetivo:** Implementar el corazón funcional del sistema: el flujo end-to-end de capturar una foto y obtener un peso estimado.

### Entregables

- CRUDs completos de `Finca`, `Rebano`, `Animal`
- Endpoint `POST /api/animales/{id}/pesajes/ia` que orquesta:
  1. Subir foto a DigitalOcean Spaces
  2. Llamar al microservicio Flask con la URL de la foto
  3. Persistir el `Pesaje` con peso estimado, rango de confianza y medidas morfométricas
- Endpoint `POST /api/animales/{id}/pesajes/manual` para pesaje sin IA
- Endpoint `PATCH /api/pesajes/{id}/correccion` para registrar corrección de peso
- Validación de fotografías (resolución mínima, formato)
- Manejo de errores del servicio de IA (timeout, fallido, animal no detectado)
- Tests unitarios de los services
- Tests de feature de los endpoints principales

### Dependencias externas

- Microservicio Flask de IA listo y desplegado
- Bucket en DigitalOcean Spaces creado y credenciales configuradas

---

## Sprint 3 — Colaboración, reportes y entrega

**Duración:** 3 semanas (junio-julio 2026)
**Objetivo:** Cerrar funcionalidades de colaboración, reportería, notificaciones y dejar el sistema desplegado en DigitalOcean.

### Entregables

- Acceso compartido finca-veterinario (otorgar, revocar, listar)
- Transferencias de animales (solicitar, aceptar, rechazar)
- Generación de reportes en PDF y Excel
- Sistema de recordatorios de pesaje con cron
- Notificaciones in-app (consultar, marcar leídas)
- Despliegue del backend en droplet de DigitalOcean
- Pipeline de CI básico (GitHub Actions: tests + lint en cada PR)
- Documentación API con Swagger / OpenAPI
- Demo end-to-end al cliente Don Iván Chavarría

### Criterio de aceptación del sprint

- Sistema accesible desde la app móvil en producción
- Demo en finca con al menos 5 animales pesados con báscula vs estimación de IA
- Reporte exportado y entregado al stakeholder
