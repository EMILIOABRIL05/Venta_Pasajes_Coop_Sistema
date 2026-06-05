---
name: qa-sprint-audit
description: 'Audita al final de cada sprint los modelos y migraciones contra intrucciones.md. Usa este skill para revisar nomenclatura, SoftDeletes, UUID y HasRoles, y para emitir el veredicto de cumplimiento o la necesidad de hotfix.'
argument-hint: 'Rol, version del sprint y alcance de auditoria'
---

# Auditoría QA de Sprint

## Cuándo usar
Usa este skill cuando necesites auditar el cierre de una versión de sprint, especialmente en versiones impares como v0.1.0 o v0.3.0, para validar si el código cumple estrictamente con `intrucciones.md`.

## Objetivo
Revisar los modelos en `app/Models` y las migraciones en `database/migrations`, cruzando cada hallazgo con las reglas de arquitectura definidas en `intrucciones.md`.

## Gobernanza por versión
La profundidad de la auditoría cambia según la versión auditada:
- Para `v0.1.0`, la revisión es estrictamente estructural y se limita a Modelos y Migraciones.
- Para versiones mayores o iguales a `v0.3.0`, la revisión se amplía de forma obligatoria a Seeders y Middlewares de rutas Livewire.
- Si la versión auditada no se indica de forma explícita, exige precisión antes de emitir veredicto.

## Alcance de la auditoría
Verifica como mínimo estos puntos:
- Nomenclatura correcta: modelos en singular PascalCase y tablas en plural snake_case.
- `SoftDeletes` donde sea obligatorio, especialmente en Rutas, Buses, Frecuencias y Usuarios.
- Uso de UUID donde sea obligatorio, especialmente en `Boletos`.
- Presencia y uso correcto de `HasRoles` en los modelos o clases que gestionan roles/permisos.
- Consistencia entre modelos y migraciones: nombres de tablas, claves primarias, claves foráneas, campos obligatorios y convenciones del proyecto.

## Reglas de revisión por nivel
### Nivel base: `v0.1.0`
- Revisa únicamente Modelos y Migraciones.
- Valida Nomenclatura, `SoftDeletes` y UUID.
- No exijas todavía Seeders ni Middlewares, salvo que el código los incluya como parte directa del modelo o la migración evaluada.

### Nivel ampliado: `>= v0.3.0`
- Revisa Modelos, Migraciones, Seeders y Middlewares de protección.
- Verifica la creación de Roles y Permisos en Seeders.
- Confirma que las rutas Livewire estén protegidas con Middlewares compatibles con `HasRoles`.
- Valida que los Seeders incorporen datos reales o dummy de Ecuador cuando correspondan a rutas y frecuencias.
- Si faltan datos de Ecuador en Seeders para rutas o frecuencias, trátalo como hallazgo de arquitectura.

## Procedimiento
1. Identifica la versión que se está auditando y calcula la versión par correspondiente para hotfix si hubiera errores, por ejemplo 0.1.0 -> 0.2.0.
2. Lee `intrucciones.md` como fuente de verdad.
3. Escanea `app/Models` y `database/migrations`.
4. Si la versión es `>= v0.3.0`, amplía el escaneo a Seeders y Middlewares de rutas Livewire.
5. Cruza cada elemento con las reglas de arquitectura y con el nivel de revisión que corresponda a la versión.
6. Clasifica cada hallazgo como correcto o incorrecto, sin asumir información que no esté presente en el código.
7. Emite el veredicto exacto según el escenario que corresponda.

## Criterios de veredicto
### Escenario A: todo correcto
Si todo cumple, responde textualmente:

Todo está correcto, no es necesario crear la versión [versión par]. Puedes continuar con la siguiente fase.

### Escenario B: hay errores
Si encuentras errores, lista los errores específicos de forma clara y detente. Luego haz exactamente esta pregunta obligatoria:

Se han encontrado errores de arquitectura. ¿Quieres que iniciemos la versión [versión par correspondiente] usando un Hotfix para solucionarlos?
La redacción de esa pregunta es estándar y no debe variarse.

## Flujo de corrección si el usuario responde "Sí"
Si el usuario autoriza el hotfix, entrega la corrección en este orden:
1. El comando de inicio: `git flow hotfix start [versión par]`.
2. Los bloques de código con las correcciones exactas.
3. Los comandos de commit atómicos, uno por cambio lógico.
4. El cierre correcto: `git flow hotfix finish [versión par]`.
5. Declara como estándar que la corrección vive en una rama de hotfix/corrección del flujo GitFlow.
6. Declara como estándar que el usuario puede subir esa rama al repositorio, revisarla, hacer merge cuando esté conforme y continuar después con la etiqueta correspondiente.

## Regla de salida
El flujo de salida sigue siendo binario: o el sistema cumple, o se reportan errores y se detiene la auditoría.
No se debe iniciar ningún `git flow hotfix` sin haber emitido primero el veredicto y la pregunta obligatoria.
La corrección debe ejecutarse en una rama de hotfix/corrección, no como cambios aislados fuera del flujo.

## Reglas de calidad
- No inventes cumplimiento: si falta evidencia, trátalo como hallazgo.
- No mezcles el veredicto con explicaciones innecesarias.
- Si hay errores, prioriza claridad, precisión y trazabilidad hacia el archivo o regla afectada.
- Mantén la auditoría enfocada en arquitectura y cumplimiento, no en cambios funcionales ajenos al sprint.
