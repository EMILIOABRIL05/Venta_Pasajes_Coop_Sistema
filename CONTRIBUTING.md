Aquí tienes el contenido exacto que debes copiar y pegar en un archivo llamado `CONTRIBUTING.md` en la raíz de tu repositorio. 

Este archivo será la "Biblia" técnica para tu equipo. Define paso a paso qué comandos ejecutar, cómo nombrar las ramas y la regla estricta de los commits para que todos terminen con una buena calificación y sin conflictos en el código.

---

# 🛠️ Guía de Contribución y GitFlow - Sistema Cooperativa Ambato

¡Bienvenidos al equipo de desarrollo! Para mantener el código limpio, evitar conflictos y asegurar que **todos tengan una cantidad equitativa de commits**, trabajaremos bajo una metodología estricta basada en **GitFlow** y **Commits Atómicos**.

Por favor, lee este documento antes de escribir tu primera línea de código.

---

## 🌳 1. Estructura de Ramas (GitFlow)

En este repositorio existen dos ramas principales que **NUNCA deben recibir commits directos**:
*   🔴 **`main`**: Es la rama de producción. Solo el Líder del Proyecto hace *merge* aquí cuando se lanza una nueva versión (Tag v0.1.0, v0.5.0, etc.). **ESTÁ PROHIBIDO TOCARLA.**
*   🟡 **`develop`**: Es la rama de integración. Aquí se une el trabajo de los 6 desarrolladores. Todo tu código debe apuntar a esta rama mediante un Pull Request (PR).

### Ramas de Trabajo (Tú creas estas)
Cada vez que inicies una tarea, debes crear una rama temporal derivada de `develop`:
*   🟢 **`feature/nombre-de-la-tarea`**: Para desarrollar algo nuevo. *(Ej: `feature/crud-buses`, `feature/venta-ventanilla`)*
*   🟠 **`fix/nombre-del-error`**: Para arreglar un bug. *(Ej: `fix/error-descuento-tercera-edad`)*

---

## 💻 2. El Flujo de Trabajo Diario (Comandos Git)

Sigue estos pasos EXACTAMENTE en este orden cada vez que vayas a programar:

### Paso A: Sincronizarte con el equipo (Al iniciar tu día)
Antes de programar, asegúrate de tener lo último que aprobó el Líder.
```bash
git checkout develop
git pull origin develop
```

### Paso B: Crear tu rama de trabajo
Crea tu rama basada en develop. Usa minúsculas y guiones.
```bash
git checkout -b feature/mi-modulo-asignado
```

### Paso C: Escribir código y hacer COMMITS ATÓMICOS
**REGLA DE ORO:** Un commit por archivo o acción lógica. Prohibido hacer un solo commit al final del día.

*Ejemplo del flujo de un módulo:*
```bash
git add database/migrations/xxx_create_buses_table.php
git commit -m "feat: crea migracion de tabla buses"

git add app/Models/Bus.php
git commit -m "feat: crea modelo Bus con relaciones y softdeletes"

git add app/Livewire/Admin/BusComponent.php
git commit -m "feat: crea componente livewire para backend de buses"

git add resources/views/livewire/admin/bus-component.blade.php
git commit -m "ui: diseña formulario tailwind para crear bus"
```

### Paso D: Subir tu rama a GitHub
Cuando termines tu tarea (o al final del día para respaldar):
```bash
git push origin feature/mi-modulo-asignado
```

### Paso E: Crear el Pull Request (PR)
1. Ve a GitHub.com.
2. Haz clic en "Compare & pull request".
3. **IMPORTANTE:** Asegúrate de que la flecha apunte a `develop` (base: `develop` <- compare: `feature/mi-modulo-asignado`).
4. Avisa al Líder de Proyecto para que revise y apruebe tu código. **Nadie puede auto-aprobarse un PR.**

---

## 📝 3. Convenciones de Código y Base de Datos

Si el Líder detecta que no sigues estas reglas, rechazará tu Pull Request:

1.  **Nomenclatura DB:** 
    *   Modelos en Singular y PascalCase: `HojaRuta`, `Boleto`.
    *   Tablas en Plural y snake_case: `hojas_ruta`, `boletos`.
2.  **Seguridad de Boletos:** La tabla `boletos` DEBE usar **UUID** (`$table->uuid('id')->primary();`) en lugar de IDs numéricos.
3.  **No Borrar Datos:** Tablas principales (Buses, Rutas, Usuarios) deben usar obligatoriamente `$table->softDeletes();` en la migración y el trait `SoftDeletes` en el modelo.
4.  **Transacciones:** Todo lo que involucre dinero (crear una venta y boletos) debe ir envuelto en una transacción de base de datos para evitar cobros si falla el sistema.
    ```php
    DB::transaction(function () {
        // Lógica de guardar venta y boletos
    });
    ```

---

## 💬 4. Tipos de Commits Permitidos (Prefijos)
Usa estos prefijos al hacer `git commit -m "..."` para mantener el historial ordenado:
*   `feat:` -> Nueva funcionalidad (Migraciones, modelos, controladores).
*   `ui:` -> Cambios visuales (Tailwind, vistas Blade).
*   `fix:` -> Solución de un error o bug.
*   `refactor:` -> Mejorar código sin añadir funcionalidades nuevas.
*   `docs:` -> Cambios en README, comentarios o este CONTRIBUTING.md.
*   `config:` -> Cambios en rutas, Docker, o configuraciones de Laravel.

---
*Si tienes dudas con un comando o la base de datos se desconfigura, detente y comunícate con el Líder del Proyecto antes de forzar un push (`--force` está estrictamente prohibido).*