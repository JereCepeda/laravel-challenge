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
        this.loadedScripts = new Set();

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

            // Procesar HTML antes de inyectarlo
            await this.injectContentWithScripts(html);

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

    /**
     * Inyecta contenido HTML procesando scripts ANTES de que Alpine lo vea
     */
    async injectContentWithScripts(html) {
        // 1. Reemplazar x-data en el HTML string antes de parsearlo
        // Usamos un placeholder que Alpine no reconoce
        let processedHtml = html.replace(/x-data=/g, 'data-x-data-deferred=');

        // 2. Crear contenedor temporal FUERA del DOM
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = processedHtml;

        // 3. Extraer scripts 
        const scripts = Array.from(tempDiv.querySelectorAll('script'));
        const externalScripts = [];
        const inlineScripts = [];

        scripts.forEach(script => {
            if (script.src) {
                if (!script.src.includes('dashboard.js')) {
                    externalScripts.push(script.src);
                }
            } else if (script.textContent.trim()) {
                inlineScripts.push(script.textContent);
            }
            script.remove();
        });

        // 4. Inyectar HTML (sin x-data, Alpine lo ignora completamente)
        this.contentContainer.innerHTML = tempDiv.innerHTML;

        // 5. Cargar scripts externos en orden SECUENCIAL
        for (const src of externalScripts) {
            await this.loadExternalScriptByUrl(src);
        }

        // 6. Ejecutar scripts inline
        for (const code of inlineScripts) {
            try {
                const fn = new Function(code);
                fn();
            } catch (e) {
                console.error('Error ejecutando script inline:', e);
            }
        }

        // 7. Ahora que los scripts cargaron, restaurar x-data y activar Alpine
        const pendingElements = this.contentContainer.querySelectorAll('[data-x-data-deferred]');
        
        pendingElements.forEach(el => {
            const xDataValue = el.getAttribute('data-x-data-deferred');
            el.removeAttribute('data-x-data-deferred');
            el.setAttribute('x-data', xDataValue);
            
            // Inicializar Alpine en este elemento
            if (window.Alpine) {
                window.Alpine.initTree(el);
            }
        });

        console.log(`✅ Contenido inyectado. Scripts: ${externalScripts.length} externos, ${inlineScripts.length} inline. Alpine elements: ${pendingElements.length}`);
    }

    /**
     * Carga un script externo por URL
     */
    loadExternalScriptByUrl(src) {
        return new Promise((resolve) => {
            // Normalizar URL
            const normalizedSrc = src.startsWith('http') ? src : new URL(src, window.location.origin).href;

            // Ya cargado?
            if (this.loadedScripts.has(normalizedSrc)) {
                console.log(`Script cargado: ${src}`);
                return resolve();
            }

            console.log(`Cargando script: ${src}`);

            const script = document.createElement('script');
            script.src = src;
            
            script.onload = () => {
                console.log(`Script cargado: ${src}`);
                this.loadedScripts.add(normalizedSrc);
                resolve();
            };
            
            script.onerror = () => {
                console.error(`Error cargando script: ${src}`);
                resolve(); // Continuar aunque falle
            };

            document.head.appendChild(script);
        });
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

    async executeScripts(container) {
        // Para la carga inicial (no SPA)
        const scripts = Array.from(container.querySelectorAll('script'));

        for (const oldScript of scripts) {
            if (oldScript.src && oldScript.src.includes('dashboard.js')) {
                oldScript.remove();
                continue;
            }

            if (oldScript.src) {
                await this.loadExternalScript(oldScript);
                oldScript.remove();
            }
        }
    }

    loadExternalScript(oldScript) {
        return new Promise((resolve, reject) => {
            const src = oldScript.getAttribute('src');
            if (!src) return resolve();

            // Normalizar URL
            const normalizedSrc = src.startsWith('http') ? src : new URL(src, window.location.origin).href;

            if (this.loadedScripts.has(normalizedSrc)) {
                console.log(`Script ya cargado: ${src}`);
                return resolve();
            }

            // Ya existe en DOM?
            if (document.querySelector(`script[src="${src}"]`) ||
                document.querySelector(`script[src="${normalizedSrc}"]`)) {
                this.loadedScripts.add(normalizedSrc);
                return resolve();
            }

            console.log(`Cargando script: ${src}`);

            const script = document.createElement('script');
            script.src = src;
            script.async = false;
            
            script.onload = () => {
                console.log(`Script cargado: ${src}`);
                this.loadedScripts.add(normalizedSrc);
                resolve();
            };
            
            script.onerror = () => {
                console.error(`Error cargando script: ${src}`);
                // No rechazar, continuar con otros scripts
                resolve();
            };

            document.body.appendChild(script);
        });
    }

    initializeBootstrapComponents() {
        // Inicializar tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));

        // Inicializar popovers
        const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
        popovers.forEach(el => new bootstrap.Popover(el));
    }

    initMobileMenu() {
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

// Auto-inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.dashboardSPA) {
            window.dashboardSPA = new DashboardSPA();
        }
    });
} else {
    if (!window.dashboardSPA && document.getElementById('spa-content')) {
        window.dashboardSPA = new DashboardSPA();
    }
}