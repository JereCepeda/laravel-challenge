<div class="container-fluid" x-data="{ store: $store.qrScanner }" x-init="store.setupScanner()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-qr-code-scan"></i> 
            Validar Tickets - Escaner QR
        </h2>
        <button class="btn btn-outline-primary" @click="store.refreshStats()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card scanner-card" :class="store.scannerStateClass">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Escaner de Codigo QR</h5>
                    <div>
                        <button class="btn btn-sm btn-success" 
                                @click="store.startScan()" 
                                x-show="!store.isScanning" 
                                :disabled="store.isScanning">
                            <i class="bi bi-camera-fill"></i> Iniciar Escaner
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                @click="store.stopScan()" 
                                x-show="store.isScanning">
                            <i class="bi bi-stop-circle"></i> Detener
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="qr-reader" class="qr-reader-container"></div>
                    
                    <div class="scanner-status mt-3 text-center" x-show="store.statusMessage">
                        <div class="alert" :class="store.statusClass" role="alert" x-text="store.statusMessage"></div>
                    </div>

                    <div class="last-scan-info mt-3" x-show="store.lastScanResult">
                        <strong>Ultimo codigo escaneado:</strong>
                        <code x-text="store.lastScanResult"></code>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card stats-card">
                <div class="card-header">
                    <h5 class="mb-0">Estadisticas del Dia</h5>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <div class="stat-label">Tickets Validados</div>
                        <div class="stat-value" x-text="store.stats.validatedToday"></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Tasa de Validacion</div>
                        <div class="stat-value">
                            <span x-text="store.stats.validationRate"></span>
                            <small>/hora</small>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Ultima Validacion</div>
                        <div class="stat-time" x-text="store.formatLastValidation()"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card manual-validation-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-keyboard"></i> 
                        Validacion Manual (Alternativa)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-10">
                            <label for="manualTicketCode" class="form-label">
                                Ingrese el codigo del ticket manualmente
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="manualTicketCode"
                                x-model="store.manualCode"
                                @keyup.enter="store.validateManual()"
                                placeholder="Ej: ABC123XYZ"
                                maxlength="50"
                                :disabled="store.isValidating">
                        </div>
                        <div class="col-md-2">
                            <button 
                                class="btn btn-primary btn-lg w-100" 
                                @click="store.validateManual()"
                                :disabled="!store.manualCode || store.isValidating">
                                <i class="bi bi-check-lg" x-show="!store.isValidating"></i>
                                <span x-text="store.isValidating ? 'Validando...' : 'Validar'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card recent-validations-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> 
                        Ultimas Validaciones
                    </h5>
                    <span class="badge bg-primary" x-text="`${store.recentValidations.length} registros`"></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-ticket-perforated"></i> Codigo</th>
                                    <th><i class="bi bi-calendar-event"></i> Evento</th>
                                    <th><i class="bi bi-geo-alt"></i> Sector</th>
                                    <th><i class="bi bi-clock"></i> Hora</th>
                                    <th class="text-center"><i class="bi bi-check-circle"></i> Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="store.recentValidations.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mt-2">No hay validaciones recientes</p>
                                        </td>
                                    </tr>
                                </template>

                                <template x-for="validation in store.recentValidations" :key="validation.id">
                                    <tr class="validation-row">
                                        <td>
                                            <code class="ticket-code" x-text="validation.ticket_code"></code>
                                        </td>
                                        <td>
                                            <strong x-text="validation.event_name"></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary" x-text="validation.sector"></span>
                                        </td>
                                        <td>
                                            <small x-text="store.formatTime(validation.validated_at)"></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Validado
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/checker.css') }}?v={{ time() }}">
<script src="{{ asset('js/checker/validation-handler.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/checker/qr-scanner-store.js') }}?v={{ time() }}"></script>