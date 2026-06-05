import './bootstrap';

import Alpine from 'alpinejs';

function extraerUuidDesdeTexto(texto) {
	const coincidencia = texto
		.trim()
		.match(/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i);

	return coincidencia ? coincidencia[0].toLowerCase() : null;
}

const choferQrScanner = (livewireId) => ({
	scanner: null,
	activo: false,
	errorCamara: null,
	async iniciar() {
		this.errorCamara = null;
		if (this.activo) {
			return;
		}
		if (!window.Livewire || typeof window.Livewire.find !== 'function') {
			this.errorCamara = 'Livewire no está disponible en esta página.';

			return;
		}
		try {
			const { Html5Qrcode } = await import('html5-qrcode');
			this.scanner = new Html5Qrcode('chofer-qr-reader');
			await this.scanner.start(
				{ facingMode: 'environment' },
				{ fps: 8, qrbox: { width: 240, height: 240 } },
				(decodedText) => {
					void this.procesarEscaneo(decodedText);
				},
				() => {},
			);
			this.activo = true;
		} catch (e) {
			this.errorCamara =
				e instanceof Error ? e.message : 'No se pudo iniciar la cámara.';
			this.activo = false;
		}
	},
	async detener() {
		if (!this.scanner || !this.activo) {
			return;
		}
		try {
			await this.scanner.stop();
			await this.scanner.clear();
		} catch {
			// ignorar errores al cerrar el lector
		}
		this.scanner = null;
		this.activo = false;
	},
	async procesarEscaneo(decodedText) {
		const uuid = extraerUuidDesdeTexto(decodedText);
		if (!uuid || !this.scanner) {
			return;
		}
		const componente = window.Livewire.find(livewireId);
		if (!componente) {
			return;
		}
		try {
			await this.scanner.pause(true);
			await componente.call('validarAbordaje', uuid);
		} finally {
			try {
				await this.scanner.resume();
			} catch {
				// si resume falla, el chofer puede detener e iniciar de nuevo
			}
		}
	},
});

window.choferQrScanner = choferQrScanner;

window.datosEntrega = {
	imprimirResumen() {
		window.print();
	},
};

function registrarChoferQrScanner(AlpineRef) {
	if (!AlpineRef || typeof AlpineRef.data !== 'function') {
		return;
	}
	AlpineRef.data('choferQrScanner', choferQrScanner);
}

document.addEventListener('alpine:init', () => {
	registrarChoferQrScanner(window.Alpine);
});

const paginaLivewire =
	document.querySelector('[wire\\:id], [wire\\:snapshot], [wire\\:initial-data]') !== null;

if (window.Alpine) {
	registrarChoferQrScanner(window.Alpine);
} else if (!paginaLivewire) {
	window.Alpine = Alpine;
	registrarChoferQrScanner(window.Alpine);
	window.Alpine.start();
}
