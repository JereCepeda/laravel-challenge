<div class="container-fluid" x-data="qrScannerComponent()">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-qr-code-scan"></i> 
            Validar Tickets - Escáner QR
        </h2>
        <button class="btn btn-outline-primary" @click="refreshStats()" id="btnRefreshChecker">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>

    <!-- Row Principal: Escáner + Estadísticas -->
    <div class="row mb-4">
        <!-- Card del Escáner QR -->
        <div class="col-lg-8 mb-4">
            <div class="card scanner-card" :class="scannerStateClass">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-video"></i> 
                        Escáner de Código QR
                    </h5>
                    <div class="scanner-controls">
                        <button 
                            class="btn btn-sm btn-success" 
                            @click="startScan()" 
                            x-show="!isScanning"
                            :disabled="isScanning">
                            <i class="bi bi-play-fill"></i> Iniciar Escaneo
                        </button>
                        <button 
                            class="btn btn-sm btn-danger" 
                            @click="stopScan()" 
                            x-show="isScanning">
                            <i class="bi bi-stop-fill"></i> Detener
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contenedor del Escáner -->
                    <div id="qr-reader" class="qr-reader-container"></div>
                    
                    <!-- Mensajes de Estado -->
                    <div class="scanner-status mt-3 text-center" x-show="statusMessage">
                        <div class="alert" :class="statusClass" role="alert" x-text="statusMessage"></div>
                    </div>
                    
                    <!-- Información del Último Escaneo -->
                    <div class="last-scan-info mt-3" x-show="lastScanResult">
                        <div class="alert alert-info">
                            <strong><i class="bi bi-info-circle"></i> Último código escaneado:</strong>
                            <code x-text="lastScanResult"></code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Estadísticas del Día -->
        <div class="col-lg-4 mb-4">
            <div class="card stats-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2"></i> 
                        Estadísticas de Hoy
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Validados Hoy -->
                    <div class="stat-item mb-4">
                        <div class="stat-label">
                            <i class="bi bi-check-circle text-success"></i> 
                            Tickets Validados
                        </div>
                        <div class="stat-value" x-text="stats.validatedToday">0</div>
                    </div>

                    <!-- Tasa de Validación -->
                    <div class="stat-item mb-4">
                        <div class="stat-label">
                            <i class="bi bi-graph-up text-primary"></i> 
                            Tasa de Validación
                        </div>
                        <div class="stat-value">
                            <span x-text="stats.validationRate">0</span>
                            <small>/hora</small>
                        </div>
                    </div>

                    <!-- Última Validación -->
                    <div class="stat-item">
                        <div class="stat-label">
                            <i class="bi bi-clock text-info"></i> 
                            Última Validación
                        </div>
                        <div class="stat-time" x-text="formatLastValidation()">
                            Sin validaciones
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Botón Ver Historial Completo -->
                    <button class="btn btn-logout btn-sm w-100" @click="viewFullHistory()">
                        <i class="bi bi-clock-history"></i> 
                        Ver Historial Completo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Validación Manual -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card manual-validation-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-keyboard"></i> 
                        Validación Manual (Alternativa)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-10">
                            <label for="manualTicketCode" class="form-label">
                                Ingrese el código del ticket manualmente
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="manualTicketCode"
                                x-model="manualCode"
                                @keyup.enter="validateManual()"
                                placeholder="Ej: ABC123XYZ"
                                maxlength="50"
                                :disabled="isValidating">
                        </div>
                        <div class="col-md-2">
                            <button 
                                class="btn btn-primary btn-lg w-100" 
                                @click="validateManual()"
                                :disabled="!manualCode || isValidating">
                                <i class="bi bi-check-lg" x-show="!isValidating"></i>
                                <span x-text="isValidating ? 'Validando...' : 'Validar'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Últimas Validaciones -->
    <div class="row">
        <div class="col-12">
            <div class="card recent-validations-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> 
                        Últimas Validaciones
                    </h5>
                    <span class="badge bg-primary" x-text="`${recentValidations.length} registros`">0 registros</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-ticket-perforated"></i> Código</th>
                                    <th><i class="bi bi-calendar-event"></i> Evento</th>
                                    <th><i class="bi bi-geo-alt"></i> Sector</th>
                                    <th><i class="bi bi-clock"></i> Hora</th>
                                    <th class="text-center"><i class="bi bi-check-circle"></i> Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="recentValidations.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mt-2">No hay validaciones recientes</p>
                                        </td>
                                    </tr>
                                </template>

                                <template x-for="validation in recentValidations" :key="validation.id">
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
                                            <small x-text="formatTime(validation.validated_at)"></small>
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

    <!-- Toast de Notificaciones -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <span id="successMessage">Ticket validado correctamente</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>

        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span id="errorMessage">Error al validar el ticket</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>

        <div id="warningToast" class="toast align-items-center text-white bg-warning border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span id="warningMessage">Advertencia</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/checker.css') }}">
<script src="{{ asset('js/checker/validation-handler.js') }}"></script>
<script src="{{ asset('js/checker/qr-scanner.js') }}"></script>