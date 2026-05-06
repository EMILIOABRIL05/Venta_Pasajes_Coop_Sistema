Incluye el contexto, la arquitectura, los colores y las tareas separadas por
Versiones (Sprints/Timeboxes).

💡 NOTA PARA EL EQUIPO (CÓMO USAR LA IA CON ESTE DOCUMENTO): Si copias la Parte 1
(Contexto) + La Tarea Específica de tu Versión y se la pegas a ChatGPT o Claude,
la IA tendrá el contexto perfecto para generarte el código exacto de tu módulo
sin dañar el resto del sistema.

🚌 PROYECTO: SISTEMA DE VENTA DE PASAJES - COOPERATIVA AMBATO

PARTE 1: CONTEXTO GENERAL Y ARQUITECTURA (Para alimentar a la IA)

Visión General: Desarrollo de una plataforma web (Monolito Modular) para la
gestión operativa y venta de boletos de la Cooperativa de Transportes Ambato
(Ecuador). El sistema gestionará rutas reales (ej. Ambato-Quito,
Ambato-Guayaquil), hojas de ruta, venta en ventanilla, venta web, y una interfaz
móvil para choferes. NO es multi-tenant, todo el sistema pertenece a una sola
empresa.

Stack Tecnológico:

  - Backend: Laravel 11.
  - Frontend: Livewire 3 + Alpine.js + Tailwind CSS.
  - Base de Datos: PostgreSQL.
  - Infraestructura: Docker (Obligatorio a partir de la v0.9.0 - docker-compose
    con App y DB).

Arquitectura de Datos (Reglas Estrictas):

  - Modelos/Tablas: Inglés prohibido. Modelos en Singular PascalCase (Boleto),
    Tablas en Plural snake_case (boletos).
  - Boletos: Obligatorio usar UUID como llave primaria para los boletos (por
    seguridad al escanear QR).
  - Borrado: SoftDeletes obligatorio en Rutas, Buses, Frecuencias y Usuarios.
  - Transacciones: Toda venta se guarda usando DB::transaction.
  - Datos Ecuador: Cédulas de 10 dígitos. Descuentos por ley (50% tercera edad
    +65 años, niños, discapacidad).

Sistema de Diseño y Colores (UI):

  - Color Principal (Marca): Azul Rey Marino #003366 (Header, botones
    primarios).
  - Color Secundario (Acentos): Rojo Ambato #CC0000 (Alertas, botones de
    cancelación, badges).
  - Color Fondo: Gris claro #F3F4F6 (Tailwind gray-100).
  - Texto Principal: Gris Oscuro #1F2937 (Tailwind gray-800).

Regla de Commits (GitFlow): Obligatorio hacer Commits Atómicos. No se sube un
módulo entero de golpe. Se hace un commit por Migración, otro por Modelo, otro
por Vista. Todos deben promediar mínimo 50 commits al final.

PARTE 2: DISTRIBUCIÓN POR VERSIONES (SPRINTS)

**Política de Versionado (SemVer + Regla Par/Impar):**
Para mantener un estándar profesional, usamos "Semantic Versioning" (`MAJOR.MINOR.PATCH`) adaptado a ciclos de estabilización, muy común en plataformas como Node.js:
- **MAJOR (1.x.x):** Lanzamientos estables para producción (ej. `v1.0.0`).
- **MINOR IMPAR (0.1.0, 0.3.0, 0.5.0...):** Desarrollo de Sprints. Aquí se crean las nuevas funcionalidades.
- **MINOR PAR (0.2.0, 0.4.0, 0.6.0...):** Fases de estabilización profundas y refactorización técnica. Si un sprint impar (ej. `0.3.0`) resulta muy estable, la versión par siguiente (`0.4.0`) simplemente se omite para ganar tiempo.
- **PATCH (0.x.y):** Parches rápidos de seguridad o corrección de bugs menores (ej. `v0.1.1`).
  *Nota del Líder sobre la v0.2.0:* Excepcionalmente, el hotfix de la fuga de keys se etiquetó como `v0.2.0` aprovechando el espacio de estabilización (aunque idealmente era un parche `v0.1.1`). De ahora en adelante, los bugs menores usarán el tercer dígito (PATCH), reservando las versiones pares (como `v0.6.0`) exclusivamente si necesitamos detenernos a refactorizar todo un sprint.

