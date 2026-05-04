<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once '../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Registrar Chamada';

// Função para obter URL base (consistente com editar.php)
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    
    // CORREÇÃO: O script está em /sistemas/escola/views/chamada/
    // Precisamos voltar 3 níveis para chegar na raiz do sistema
    $basePath = str_replace('/views/chamada', '', $scriptDir);
    return $protocol . '://' . $host . $basePath;
}
// Incluir header (já contém navbar e todos os CSS/JS)
require_once '../../includes/header.php';

// Recupera dados do usuário logado
$usuario_id       = $_SESSION['usuario_id'] ?? null;
$nome_usuario     = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil           = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id   = $_SESSION['congregacao_id'] ?? null;

// Verificar se a congregação está definida para não-admin
if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado: congregação não definida para este usuário.');
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
                <i class="fas fa-clipboard-list me-3" style="color: var(--primary-600);"></i>
                Registro de Chamada
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= $baseUrl ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-book-open me-1"></i> Registrar Chamada
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge-ebd badge-primary">
                <i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
            </span>
        </div>
    </div>

    <!-- Card Principal - Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros da Aula
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Congregação -->
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-church me-2 text-primary"></i>
                        <span class="fw-semibold">Congregação</span>
                    </label>
                    <select id="congregacaoSelect" class="form-select" <?= $perfil !== 'admin' ? 'disabled' : '' ?>>
                        <option value="">Selecione uma congregação...</option>
                    </select>
                    <?php if ($perfil !== 'admin' && $congregacao_id): ?>
                        <input type="hidden" id="congregacaoHidden" value="<?= $congregacao_id ?>">
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Filtrado pela sua congregação
                        </small>
                    <?php endif; ?>
                </div>

                <!-- Classe -->
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-users me-2 text-primary"></i>
                        <span class="fw-semibold">Classe</span>
                    </label>
                    <select id="classeSelect" class="form-select" disabled>
                        <option value="">Selecione uma classe primeiro</option>
                    </select>
                </div>

                <!-- Ano -->
                <div class="col-6 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar me-2 text-primary"></i>
                        <span class="fw-semibold">Ano</span>
                    </label>
                    <select id="anoSelect" class="form-select">
                        <?php
                        for ($ano = $anoAtual - 1; $ano <= $anoAtual + 1; $ano++) {
                            $selected = $ano == $anoAtual ? 'selected' : '';
                            echo "<option value=\"$ano\" $selected>$ano</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Trimestre -->
                <div class="col-6 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        <span class="fw-semibold">Trimestre</span>
                    </label>
                    <select id="trimestreSelect" class="form-select">
                        <option value="1" <?= $trimestreAtual == 1 ? 'selected' : '' ?>>1º Trimestre</option>
                        <option value="2" <?= $trimestreAtual == 2 ? 'selected' : '' ?>>2º Trimestre</option>
                        <option value="3" <?= $trimestreAtual == 3 ? 'selected' : '' ?>>3º Trimestre</option>
                        <option value="4" <?= $trimestreAtual == 4 ? 'selected' : '' ?>>4º Trimestre</option>
                    </select>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="button" id="btnCarregarAlunos" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-users me-2"></i> Carregar Alunos
                    </button>
                    <button type="button" id="btnVerificarChamada" class="btn btn-modern btn-modern-secondary ms-2">
                        <i class="fas fa-search me-2"></i> Verificar Chamada
                    </button>
                    <span id="loadingAlunos" class="ms-3 d-none">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        <span class="ms-2">Carregando...</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações da Aula -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--gray-700) 0%, var(--gray-800) 100%); color: white;">
            <h5 class="mb-0">
                <i class="fas fa-calendar-check me-2"></i> Informações da Aula
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-calendar-day me-2 text-primary"></i>
                        <span class="fw-semibold">Data da Aula</span>
                    </label>
                    <input type="date" id="dataChamada" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-tag me-2 text-primary"></i>
                        <span class="fw-semibold">Trimestre de Registro</span>
                    </label>
                    <div class="alert-ebd alert-info-ebd mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong id="trimestreFormatadoDisplay"><?= $anoAtual ?>-T<?= $trimestreAtual ?></strong>
                        <small class="d-block mt-1">Formato salvo: ANO-TRIMESTRE</small>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-chalkboard-user me-2 text-primary"></i>
                        <span class="fw-semibold">Professor</span>
                    </label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($nome_usuario) ?>" readonly disabled>
                    <input type="hidden" id="professorId" value="<?= $usuario_id ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta de chamada existente -->
    <div id="chamadaExistenteAlert" class="alert-ebd alert-warning-ebd d-none mb-4" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="fas fa-exclamation-triangle me-2" style="color: var(--warning);"></i>
                <span id="chamadaExistenteMsg" class="fw-semibold"></span>
            </div>
            <button type="button" id="btnEditarExistente" class="btn btn-modern" style="background: var(--warning); color: white;">
                <i class="fas fa-edit me-1"></i> Editar Chamada Existente
            </button>
        </div>
    </div>

    <!-- Tabela de Alunos -->
    <div id="containerAlunos" class="modern-card mb-4 d-none" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-success">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-users me-2"></i> Alunos Matriculados
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" id="btnSelectAllPresentes" class="btn btn-light btn-sm">
                        <i class="fas fa-check-double me-1" style="color: var(--success);"></i> Marcar Todos
                    </button>
                    <button type="button" id="btnClearAll" class="btn btn-light btn-sm">
                        <i class="fas fa-undo-alt me-1" style="color: var(--warning);"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Nome do Aluno</th>
                            <th style="min-width: 200px">Status de Presença</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaAlunos">
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-users-slash fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                                <p class="text-muted mb-0">Nenhum aluno carregado.<br>Selecione uma classe e clique em "Carregar Alunos".</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totais e Ofertas -->
    <div id="containerTotais" class="modern-card mb-4 d-none" data-aos="fade-up" data-aos-delay="400">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%); color: white;">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i> Resumo da Aula
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-dollar-sign me-2" style="color: var(--success);"></i>
                        <span class="fw-semibold">Oferta (R$)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" step="0.01" min="0" id="ofertaClasse" class="form-control" value="0.00">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-user-plus me-2" style="color: var(--info);"></i>
                        <span class="fw-semibold">Visitantes</span>
                    </label>
                    <input type="number" min="0" id="totalVisitantes" class="form-control" value="0">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-book me-2 text-primary"></i>
                        <span class="fw-semibold">Bíblias</span>
                    </label>
                    <input type="number" min="0" id="totalBiblias" class="form-control" value="0">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-magazine me-2" style="color: var(--warning);"></i>
                        <span class="fw-semibold">Revistas</span>
                    </label>
                    <input type="number" min="0" id="totalRevistas" class="form-control" value="0">
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="button" id="btnSalvarChamada" class="btn btn-modern btn-modern-success">
                        <i class="fas fa-save me-2"></i> Salvar Chamada
                    </button>
                    <span id="loadingSalvar" class="ms-3 d-none">
                        <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                        <span class="ms-2">Salvando...</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button para Mobile -->
