<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Editar Chamada';

// Incluir header
require_once __DIR__ . '/../../includes/header.php';

$usuario_id      = $_SESSION['usuario_id'] ?? null;
$nome_usuario    = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil          = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id  = $_SESSION['congregacao_id'] ?? null;

// Obtém o ID da chamada via GET
$chamadaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$chamadaId) {
    die("Chamada não especificada.");
}

$anoAtual = date('Y');
$trimestreAtual = getTrimestreAtual();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="text-primary"><i class="fas fa-edit me-2"></i>Editar Chamada</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Chamada</a></li>
                    <li class="breadcrumb-item"><a href="listar.php">Histórico</a></li>
                    <li class="breadcrumb-item active">Editar #<?= $chamadaId ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="text-muted me-3"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nome_usuario) ?> <span class="badge bg-secondary"><?= ucfirst($perfil) ?></span></span>
            <a href="listar.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar</a>
            <a href="index.php" class="btn btn-outline-primary btn-sm ms-1"><i class="fas fa-plus"></i> Nova Chamada</a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header card-header-custom">
            <h5 class="mb-0"><i class="fas fa-pencil-alt me-2"></i> Editar Dados da Chamada</h5>
        </div>
        <div class="card-body p-4" id="formContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <p class="text-muted">Carregando dados da chamada...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirmar Alterações</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalConfirmacaoBody">
                <p>Tem certeza que deseja salvar as alterações?</p>
                <div class="alert alert-info mt-2 mb-0"><i class="fas fa-info-circle me-2"></i><small>Esta ação atualizará os dados da chamada permanentemente.</small></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarSalvar" class="btn btn-warning"><i class="fas fa-save me-1"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sucesso -->
<div class="modal fade" id="modalSucesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i> Sucesso!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <p id="sucessoMensagem" class="mb-0">Chamada atualizada com sucesso!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" id="btnVoltarListagem"><i class="fas fa-list me-1"></i> Voltar para Histórico</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const CHAMADA_ID = <?= $chamadaId ?>;
    const USUARIO_ID = <?= $usuario_id ?>;
    const USUARIO_PERFIL = '<?= $perfil ?>';
    const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    const BASE_URL = '/escola/controllers/chamada.php';
    const ANO_ATUAL = <?= $anoAtual ?>;
    const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;
</script>
<script src="js/editar.js"></script>
</body>
</html>