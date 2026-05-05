<?php
// includes/footer.php - Rodapé do Sistema
?>

    </main><!-- /.main-content -->

    <footer class="footer mt-auto py-3 no-print" style="background: white; border-top: 1px solid #e2e8f0;">
        <div class="container-fluid px-3 px-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    <i class="fas fa-church me-1 text-primary"></i>
                    &copy; <?= date('Y') ?> EBD System — Todos os direitos reservados
                </div>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted small text-decoration-none" data-bs-toggle="modal" data-bs-target="#aboutModal">
                        <i class="fas fa-info-circle me-1"></i> Sobre
                    </a>
                    <span class="text-muted small">|</span>
                    <span class="text-muted small">
                        <i class="fas fa-code-branch me-1"></i> v3.0
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Sobre -->
    <div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                    <h5 class="modal-title text-white" id="aboutModalLabel">
                        <i class="fas fa-church me-2"></i>Sobre o EBD System
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="brand-icon-wrapper rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 70px; height: 70px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                            <img src="<?= BASE_URL ?>/assets/images/biblia.png" alt="Logo" style="width: 40px; height: 40px; filter: brightness(0) invert(1);">
                        </div>
                    </div>
                    <p class="text-center fw-bold mb-1">Escola Bíblica Dominical</p>
                    <p class="text-center text-muted small mb-3">Sistema de Gestão</p>
                    <hr>
                    <p class="small">
                        <strong>Versão:</strong> 3.0<br>
                        <strong>Desenvolvido para:</strong> Gestão completa da EBD<br>
                        <strong>Funcionalidades:</strong> Matrículas, Chamadas, Relatórios, Financeiro<br>
                        <strong>Tecnologias:</strong> PHP, MySQL, Bootstrap 5, jQuery, DataTables
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>