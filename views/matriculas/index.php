<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Gerenciar Matrículas';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';

$usuario_id       = $_SESSION['usuario_id'] ?? null;
$nome_usuario     = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil           = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id   = $_SESSION['congregacao_id'] ?? null;

if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado: congregação não definida.');
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

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-users me-3" style="color: var(--primary-600);"></i>
                Gerenciar Matrículas
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-user-plus me-1"></i> Matrículas
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Gerencie as matrículas dos alunos por trimestre e classe
            </p>
        </div>
        <div>
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nome_usuario) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item" href="../chamada/listar.php"><i class="fas fa-clipboard-list me-2 text-primary"></i> Chamadas</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/escola/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4 g-4" id="statsContainer" data-aos="fade-up" data-aos-delay="100">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalMatriculas">--</div>
                <div class="stat-label">Total Matrículas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value" id="matriculasAtivas">--</div>
                <div class="stat-label">Matrículas Ativas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value" id="matriculasInativas">--</div>
                <div class="stat-label">Matrículas Inativas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value"><?= $anoAtual ?>-T<?= $trimestreAtual ?></div>
                <div class="stat-label">Trimestre Atual</div>
            </div>
        </div>
    </div>

    <!-- Botões de ação -->
    <div class="mb-4" data-aos="fade-up" data-aos-delay="200">
        <button type="button" class="btn btn-modern btn-modern-success" data-bs-toggle="modal" data-bs-target="#modalMatricula">
            <i class="fas fa-plus me-2"></i> Nova Matrícula
        </button>
        <button type="button" class="btn btn-modern btn-modern-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalMigracao">
            <i class="fas fa-exchange-alt me-2"></i> Migrar Matrículas
        </button>
    </div>

    <!-- Tabela de Matrículas -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Matrículas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table mb-0" id="tabelaMatriculas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th>Trimestre</th>
                            <th>Data Matrícula</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando matrículas...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Matrícula -->
<div class="modal fade" id="modalMatricula" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-plus me-2"></i> <span id="modalTitle">Nova Matrícula</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="matriculaId">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-user-graduate text-primary me-1"></i> Aluno <span class="text-danger">*</span>
                        </label>
                        <select id="alunoId" class="form-select" required>
                            <option value="">Selecione um aluno...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">
                            <i class="fas fa-chalkboard-user text-primary me-1"></i> Classe <span class="text-danger">*</span>
                        </label>
                        <select id="classeId" class="form-select" required>
                            <option value="">Selecione uma classe...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">
                            <i class="fas fa-church text-primary me-1"></i> Congregação <span class="text-danger">*</span>
                        </label>
                        <select id="congregacaoId" class="form-select" required>
                            <option value="">Selecione uma congregação...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">
                            <i class="fas fa-chart-line text-primary me-1"></i> Trimestre <span class="text-danger">*</span>
                        </label>
                        <select id="trimestre" class="form-select" required>
                            <option value="">Selecione o trimestre...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">
                            <i class="fas fa-circle text-primary me-1"></i> Status <span class="text-danger">*</span>
                        </label>
                        <select id="status" class="form-select" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Data da Matrícula
                        </label>
                        <input type="date" id="dataMatricula" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnSalvarMatricula" class="btn btn-modern btn-modern-primary">
                    <i class="fas fa-save me-1"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Migração -->
<div class="modal fade" id="modalMigracao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exchange-alt me-2"></i> Migrar Matrículas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-ebd alert-info-ebd mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Esta ação copiará todas as matrículas ativas para o novo trimestre.
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Trimestre de Origem
                        </label>
                        <input type="text" id="trimestreOrigem" class="form-control" 
                               placeholder="Ex: 2026-T2" value="<?= $anoAtual ?>-T<?= $trimestreAtual ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-calendar-plus text-primary me-1"></i> Trimestre de Destino <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="trimestreDestino" class="form-control" 
                               placeholder="Ex: 2026-T3" required>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Formato: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4
                        </small>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" id="manterStatus" class="form-check-input" checked>
                            <label class="form-check-label">
                                <i class="fas fa-check-circle me-1 text-success"></i>
                                Manter o status original das matrículas
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnMigrar" class="btn btn-modern" style="background: var(--info); color: white;">
                    <i class="fas fa-exchange-alt me-1"></i> Migrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-4x mb-3" style="color: var(--danger);"></i>
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir esta matrícula?</p>
                <p class="text-danger small mt-2">
                    <i class="fas fa-info-circle me-1"></i> Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer justify-content-center" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmarExcluir" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para a página de matrículas */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Badge de status */
.badge-status-ativo {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-status-inativo {
    background: linear-gradient(135deg, var(--gray-500) 0%, var(--gray-600) 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* DataTables personalizado */
.dataTables_wrapper .dataTables_filter input {
    border-radius: 10px;
    border: 1.5px solid var(--gray-200);
    padding: 0.5rem 0.75rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.dataTables_wrapper .dataTables_length select {
    border-radius: 10px;
    border: 1.5px solid var(--gray-200);
    padding: 0.25rem 0.5rem;
}

/* Alertas personalizados */
.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 8px;
    padding: 1rem;
}

/* Responsividade */
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
    
    .stat-value {
        font-size: 1.5rem;
    }
}
</style>

<script>
    const USUARIO_PERFIL = '<?= $perfil ?>';
    const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    const USUARIO_ID = <?= (int)$usuario_id ?>;
    const BASE_URL = '/escola/controllers/matricula.php';
    const ANO_ATUAL = <?= $anoAtual ?>;
    const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;
    
    let matriculaIdParaExcluir = null;
</script>
<script src="js/matricula.js"></script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>