<?php

namespace Database\Seeders;

use App\Models\SolicitudCambio;
use App\Models\ReporteTecnicoCambio;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class SolicitudCambioSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Step 1: Ensure real developers exist ──────────────────────────────
        $team = [
            ['name' => 'Kevin Velasco',     'email' => 'kevin@coop.com'],
            ['name' => 'Emilio Abril',      'email' => 'emilio@coop.com'],
            ['name' => 'Luis Miranda',      'email' => 'luis@coop.com'],
            ['name' => 'Anthony Semblantes','email' => 'anthony@coop.com'],
            ['name' => 'Manuel Cusme',      'email' => 'manuel@coop.com'],
            ['name' => 'Manolo Garcia',     'email' => 'manolo@coop.com'],
            ['name' => 'Oficinista Coop',   'email' => 'oficinista@coop.com'],
            ['name' => 'Chofer Coop',       'email' => 'chofer@coop.com'],
        ];

        $users = collect($team)->map(fn($u) => User::firstOrCreate(
            ['email' => $u['email']],
            [
                'name'     => $u['name'],
                'password' => Hash::make('password123'),
            ]
        ));

        $kevin    = $users->firstWhere('name', 'Kevin Velasco');
        $emilio   = $users->firstWhere('name', 'Emilio Abril');
        $luis     = $users->firstWhere('name', 'Luis Miranda');
        $anthony  = $users->firstWhere('name', 'Anthony Semblantes');
        $manuel   = $users->firstWhere('name', 'Manuel Cusme');
        $manolo   = $users->firstWhere('name', 'Manolo Garcia');
        $oficinista = $users->firstWhere('name', 'Oficinista Coop');
        $chofer   = $users->firstWhere('name', 'Chofer Coop');

        // ─── Step 2: Seed Deployed Issues (estado: Desplegado) ─────────────────
        $deployed = [
            // Manuel Cusme (3)
            [
                'user' => $manuel, 'tipo' => 'Modificación de BD', 'origen' => 'Iniciativa Técnica',
                'modulo' => 'Operativa', 'prioridad' => 'Alta',
                'desc' => 'Eliminar la categoría de buses del sistema ya que fue reemplazada por categorías por asiento.',
                'rama' => 'feature/eliminar-categoria-buses', 'commit' => '0a2fb37', 'issue' => '#301',
                'sandbox' => 'Probados: Operativa (CRUDs), Base de Datos (PostgreSQL)',
                'riesgo' => 'Medio. Requiere migración para eliminar columna y tabla.',
                'rollback' => 'Restaurar tabla categorias_bus desde backup y revertir migración.',
                'dias' => 25,
            ],
            [
                'user' => $manuel, 'tipo' => 'Modificación de BD', 'origen' => 'Requerimiento del Ingeniero/Docente',
                'modulo' => 'Operativa', 'prioridad' => 'Alta',
                'desc' => 'Implementar categoría por asiento en buses con recargo fijo por tipo de asiento (VIP, Preferencial, Estándar).',
                'rama' => 'feature/categoria-por-asiento', 'commit' => '404dd62', 'issue' => '#302',
                'sandbox' => 'Probados: Operativa (CRUDs), Ventanilla (Venta)',
                'riesgo' => 'Alto. Afecta modelo de precios y venta de pasajes.',
                'rollback' => 'Revertir migraciones de categorias_asiento y restaurar precios anteriores.',
                'dias' => 20,
            ],
            [
                'user' => $manuel, 'tipo' => 'Optimización', 'origen' => 'Iniciativa Técnica',
                'modulo' => 'Base de Datos', 'prioridad' => 'Media',
                'desc' => 'Agregar SoftDeletes a entidades de auditoría: reembolsos, pagos, cierres de turno y validaciones de boleto.',
                'rama' => 'feature/soft-deletes-auditoria', 'commit' => '270d78f', 'issue' => '#303',
                'sandbox' => 'Probados: Base de Datos (PostgreSQL), Operativa (CRUDs)',
                'riesgo' => 'Medio. Requiere actualizar queries con withTrashed().',
                'rollback' => 'Eliminar SoftDeletes y restaurar deleted_at como null.',
                'dias' => 15,
            ],

            // Kevin Velasco (3)
            [
                'user' => $kevin, 'tipo' => 'Modificación de BD', 'origen' => 'Requerimiento del Ingeniero/Docente',
                'modulo' => 'Base de Datos', 'prioridad' => 'Alta',
                'desc' => 'Definir precio base por ruta/frecuencia y recargo fijo por categoría de asiento para cálculo de precios.',
                'rama' => 'feature/precio-base-recargo', 'commit' => '7d2162a', 'issue' => '#304',
                'sandbox' => 'Probados: Base de Datos (PostgreSQL), Ventanilla (Venta)',
                'riesgo' => 'Alto. Cambia lógica central de precios del sistema.',
                'rollback' => 'Restaurar precio_base en rutas y eliminar recargo de categorias_asiento.',
                'dias' => 18,
            ],
            [
                'user' => $kevin, 'tipo' => 'Nueva Regla de Negocio', 'origen' => 'Requerimiento del Ingeniero/Docente',
                'modulo' => 'Infraestructura', 'prioridad' => 'Alta',
                'desc' => 'Implementar módulo de Control de Cambios y Trazabilidad DevOps con RBAC para developer/admin vs usuarios.',
                'rama' => 'feature/control-cambios-rbac', 'commit' => '4107167', 'issue' => '#305',
                'sandbox' => 'Probados: Operativa (CRUDs), Livewire, Estilos Tailwind CSS',
                'riesgo' => 'Medio. Nuevo módulo con rutas, controladores y vistas.',
                'rollback' => 'Eliminar rutas, controladores y tablas de solicitudes_cambio.',
                'dias' => 8,
            ],
            [
                'user' => $kevin, 'tipo' => 'Ajuste de Interfaz / UX', 'origen' => 'Retroalimentación del Usuario',
                'modulo' => 'Ventanilla', 'prioridad' => 'Media',
                'desc' => 'Rediseño estético y flujo dinámico con Radio Cards y Alpine.js para el formulario de Solicitudes de Cambio.',
                'rama' => 'feature/ux-tarjetas-solicitud', 'commit' => '2d38342', 'issue' => '#306',
                'sandbox' => 'Probados: Ventanilla (Venta), Componentes Livewire, Estilos Tailwind CSS',
                'riesgo' => 'Bajo. Solo cambios de UI en formulario existente.',
                'rollback' => 'Revertir a dropdowns estándar y eliminar Alpine.js del form.',
                'dias' => 5,
            ],

            // Anthony Semblantes (1)
            [
                'user' => $anthony, 'tipo' => 'Corrección de Error', 'origen' => 'Falla Crítica',
                'modulo' => 'Web Client (Pasajero)', 'prioridad' => 'Alta',
                'desc' => 'Ajustar compra web al precio por categoría de asiento aplicando recargo correctamente en carrito y checkout.',
                'rama' => 'feature/compra-web-categoria', 'commit' => '175bec2', 'issue' => '#307',
                'sandbox' => 'Probados: Web Client (Carrito), Componentes Livewire',
                'riesgo' => 'Alto. Afecta flujo de compra y cobro en producción.',
                'rollback' => 'Revertir lógica de PricingService en CarritoCompra.',
                'dias' => 10,
            ],

            // Luis Miranda (1)
            [
                'user' => $luis, 'tipo' => 'Nueva Regla de Negocio', 'origen' => 'Requerimiento del Ingeniero/Docente',
                'modulo' => 'Web Client (Pasajero)', 'prioridad' => 'Alta',
                'desc' => 'Integrar pasarelas de pago reales: PayPal, tarjetas de crédito/débito y Deuna con código de verificación.',
                'rama' => 'feature/integrar-pagos-reales', 'commit' => '6fb1ce1', 'issue' => '#308',
                'sandbox' => 'Probados: Web Client (Carrito), Base de Datos (PostgreSQL)',
                'riesgo' => 'Alto. Integración con APIs externas y manejo de datos sensibles.',
                'rollback' => 'Deshabilitar rutas de pago y restaurar método de pago por defecto.',
                'dias' => 3,
            ],

            // Manolo Garcia (2)
            [
                'user' => $manolo, 'tipo' => 'Corrección de Error', 'origen' => 'Falla Crítica',
                'modulo' => 'Ventanilla', 'prioridad' => 'Alta',
                'desc' => 'Ajustar venta en ventanilla al precio por categoría de asiento con validación server-side del recargo.',
                'rama' => 'feature/venta-ventanilla-categoria', 'commit' => '6725a53', 'issue' => '#309',
                'sandbox' => 'Probados: Ventanilla (Venta), Base de Datos (PostgreSQL)',
                'riesgo' => 'Alto. Cambia cálculo de precio en punto de venta principal.',
                'rollback' => 'Revertir PricingService en Ventanilla\VentaController.',
                'dias' => 14,
            ],
            [
                'user' => $manolo, 'tipo' => 'Corrección de Error', 'origen' => 'Retroalimentación del Usuario',
                'modulo' => 'Ventanilla', 'prioridad' => 'Media',
                'desc' => 'Ajustar venta express del chofer al precio por categoría de asiento manteniendo flujo rápido de cobro.',
                'rama' => 'feature/venta-express-categoria', 'commit' => '71eccb1', 'issue' => '#310',
                'sandbox' => 'Probados: Ventanilla (Venta), Componentes Livewire',
                'riesgo' => 'Medio. Afecta venta en ruta sin interfaz completa.',
                'rollback' => 'Restaurar precio base sin recargo en PanelPrincipal.',
                'dias' => 11,
            ],

            // Emilio Abril (6)
            [
                'user' => $emilio, 'tipo' => 'Optimización', 'origen' => 'Iniciativa Técnica',
                'modulo' => 'Infraestructura', 'prioridad' => 'Baja',
                'desc' => 'Actualizar documentación README y guías adicionales del proyecto con instrucciones de instalación y uso.',
                'rama' => 'docs/actualizar-readme', 'commit' => '95b44c8', 'issue' => '#311',
                'sandbox' => 'Probados: Estilos Tailwind CSS',
                'riesgo' => 'Bajo. Solo documentación sin impacto en código.',
                'rollback' => 'Revertir cambios en README.md y docs/.',
                'dias' => 22,
            ],
            [
                'user' => $emilio, 'tipo' => 'Modificación de BD', 'origen' => 'Requerimiento del Ingeniero/Docente',
                'modulo' => 'Base de Datos', 'prioridad' => 'Media',
                'desc' => 'Actualizar seeders reales para v1.0.0 con datos de prueba coherentes y relaciones funcionales.',
                'rama' => 'feature/seeders-v1', 'commit' => '93267fb', 'issue' => '#312',
                'sandbox' => 'Probados: Base de Datos (PostgreSQL), Operativa (CRUDs)',
                'riesgo' => 'Medio. Seeders afectan datos de desarrollo y testing.',
                'rollback' => 'Ejecutar migrate:fresh con seeders anteriores.',
                'dias' => 6,
            ],
            [
                'user' => $emilio, 'tipo' => 'Modificación de BD', 'origen' => 'Iniciativa Técnica',
                'modulo' => 'Base de Datos', 'prioridad' => 'Alta',
                'desc' => 'Actualizar seeders y datos base por cambios de categorías de asiento y precios por ruta.',
                'rama' => 'feature/seeders-categorias-precios', 'commit' => 'fcefa81', 'issue' => '#313',
                'sandbox' => 'Probados: Base de Datos (PostgreSQL), Ventanilla (Venta)',
                'riesgo' => 'Alto. Seeders deben ser consistentes con migraciones de precios.',
                'rollback' => 'Restaurar seeders previos y ejecutar migrate:fresh --seed.',
                'dias' => 4,
            ],
            [
                'user' => $emilio, 'tipo' => 'Nueva Regla de Negocio', 'origen' => 'Requerimiento del Ingeniero/Docente',
                'modulo' => 'Infraestructura', 'prioridad' => 'Alta',
                'desc' => 'Dockerizar el sistema completo para v1.0.0 con servicios de PostgreSQL, Nginx, PHP-FPM y Redis.',
                'rama' => 'feature/dockerizar-sistema', 'commit' => '7633563', 'issue' => '#314',
                'sandbox' => 'Probados: Base de Datos (PostgreSQL), Infraestructura',
                'riesgo' => 'Alto. Cambio de infraestructura de despliegue.',
                'rollback' => 'Restaurar configuración de servidor tradicional sin Docker.',
                'dias' => 2,
            ],
            [
                'user' => $emilio, 'tipo' => 'Ajuste de Interfaz / UX', 'origen' => 'Retroalimentación del Usuario',
                'modulo' => 'Ventanilla', 'prioridad' => 'Media',
                'desc' => 'Revisión responsive final de landing page, buscador de pasajes, ventanilla y panel de chofer.',
                'rama' => 'fix/responsive-final', 'commit' => 'd20f6e6', 'issue' => '#315',
                'sandbox' => 'Probados: Web Client (Carrito), Ventanilla (Venta), Componentes Livewire',
                'riesgo' => 'Bajo. Solo cambios de CSS y layouts responsivos.',
                'rollback' => 'Revertir commits de estilos responsive.',
                'dias' => 12,
            ],
            [
                'user' => $emilio, 'tipo' => 'Corrección de Error', 'origen' => 'Falla Crítica',
                'modulo' => 'Infraestructura', 'prioridad' => 'Media',
                'desc' => 'Validar generación de PDF con QR en entorno Docker. El servicio dompdf fallaba con fuentes personalizadas.',
                'rama' => 'fix/validar-qr-docker', 'commit' => 'ba3d440', 'issue' => '#316',
                'sandbox' => 'Probados: Infraestructura, Base de Datos (PostgreSQL)',
                'riesgo' => 'Medio. Problema de compatibilidad de fuentes en contenedor.',
                'rollback' => 'Usar fuentes del sistema en lugar de custom fonts.',
                'dias' => 7,
            ],
        ];

        foreach ($deployed as $data) {
            $created = Carbon::create(2026, 5, 27)->addDays(rand(0, 7))->addHours(rand(0, 23));
            $updated = $created->copy()->addHours(rand(1, 72));

            $solicitud = SolicitudCambio::create([
                'user_id'          => $data['user']->id,
                'evaluador_id'     => $data['user']->id,
                'tipo_solicitud'   => $data['tipo'],
                'origen_solicitud' => $data['origen'],
                'descripcion'      => $data['desc'],
                'prioridad'        => $data['prioridad'],
                'estado_pipeline'  => 'Desplegado',
                'created_at'       => $created,
                'updated_at'       => $updated,
            ]);

            ReporteTecnicoCambio::create([
                'solicitud_cambio_id' => $solicitud->id,
                'developer_id'        => $data['user']->id,
                'modulo_afectado'     => $data['modulo'],
                'github_issue_id'     => $data['issue'],
                'git_branch'          => $data['rama'],
                'commit_hash'         => $data['commit'],
                'sandbox_status'      => $data['sandbox'],
                'risk_analysis'       => $data['riesgo'],
                'rollback_plan'       => $data['rollback'],
                'created_at'          => $created,
                'updated_at'          => $updated,
            ]);
        }

        // ─── Step 3: Seed Rejected Issues (estado: Rechazado) ──────────────────
        $rechazados = [
            [
                'user' => $kevin, 'evaluador' => $kevin, 'tipo' => 'Nueva Regla de Negocio', 'origen' => 'Iniciativa Técnica',
                'modulo' => 'Ventanilla', 'prioridad' => 'Baja',
                'desc' => 'Implementar escaneo de códigos de barras como alternativa al QR para validación de boletos.',
                'rama' => 'feature/escaneo-barras', 'commit' => null, 'issue' => '#401',
                'sandbox' => 'No probado',
                'riesgo' => 'Medio. Omitido para mantener un modelo de datos simplificado.',
                'rollback' => null,
                'motivo' => 'Omitido para mantener un modelo de datos simplificado. El sistema ya cuenta con validación QR funcional.',
                'dias' => 28,
            ],
            [
                'user' => $chofer, 'evaluador' => $kevin, 'tipo' => 'Corrección de Error', 'origen' => 'Retroalimentación del Usuario',
                'modulo' => 'Ventanilla', 'prioridad' => 'Alta',
                'desc' => 'Permitir edición manual de precios por ruta desde el panel de ventanilla.',
                'rama' => 'feature/precio-manual', 'commit' => null, 'issue' => '#402',
                'sandbox' => 'No probado',
                'riesgo' => 'Alto. Viola las políticas de precios de la cooperativa.',
                'rollback' => null,
                'motivo' => 'Viola las políticas de precios de la cooperativa. Los precios deben ser calculados automáticamente según ruta y categoría de asiento.',
                'dias' => 26,
            ],
            [
                'user' => $oficinista, 'evaluador' => $emilio, 'tipo' => 'Modificación de BD', 'origen' => 'Retroalimentación del Usuario',
                'modulo' => 'Base de Datos', 'prioridad' => 'Media',
                'desc' => 'Eliminar historial de boletos anulados para liberar espacio en base de datos.',
                'rama' => 'feature/limpiar-anulados', 'commit' => null, 'issue' => '#403',
                'sandbox' => 'No probado',
                'riesgo' => 'Alto. Viola regulaciones de auditoría y trazabilidad.',
                'rollback' => null,
                'motivo' => 'Viola regulaciones de auditoría y trazabilidad. Los boletos anulados deben conservarse para fines de control interno.',
                'dias' => 24,
            ],
        ];

        foreach ($rechazados as $data) {
            $created = Carbon::create(2026, 5, 27)->addDays(rand(0, 7))->addHours(rand(0, 23));
            $updated = $created->copy()->addHours(rand(1, 48));

            $solicitud = SolicitudCambio::create([
                'user_id'          => $data['user']->id,
                'evaluador_id'     => $data['evaluador']->id,
                'tipo_solicitud'   => $data['tipo'],
                'origen_solicitud' => $data['origen'],
                'descripcion'      => $data['desc'],
                'prioridad'        => $data['prioridad'],
                'estado_pipeline'  => 'Rechazado',
                'motivo_rechazo'   => $data['motivo'],
                'created_at'       => $created,
                'updated_at'       => $updated,
            ]);

            ReporteTecnicoCambio::create([
                'solicitud_cambio_id' => $solicitud->id,
                'developer_id'        => $data['user']->id,
                'modulo_afectado'     => $data['modulo'],
                'github_issue_id'     => $data['issue'],
                'git_branch'          => $data['rama'],
                'commit_hash'         => null,
                'sandbox_status'      => $data['sandbox'],
                'risk_analysis'       => $data['riesgo'],
                'rollback_plan'       => $data['rollback'],
                'created_at'          => $created,
                'updated_at'          => $updated,
            ]);
        }

        // ─── Step 4: Seed In Development Issue (estado: En Desarrollo) ─────────
        $enDesarrollo = [
            [
                'user' => $kevin, 'tipo' => 'Optimización', 'origen' => 'Iniciativa Técnica',
                'modulo' => 'Infraestructura', 'prioridad' => 'Media',
                'desc' => 'Exportación de métricas del dashboard de auditoría a PDF con gráficos y tablas de rendimiento.',
                'rama' => 'feature/exportar-metricas-pdf', 'commit' => null, 'issue' => '#501',
                'sandbox' => 'No probado',
                'riesgo' => 'Bajo. Solo agrega funcionalidad de exportación.',
                'rollback' => null,
                'dias' => 1,
            ],
        ];

        foreach ($enDesarrollo as $data) {
            $created = Carbon::create(2026, 5, 27)->addDays(rand(0, 7))->addHours(rand(0, 23));
            $updated = $created->copy()->addHours(rand(1, 24));

            $solicitud = SolicitudCambio::create([
                'user_id'          => $data['user']->id,
                'evaluador_id'     => $data['user']->id,
                'tipo_solicitud'   => $data['tipo'],
                'origen_solicitud' => $data['origen'],
                'descripcion'      => $data['desc'],
                'prioridad'        => $data['prioridad'],
                'estado_pipeline'  => 'En Desarrollo',
                'created_at'       => $created,
                'updated_at'       => $updated,
            ]);

            ReporteTecnicoCambio::create([
                'solicitud_cambio_id' => $solicitud->id,
                'developer_id'        => $data['user']->id,
                'modulo_afectado'     => $data['modulo'],
                'github_issue_id'     => $data['issue'],
                'git_branch'          => $data['rama'],
                'commit_hash'         => null,
                'sandbox_status'      => $data['sandbox'],
                'risk_analysis'       => $data['riesgo'],
                'rollback_plan'       => $data['rollback'],
                'created_at'          => $created,
                'updated_at'          => $updated,
            ]);
        }

        $this->command->info('20 solicitudes de cambio reales creadas: 16 Desplegadas, 3 Rechazadas, 1 En Desarrollo.');
    }
}
