<?php
// includes/footer.php - LOCALIZAÇÃO: escola/includes/
// Caminhos ajustados para a nova estrutura
?>

<!-- Scripts Externos -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Scripts Locais -->
<?php if (file_exists(__DIR__ . '/../assets/js/app.js')): ?>
    <script src="../assets/js/app.js"></script>
<?php endif; ?>

<script>
(function() {
    'use strict';
    
    function ready(callback) {
        if (document.readyState !== 'loading') { 
            callback(); 
        } else { 
            document.addEventListener('DOMContentLoaded', callback); 
        }
    }
    
    ready(function() {
        // Highlight do menu ativo baseado na URL
        const path = window.location.pathname;
        document.querySelectorAll('.navbar-nav .nav-link').forEach(function(link) {
            if (link.href && path.indexOf(link.href.split('/').pop()) !== -1) {
                link.classList.add('active');
                const dropdown = link.closest('.dropdown');
                if (dropdown) {
                    dropdown.querySelector('.dropdown-toggle')?.classList.add('active');
                }
            }
        });
        
        // Sistema de notificações (Toast/Swal)
        if (typeof Swal !== 'undefined') {
            window.ebdAlert = {
                success: function(msg) { 
                    Swal.fire({icon:'success', title:'Sucesso', text:msg, toast:true, position:'top-end', showConfirmButton:false, timer:3000}); 
                },
                error: function(msg) { 
                    Swal.fire({icon:'error', title:'Erro', text:msg, toast:true, position:'top-end', showConfirmButton:false, timer:4000}); 
                },
                confirm: function(msg) { 
                    return Swal.fire({icon:'question', title:'Confirmar', text:msg, showCancelButton:true, confirmButtonText:'Sim', cancelButtonText:'Não', reverseButtons:true}); 
                }
            };
        }
        
        // Prevenção de duplo envio em formulários
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"], input[type="submit"]');
                if (btn && !btn.disabled) {
                    const original = btn.innerHTML || btn.value;
                    btn.disabled = true;
                    if (btn.tagName === 'BUTTON') {
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    } else {
                        btn.value = 'Processando...';
                    }
                    setTimeout(function() { 
                        btn.disabled = false; 
                        if (btn.tagName === 'BUTTON') {
                            btn.innerHTML = original; 
                        } else {
                            btn.value = original; 
                        }
                    }, 30000);
                }
            });
        });
    });
})();
</script>

<!-- Footer HTML -->
<footer class="bg-white border-top py-3 mt-5">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> Sistema E.B.D - Todos os direitos reservados
        <?php if (defined('APP_VERSION')): ?>
            <span class="ms-2">v<?= APP_VERSION ?></span>
        <?php endif; ?>
    </div>
</footer>

</body>
</html>