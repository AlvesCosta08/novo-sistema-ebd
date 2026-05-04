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

<!-- CSS adicional -->
<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}
.spinner-custom {
    width: 50px;
    height: 50px;
    border: 4px solid #fff;
    border-top-color: #4f46e5;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.badge-status-ativo {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}
.badge-status-inativo {
    background: #6b7280;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}
.alert-info-ebd {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    padding: 1rem;
}
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}
.stat-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
}
.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}
.modern-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-header-modern {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}
.custom-table {
    width: 100%;
    margin-bottom: 0;
}
.custom-table th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}
.custom-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #e5e7eb;
}
.btn-modern {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-modern-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border: none;
}
.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.btn-modern-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
}
.btn-modern-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
#toastContainer {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}
</style>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: #1f2937;">
                <i class="fas fa-users me-3" style="color: #4f46e5;"></i>
                Gerenciar Matrículas
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: #4f46e5;">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-user-plus me-1"></i> Matrículas
                    </li>
                </ol>
            </nav>
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
    <div class="row mb-4 g-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalMatriculas">0</div>
                <div class="stat-label">Total Matrículas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value" id="matriculasAtivas">0</div>
                <div class="stat-label">Matrículas Ativas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value" id="matriculasInativas">0</div>
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
    <div class="mb-4">
        <button type="button" class="btn btn-modern btn-modern-success" data-bs-toggle="modal" data-bs-target="#modalMatricula">
            <i class="fas fa-plus me-2"></i> Nova Matrícula
        </button>
        <button type="button" class="btn btn-modern btn-modern-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalMigracao">
            <i class="fas fa-exchange-alt me-2"></i> Migrar Matrículas
        </button>
    </div>

    <!-- Filtros -->
    <div class="modern-card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="filtroStatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="ativo">Ativos</option>
                        <option value="inativo">Inativos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trimestre</label>
                    <select id="filtroTrimestre" class="form-select">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" id="filtroBusca" class="form-control" placeholder="Digite para buscar...">
                </div>
                <div class="col-md-2">
                    <button id="btnFiltrar" class="btn btn-modern btn-modern-primary w-100">
                        <i class="fas fa-search me-2"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Matrículas -->
    <div class="modern-card">
        <div class="card-header-modern">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Matrículas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table mb-0" id="tabelaMatriculas" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th>Trimestre</th>
                            <th>Data Matrícula</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: #4f46e5;"></i>
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
            <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-plus me-2"></i> <span id="modalTitle">Nova Matrícula</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="matriculaId">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label">Aluno <span class="text-danger">*</span></label>
                        <select id="alunoId" class="form-select" required>
                            <option value="">Selecione um aluno...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Classe <span class="text-danger">*</span></label>
                        <select id="classeId" class="form-select" required>
                            <option value="">Selecione uma classe...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Congregação <span class="text-danger">*</span></label>
                        <select id="congregacaoId" class="form-select" required>
                            <option value="">Selecione uma congregação...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Trimestre <span class="text-danger">*</span></label>
                        <select id="trimestre" class="form-select" required>
                            <option value="">Selecione o trimestre...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" class="form-select" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Data da Matrícula</label>
                        <input type="date" id="dataMatricula" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnSalvarMatricula" class="btn btn-modern btn-modern-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Migração -->
<div class="modal fade" id="modalMigracao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <h5 class="modal-title text-white">Migrar Matrículas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-info-ebd mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Esta ação copiará todas as matrículas ativas para o novo trimestre.
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Trimestre de Origem</label>
                        <input type="text" id="trimestreOrigem" class="form-control" value="<?= $anoAtual ?>-T<?= $trimestreAtual ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Trimestre de Destino <span class="text-danger">*</span></label>
                        <input type="text" id="trimestreDestino" class="form-control" placeholder="Ex: 2026-T3" required>
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
                <button type="button" id="btnMigrar" class="btn btn-modern" style="background: #3b82f6; color: white;">Migrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <h5 class="modal-title text-white">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-4x mb-3" style="color: #ef4444;"></i>
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir esta matrícula?</p>
                <p class="text-danger small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarExcluir" class="btn btn-danger">Excluir</button>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer"></div>

<script>
// Configurações globais
const USUARIO_PERFIL = '<?= $perfil ?>';
const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
const USUARIO_ID = <?= (int)$usuario_id ?>;
const BASE_URL = '../../controllers/matricula.php';
const ANO_ATUAL = <?= $anoAtual ?>;
const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;
</script>

