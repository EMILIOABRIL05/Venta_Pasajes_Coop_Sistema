<div class="min-h-screen bg-[#F3F4F6] py-10" x-data="dashboardCharts('{{ $ventasPorDia }}', '{{ $rutasPopulares }}')">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header -->
        <div class="rounded-3xl border border-slate-200 bg-[#003366] p-8 text-white shadow-2xl shadow-blue-900/20 relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            
            <div class="relative z-10">
                <span class="inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white backdrop-blur-sm">Dashboard Administrativo</span>
                <h1 class="mt-4 text-4xl font-black tracking-tight">Análisis y Catálogos</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-100">Supervisa las ventas en tiempo real, analiza las rutas más populares y gestiona la flota desde tu centro de control.</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Ventas Chart -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-[#1F2937]">Ventas por Día</h2>
                        <p class="text-sm text-slate-500">Ingresos generados en los últimos días</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-[#003366]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="ventasChart"></canvas>
                </div>
            </div>

            <!-- Rutas Chart -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-[#1F2937]">Rutas más Vendidas</h2>
                        <p class="text-sm text-slate-500">Distribución de boletos por destino</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-[#CC0000]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3zm12-3c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3zM9 10l12-3" /></svg>
                    </div>
                </div>
                <div class="relative h-72 w-full flex justify-center">
                    <canvas id="rutasChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Shortcuts Section -->
        <h3 class="text-lg font-bold text-[#1F2937] ml-2">Gestión de Flota</h3>
        <div class="grid gap-6 md:grid-cols-2">
            <a href="{{ route('catalogos.buses') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#003366] hover:shadow-xl hover:shadow-blue-900/10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#1F2937] group-hover:text-[#003366] transition-colors">Buses</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Registra la flota, su foto, estado operativo y mapa lógico de asientos.</p>
                    </div>
                    <span class="rounded-full bg-[#F3F4F6] px-3 py-1 text-xs font-semibold text-[#1F2937] transition group-hover:bg-[#003366] group-hover:text-white">Abrir</span>
                </div>
            </a>

        </div>
    </div>

    <!-- Chart.js and Alpine Logic -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCharts', (ventasRaw, rutasRaw) => ({
                init() {
                    const ventasData = JSON.parse(ventasRaw);
                    const rutasData = JSON.parse(rutasRaw);
                    
                    this.initVentasChart(ventasData);
                    this.initRutasChart(rutasData);
                },
                initVentasChart(data) {
                    const ctx = document.getElementById('ventasChart').getContext('2d');
                    
                    // Gradient for line
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(0, 51, 102, 0.5)'); // #003366 con opacidad
                    gradient.addColorStop(1, 'rgba(0, 51, 102, 0.0)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(d => d.fecha),
                            datasets: [{
                                label: 'Total Ventas ($)',
                                data: data.map(d => d.total),
                                borderColor: '#003366',
                                backgroundColor: gradient,
                                borderWidth: 3,
                                pointBackgroundColor: '#CC0000',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#CC0000',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { borderDash: [4, 4], color: '#E5E7EB' }
                                },
                                x: {
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                },
                initRutasChart(data) {
                    const ctx = document.getElementById('rutasChart').getContext('2d');
                    
                    // Colors
                    const backgroundColors = [
                        '#003366', // Azul Rey Marino
                        '#CC0000', // Rojo Ambato
                        '#1F2937', // Gris Oscuro
                        '#3B82F6', // Blue Tailwind
                        '#F87171'  // Red Tailwind
                    ];

                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.map(d => d.ruta),
                            datasets: [{
                                data: data.map(d => d.cantidad),
                                backgroundColor: backgroundColors,
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { family: "'Inter', sans-serif" }
                                    }
                                }
                            }
                        }
                    });
                }
            }));
        });
    </script>
</div>
