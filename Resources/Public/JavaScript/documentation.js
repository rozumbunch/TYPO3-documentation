/**
 * Documentation Module JavaScript
 */

function t(key) {
    return window.documentationTranslations && window.documentationTranslations[key] 
        ? window.documentationTranslations[key] 
        : key;
}

function saveToggleStates() {
    const states = {};
    document.querySelectorAll('.rb-doc__nav-toggle').forEach(button => {
        const targetId = button.getAttribute('data-target');
        const element = document.getElementById(targetId);
        if (element) {
            states[targetId] = !element.classList.contains('collapsed');
        }
    });
    localStorage.setItem('docToggleStates', JSON.stringify(states));
}

function restoreToggleStates() {
    const saved = localStorage.getItem('docToggleStates');
    if (!saved) return;
    
    try {
        const states = JSON.parse(saved);
        Object.entries(states).forEach(([targetId, isExpanded]) => {
            const element = document.getElementById(targetId);
            const button = document.querySelector(`[data-target="${targetId}"]`);
            const icon = button?.querySelector('.toggle-icon');
            
            if (element && button && icon) {
                element.classList.toggle('collapsed', !isExpanded);
                button.classList.toggle('active', isExpanded);
                icon.textContent = isExpanded ? '-' : '+';
            }
        });
    } catch (e) {
        console.warn(t('toggleStatesRestoreError'), e);
    }
}

function loadPageContent(path) {
    const contentArea = document.getElementById('rb-doc-content');
    const url = new URL(window.location);
    url.searchParams.set('page', path);
    
    fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.ok ? response.json() : Promise.reject(response))
    .then(data => {
        contentArea.innerHTML = data.success ? data.content : '<div class="rb-doc__error">' + t('contentLoadError') + '</div>';
        window.history.pushState({path}, '', url.toString());
    })
    .catch(() => {
        contentArea.innerHTML = '<div class="rb-doc__error">' + t('contentLoadErrorGeneral') + '</div>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const navToggleMain = document.getElementById('nav-toggle-main');
    const mainNavigation = document.getElementById('main-navigation');
    
    navToggleMain?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const isCollapsed = mainNavigation.classList.contains('collapsed');
        mainNavigation.classList.toggle('collapsed', !isCollapsed);
        this.title = isCollapsed ? t('navigationHide') : t('navigationShow');
    });
    
    const navLinks = document.querySelectorAll('.rb-doc__nav-link');
    const contentArea = document.getElementById('rb-doc-content');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (e.target.closest('.rb-doc__nav-toggle')) return;
            
            e.preventDefault();
            const path = this.getAttribute('data-path');
            if (!path) return;
            
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            showPdfButtons(path);
            
            contentArea.innerHTML = '<div class="rb-doc__loading">' + t('loadingContent') + '</div>';
            loadPageContent(path);
        });
    });
    
    document.querySelectorAll('.rb-doc__nav-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = this.getAttribute('data-target');
            const element = document.getElementById(targetId);
            const icon = this.querySelector('.toggle-icon');
            
            if (element && icon) {
                const isCollapsed = element.classList.contains('collapsed');
                element.classList.toggle('collapsed', !isCollapsed);
                this.classList.toggle('active', !isCollapsed);
                icon.textContent = isCollapsed ? '-' : '+';
                saveToggleStates();
            }
        });
    });
    
    document.querySelectorAll('.rb-doc__nav-pages, .rb-doc__nav-subpages').forEach(subpage => {
        const hasActiveLink = subpage.querySelector('.rb-doc__nav-link.active');
        const button = document.querySelector(`[data-target="${subpage.id}"]`);
        const icon = button?.querySelector('.toggle-icon');
        
        if (!hasActiveLink) {
            subpage.classList.add('collapsed');
            if (icon) icon.textContent = '+';
        } else {
            if (icon) icon.textContent = '-';
            button?.classList.add('active');
        }
    });
    
    restoreToggleStates();
    
    const pagePdfBtn = document.getElementById('export-page-pdf');
    if (pagePdfBtn) {
        pagePdfBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const currentPath = this.getAttribute('data-path');
            if (!currentPath) {
                return;
            }
            downloadPagePdf(currentPath);
        });
    }
    
    const sourcePdfBtn = document.getElementById('export-source-pdf');
    if (sourcePdfBtn) {
        sourcePdfBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            
            const currentPath = this.getAttribute('data-source');
            if (!currentPath) {
                console.error(t('noDataSourceFound'));
                return;
            }
            
           
            let source = 'documentationhub'; // Fallback
            if (currentPath.includes('::')) {
                source = currentPath.split('::')[0];
            }  
            downloadSourcePdf(source);
        });
    }
    
    window.addEventListener('popstate', function(event) {
        if (event.state?.path) {
            navLinks.forEach(l => l.classList.remove('active'));
            const activeLink = document.querySelector(`[data-path="${event.state.path}"]`);
            activeLink?.classList.add('active');
            loadPageContent(event.state.path);
        }
    });
    
});

function showPdfButtons(path) {
    const pdfButtonsContainer = document.querySelector('.rb-doc__pdf-export-buttons');
    const pagePdfBtn = document.getElementById('export-page-pdf');
    const sourcePdfBtn = document.getElementById('export-source-pdf');
    
    if (pdfButtonsContainer && pagePdfBtn && sourcePdfBtn) {
      
        pagePdfBtn.setAttribute('data-path', path);
        sourcePdfBtn.setAttribute('data-source', path);
        pdfButtonsContainer.style.display = 'flex';
        //console.log(t('pdfButtonsDisplayed'), path);
    }
}


function downloadPagePdf(path) {
    const url = new URL(window.location);
    url.searchParams.set('action', 'exportPagePdf');
    url.searchParams.set('path', path);
    const pdfUrl = url.toString();
    
    const a = document.createElement('a');
    a.href = pdfUrl;
    a.download = `seite_${path.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}


function downloadSourcePdf(source) {
    const url = new URL(window.location);
    url.searchParams.set('action', 'exportSourcePdf');
    url.searchParams.set('source', source);
    const pdfUrl = url.toString();
    
    const a = document.createElement('a');
    a.href = pdfUrl;
    a.download = `${source.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
