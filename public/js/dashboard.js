/**
 * Dashboard SPA - Sistema de navegación sin recargar página
 */
class DashboardSPA {
    constructor() {
        this.contentContainer = document.getElementById('spa-content');
        this.pageTitle = document.getElementById('pageTitle');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.sidebar = document.getElementById('sidebar');
        this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
        
        this.init();
    }
    
    init() {
        // Inicializar navegación SPA
        this.initSPALinks();
        
        // Inicializar menú móvil
        this.initMobileMenu();
        
        // Manejar botón atrás del navegador
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.section) {
                this.loadSection(e.state.section, false);
            }
        });
    }
    
    /**
     * Inicializar links de navegación SPA
     */
    initSPALinks() {
        document.querySelectorAll('.spa-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const section = link.dataset.section;
                const title = link.dataset.title;
                
                // Actualizar link activo
                this.setActiveLink(link);
                
                // Actualizar título
                if (title) {
                    this.pageTitle.textContent = title;
                }
                
                // Cargar sección
                this.loadSection(section, true);
                
                // Cerrar menú móvil si está abierto
                this.closeMobileMenu();
            });
        });
    }
    
    /**
     * Cargar sección del dashboard
     */
    async loadSection(section, updateHistory = true) {
        // Mostrar loading
        this.showLoading();
        
        try {
            const response = await fetch(`/dashboard/${section}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.App.csrfToken
                }
            });
            
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('No tienes permisos para acceder a esta sección');
                }
                throw new Error('Error al cargar la sección');
            }
            
            const html = await response.text();
            
            // Insertar contenido
            this.contentContainer.innerHTML = html;
            
            // Actualizar historial del navegador
            if (updateHistory) {
                history.pushState(
                    { section }, 
                    '', 
                    `/dashboard#${section}`
                );
            }
            
            // Inicializar componentes de Bootstrap en la nueva vista
            this.initializeBootstrapComponents();
            
            // Scroll al inicio
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
        } catch (error) {
            console.error('Error:', error);
            this.showError(error.message);
        }
    }
    
    /**
     * Mostrar loading spinner
     */
    showLoading() {
        this.contentContainer.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner-border spinner-border-custom text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;
    }
    
    /**
     * Mostrar mensaje de error
     */
    showError(message) {
        this.contentContainer.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }
    
    /**
     * Marcar link como activo
     */
    setActiveLink(activeLink) {
        document.querySelectorAll('.spa-link').forEach(link => {
            link.classList.remove('active');
        });
        activeLink.classList.add('active');
    }
    
    /**
     * Inicializar componentes de Bootstrap
     */
    initializeBootstrapComponents() {
        // Inicializar tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Inicializar popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
    
    /**
     * Inicializar menú móvil
     */
    initMobileMenu() {
        if (this.mobileMenuToggle) {
            this.mobileMenuToggle.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
        }
        
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.closeMobileMenu();
            });
        }
    }
    
    /**
     * Toggle menú móvil
     */
    toggleMobileMenu() {
        this.sidebar.classList.toggle('active');
        this.sidebarOverlay.classList.toggle('active');
    }
    
    /**
     * Cerrar menú móvil
     */
    closeMobileMenu() {
        this.sidebar.classList.remove('active');
        this.sidebarOverlay.classList.remove('active');
    }
}

// Inicializar SPA cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardSPA = new DashboardSPA();
});