Los plazos son estrictos. Cada entrega de versión se compila en la rama develop
y es revisada por el Estudiante 1 (Líder). 

🏷️ VERSIÓN 0.1.0 - SPRINT 1: Bases y Estructura

Timebox: Desde hoy hasta el 26 de Abril. Objetivo: Tener el proyecto creado y
todas las tablas en la Base de Datos.

  - Estudiante 1 (Líder): Crea proyecto Laravel 11, instala Livewire/Tailwind.
    Instala spatie/laravel-permission. Crea la plantilla maestra (Layout Blade)
    usando los colores de Cooperativa Ambato. Configura Login base.
  - Estudiante 2 (Catálogos): Crea Migraciones y Modelos (con SoftDeletes) para:
    Buses, CategoriasBus, Usuarios (Admin, Oficinista, Chofer).
  - Estudiante 3 (Operativa): Crea Migraciones y Modelos para: Rutas,
    Frecuencias y Paradas (con datos dummy de Ecuador en Seeders).
  - Estudiante 4 (Ventanilla): Crea Migraciones y Modelos para: Ventas, Boletos
    (UUID) y Pasajeros.
  - Estudiante 5 (Web): Maqueta la Landing Page pública (Diseño UI con Tailwind)
    y la interfaz vacía del buscador web de pasajes.
  - Estudiante 6 (Móvil/Post): Crea Migraciones y Modelos para: Reembolsos,
    Pagos y tabla pivote Boleto_Validacion (Para el QR).

🏷️ VERSIÓN 0.3.0 - SPRINT 2: Catálogos y Hoja de Ruta

Timebox: 27 de Abril al 30 de Abril. Objetivo: El sistema permite registrar
flota, rutas y armar viajes.

  - Estudiante 1 (Líder): Crea los Seeders maestros de Roles y Permisos. Protege
    las rutas creadas por el equipo con Middlewares de Livewire.
  - Estudiante 2 (Catálogos): Desarrolla el CRUD (Livewire) completo de Buses
    (con subida de foto) y Categorías. Define el JSON/Estructura lógica de
    asientos (ej. {"filas":10, "pasillo":true}).
  - Estudiante 3 (Operativa): Desarrolla CRUD de Frecuencias. Crea la vista
    clave: Armar Hoja de Ruta (Cruzar Frecuencia + Fecha + Bus disponible).
  - Estudiante 4 (Ventanilla): Construye la UI del buscador interno para
    oficinistas (Lista las Hojas de Ruta del día actual listas para vender).
  - Estudiante 5 (Web): Conecta el buscador público web a la BD para que muestre
    viajes reales programados por el Est. 3.
  - Estudiante 6 (Móvil/Post): Instala librería para PDF
    (barryvdh/laravel-dompdf). Diseña el layout del boleto físico/digital,
    dejando espacio para el QR.

🏷️ VERSIÓN 0.5.0 - SPRINT 3: Motor de Ventas y Descuentos

Timebox: 1 de Mayo al 4 de Mayo. (Fase más crítica del proyecto) Objetivo:
Vender un boleto descontando asientos y validando reglas de Ecuador.

  - Estudiante 1 (Líder): Configura Mailables en Laravel y eventos (Listeners)
    simulados para el envío de correos tras compras y creación de usuarios.
  - Estudiante 2 (Catálogos): Programa Traits/Helpers que calculen el 50% de
    descuento basado en la edad (Tercera Edad / Niños). Crea el componente
    visual de Asientos Libres/Ocupados.
  - Estudiante 3 (Operativa): Programa bloqueos lógicos: Un bus no puede estar
    en dos rutas el mismo día a la misma hora. Un bus dañado no aparece en la
    lista.
  - Estudiante 4 (Ventanilla): Desarrolla el núcleo de Venta en Oficina: Integra
    el mapa de asientos del Est. 2, pide datos, aplica descuento del Est. 2, y
    guarda la venta usando DB::transaction.
  - Estudiante 5 (Web): Desarrolla el "Carrito de Compra" web: Mismo mapa de
    asientos pero interfaz pública, pidiendo datos del cliente web.
  - Estudiante 6 (Móvil/Post): Desarrolla el módulo de Reembolsos: Formulario
    público para solicitarlo, panel interno para que el oficinista lo apruebe.

