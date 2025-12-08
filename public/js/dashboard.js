/**
 * Dashboard SPA
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
    }
    
    initLogout() {
        document.querySelectorAll('[data-action="logout"]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                await this.logout();
            });
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
    
    toggleMobileMenu() {
        this.sidebar.classList.toggle('active');
        this.sidebarOverlay.classList.toggle('active');
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