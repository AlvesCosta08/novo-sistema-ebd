<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../auth/valida_sessao.php';

$pageTitle = 'Listar Matrículas';
require_once __DIR__ . '/../../includes/header.php';

$usuario_id     = $_SESSION['usuario_id'] ?? null;
$nome_usuario   = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil         = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id = $_SESSION['congregacao_id'] ?? null;

if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado.');
}

function getTrimestreAtual() {
    $mes = (int)date('n');
    if ($mes <= 3) return 1;
    if ($mes <= 6) return 2;
    if ($mes <= 9) return 3;
    return 4;
}
$anoAtual       = (int)date('Y');
$trimestreAtual = getTrimestreAtual();
?>

<style>
.loading-overlay {
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,.5); display:none;
    justify-content:center; align-items:center; z-index:10000;
}
.spinner-custom {
    width:50px; height:50px;
    border:4px solid #fff; border-top-color:#4f46e5;
    border-radius:50%; animation:spin 1s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }
.badge-id { background:#f3f4f6; padding:.25rem .5rem; border-radius:8px; }
.badge-trimestre { background:#e0e7ff; padding:.25rem .5rem; border-radius:8px; }
.badge-status-ativo {
    background:linear-gradient(135deg,#10b981,#059669); color:#fff;
    padding:.25rem .75rem; border-radius:20px; font-size:.75rem; font-weight:600; display:inline-block;
}
.badge-status-inativo {
    background:#6b7280; color:#fff;
    padding:.25rem .75rem; border-radius:20px; font-size:.75rem; font-weight:600; display:inline-block;
}
.modern-card { background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,.1); overflow:hidden; }
.card-header-modern { padding:1rem 1.25rem; background:linear-gradient(135deg,#4f46e5,#7c3aed); }
.custom-table { width:100%; margin-bottom:0; }
.custom-table th { background:#f9fafb; padding:.75rem 1rem; font-weight:600; color:#374151; border-bottom:2px solid #e5e7eb; }
.custom-table td { padding:.75rem 1rem; vertical-align:middle; border-bottom:1px solid #e5e7eb; }
.btn-modern { padding:.5rem 1rem; border-radius:8px; font-weight:500; transition:all .2s; }
.btn-modern-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; border:none; }
.btn-modern-primary:hover { transform:translateY(-2px); box-shadow:0 4px 6px rgba(0,0,0,.15); color:#fff; }
.stat-card { background:#fff; border-radius:16px; padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,.1); }
.stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:.75rem; }
.stat-value { font-size:1.875rem; font-weight:700; color:#1f2937; }
.stat-label { font-size:.875rem; color:#6b7280; }
.btn-action {
    display:inline-flex; align-items:center; justify-content:center;
    width:32px; height:32px; border-radius:8px; border:none; cursor:pointer; transition:all .2s;
}
.btn-action:hover { transform:translateY(-2px); }
.btn-view { background:#e0e7ff; color:#4f46e5; }
.btn-view:hover { background:#c7d2fe; }
.btn-edit { background:#fef3c7; color:#d97706; }
.btn-edit:hover { background:#fde68a; }
.btn-delete { background:#fee2e2; color:#ef4444; }
.btn-delete:hover { background:#fecaca; }
.detalhe-item { display:flex; justify-content:space-between; align-items:center; padding:.6rem 0; border-bottom:1px solid #e5e7eb; }
.detalhe-item:last-child { border-bottom:none; }
.detalhe-label { font-weight:600; color:#374151; }
</style>

<div class="container-fluid px-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color:#1f2937;">
                <i class="fas fa-list me-3" style="color:#4f46e5;"></i>Listar Matrículas
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color:#4f46e5;"><i class="fas fa-home me-1"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php" style="color:#4f46e5;"><i class="fas fa-user-plus me-1"></i>Matrículas</a>
                    </li>
                    <li class="breadcrumb-item active"><i class="fas fa-list me-1"></i>Listar</li>
                </ol>
            </nav>
        </div>
        <a href="index.php" class="btn btn-modern btn-modern-primary">
            <i class="fas fa-plus me-2"></i>Nova Matrícula
        </a>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4 g-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
                <div class="stat-value" id="totalMatriculas">—</div>
                <div class="stat-label">Total (página)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value" id="matriculasAtivas">—</div>
                <div class="stat-label">Ativas (página)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-times-circle"></i></div>
                <div class="stat-value" id="matriculasInativas">—</div>
                <div class="stat-label">Inativas (página)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-value"><?= $anoAtual ?>-T<?= $trimestreAtual ?></div>
                <div class="stat-label">Trimestre atual</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="modern-card mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select id="filtroStatus" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Trimestre</label>
                    <select id="filtroTrimestre" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-<?= $perfil === 'admin' ? '3' : '5' ?>">
                    <label class="form-label mb-1">Buscar</label>
                    <input type="text" id="filtroBusca" class="form-control form-control-sm" placeholder="Aluno, classe ou congregação...">
                </div>
                <?php if ($perfil === 'admin'): ?>
                <div class="col-md-2">
                    <label class="form-label mb-1">Congregação</label>
                    <select id="filtroCongregacao" class="form-select form-select-sm"><option value="">Todas</option></select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <button id="btnFiltrar" class="btn btn-modern btn-modern-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="modern-card">
        <div class="card-header-modern">
            <h5 class="mb-0 text-white"><i class="fas fa-table me-2"></i>Matrículas Registradas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table" id="tabelaMatriculas" width="100%">
                    <thead>
                        <tr>
                            <th style="width:60px">ID</th>
                            <th>Aluno</th><th>Classe</th><th>Congregação</th>
                            <th style="width:100px">Trimestre</th>
                            <th style="width:110px">Data</th>
                            <th style="width:100px">Status</th>
                            <th style="width:110px" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ====== MODAL: VISUALIZAR ====== -->
<div class="modal fade" id="modalVisualizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <h5 class="modal-title text-white"><i class="fas fa-info-circle me-2"></i>Detalhes da Matrícula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalhesMatricula">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 mb-0">Carregando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Fechar
                </button>
                <button type="button" id="btnEditarDoModal" class="btn btn-modern btn-modern-primary" style="display:none">
                    <i class="fas fa-edit me-1"></i>Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ====== MODAL: EXCLUIR ====== -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-4x mb-3" style="color:#ef4444;"></i>
                <p class="fs-5 fw-semibold mb-1">Tem certeza que deseja excluir esta matrícula?</p>
                <p class="text-danger small mb-0"><i class="fas fa-exclamation-circle me-1"></i>Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarExcluir" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer"></div>
<div id="globalLoading" class="loading-overlay"><div class="spinner-custom"></div></div>

<script>
const USUARIO_PERFIL   = '<?= htmlspecialchars($perfil) ?>';
const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
const USUARIO_ID       = <?= (int)$usuario_id ?>;
const BASE_URL         = '../../controllers/matricula.php';
const ANO_ATUAL        = <?= $anoAtual ?>;
const TRIMESTRE_ATUAL  = <?= $trimestreAtual ?>;
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<script>
$(document).ready(function () {
    // ── Estado ──────────────────────────────────────────────────────────────
    let tabela = null;
    let matriculaIdParaExcluir = null;
    let matriculaIdNoModal = null;

    const modalVisualizar = new bootstrap.Modal(document.getElementById('modalVisualizar'));
    const modalExcluir    = new bootstrap.Modal(document.getElementById('modalExcluir'));

    // ── Utilitários ──────────────────────────────────────────────────────────
    function escapeHtml(t) {
        if (t == null) return '';
        const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
    }
    function exibirToast(msg, tipo = 'success') {
        const container = document.getElementById('toastContainer');
        const icon = { success:'check-circle', danger:'exclamation-triangle', warning:'exclamation-circle' }[tipo] || 'info-circle';
        const el = document.createElement('div');
        el.className = `toast align-items-center text-white bg-${tipo} border-0 show`;
        el.style.cssText = 'min-width:320px;margin-bottom:10px;border-radius:8px;';
        el.innerHTML = `<div class="d-flex"><div class="toast-body"><i class="fas fa-${icon} me-2"></i>${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(el);
        setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 350); }, 4500);
    }

    // ── Carregar trimestres para filtro ──────────────────────────────────────
    function carregarTrimestres() {
        $.ajax({ url: BASE_URL, method: 'POST', data: { acao: 'getTrimestresSugeridos' }, dataType: 'json' })
            .then(function (r) {
                if (!r.sucesso || !r.dados) return;
                const $f = $('#filtroTrimestre');
                $f.empty().append('<option value="">Todos</option>');
                r.dados.forEach(t => $f.append(`<option value="${t.valor}">${t.label}</option>`));
            });
    }

    // ── Carregar congregações para admin ─────────────────────────────────────
    function carregarCongregacoes() {
        if (USUARIO_PERFIL !== 'admin') return;
        $.ajax({ url: BASE_URL, method: 'POST', data: { acao: 'listarCongregacoes' }, dataType: 'json' })
            .then(function (r) {
                if (!r.sucesso || !r.dados) return;
                const $s = $('#filtroCongregacao');
                if (!$s.length) return;
                $s.empty().append('<option value="">Todas</option>');
                r.dados.forEach(c => $s.append(`<option value="${c.id}">${escapeHtml(c.nome)}</option>`));
            });
    }

    // ── DataTable ────────────────────────────────────────────────────────────
    function inicializarDataTable() {
        tabela = $('#tabelaMatriculas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL,
                type: 'POST',
                data: function (d) {
                    d.acao        = 'listarMatriculas';
                    d.congregacao = USUARIO_PERFIL !== 'admin' ? (USUARIO_CONGR_ID || '') : ($('#filtroCongregacao').val() || '');
                    d.status      = $('#filtroStatus').val() || '';
                    d.trimestre   = $('#filtroTrimestre').val() || '';
                    if ($('#filtroBusca').val()) d.search = { value: $('#filtroBusca').val() };
                },
                error: function () { exibirToast('Erro ao carregar matrículas.', 'danger'); }
            },
            columns: [
                { data:'id', render: d => `<span class="badge-id">#${d}</span>` },
                { data:'aluno', render: d => `<i class="fas fa-user-graduate me-2 text-primary"></i>${escapeHtml(d)}` },
                { data:'classe', render: d => `<i class="fas fa-chalkboard-user me-2 text-success"></i>${escapeHtml(d)}` },
                { data:'congregacao', render: d => `<i class="fas fa-church me-2 text-primary"></i>${escapeHtml(d)}` },
                { data:'trimestre', render: d => `<span class="badge-trimestre">${escapeHtml(d)}</span>` },
                { data:'data_matricula', render: d => (!d || d === '0000-00-00') ? '-' : d },
                { data:'status', render: d => d === 'ativo'
                    ? '<span class="badge-status-ativo"><i class="fas fa-check-circle me-1"></i>Ativo</span>'
                    : '<span class="badge-status-inativo"><i class="fas fa-times-circle me-1"></i>Inativo</span>'
                },
                {
                    data: null, className:'text-center', orderable:false,
                    render: d => `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn-action btn-view btn-ver" data-id="${d.id}" title="Visualizar"><i class="fas fa-eye"></i></button>
                            <button class="btn-action btn-edit btn-editar" data-id="${d.id}" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn-action btn-delete btn-excluir" data-id="${d.id}" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                        </div>`
                }
            ],
            language: { url:'//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json', processing:'Carregando...' },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            drawCallback: function () {
                let total = 0, ativas = 0;
                tabela.rows().data().each(r => { total++; if (r.status === 'ativo') ativas++; });
                $('#totalMatriculas').text(total);
                $('#matriculasAtivas').text(ativas);
                $('#matriculasInativas').text(total - ativas);
            }
        });

        // Event delegation — funciona corretamente com server-side
        $('#tabelaMatriculas')
            .on('click', '.btn-ver', function () { visualizarMatricula($(this).data('id')); })
            .on('click', '.btn-editar', function () { window.location.href = `index.php?id=${$(this).data('id')}`; })
            .on('click', '.btn-excluir', function () {
                matriculaIdParaExcluir = $(this).data('id');
                modalExcluir.show();
            });
    }

    // ── Visualizar ────────────────────────────────────────────────────────────
    function visualizarMatricula(id) {
        matriculaIdNoModal = id;
        $('#detalhesMatricula').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 mb-0">Carregando...</p></div>');
        $('#btnEditarDoModal').hide();
        modalVisualizar.show();

        $.ajax({ url: BASE_URL, method: 'POST', data: { acao: 'buscarMatricula', id }, dataType: 'json' })
            .then(function (r) {
                if (r.sucesso && r.dados) {
                    const d = r.dados;
                    const statusBadge = d.status === 'ativo'
                        ? '<span class="badge-status-ativo"><i class="fas fa-check-circle me-1"></i>Ativo</span>'
                        : '<span class="badge-status-inativo"><i class="fas fa-times-circle me-1"></i>Inativo</span>';
                    $('#detalhesMatricula').html(`
                        <div class="detalhe-item"><span class="detalhe-label">ID</span><span>#${d.id}</span></div>
                        <div class="detalhe-item"><span class="detalhe-label">Aluno</span><span>${escapeHtml(d.aluno)}</span></div>
                        <div class="detalhe-item"><span class="detalhe-label">Classe</span><span>${escapeHtml(d.classe)}</span></div>
                        <div class="detalhe-item"><span class="detalhe-label">Congregação</span><span>${escapeHtml(d.congregacao)}</span></div>
                        <div class="detalhe-item"><span class="detalhe-label">Trimestre</span><span>${escapeHtml(d.trimestre)}</span></div>
                        <div class="detalhe-item"><span class="detalhe-label">Data de matrícula</span><span>${(d.data_matricula && d.data_matricula !== '0000-00-00') ? d.data_matricula : '—'}</span></div>
                        <div class="detalhe-item"><span class="detalhe-label">Status</span>${statusBadge}</div>
                    `);
                    $('#btnEditarDoModal').show().off('click').on('click', function () {
                        window.location.href = `index.php?id=${matriculaIdNoModal}`;
                    });
                } else {
                    $('#detalhesMatricula').html(`<p class="text-danger text-center py-4">${r.mensagem || 'Matrícula não encontrada.'}</p>`);
                }
            }).catch(function () {
                $('#detalhesMatricula').html('<p class="text-danger text-center py-4">Falha de conexão.</p>');
            });
    }

    // ── Excluir ───────────────────────────────────────────────────────────────
    $('#btnConfirmarExcluir').on('click', function () {
        if (!matriculaIdParaExcluir) return;
        const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Excluindo...');
        $.ajax({ url: BASE_URL, method: 'POST', data: { acao: 'excluirMatricula', id: matriculaIdParaExcluir }, dataType: 'json' })
            .then(function (r) {
                $btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Excluir');
                if (r.sucesso) {
                    modalExcluir.hide();
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirToast(r.mensagem || 'Matrícula excluída.', 'success');
                    matriculaIdParaExcluir = null;
                } else {
                    exibirToast(r.mensagem || 'Erro ao excluir.', 'danger');
                }
            }).catch(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Excluir');
                exibirToast('Falha de conexão ao excluir.', 'danger');
            });
    });

    $('#modalExcluir').on('hidden.bs.modal', function () {
        matriculaIdParaExcluir = null;
        $('#btnConfirmarExcluir').prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Excluir');
    });

    // ── Filtros ───────────────────────────────────────────────────────────────
    $('#btnFiltrar').on('click', function () { if (tabela) tabela.ajax.reload(); });
    $('#filtroBusca').on('keypress', function (e) { if (e.which === 13 && tabela) tabela.ajax.reload(); });

    // ── Inicialização ─────────────────────────────────────────────────────────
    carregarTrimestres();
    carregarCongregacoes();
    inicializarDataTable();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>