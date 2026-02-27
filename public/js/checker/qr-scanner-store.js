document.addEventListener('alpine:init', () => {
    Alpine.store('qrScanner', {
        // Estado
        scanner: null,
        isScanning: false,
        isValidating: false,
        manualCode: '',
        lastScanResult: null,
        statusMessage: '',
        statusClass: '',
        lastScannedCode: null,
        scanCooldown: false,
        stats: {
            validatedToday: 0,
            validationRate: 0,
            lastValidation: null
        },
        recentValidations: [],

        // Computed
        get scannerStateClass() {
            if (this.isValidating) return 'scanner-validating';
            if (this.statusClass.includes('success')) return 'scanner-success';
            if (this.statusClass.includes('danger')) return 'scanner-error';
            return this.isScanning ? 'scanner-active' : 'scanner-idle';
        },

        // Metodos
        init() {
            console.log('QR Scanner Store inicializado');
            this.loadInitialData();
        },

        setupScanner() {
            if (this.scanner) return;
            this.scanner = new Html5Qrcode("qr-reader");
        },

        async startScan() {
            if (!this.scanner) this.setupScanner();
            if (this.isScanning) return;

            try {
                await this.scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => this.handleScanSuccess(decodedText),
                    (error) => {}
                );
                this.isScanning = true;
                this.statusMessage = '';
                this.lastScannedCode = null;
                this.scanCooldown = false;
                console.log('Escaner iniciado');
            } catch (err) {
                console.error('Error al iniciar escaner:', err);
                this.showToast('error', 'No se pudo acceder a la camara');
            }
        },

        async stopScan() {
            if (!this.scanner || !this.isScanning) return;
            try {
                await this.scanner.stop();
                this.isScanning = false;
                console.log('Escaner detenido');
            } catch (err) {
                console.error('Error al detener escaner:', err);
            }
        },

        async handleScanSuccess(decodedText) {
            if (this.scanCooldown || this.isValidating || this.lastScannedCode === decodedText) {
                console.log('Escaneo ignorado - cooldown:', this.scanCooldown, 'validando:', this.isValidating, 'duplicado:', this.lastScannedCode === decodedText);
                return;
            }

            console.log('QR detectado:', decodedText);
            
            this.scanCooldown = true;
            this.lastScannedCode = decodedText;
            this.lastScanResult = decodedText;

            await this.stopScan();

            try {
                await this.validateTicket(decodedText);
            } catch (error) {
                console.error('Error en handleScanSuccess:', error);
            } finally {
                setTimeout(() => {
                    this.scanCooldown = false;
                }, 5000);
            }
        },

        async validateTicket(ticketCode) {
            if (this.isValidating) {
                console.log('Validacion ya en progreso, ignorando...');
                return;
            }
            
            this.isValidating = true;
            this.statusMessage = 'Procesando...';
            this.statusClass = 'alert-info';

            try {
                const realCode = this.extractCodeFromUrl(ticketCode);
                console.log('Codigo a procesar:', realCode);

                // Detectar si es invitación:
                // 1. Comienza con INV- (ej: INV-MC4MKM8B)
                // 2. Es un hash de 6 caracteres (ej: a8f22d)
                const isInvitationHash = /^[a-z0-9]{6}$/i.test(realCode);
                const isInvitationCode = realCode.toUpperCase().startsWith('INV-');
                const isInvitation = isInvitationHash || isInvitationCode;

                let result;
                if (isInvitation) {
                    // Extraer solo el hash si viene con formato INV-XXXXXX
                    const hash = isInvitationCode ? realCode.substring(4) : realCode;
                    console.log('Detectada invitacion, hash:', hash);
                    result = await window.TicketValidationHandler.redeemInvitation(hash);
                } else {
                    console.log('Detectado ticket:', realCode);
                    result = await window.TicketValidationHandler.validate(realCode);
                }

                console.log('Respuesta completa:', result);

                const validationData = result.data || result;
                
                if (validationData.access_granted === true || result.success === true) {
                    this.handleValidationSuccess(validationData, isInvitation);
                } else {
                    const errorMsg = validationData.message || result.message || 'Codigo invalido';
                    this.handleValidationError(errorMsg);
                }
            } catch (error) {
                console.error('Error en validateTicket:', error);
                const message = error.message || 'Error al procesar el codigo';
                this.handleValidationError(message);
            } finally {
                this.isValidating = false;
            }
        },

        extractCodeFromUrl(text) {
            if (text.includes('me-qr.com') || text.includes('qr1.me-qr.com')) {
                const match = text.match(/\/([a-zA-Z0-9]+)$/);
                if (match) {
                    return match[1];
                }
            }
            
            try {
                const url = new URL(text);
                const code = url.searchParams.get('code') || 
                            url.searchParams.get('ticket') ||
                            url.searchParams.get('id');
                if (code) return code;
            } catch (e) {
                // No es URL
            }

            return text;
        },

        async validateManual() {
            if (!this.manualCode.trim()) return;
            await this.validateTicket(this.manualCode);
            this.manualCode = '';
        },

        handleValidationSuccess(result, isInvitation = false) {
            const ticketInfo = result.ticket_info || result.ticket || result.tickets?.[0] || {};
            const code = ticketInfo.code || this.lastScannedCode || 'N/A';
            
            let message;
            if (isInvitation) {
                const guestCount = result.guest_count || result.tickets?.length || 1;
                message = `Invitacion canjeada: ${guestCount} ticket(s) generado(s)`;
            } else {
                message = result.message || `Ticket validado: ${code}`;
            }
            
            this.statusMessage = message;
            this.statusClass = 'alert-success';
            this.showToast('success', message);

            // Si es invitación con múltiples tickets, agregar todos
            if (isInvitation && result.tickets) {
                result.tickets.forEach(ticket => {
                    const validationData = {
                        id: Date.now() + Math.random(),
                        ticket_code: ticket.code || ticket.ticket_code || 'N/A',
                        event_name: ticket.event || ticket.event_name || 'Evento',
                        sector: ticket.sector || 'General',
                        validated_at: ticket.created_at || new Date().toISOString()
                    };
                    this.updateRecentValidations(validationData);
                });
            } else {
                const validationData = {
                    id: Date.now(),
                    ticket_code: code,
                    event_name: ticketInfo.event || ticketInfo.event_name || 'Evento',
                    sector: ticketInfo.sector || 'General',
                    validated_at: ticketInfo.validated_at || ticketInfo.created_at || new Date().toISOString()
                };
                this.updateRecentValidations(validationData);
            }

            this.refreshStats();

            setTimeout(() => {
                this.statusMessage = '';
            }, 5000);
        },

        handleValidationError(message) {
            this.statusMessage = message;
            this.statusClass = 'alert-danger';
            this.showToast('error', message);
            
            setTimeout(() => {
                this.statusMessage = '';
            }, 5000);
        },

        showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}"></i>
                </div>
                <div class="toast-message">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        },

        async loadInitialData() {
            await Promise.all([
                this.refreshStats(),
                this.loadRecentValidations()
            ]);
        },

        async refreshStats() {
            try {
                const data = await window.TicketValidationHandler.getStats();
                this.stats.validatedToday = data.validated_today || 0;
                this.stats.validationRate = data.validation_rate || 0;
                this.stats.lastValidation = data.last_validation || null;
            } catch (error) {
                console.error('Error al cargar estadisticas:', error);
            }
        },

        async loadRecentValidations() {
            try {
                const response = await window.TicketValidationHandler.getHistory(5);
                this.recentValidations = response.data || [];
            } catch (error) {
                console.error('Error al cargar historial:', error);
            }
        },

        updateRecentValidations(newTicket) {
            this.recentValidations.unshift(newTicket);
            if (this.recentValidations.length > 5) {
                this.recentValidations = this.recentValidations.slice(0, 5);
            }
        },

        formatLastValidation() {
            if (!this.stats.lastValidation) return 'Sin validaciones recientes';
            const date = new Date(this.stats.lastValidation);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 1) return 'Hace menos de 1 minuto';
            if (diffMins < 60) return `Hace ${diffMins} minutos`;
            const diffHours = Math.floor(diffMins / 60);
            return `Hace ${diffHours} horas`;
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },

        cleanup() {
            if (this.scanner && this.isScanning) {
                this.stopScan();
            }
        }
    });
});

console.log('QR Scanner Store registrado');