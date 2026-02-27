/**
 * ========================================
 * CHECKER QR SCANNER - COMPONENTE ALPINE.JS
 * ========================================
 * 
 * Componente principal para el escaneo y validación de tickets
 * Integra html5-qrcode con Alpine.js
 */

function qrScannerComponent() {
    return {
        // ===== ESTADO DEL COMPONENTE =====
        scanner: null,
        isScanning: false,
        isValidating: false,
        manualCode: '',
        lastScanResult: null,
        statusMessage: '',
        statusClass: '',
        
        // ===== DATOS =====
        stats: {
            validatedToday: 0,
            validationRate: 0,
            lastValidation: null
        },
        recentValidations: [],

        // ===== COMPUTED PROPERTIES =====
        get scannerStateClass() {
            if (this.isValidating) return 'scanner-active';
            if (this.statusClass.includes('success')) return 'scanner-success';
            if (this.statusClass.includes('danger')) return 'scanner-error';
            return this.isScanning ? 'scanner-active' : 'scanner-idle';
        },

        // ===== INICIALIZACIÓN =====
        init() {
            console.log('🚀 Inicializando componente QR Scanner...');
            this.loadInitialData();
            this.setupScanner();
        },

        // ===== CONFIGURACIÓN DEL ESCÁNER =====
        setupScanner() {
            try {
                this.scanner = new Html5Qrcode("qr-reader");
                console.log('✅ Escáner QR inicializado correctamente');
            } catch (error) {
                console.error('❌ Error al inicializar escáner:', error);
                this.showToast('error', 'No se pudo inicializar el escáner de códigos QR');
            }
        },

        // ===== CONTROL DEL ESCÁNER =====
        async startScan() {
            if (this.isScanning) return;

            try {
                this.isScanning = true;
                this.statusMessage = 'Iniciando cámara...';
                this.statusClass = 'alert-info';

                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };

                await this.scanner.start(
                    { facingMode: "environment" }, // Cámara trasera
                    config,
                    this.handleScanSuccess.bind(this),
                    this.handleScanError.bind(this)
                );

                this.statusMessage = 'Escaneando... Apunte al código QR';
                this.statusClass = 'alert-primary';
                console.log('📸 Escaneo iniciado');

            } catch (error) {
                console.error('❌ Error al iniciar escaneo:', error);
                this.isScanning = false;
                this.statusMessage = 'No se pudo acceder a la cámara';
                this.statusClass = 'alert-danger';
                this.showToast('error', 'Verifique los permisos de la cámara');
            }
        },

        async stopScan() {
            if (!this.isScanning || !this.scanner) return;

            try {
                await this.scanner.stop();
                this.isScanning = false;
                this.statusMessage = '';
                console.log('⏸️ Escaneo detenido');
            } catch (error) {
                console.error('❌ Error al detener escaneo:', error);
            }
        },

        // ===== CALLBACKS DEL ESCÁNER =====
        handleScanSuccess(decodedText, decodedResult) {
            console.log('✅ QR Detectado:', decodedText);
            
            // Detener escaneo temporalmente para procesar
            this.stopScan();
            
            this.lastScanResult = decodedText;
            this.statusMessage = 'Código detectado, validando...';
            this.statusClass = 'alert-info';

            // Validar el ticket
            this.validateTicket(decodedText);
        },

        handleScanError(error) {
            // Ignorar errores de "no se encontró QR" (normales durante escaneo)
            if (!error.includes('NotFoundException')) {
                console.warn('⚠️ Error de escaneo:', error);
            }
        },

        // ===== VALIDACIÓN DE TICKETS =====
        async validateTicket(ticketCode) {
            if (!ticketCode || this.isValidating) return;

            this.isValidating = true;
            this.statusMessage = 'Validando ticket...';
            this.statusClass = 'alert-info';

            try {
                const result = await window.TicketValidationHandler.validate(ticketCode);

                if (result.success) {
                    this.handleValidationSuccess(result.data);
                } else {
                    this.handleValidationError(result.message);
                }

            } catch (error) {
                console.error('❌ Error en validación:', error);
                this.handleValidationError('Error de conexión al servidor');
            } finally {
                this.isValidating = false;
            }
        },

        async validateManual() {
            const code = this.manualCode.trim().toUpperCase();
            
            if (!code) {
                this.showToast('warning', 'Ingrese un código de ticket válido');
                return;
            }

            console.log('🔍 Iniciando validación manual:', code);
            await this.validateTicket(code);
            this.manualCode = '';
        },

        // ===== MANEJO DE RESULTADOS =====
        handleValidationSuccess(ticketData) {
            console.log('✅ Ticket validado:', ticketData);

            this.statusMessage = '¡Ticket validado correctamente!';
            this.statusClass = 'alert-success';

            // Mostrar toast de éxito
            this.showToast('success', `Ticket ${ticketData.ticket_code} validado correctamente`);

            // Actualizar datos
            this.updateRecentValidations(ticketData);
            this.refreshStats();

            // Resetear después de 3 segundos
            setTimeout(() => {
                this.statusMessage = '';
                this.lastScanResult = null;
                if (this.isScanning) {
                    this.statusMessage = 'Escaneando... Apunte al código QR';
                    this.statusClass = 'alert-primary';
                }
            }, 3000);
        },

        handleValidationError(message) {
            console.error('❌ Validación fallida:', message);

            this.statusMessage = message;
            this.statusClass = 'alert-danger';

            // Mostrar toast de error
            this.showToast('error', message);

            // Resetear después de 5 segundos
            setTimeout(() => {
                this.statusMessage = '';
                this.lastScanResult = null;
            }, 5000);
        },

        // ===== ACTUALIZACIÓN DE DATOS =====
        async loadInitialData() {
            try {
                await Promise.all([
                    this.refreshStats(),
                    this.loadRecentValidations()
                ]);
                console.log('✅ Datos iniciales cargados');
            } catch (error) {
                console.error('❌ Error al cargar datos iniciales:', error);
            }
        },

        async refreshStats() {
            try {
                const data = await window.TicketValidationHandler.getStats();
                this.stats = {
                    validatedToday: data.validated_today || 0,
                    validationRate: data.validation_rate || 0,
                    lastValidation: data.last_validation || null
                };
            } catch (error) {
                console.error('❌ Error al cargar estadísticas:', error);
            }
        },

        async loadRecentValidations() {
            try {
                const data = await window.TicketValidationHandler.getHistory(5);
                this.recentValidations = data.data || [];
            } catch (error) {
                console.error('❌ Error al cargar historial:', error);
            }
        },

        updateRecentValidations(newTicket) {
            // Agregar al inicio del array
            this.recentValidations.unshift(newTicket);
            
            // Mantener solo los últimos 5
            if (this.recentValidations.length > 5) {
                this.recentValidations.pop();
            }
        },

        // ===== HELPERS =====
        formatLastValidation() {
            if (!this.stats.lastValidation) {
                return 'Sin validaciones';
            }

            const date = new Date(this.stats.lastValidation);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / 1000 / 60);

            if (diffMinutes < 1) return 'Hace un momento';
            if (diffMinutes < 60) return `Hace ${diffMinutes} min`;
            
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        },

        formatTime(timestamp) {
            if (!timestamp) return '--:--';
            
            const date = new Date(timestamp);
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const seconds = date.getSeconds().toString().padStart(2, '0');
            
            return `${hours}:${minutes}:${seconds}`;
        },

        viewFullHistory() {
            // Trigger para cargar sección de historial completo
            const link = document.querySelector('a[data-section="validate-ticket"]');
            if (link) {
                link.click();
            }
        },

        showToast(type, message) {
            const toastId = type === 'success' ? 'successToast' : 
                           type === 'error' ? 'errorToast' : 'warningToast';
            const messageId = type === 'success' ? 'successMessage' : 
                             type === 'error' ? 'errorMessage' : 'warningMessage';

            const toastElement = document.getElementById(toastId);
            const messageElement = document.getElementById(messageId);

            if (toastElement && messageElement) {
                messageElement.textContent = message;
                const toast = new bootstrap.Toast(toastElement, {
                    autohide: true,
                    delay: type === 'error' ? 5000 : 3000
                });
                toast.show();
            }
        },

        // ===== CLEANUP =====
        destroy() {
            if (this.scanner) {
                this.stopScan();
                this.scanner.clear();
            }
        }
    };
}

// Exponer explícitamente en window para Alpine
window.qrScannerComponent = qrScannerComponent;

// Registrar con Alpine.data() si Alpine ya está disponible
if (typeof Alpine !== 'undefined') {
    Alpine.data('qrScannerComponent', qrScannerComponent);
    console.log('🎨 QR Scanner Component registrado con Alpine.data()');
} else {
    // Si Alpine no está listo, esperar al evento
    document.addEventListener('alpine:init', () => {
        Alpine.data('qrScannerComponent', qrScannerComponent);
        console.log('🎨 QR Scanner Component registrado con Alpine.data() (deferred)');
    });
    console.log('🎨 QR Scanner Component registrado en window.qrScannerComponent');
}
