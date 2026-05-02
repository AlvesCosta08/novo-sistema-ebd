<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Histórico de Chamadas';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';

$usuario_id      = $_SESSION['usuario_id'] ?? null;
$nome_usuario    = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil          = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id  = $_SESSION['congregacao_id'] ?? null;

$anoAtual = date('Y');
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-history me-3" style="color: var(--primary-600);"></i>
                Histórico de Chamadas
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
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-history me-1"></i> Histórico
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Consulte, edite ou exclua chamadas registradas
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="text-muted me-2 align-self-center">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nome_usuario) ?>
                <span class="badge-ebd badge-primary ms-1"><?= ucfirst($perfil) ?></span>
            </span>
            <a href="index.php" class="btn btn-modern btn-modern-primary">
                <i class="fas fa-plus me-2"></i> Nova Chamada
            </a>
        </div>
    </div>

    <!-- Card de Filtros - VERSÃO SIMPLIFICADA E CORRIGIDA -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Busca
            </h5>
        </div>
        <div class="card-body p-4">
            <!-- Linha 1 -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <select id="filtroCongregacao" class="form-select" <?= $perfil !== 'admin' ? 'disabled' : '' ?>>
                        <option value="">Todas as congregações</option>
                    </select>
                    <?php if ($perfil !== 'admin' && $congregacao_id): ?>
                        <input type="hidden" id="congregacaoHidden" value="<?= $congregacao_id ?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-users me-1 text-primary"></i> Classe
                    </label>
                    <select id="filtroClasse" class="form-select" disabled>
                        <option value="">Todas as classes</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar me-1 text-primary"></i> Ano
                    </label>
                    <select id="filtroAno" class="form-select">
                        <?php for ($ano = $anoAtual - 2; $ano <= $anoAtual + 1; $ano++): ?>
                            <option value="<?= $ano ?>" <?= $ano == $anoAtual ? 'selected' : '' ?>><?= $ano ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                    </label>
                    <select id="filtroTrimestre" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">1º Trimestre</option>
                        <option value="2">2º Trimestre</option>
                        <option value="3">3º Trimestre</option>
                        <option value="4">4º Trimestre</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar-week me-1 text-primary"></i> Data Início
                    </label>
                    <input type="date" id="filtroDataInicio" class="form-control">
                </div>
            </div>

            <!-- Linha 2 -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <!-- Espaço vazio para alinhamento -->
                </div>

                <div class="col-md-3 mb-3">
                    <!-- Espaço vazio para alinhamento -->
                </div>

                <div class="col-md-2 mb-3">
                    <!-- Espaço vazio para alinhamento -->
                </div>

                <div class="col-md-2 mb-3">
                    <!-- Espaço vazio para alinhamento -->
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar-week me-1 text-primary"></i> Data Fim
                    </label>
                    <input type="date" id="filtroDataFim" class="form-control">
                </div>
            </div>

            <!-- Linha 3 - Botões -->
            <div class="row mt-2">
                <div class="col-12">
                    <button type="button" id="btnFiltrar" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-search me-2"></i> Filtrar
                    </button>
                    <button type="button" id="btnLimparFiltros" class="btn btn-modern btn-outline-secondary ms-2">
                        <i class="fas fa-undo-alt me-2"></i> Limpar
                    </button>
                    <button type="button" id="btnExportarCSV" class="btn btn-modern btn-outline-success ms-2">
                        <i class="fas fa-file-csv me-2"></i> Exportar CSV
                    </button>
                    <span id="resultCount" class="ms-3 text-muted"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="col-sm-6 col-md-3">
            <div class="modern-card text-center p-3 h-100">
                <div class="stat-icon bg-primary bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                    <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                </div>
                <h3 id="totalChamadas" class="mb-0 fs-2 fw-bold">0</h3>
                <p class="text-muted mb-0 small">Total de Chamadas</p>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="modern-card text-center p-3 h-100">
                <div class="stat-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                    <i class="fas fa-user-check fa-2x text-success"></i>
                </div>
                <h3 id="totalPresencas" class="mb-0 fs-2 fw-bold">0</h3>
                <p class="text-muted mb-0 small">Total de Presenças</p>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="modern-card text-center p-3 h-100">
                <div class="stat-icon bg-info bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                    <i class="fas fa-chart-line fa-2x text-info"></i>
                </div>
                <h3 id="mediaPresenca" class="mb-0 fs-2 fw-bold">0<small class="fs-6">%</small></h3>
                <p class="text-muted mb-0 small">Média de Presença</p>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="modern-card text-center p-3 h-100">
                <div class="stat-icon bg-warning bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                    <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                </div>
                <h3 id="totalOfertas" class="mb-0 fs-2 fw-bold">R$ 0,00</h3>
                <p class="text-muted mb-0 small">Total em Ofertas</p>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--gray-700) 0%, var(--gray-800) 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Chamadas Registradas
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="loadingIndicator" class="text-center py-5 d-none">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="text-muted">Carregando chamadas...</p>
            </div>
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Congregação</th>
                            <th>Classe</th>
                            <th>Professor</th>
                            <th>Trimestre</th>
                            <th class="text-center">Presentes</th>
                            <th class="text-center">Ausentes</th>
                            <th class="text-center">Justif.</th>
                            <th class="text-end">Oferta</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaResultados">
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-search fa-3x mb-3 d-block text-muted"></i>
                                <p class="text-muted">Aplique os filtros para visualizar as chamadas</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modais -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-info-circle me-2"></i> Detalhes da Chamada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetalhesBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-trash-alt me-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-4x mb-3" style="color: var(--danger);"></i>
                <p id="msgConfirmacaoExclusao" class="mb-2 fs-5">Tem certeza que deseja excluir esta chamada?</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmaExcluir" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

<style>
.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
/* Garantir que os campos de data apareçam corretamente */
input[type="date"] {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: var(--gray-700);
    background-color: #fff;
    border: 1.5px solid var(--gray-200);
    border-radius: 10px;
    transition: all 0.2s ease;
}
input[type="date"]:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}
</style>

<script>
const USUARIO_PERFIL = '<?= $perfil ?>';
const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
const BASE_URL = '../../controllers/chamada.php';
const ANO_ATUAL = <?= $anoAtual ?>;
</script>
<script src="js/listar.js"></script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>