<button id="fabSalvar" class="fab-mobile" style="display: none;">
    <i class="fas fa-save fa-lg"></i>
</button>

<style>
/* Estilos adicionais específicos para esta página */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

.input-group-text {
    background-color: var(--gray-100);
    border: 1.5px solid var(--gray-200);
    border-radius: 10px 0 0 10px;
}

.form-select:disabled {
    background-color: var(--gray-100);
    cursor: not-allowed;
}

.modern-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.alert-ebd {
    border-radius: 12px;
    padding: 16px 20px;
}

.fab-mobile {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
    z-index: 1000;
}

.fab-mobile:hover {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 0 16px !important;
    }
    
    .display-5 {
        font-size: 1.75rem;
    }
    
    .btn-modern {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .btn-modern.ms-2 {
        margin-left: 0 !important;
    }
}
</style>

<script>
// CORREÇÃO: Usar caminho absoluto a partir da raiz do site
const API_URL = '../../controllers/chamada.php';
const USUARIO_PERFIL = '<?= $perfil ?>';
const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
const USUARIO_ID = <?= (int)$usuario_id ?>;
const ANO_ATUAL = <?= $anoAtual ?>;
const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;

console.log('API_URL:', API_URL);
</script>
<!-- Carregar o JavaScript externo -->
<script src="js/chamada.js"></script>

<?php
// Incluir footer
require_once '../../includes/footer.php';
?>