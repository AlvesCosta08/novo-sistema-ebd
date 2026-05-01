<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Gerenciar Matrículas';

// Incluir header
require_once __DIR__ . '/../../includes/header.php';

$usuario_id       = $_SESSION['usuario_id'] ?? null;
$nome_usuario     = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil           = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id   = $_SESSION['congregacao_id'] ?? null;

if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado: congregação não definida.');
}

$anoAtual = date('Y');
$trimestreAtual = getTrimestreAtual();
?>

<!-- Conteúdo específico da página -->
<div class="container py-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-2">
                <i class="fas fa-users me-2 text-primary"></i>
                Gerenciar Matrículas
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-church me-1"></i> Escola Bíblica Dominical
            </p>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nome_usuario) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../chamada/listar.php"><i class="fas fa-clipboard-list me-2"></i> Chamadas</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/escola/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
            </ul>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4 g-3" id="statsContainer">
        <div class="col-6 col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">Total Matrículas</h6>
                    <h3 class="mb-0" id="totalMatriculas">--</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">Matrículas Ativas</h6>
                    <h3 class="mb-0" id="matriculasAtivas">--</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">Matrículas Inativas</h6>
                    <h3 class="mb-0" id="matriculasInativas">--</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">Trimestre Atual</h6>
                    <h3 class="mb-0"><?= $anoAtual ?>-T<?= $trimestreAtual ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de ação -->
    <div class="mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalMatricula">
            <i class="fas fa-plus me-2"></i> Nova Matrícula
        </button>
        <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalMigracao">
            <i class="fas fa-exchange-alt me-2"></i> Migrar Matrículas
        </button>
    </div>

    <!-- Tabela de Matrículas -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i> Lista de Matrículas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaMatriculas">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th>Trimestre</th>
                            <th>Data Matrícula</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="8" class="text-center text-muted py-4">Carregando dados...</td></tr>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> <span id="modalTitle">Nova Matrícula</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="matriculaId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Aluno <span class="text-danger">*</span></label>
                        <select id="alunoId" class="form-select" required>
                            <option value="">Selecione um aluno...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                        <select id="classeId" class="form-select" required>
                            <option value="">Selecione uma classe...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Congregação <span class="text-danger">*</span></label>
                        <select id="congregacaoId" class="form-select" required>
                            <option value="">Selecione uma congregação...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Trimestre <span class="text-danger">*</span></label>
                        <select id="trimestre" class="form-select" required>
                            <option value="">Selecione o trimestre...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select id="status" class="form-select" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Data da Matrícula</label>
                        <input type="date" id="dataMatricula" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnSalvarMatricula" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Migração -->
<div class="modal fade" id="modalMigracao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i> Migrar Matrículas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Esta ação copiará todas as matrículas ativas para o novo trimestre.
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Trimestre de Origem</label>
                        <input type="text" id="trimestreOrigem" class="form-control" placeholder="Ex: 2026-T2" value="<?= $anoAtual ?>-T<?= $trimestreAtual ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Trimestre de Destino <span class="text-danger">*</span></label>
                        <input type="text" id="trimestreDestino" class="form-control" placeholder="Ex: 2026-T3" required>
                        <small class="text-muted">Formato: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4</small>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" id="manterStatus" class="form-check-input" checked>
                            <label class="form-check-label">Manter o status original das matrículas</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnMigrar" class="btn btn-info"><i class="fas fa-exchange-alt me-1"></i> Migrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta matrícula?</p>
                <p class="text-danger mb-0"><small><i class="fas fa-info-circle"></i> Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarExcluir" class="btn btn-danger">Excluir</button>
            </div>
        </div>
    </div>
</div>

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