<!-- jQuery e Bootstrap primeiro -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Página carregada - Iniciando...');
    
    // Variáveis
    let modalMatricula = new bootstrap.Modal(document.getElementById('modalMatricula'));
    let modalExcluir = new bootstrap.Modal(document.getElementById('modalExcluir'));
    let modalMigracao = new bootstrap.Modal(document.getElementById('modalMigracao'));
    let matriculaIdParaExcluir = null;
    let dataTable = null;
    
    // Função de toast
    function exibirToast(mensagem, tipo = 'success') {
        let toastContainer = document.getElementById('toastContainer');
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${tipo} border-0 show`;
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '300px';
        toastEl.style.marginBottom = '10px';
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${mensagem}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 300);
        }, 4000);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Carregar selects
    function carregarSelects() {
        console.log('Carregando selects...');
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'carregarSelects' },
            dataType: 'json',
            success: function(response) {
                console.log('Selects carregados:', response);
                if (response.sucesso) {
                    const dados = response.dados;
                    
                    $('#alunoId').empty().append('<option value="">Selecione um aluno...</option>');
                    dados.alunos.forEach(aluno => {
                        $('#alunoId').append(`<option value="${aluno.id}">${escapeHtml(aluno.nome)}</option>`);
                    });
                    
                    $('#classeId').empty().append('<option value="">Selecione uma classe...</option>');
                    dados.classes.forEach(classe => {
                        $('#classeId').append(`<option value="${classe.id}">${escapeHtml(classe.nome)}</option>`);
                    });
                    
                    $('#congregacaoId').empty().append('<option value="">Selecione uma congregação...</option>');
                    dados.congregacoes.forEach(cong => {
                        $('#congregacaoId').append(`<option value="${cong.id}">${escapeHtml(cong.nome)}</option>`);
                    });
                    
                    if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) {
                        $('#congregacaoId').val(USUARIO_CONGR_ID).prop('disabled', true);
                    }
                }
            },
            error: function(xhr) {
                console.error('Erro carregarSelects:', xhr);
            }
        });
    }
    
    // Carregar trimestres
    function carregarTrimestres() {
        console.log('Carregando trimestres...');
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'getTrimestresSugeridos' },
            dataType: 'json',
            success: function(response) {
                console.log('Trimestres carregados:', response);
                if (response.sucesso && response.dados) {
                    const select = $('#trimestre');
                    select.empty().append('<option value="">Selecione o trimestre...</option>');
                    response.dados.forEach(trim => {
                        select.append(`<option value="${trim.valor}">${trim.label}</option>`);
                    });
                    
                    const selectFiltro = $('#filtroTrimestre');
                    selectFiltro.empty().append('<option value="">Todos</option>');
                    response.dados.forEach(trim => {
                        selectFiltro.append(`<option value="${trim.valor}">${trim.label}</option>`);
                    });
                    
                    const trimAtual = `${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`;
                    select.val(trimAtual);
                }
            },
            error: function(xhr) {
                console.error('Erro carregarTrimestres:', xhr);
            }
        });
    }
    
    // Inicializar DataTable
    function inicializarDataTable() {
        console.log('Inicializando DataTable...');
        
        dataTable = $('#tabelaMatriculas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL,
                type: 'POST',
                data: function(d) {
                    d.acao = 'listarMatriculas';
                    d.congregacao = USUARIO_PERFIL !== 'admin' ? USUARIO_CONGR_ID : '';
                    d.status = $('#filtroStatus').val();
                    d.trimestre = $('#filtroTrimestre').val();
                    if ($('#filtroBusca').val()) {
                        d.search = { value: $('#filtroBusca').val() };
                    }
                },
                error: function(xhr) {
                    console.error('Erro DataTable:', xhr);
                    exibirToast('Erro ao carregar matrículas', 'danger');
                }
            },
            columns: [
                { data: 'id' },
                { data: 'aluno' },
                { data: 'classe' },
                { data: 'congregacao' },
                { data: 'trimestre' },
                { data: 'data_matricula' },
                { 
                    data: 'status',
                    render: function(data) {
                        return data === 'ativo' 
                            ? '<span class="badge-status-ativo"><i class="fas fa-check-circle me-1"></i> Ativo</span>'
                            : '<span class="badge-status-inativo"><i class="fas fa-times-circle me-1"></i> Inativo</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-editar me-1" data-id="${data.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir" data-id="${data.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            order: [[0, 'desc']],
            drawCallback: function() {
                $('#totalMatriculas').text(dataTable.rows().count());
                let ativas = 0;
                dataTable.rows().data().each(function(row) {
                    if (row.status === 'ativo') ativas++;
                });
                $('#matriculasAtivas').text(ativas);
                $('#matriculasInativas').text(dataTable.rows().count() - ativas);
            }
        });
        
        // Eventos dos botões
        $('#tabelaMatriculas').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            editarMatricula(id);
        });
        
        $('#tabelaMatriculas').on('click', '.btn-excluir', function() {
            matriculaIdParaExcluir = $(this).data('id');
            modalExcluir.show();
        });
    }
    
    // Funções CRUD
    function salvarMatricula() {
        const id = $('#matriculaId').val();
        const dados = {
            aluno_id: $('#alunoId').val(),
            classe_id: $('#classeId').val(),
            congregacao_id: $('#congregacaoId').val(),
            professor_id: USUARIO_ID,
            trimestre: $('#trimestre').val(),
            status: $('#status').val(),
            data_matricula: $('#dataMatricula').val()
        };
        
        if (!dados.aluno_id || !dados.classe_id || !dados.congregacao_id || !dados.trimestre) {
            exibirToast('Preencha todos os campos obrigatórios', 'warning');
            return;
        }
        
        const acao = id ? 'atualizarMatricula' : 'criarMatricula';
        if (id) dados.id = id;
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { ...dados, acao: acao },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    modalMatricula.hide();
                    dataTable.ajax.reload();
                    limparFormulario();
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function() {
                exibirToast('Erro ao salvar matrícula', 'danger');
            }
        });
    }
    
    function editarMatricula(id) {
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'buscarMatricula', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso && response.dados) {
                    const dados = response.dados;
                    $('#matriculaId').val(dados.id);
                    $('#alunoId').val(dados.aluno_id);
                    $('#classeId').val(dados.classe_id);
                    $('#congregacaoId').val(dados.congregacao_id);
                    $('#trimestre').val(dados.trimestre);
                    $('#status').val(dados.status);
                    $('#dataMatricula').val(dados.data_matricula);
                    $('#modalTitle').text('Editar Matrícula');
                    modalMatricula.show();
                } else {
                    exibirToast('Matrícula não encontrada', 'danger');
                }
            },
            error: function() {
                exibirToast('Erro ao buscar matrícula', 'danger');
            }
        });
    }
    
    function excluirMatricula() {
        if (!matriculaIdParaExcluir) return;
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'excluirMatricula', id: matriculaIdParaExcluir },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    dataTable.ajax.reload();
                    modalExcluir.hide();
                    matriculaIdParaExcluir = null;
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function() {
                exibirToast('Erro ao excluir matrícula', 'danger');
            }
        });
    }
    
    function migrarMatriculas() {
        const trimestreOrigem = $('#trimestreOrigem').val();
        const trimestreDestino = $('#trimestreDestino').val();
        const manterStatus = $('#manterStatus').is(':checked');
        
        if (!trimestreDestino) {
            exibirToast('Informe o trimestre de destino', 'warning');
            return;
        }
        
        if (!trimestreDestino.match(/^\d{4}-T[1-4]$/)) {
            exibirToast('Formato inválido. Use: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4', 'warning');
            return;
        }
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: {
                acao: 'migrarMatriculas',
                trimestre_atual: trimestreOrigem,
                novo_trimestre: trimestreDestino,
                congregacao_id: USUARIO_PERFIL !== 'admin' ? USUARIO_CONGR_ID : $('#congregacaoId').val(),
                manter_status: manterStatus ? 1 : 0
            },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    modalMigracao.hide();
                    dataTable.ajax.reload();
                    $('#trimestreDestino').val('');
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function() {
                exibirToast('Erro ao migrar matrículas', 'danger');
            }
        });
    }
    
    function limparFormulario() {
        $('#matriculaId').val('');
        $('#alunoId').val('');
        $('#classeId').val('');
        if (USUARIO_PERFIL === 'admin') {
            $('#congregacaoId').prop('disabled', false).val('');
        } else {
            $('#congregacaoId').prop('disabled', true).val(USUARIO_CONGR_ID);
        }
        $('#trimestre').val(`${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`);
        $('#status').val('ativo');
        $('#dataMatricula').val(new Date().toISOString().split('T')[0]);
        $('#modalTitle').text('Nova Matrícula');
    }
    
    // Eventos
    $('#btnSalvarMatricula').on('click', salvarMatricula);
    $('#btnConfirmarExcluir').on('click', excluirMatricula);
    $('#btnMigrar').on('click', migrarMatriculas);
    $('#btnFiltrar').on('click', function() {
        dataTable.ajax.reload();
    });
    $('#filtroBusca').on('keypress', function(e) {
        if (e.which === 13) dataTable.ajax.reload();
    });
    
    $('#modalMatricula').on('hidden.bs.modal', limparFormulario);
    
    // Inicialização
    carregarSelects();
    carregarTrimestres();
    setTimeout(function() {
        inicializarDataTable();
    }, 1000);
    
    console.log('Inicialização completa!');
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>