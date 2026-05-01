<?php
/**
 * Footer Principal do Sistema
 * 
 * Este arquivo deve ser incluído no final de TODAS as páginas do sistema
 * 
 * @package Escola\Includes
 * @version 3.0
 */
?>
</main> <!-- Fechamento do main iniciado no header -->

<!-- Scripts Externos -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Scripts Locais -->
<?php if (file_exists(__DIR__ . '/../assets/js/app.js')): ?>
    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
<?php endif; ?>

<script>
(function() {
    'use strict';
    
    // Inicialização quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function() {
        
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function(popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Auto-fechar alertas após 5 segundos
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
            setTimeout(function() {
                if (alert && alert.remove) {
                    alert.remove();
                }
            }, 5000);
        });
        
        // Destacar menu ativo
        const currentPath = window.location.pathname;
        document.querySelectorAll('.navbar-nav .nav-link').forEach(function(link) {
            const href = link.getAttribute('href');
            if (href && href !== '#' && currentPath.indexOf(href) !== -1) {
                link.classList.add('active');
                // Se estiver dentro de dropdown, ativar também o dropdown
                const dropdown = link.closest('.dropdown');
                if (dropdown) {
                    const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                    if (dropdownToggle) dropdownToggle.classList.add('active');
                }
            }
        });
        
        // Prevenir duplo envio de formulários
        document.querySelectorAll('form').forEach(function(form) {
            let submitted = false;
            form.addEventListener('submit', function(e) {
                if (submitted) {
                    e.preventDefault();
                    return false;
                }
                submitted = true;
                
                // Desabilitar botão submit
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processando...';
                        setTimeout(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            submitted = false;
                        }, 30000);
                    }
                }
            });
        });
    });
})();

// Função para escapar HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Função para debug (remover em produção)
function logDebug(message, data) {
    if (console && console.log && (typeof DEBUG !== 'undefined' && DEBUG)) {
        console.log(`[DEBUG] ${message}`, data || '');
    }
}
</script>

<!-- Footer HTML -->
<footer class="bg-white border-top py-3 mt-4">
    <div class="container text-center text-muted small">
        <div class="row">
            <div class="col-12">
                <i class="fas fa-church me-1"></i>
                &copy; <?= date('Y') ?> Escola Bíblica Dominical - Todos os direitos reservados
            </div>
            <div class="col-12 mt-1">
                <small>
                    <i class="fas fa-code-branch me-1"></i> Versão 3.0
                    <span class="mx-1">|</span>
                    <i class="fas fa-database me-1"></i> Sistema de Gestão EBD
                </small>
            </div>
        </div>
    </div>
</footer>

</body>
</html>