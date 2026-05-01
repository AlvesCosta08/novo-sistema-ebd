<?php
require_once __DIR__ . '/../../auth/valida_sessao.php';
$usuario_id      = $_SESSION['usuario_id'] ?? null;
$nome_usuario    = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil          = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id  = $_SESSION['congregacao_id'] ?? null;

// Obtém o ID da chamada via GET
$chamadaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$chamadaId) {
    die("Chamada não especificada.");
}

// Função para obter o trimestre atual
function getTrimestreAtual() {
    $mes = date('n');
    if ($mes >= 1 && $mes <= 3) return 1;
    if ($mes >= 4 && $mes <= 6) return 2;
    if ($mes >= 7 && $mes <= 9) return 3;
    return 4;
}

$anoAtual = date('Y');
$trimestreAtual = getTrimestreAtual();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Chamada - Escola Bíblica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .radio-group label { 
            margin-right: 10px; 
            cursor: pointer; 
        }
        .card-header-custom {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .table-fixed-header {
            max-height: 400px;
            overflow-y: auto;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .form-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .loading-overlay {
            position: relative;
            min-height: 200px;
        }
        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2><i class="fas fa-edit me-2 text-primary"></i>Editar Chamada</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Chamada</a></li>
                    <li class="breadcrumb-item"><a href="listar.php">Histórico</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Chamada #<?= $chamadaId ?></li>
                </ol>
            </nav>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="text-muted me-3">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($nome_usuario) ?>
                <span class="badge bg-secondary"><?= ucfirst($perfil) ?></span>
            </span>
            <a href="listar.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <a href="index.php" class="btn btn-outline-primary btn-sm ms-1">
                <i class="fas fa-plus"></i> Nova Chamada
            </a>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card shadow">
        <div class="card-header card-header-custom">
            <h5 class="mb-0"><i class="fas fa-pencil-alt me-2"></i>Editar Dados da Chamada</h5>
        </div>
        <div class="card-body" id="formContainer">
            <!-- Carregado dinamicamente via JavaScript -->
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="text-muted">Carregando dados da chamada...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Atenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalConfirmacaoBody">
                Tem certeza que deseja salvar as alterações?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarSalvar" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Variáveis globais
    const CHAMADA_ID = <?= $chamadaId ?>;
    const USUARIO_ID = <?= $usuario_id ?>;
    const USUARIO_PERFIL = '<?= $perfil ?>';
    const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    const BASE_URL = '/escola/controllers/chamada.php';
    const ANO_ATUAL = <?= $anoAtual ?>;
    const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;
    
    // Função auxiliar para debug (opcional)
    function logDebug(message, data = null) {
        if (console && console.log) {
            console.log(`[Editar Chamada] ${message}`, data ? data : '');
        }
    }
    
    // Função para exibir toast de notificação
    function showToast(message, type = 'success') {
        // Cria container de toasts se não existir
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.style.minWidth = '250px';
        toastEl.style.marginBottom = '10px';
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toastEl);
        
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
</script>
<script src="js/editar.js"></script>
</body>
</html>