<?php
// includes/footer.php - Rodapé do Sistema
?>

    </main><!-- /.container-fluid.py-3 aberto no header -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <script>
        AOS.init({ duration: 800, once: true });

        // Tooltips Bootstrap
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });

        // Destacar link ativo no menu
        (function() {
            const path = window.location.pathname;
            document.querySelectorAll('.nav-link, .dropdown-item').forEach(function(link) {
                const href = link.getAttribute('href');
                if (href && href !== '#' && path.endsWith(href.split('/').pop())) {
                    link.classList.add('active');
                }
            });
        })();

        // Prevenir duplo envio de formulários
        document.querySelectorAll('form').forEach(function(form) {
            let submitted = false;
            form.addEventListener('submit', function(e) {
                if (submitted) { e.preventDefault(); return false; }
                submitted = true;
                const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (btn) {
                    const orig = btn.innerHTML;
                    btn.disabled = true;
                    if (btn.tagName === 'BUTTON') {
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processando...';
                    }
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = orig;
                        submitted = false;
                    }, 30000);
                }
            });
        });
    </script>

    <?php if (isset($pageSpecificScripts)) echo $pageSpecificScripts; ?>

    <!-- Rodapé HTML -->
    <footer class="bg-white border-top py-3 mt-4">
        <div class="container text-center text-muted small">
            <i class="fas fa-church me-1"></i>
            &copy; <?= date('Y') ?> Escola Bíblica Dominical — Todos os direitos reservados
            <span class="mx-2">|</span>
            <i class="fas fa-code-branch me-1"></i> v3.0
        </div>
    </footer>

</body>
</html>