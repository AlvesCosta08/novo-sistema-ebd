<?php
require_once __DIR__ . '/../../auth/valida_sessao.php';
$usuario_id      = $_SESSION['usuario_id'] ?? null;
$nome_usuario    = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil          = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id  = $_SESSION['congregacao_id'] ?? null;

$chamadaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$chamadaId) {
    die("Chamada não especificada.");
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Editar Chamada - Escola Bíblica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border-bottom: none;
        }
        .table-wrapper {
            max-height: 450px;
            overflow-y: auto;
            border-radius: 8px;
        }
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 600px;
        }
        .custom-table thead th {
            background: #f8f9fa;
            padding: 12px 16px;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .custom-table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .custom-table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .radio-option {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            padding: 5px 12px;
            border-radius: 50px;
            transition: all 0.2s ease;
        }
        .radio-option:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .badge-presente { background: linear-gradient(135deg, #198754, #146c43); color: white; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; }
        .badge-ausente { background: linear-gradient(135deg, #dc3545, #b02a37); color: white; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; }
        .badge-justificado { background: linear-gradient(135deg, #ffc107, #d39e00); color: #000; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .spinner-custom {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .btn-modern {
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-modern-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
        }
        .btn-modern-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        @media (max-width: 768px) {
            .custom-table td, .custom-table th { padding: 8px 12px; font-size: 0.875rem; }
            .radio-group { gap: 8px; }
        }
    </style>
</head>
<body class="bg-light">

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