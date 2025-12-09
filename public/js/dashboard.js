/**
 * Dashboard SPA
 */
class DashboardSPA {
    constructor() {
        // Prevenir múltiples instancias
        if (window.dashboardSPA) {
            return window.dashboardSPA;
        }
        
        this.contentContainer = document.getElementById('spa-content');
        this.pageTitle = document.getElementById('pageTitle');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.sidebar = document.getElementById('sidebar');
        this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
        this.initialized = false;
        
        this.init();
        window.dashboardSPA = this;
    }
    
    init() {
        if (this.initialized) {
            return;
        }
        
        this.initSPALinks();
        this.initMobileMenu();
        this.initLogout();
        
        // Ejecutar scripts de la vista inicial
        if (this.contentContainer) {
            this.executeScripts(this.contentContainer);
        }
        
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.section) {
                this.loadSection(e.state.section, false);
            }
        });
        
        this.initialized = true;
    }
    
    initLogout() {
        document.querySelectorAll('[data-action="logout"]').forEach(btn => {
            // Evitar agregar listeners duplicados
            if (btn.dataset.listenerAdded) {
                return;
            }
            
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                await this.logout();
            });
            
            btn.dataset.listenerAdded = 'true';
        });
    }
    
    async logout() {
        const token = localStorage.getItem('auth_token');
        
        try {
            await fetch(`${window.App.baseUrl}/api/logout`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': window.App.csrfToken
                }
            });
        } catch (error) {
            console.error('Error logout:', error);
        } finally {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = `${window.App.baseUrl}/login`;
        }
    }
    
    initSPALinks() {
        document.querySelectorAll('.spa-link').forEach(link => {
            // Evitar agregar listeners duplicados
            if (link.dataset.listenerAdded) {
                return;
            }
            
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const section = link.dataset.section;
                const title = link.dataset.title;
                
                this.setActiveLink(link);
                
                if (title) {
                    this.pageTitle.textContent = title;
                }
                
                this.loadSection(section, true);
                this.closeMobileMenu();
            });
            
            link.dataset.listenerAdded = 'true';
        });
    }
    
    async loadSection(section, updateHistory = true) {
        this.showLoading();
        
        try {
            const token = localStorage.getItem('auth_token');
            
            const response = await fetch(`${window.App.baseUrl}/api/dashboard/${section}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'text/html'
                }
            });
            
            if (response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = `${window.App.baseUrl}/login`;
                return;
            }
            
            if (!response.ok) {
                throw new Error(response.status === 403 ? 
                    'No tienes permisos' : 
                    'Error al cargar sección');
            }
            
            const html = await response.text();
            
            this.contentContainer.innerHTML = html;
            this.executeScripts(this.contentContainer);
            
            if (updateHistory) {
                history.pushState({ section }, '', `${window.App.baseUrl}/dashboard#${section}`);
            }
            
            this.initializeBootstrapComponents();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
        } catch (error) {
            console.error('Error:', error);
            this.showError(error.message);
        }
    }
    
    showLoading() {
        this.contentContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Cargando...</p>
            </div>
        `;
    }
    
    showError(message) {
        this.contentContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> ${message}
            </div>
        `;
    }
    
    setActiveLink(activeLink) {
        document.querySelectorAll('.spa-link').forEach(link => {
            link.classList.remove('active');
        });
        activeLink.classList.add('active');
    }
    
    executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            // Evitar re-ejecutar el script principal del dashboard
            if (oldScript.src && oldScript.src.includes('dashboard.js')) {
                return;
            }
            
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }
    
    initializeBootstrapComponents() {
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(el => new bootstrap.Tooltip(el));
        document.querySelectorAll('[data-bs-toggle="popover"]')
            .forEach(el => new bootstrap.Popover(el));
    }
    
    initMobileMenu() {
        // Usar event delegation para evitar listeners duplicados
        // Solo agregar el listener una vez al elemento que nunca cambia
        if (!this.mobileMenuToggle.dataset.listenerAdded) {
            this.mobileMenuToggle.addEventListener('click', () => {
                this.sidebar.classList.toggle('active');
                this.sidebarOverlay.classList.toggle('active');
            });
            this.mobileMenuToggle.dataset.listenerAdded = 'true';
        }
        
        if (!this.sidebarOverlay.dataset.listenerAdded) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.closeMobileMenu();
            });
            this.sidebarOverlay.dataset.listenerAdded = 'true';
        }
    }
    
    closeMobileMenu() {
        this.sidebar.classList.remove('active');
        this.sidebarOverlay.classList.remove('active');
    }
}

// Solo auto-inicializar si el DOM ya está cargado y no se carga dinámicamente
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.dashboardSPA) {
            window.dashboardSPA = new DashboardSPA();
        }
    });
} else {
    // DOM ya está listo (carga estática desde dashboard.blade.php)
    if (!window.dashboardSPA && document.getElementById('spa-content')) {
        window.dashboardSPA = new DashboardSPA();
    }
}