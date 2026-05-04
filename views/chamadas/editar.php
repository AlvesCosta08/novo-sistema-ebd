<?php
// ========== LOGS DE DEPURAÇÃO ==========
// Ativa exibição de erros (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Arquivo de log personalizado (opcional)
$logFile = __DIR__ . '/../../logs/editar_chamada.log';
if (!file_exists(dirname($logFile))) mkdir(dirname($logFile), 0777, true);

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    error_log($logEntry, 3, $logFile);
    error_log($message);
}

logMessage("=== INÍCIO editar.php ===");
logMessage("GET: " . json_encode($_GET));
logMessage("POST: " . json_encode($_POST));

// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    logMessage("Sessão iniciada. Session ID: " . session_id());
}
logMessage("SESSION: " . json_encode($_SESSION));

// Verificar se usuário está logado
require_once '../../auth/valida_sessao.php';
logMessage("Validação de sessão concluída. Usuário ID: " . ($_SESSION['usuario_id'] ?? 'NULO'));

// Função para obter URL base
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    
    // CORREÇÃO: O script está em /sistemas/escola/views/chamada/
    // Precisamos voltar 3 níveis para chegar na raiz do sistema
    $basePath = str_replace('/views/chamada', '', $scriptDir);
    return $protocol . '://' . $host . $basePath;
}

// Configurar título da página
$pageTitle = 'Editar Chamada';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';

$usuario_id      = $_SESSION['usuario_id'] ?? null;
$nome_usuario    = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil          = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id  = $_SESSION['congregacao_id'] ?? null;

// Obtém o ID da chamada via GET (prioritário) ou POST (fallback)
$chamadaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$chamadaId && isset($_POST['id'])) {
    $chamadaId = (int)$_POST['id'];
}

// Verificação final: se ainda não tiver ID, exibe erro amigável
if (!$chamadaId) {
    ?>
    <div class="container-fluid px-4 mt-4">
        <div class="modern-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x mb-3" style="color: var(--danger);"></i>
                <h3 class="mb-3">Chamada não especificada</h3>
                <p class="text-muted mb-4">Nenhuma chamada foi selecionada para edição.</p>
                <a href="index.php" class="btn btn-modern btn-modern-primary">
                    <i class="fas fa-arrow-left me-2"></i> Voltar para Registro de Chamada
                </a>
                <a href="listar.php" class="btn btn-modern btn-modern-secondary ms-2">
                    <i class="fas fa-history me-2"></i> Ver Histórico
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
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
$baseUrl = getBaseUrl();
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-edit me-3" style="color: var(--warning);"></i>
                Editar Chamada #<?= $chamadaId ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php" style="color: var(--primary-600);">
                            <i class="fas fa-book-open me-1"></i> Chamadas
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="listar.php" style="color: var(--primary-600);">
                            <i class="fas fa-history me-1"></i> Histórico
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-edit me-1"></i> Editar #<?= $chamadaId ?>
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Edite os dados da chamada, incluindo presenças, ofertas e informações da aula
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="text-muted me-2 align-self-center">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nome_usuario) ?>
                <span class="badge-ebd badge-primary ms-1"><?= ucfirst($perfil) ?></span>
            </span>
            <a href="listar.php" class="btn btn-modern btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
            <a href="index.php" class="btn btn-modern btn-modern-primary">
                <i class="fas fa-plus me-2"></i> Nova Chamada
            </a>
        </div>
    </div>

    <!-- Card de Edição -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-pencil-alt me-2"></i> Editar Dados da Chamada
            </h5>
        </div>
        <div class="card-body p-4" id="formContainer">
            <div class="text-center py-5">
                <div class="spinner-border mb-3" style="width: 3rem; height: 3rem; color: var(--warning);" role="status"></div>
                <p class="text-muted">Carregando dados da chamada <strong>#<?= $chamadaId ?></strong>...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Alterações
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-save fa-4x mb-3" style="color: var(--warning);"></i>
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja salvar as alterações?</p>
                <div class="alert-ebd alert-warning-ebd mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Esta ação atualizará os dados da chamada permanentemente.</small>
                </div>
            </div>
            <div class="modal-footer justify-content-center" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmarSalvar" class="btn btn-modern" style="background: var(--warning); color: white;">
                    <i class="fas fa-save me-1"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sucesso -->
<div class="modal fade" id="modalSucesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-check-circle me-2"></i> Sucesso!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle fa-4x mb-3" style="color: var(--success);"></i>
                <p id="sucessoMensagem" class="mb-2 fs-5 fw-semibold">Chamada atualizada com sucesso!</p>
                <p class="text-muted">Os dados foram salvos permanentemente.</p>
            </div>
            <div class="modal-footer justify-content-center" style="background: var(--gray-50);">
                <button type="button" id="btnVoltarListagem" class="btn btn-modern btn-modern-success">
                    <i class="fas fa-list me-2"></i> Voltar para Histórico
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Erro -->
<div class="modal fade" id="modalErro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-circle me-2"></i> Erro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-4x mb-3" style="color: var(--danger);"></i>
                <p id="erroMensagem" class="mb-2 fs-5">Ocorreu um erro ao salvar!</p>
                <p class="text-muted">Tente novamente ou contate o administrador.</p>
            </div>
            <div class="modal-footer justify-content-center" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container para Toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<style>
/* Estilos específicos para edição de chamadas */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Alertas personalizados */
.alert-warning-ebd {
    background: linear-gradient(135deg, var(--accent-50) 0%, white 100%);
    border-left: 4px solid var(--warning);
    border-radius: 8px;
    padding: 0.75rem;
}

.formulario-edicao .aluno-row {
    transition: all 0.2s ease;
    border-radius: 12px;
}

.formulario-edicao .aluno-row:hover {
    background: var(--gray-50);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-presente {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
}

.status-ausente {
    background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
    color: white;
}

.status-justificado {
    background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);
    color: white;
}

.btn-quick-select {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn-quick-select:hover {
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .modal-footer {
        flex-direction: column-reverse;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin: 0.25rem 0;
    }
}
</style>

<script>
    // Variáveis globais para o editar.js
    const CHAMADA_ID = <?= $chamadaId ?>;
    const API_URL = '/sistemas/escola/controllers/chamada.php';
    const USUARIO_ID = <?= $usuario_id ?? 0 ?>;
    const USUARIO_PERFIL = '<?= $perfil ?>';
    const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    const ANO_ATUAL = <?= $anoAtual ?>;
    const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;
    
    // Função para exibir mensagem toast (fallback caso não exista no editar.js)
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? 'check-circle' : 'exclamation-triangle';
        
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white show`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fas fa-${icon}"></i>
                <span class="flex-grow-1">${mensagem}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>`;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
    
    // Inicializar AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });

    console.log('Editar chamada - ID:', CHAMADA_ID);
    console.log('API_URL:', API_URL);
</script>

<script src="js/editar.js"></script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>