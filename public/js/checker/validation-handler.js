/**
 * ========================================
 * TICKET VALIDATION HANDLER
 * ========================================
 * 
 * Maneja todas las llamadas a la API de validación de tickets
 * Centraliza la lógica de comunicación con el backend
 */

window.TicketValidationHandler = (function() {
    'use strict';

    // ===== CONFIGURACIÓN =====
    // Usar baseUrl dinámico de window.App o fallback a /api
    function getApiBaseUrl() {
        const baseUrl = (window.App && window.App.baseUrl) ? window.App.baseUrl + '/api' : '/api';
        console.log('🔗 API Base URL:', baseUrl, '| window.App:', window.App);
        return baseUrl;
    }
    
    const ENDPOINTS = {
        VALIDATE: '/tickets/validate',
        STATS: '/checker/stats/today',
        HISTORY: '/checker/history'
    };

    // ===== HELPERS PRIVADOS =====
    
    /**
     * Obtener token de autenticación (Bearer para API)
     */
    function getAuthToken() {
        return localStorage.getItem('auth_token');
    }

    /**
     * Obtener CSRF token para autenticación de sesión
     */
    function getCsrfToken() {
        // Buscar en meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Buscar en cookies
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'XSRF-TOKEN') {
                return decodeURIComponent(value);
            }
        }
        
        return null;
    }

    /**
     * Headers por defecto para requests
     */
    function getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        // Intentar autenticación Bearer primero (para API)
        const authToken = getAuthToken();
        if (authToken) {
            headers['Authorization'] = `Bearer ${authToken}`;
        }

        // Agregar CSRF token para autenticación de sesión (para web)
        const csrfToken = getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return headers;
    }

    /**
     * Realizar request HTTP
     */
    async function makeRequest(url, options = {}) {
        const defaultOptions = {
            headers: getHeaders()
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || data.error || 'Error en la solicitud');
            }

            return { success: true, data };

        } catch (error) {
            console.error('❌ Request error:', error);
            return { 
                success: false, 
                message: error.message || 'Error de conexión con el servidor'
            };
        }
    }

    // ===== API PÚBLICA =====

    /**
     * Validar un ticket por código
     * @param {string} ticketCode - Código del ticket a validar
     * @returns {Promise<Object>} Resultado de la validación
     */
    async function validateTicket(ticketCode) {
        console.log('🔍 Validando ticket:', ticketCode);

        const url = `${getApiBaseUrl()}${ENDPOINTS.VALIDATE}`;
        const result = await makeRequest(url, {
            method: 'POST',
            body: JSON.stringify({ ticket_code: ticketCode })
        });

        if (result.success) {
            console.log('✅ Ticket validado exitosamente:', result.data);
            return {
                success: true,
                data: result.data.ticket || result.data
            };
        } else {
            console.error('❌ Error al validar ticket:', result.message);
            return {
                success: false,
                message: result.message || 'Ticket inválido o ya utilizado'
            };
        }
    }

    /**
     * Obtener estadísticas del día actual
     * @returns {Promise<Object>} Estadísticas del checker
     */
    async function getTodayStats() {
        console.log('📊 Obteniendo estadísticas del día...');

        const url = `${getApiBaseUrl()}${ENDPOINTS.STATS}`;
        const result = await makeRequest(url, {
            method: 'GET'
        });

        if (result.success) {
            console.log('✅ Estadísticas obtenidas:', result.data);
            return result.data;
        } else {
            console.error('❌ Error al obtener estadísticas');
            return {
                validated_today: 0,
                validation_rate: 0,
                last_validation: null
            };
        }
    }

    /**
     * Obtener historial de validaciones
     * @param {number} limit - Número de registros a obtener
     * @returns {Promise<Object>} Historial de validaciones
     */
    async function getValidationHistory(limit = 5) {
        console.log(`📜 Obteniendo historial (últimos ${limit})...`);

        const url = `${getApiBaseUrl()}${ENDPOINTS.HISTORY}?limit=${limit}`;
        const result = await makeRequest(url, {
            method: 'GET'
        });

        if (result.success) {
            console.log('✅ Historial obtenido:', result.data);
            return result.data;
        } else {
            console.error('❌ Error al obtener historial');
            return { data: [] };
        }
    }

    /**
     * Obtener eventos activos disponibles para validación
     * @returns {Promise<Array>} Lista de eventos activos
     */
    async function getActiveEvents() {
        console.log('🎫 Obteniendo eventos activos...');

        const url = `${getApiBaseUrl()}/checker/events`;
        const result = await makeRequest(url, {
            method: 'GET'
        });

        if (result.success) {
            console.log('✅ Eventos activos obtenidos:', result.data);
            return result.data.data || [];
        } else {
            console.error('❌ Error al obtener eventos');
            return [];
        }
    }

    // ===== EXPORT PÚBLICO =====
    return {
        validate: validateTicket,
        getStats: getTodayStats,
        getHistory: getValidationHistory,
        getActiveEvents: getActiveEvents
    };

})();

// Log de inicialización
console.log('✅ TicketValidationHandler inicializado y listo');