🏷️ VERSIÓN 0.7.0 - SPRINT 4: E-Commerce y Cierre de Caja

Timebox: 5 de Mayo al 8 de Mayo. Objetivo: Web terminada con pagos y
contabilidad de oficinistas.

  - Estudiante 1 (Líder): Diseña y conecta el Dashboard Administrativo (Gráficos
    estadísticos: Ventas por día, rutas más vendidas).
  - Estudiante 2 (Catálogos): Desarrolla CRUD de creación de Cuentas para
    Oficinistas y Choferes.
  - Estudiante 3 (Operativa): Panel de estados: Botones en la Hoja de Ruta para
    cambiar el estado a "En Terminal", "En Curso" (cuando partió el bus) y
    "Finalizada".
  - Estudiante 4 (Ventanilla): Programa el módulo de Cierre de Caja: Vista donde
    el oficinista logueado ve "Total cobrado en mi turno" y exporta un reporte.
  - Estudiante 5 (Web): Integra módulo de pago en web: Opción de subir
    comprobante bancario (imagen) y pasarela simulada. Panel de historial de
    cliente ("Mis Viajes").
  - Estudiante 6 (Móvil/Post): Maquetación de la interfaz móvil (Responsive)
    para el Chofer (Botones grandes, fácil lectura en celular).

🏷️ VERSIÓN 0.9.0 - SPRINT 5: Docker y App Chofer

Timebox: 9 de Mayo al 11 de Mayo. Objetivo: Entorno dockerizado y escaneo de
boletos en el bus.

  - Estudiante 1 (Líder) - OBLIGATORIO: Crea Dockerfile (PHP 8.3/Nginx) y
    docker-compose.yml integrando PostgreSQL. Verifica que el sistema corra con
    un solo comando.
  - Estudiante 2 y 4 (Catálogos y Ventanilla): QA Conjunto: Pruebas de "Race
    Conditions" (Qué pasa si dos personas clican el mismo asiento a la misma vez
    en diferentes PCs).
  - Estudiante 3 (Operativa): Integra regla de negocio: Si el bus de Ambato a
    Quito cambia a estado "En Curso", bloquear la venta desde Ambato en la
    ventanilla del Est 4.
  - Estudiante 5 (Web): Conecta el PDF generado por Est. 6 en el panel web para
    que el cliente descargue su boleto con QR en cualquier momento.
  - Estudiante 6 (Móvil/Post): Check-in QR: Integra librería JS (ej.
    html5-qrcode) vía Alpine.js. El celular del chofer lee el QR del boleto
    web/físico y marca "Pasajero a bordo". Agrega botón de Venta Express (Suma
    $X, resta un asiento libre sin pedir datos).

🏷️ VERSIÓN 1.0.0 - SPRINT 6: Pulido y Entrega Final

Timebox: 12 de Mayo al 13 de Mayo. (Quedan 2 días de colchón para imprevistos).
Objetivo: Estabilidad perfecta y datos reales.

  - Estudiante 1 (Líder): Limpieza final de código. Verifica que todos los
    Sprints estén en main. Genera el Release v1.0.0.
  - Estudiantes 2, 3, 4, 5, 6:
      - Llenar los Seeders con datos 100% reales de Cooperativa Ambato (Destinos
        reales: Baños, Puyo, Tena, Guayaquil).
      - Subir fotografías reales de autobuses.
      - Revisiones de Responsividad (Mobile-first) en vistas clave.


lider : emilio abril 
integrante 2 : manuel cusme
integrante 3 : kevin velasco
intergante 4 : manolo garcia
integrante 5 : anthony semblantes
integrante 6 : luis mirandas