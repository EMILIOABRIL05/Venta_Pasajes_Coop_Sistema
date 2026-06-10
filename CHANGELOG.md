# Historial de cambios

Todas las modificaciones notables de este proyecto se documentarán en este
archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y
el versionado sigue SemVer adaptado a los sprints del proyecto.

## [Sin publicar]

### Añadido

- Documentación estándar del repositorio: licencia, código de conducta,
  política de seguridad y changelog.

### Cambiado

- Mejora de README y guía de contribución para alinear el repositorio con buenas
  prácticas de GitHub.

### Corregido

- Claridad en enlaces y políticas documentales del proyecto.

## [0.9.0] - 2026-05-11

### Añadido

- App de chofer para lectura de QR y registro de pasajeros a bordo.
- Descarga de boletos PDF con QR desde el historial web.
- Reglas de bloqueo para ventas cuando un viaje se encuentra en curso.

## [0.7.0] - 2026-05-08

### Añadido

- Dashboard administrativo.
- Cierre de caja para oficinistas.
- Flujo web de pagos y comprobantes.
- Paneles de estado para hojas de ruta.

## [0.6.0] - 2026-05-08

### Cambiado

- Fase de estabilización técnica posterior al sprint de ventas y pagos.

## [0.5.0] - 2026-05-04

### Añadido

- Motor de venta en oficina y web.
- Cálculo de descuentos legales.
- Integración de mapa de asientos.
- Flujo de reembolsos.
- Eventos y correos simulados posteriores a compras y creación de usuarios.

## [0.3.0] - 2026-04-30

### Añadido

- CRUD de buses, categorías y frecuencias.
- Hoja de ruta para programación de viajes.
- Buscador interno de ventanilla.
- Buscador público conectado a viajes reales.
- Layout base de boleto físico y digital.

## [0.2.0] - 2026-04-30

### Corregido

- Hotfix de seguridad documentado por el equipo para atender una fuga de llaves.

## [0.1.0] - 2026-04-26

### Añadido

- Proyecto base en Laravel 11.
- Login base, layout maestro y configuración inicial de UI.
- Migraciones y modelos iniciales para usuarios, buses, rutas, frecuencias,
  paradas, ventas, boletos, pasajeros, pagos, reembolsos y validación de QR.
- Instalación de Livewire, Tailwind CSS y Spatie Laravel Permission.
