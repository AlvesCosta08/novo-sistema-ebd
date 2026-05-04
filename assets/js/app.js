/**
 * assets/js/app.js
 * Módulo principal de funcionalidades frontend do EBD System
 * 
 * @module EBDSystem
 * @version 3.0.0
 */

// Configurações globais
const EBDConfig = {
    apiUrl: window.BASE_API_URL || '/api',
    debug: window.APP_ENV === 'development',
    toastDuration: 4000
};

/**
 * Utilitários do sistema
 */
const EBDUtils = {
    /**
     * Exibe notificação toast
     */
    toast: function(message, type = 'info', title = null) {
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        `;
        
        document.body.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: EBDConfig.toastDuration });
        toast.show();
        
        // Limpeza automática
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        
        return toast;
    },

    /**
     * Confirmação de ação com modal
     */
    confirm: function(message, options = {}) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.setAttribute('tabindex', '-1');
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${options.title || 'Confirmação'}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body"><p>${message}</p></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${options.cancelText || 'Cancelar'}</button>
                            <button type="button" class="btn ${options.confirmClass || 'btn-primary'}" data-action="confirm">${options.confirmText || 'Confirmar'}</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            
            modal.querySelector('[data-action="confirm"]').addEventListener('click', () => {
                resolve(true);
                bsModal.hide();
            });
            
            modal.addEventListener('hidden.bs.modal', () => {
                if (!modal.getAttribute('data-confirmed')) resolve(false);
                modal.remove();
            });
            
            modal.setAttribute('data-confirmed', 'false');
            bsModal.show();
        });
    }
};

// Exportar para escopo global (se necessário para scripts legados)
window.EBDUtils = EBDUtils;
window.EBDConfig = EBDConfig;

// Inicialização automática de DataTables
document.addEventListener('DOMContentLoaded', () => {
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        document.querySelectorAll('.datatable').forEach(table => {
            if (table.id) {
                $(`#${table.id}`).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/2.2.2/i18n/pt-BR.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'desc']],
                    ... (table.dataset.options ? JSON.parse(table.dataset.options) : {})
                });
            }
        });
    }
});