{{-- filepath: c:\xampp\htdocs\laravel-challenge\resources\views\dashboard\checker\redeem-invitation.blade.php --}}
<div class="container-fluid" x-data="{ store: $store.qrScanner }" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="bi bi-gift"></i> Intercambio de Invitacion/Ticket</h1>
            <p>Escanea el codigo QR de la invitacion para redimirla.</p>
        </div>
        <button class="btn btn-outline-primary" @click="store.refreshStats()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card scanner-card" :class="store.scannerStateClass">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Escaner de Invitacion</h5>
                    <div>
                        <button class="btn btn-sm btn-success" 
                                @click="store.startScan()" 
                                x-show="!store.isScanning">
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
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informacion</h5>
                </div>
                <div class="card-body">
                    <p>Las invitaciones se convierten automaticamente en tickets al ser escaneadas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/checker.css') }}?v={{ time() }}">
<script src="{{ asset('js/checker/validation-handler.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/checker/qr-scanner-store.js') }}?v={{ time() }}"></script>