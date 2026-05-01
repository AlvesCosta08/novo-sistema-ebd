<?php  
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    // ❌ PATH_BASE REMOVIDO - não é mais necessário
    
    // ✅ CAMINHOS AJUSTADOS
    require_once __DIR__ . '/../../auth/valida_sessao.php';
    require_once __DIR__ . '/../../config/conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D - Alunos</title>
    <link rel="icon" href="../../assets/images/biblia.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <?php if (file_exists(__DIR__ . '/../../assets/css/dashboard.css')): ?>
        <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <?php endif; ?>
    <?php if (file_exists(__DIR__ . '/../../assets/css/aluno.css')): ?>
        <link rel="stylesheet" href="../../assets/css/aluno.css">
    <?php endif; ?>
    
    <style>
        :root {
            --color-primary: #3b82f6; --color-primary-dark: #2563eb; --color-success: #10b981;
            --color-warning: #f59e0b; --color-danger: #ef4444; --color-gray-50: #f8fafc;
            --color-gray-100: #f1f5f9; --color-gray-200: #e2e8f0; --color-gray-300: #cbd5e1;
            --color-gray-600: #475569; --color-gray-800: #1e293b; --color-white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --radius: 8px; --radius-lg: 12px; --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--color-gray-50); color: var(--color-gray-800); line-height: 1.5; padding-top: 56px; overflow-x: hidden; }
        .page-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { font-size: 1.5rem; font-weight: 600; color: var(--color-gray-800); margin: 0; }
        .navbar { box-shadow: var(--shadow-sm); transition: var(--transition); }
        .navbar.scrolled { box-shadow: var(--shadow-md); }
        .navbar-brand { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; }
        .navbar-brand img { height: 30px; width: auto; }
        .navbar-nav .nav-link { font-weight: 500; color: var(--color-gray-600); transition: var(--transition); }
        .navbar-nav .nav-link:hover { color: var(--color-primary-dark); }
        .navbar-nav .nav-link.active { color: var(--color-primary-dark); font-weight: 600; }
        .navbar-toggler { border: 1px solid var(--color-gray-200); padding: 0.25rem 0.75rem; }
        .navbar-toggler:focus { box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); outline: none; }
        .navbar .btn-outline-danger { border-radius: var(--radius); font-weight: 500; transition: var(--transition); }
        .navbar .btn-outline-danger:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        @media (max-width: 991px) {
            .navbar-collapse { background: var(--color-white); padding: 1rem; border-radius: var(--radius-lg); margin-top: 0.75rem; box-shadow: var(--shadow-md); border: 1px solid var(--color-gray-200); }
            .navbar-nav .nav-link { padding: 0.75rem 1rem; border-radius: var(--radius); }
            .navbar-nav .nav-link.active { background-color: rgba(59, 130, 246, 0.08); }
            .navbar .d-flex { width: 100%; justify-content: center; padding-top: 0.75rem; border-top: 1px solid var(--color-gray-200); margin-top: 0.5rem; }
            .navbar .btn-outline-danger { width: 100%; justify-content: center; }
        }
        .btn { border-radius: var(--radius); font-weight: 500; padding: 0.5rem 1.25rem; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .btn-primary { background-color: var(--color-primary); border: none; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background-color: var(--color-primary-dark); box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .btn-warning { background-color: rgba(245, 158, 11, 0.1); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.2); }
        .btn-warning:hover { background-color: rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.4); }
        .btn-danger { background-color: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background-color: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.875rem; }
        .table-container { background: var(--color-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200); overflow: hidden; }
        #tabelaAlunos { margin-bottom: 0; }
        #tabelaAlunos thead th { background-color: var(--color-gray-100); color: var(--color-gray-600); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.85rem 1rem; border-bottom: 2px solid var(--color-gray-200); }
        #tabelaAlunos tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-color: var(--color-gray-200); color: var(--color-gray-800); }
        #tabelaAlunos tbody tr:hover { background-color: var(--color-gray-50); }
        .badge-classe { background-color: rgba(59, 130, 246, 0.1); color: var(--color-primary-dark); padding: 0.3rem 0.7rem; border-radius: 9999px; font-weight: 500; font-size: 0.8rem; }
        .aluno-card { background: var(--color-white); border-radius: var(--radius-lg); border: 1px solid var(--color-gray-200); box-shadow: var(--shadow-sm); transition: var(--transition); height: 100%; display: flex; flex-direction: column; }
        .aluno-card:hover { box-shadow: var(--shadow-md); border-color: var(--color-gray-300); }
        .aluno-card .card-body { padding: 1rem; flex: 1; }
        .aluno-card .card-title { font-size: 1.1rem; font-weight: 600; color: var(--color-gray-800); margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--color-gray-200); }
        .aluno-card .info-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; font-size: 0.9rem; color: var(--color-gray-600); }
        .aluno-card .info-row strong { color: var(--color-gray-800); font-weight: 500; min-width: 80px; }
        .aluno-card .card-actions { display: flex; gap: 0.5rem; margin-top: auto; padding-top: 0.75rem; }
        .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }
        .modal-header { background-color: var(--color-primary); color: var(--color-white); padding: 1rem 1.25rem; border: none; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { background-color: var(--color-gray-50); padding: 0.85rem 1.25rem; border-top: 1px solid var(--color-gray-200); }
        .form-control, .form-select { border-radius: var(--radius); padding: 0.6rem 0.85rem; border: 1px solid var(--color-gray-300); transition: var(--transition); }
        .form-control:focus, .form-select:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-label { font-weight: 500; color: var(--color-gray-800); margin-bottom: 0.4rem; }
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 1090; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; }
        .toast-container > * { pointer-events: auto; }
        .custom-toast { min-width: 300px; border-radius: var(--radius); box-shadow: var(--shadow-md); border: none; }
        .custom-toast.bg-success { background: linear-gradient(135deg, var(--color-success), #059669); color: white; }
        .custom-toast.bg-danger { background: linear-gradient(135deg, var(--color-danger), #dc2626); color: white; }
        @media (max-width: 767px) { #tabelaContainer { display: none !important; } #cartoesContainer { display: flex !important; } .page-header { flex-direction: column; align-items: flex-start; } .page-header .btn { width: 100%; } }
        @media (min-width: 768px) { #tabelaContainer { display: block !important; } #cartoesContainer { display: none !important; } }
        @media (max-width: 576px) { .navbar-brand span { display: none; } .modal-dialog { margin: 0.5rem; } .modal-footer { flex-direction: column-reverse; } .modal-footer .btn { width: 100%; } }
        :focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
    </style>
</head>
<body>

<!-- ✅ NAVBAR CORRIGIDA - SEM PATH_BASE -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">
            <img src="../../assets/images/biblia.png" alt="EBD" height="30" class="d-inline-block align-text-top">
            <span class="d-none d-sm-inline">Escola Bíblica</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="../dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="../alunos/index.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="../classes/index.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="../professores/index.php">Professores</a></li>
                <li class="nav-item"><a class="nav-link" href="../congregacao/index.php">Congregações</a></li>
                <li class="nav-item"><a class="nav-link" href="../matriculas/index.php">Matrículas</a></li>
                <li class="nav-item"><a class="nav-link" href="../usuario/index.php">Usuários</a></li>
                <li class="nav-item"><a class="nav-link" href="../relatorios/index.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-danger btn-sm" href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i><span class="d-none d-md-inline">Sair</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestão de Alunos</h1>
            <p class="text-muted mb-0">Cadastre, edite e gerencie os alunos da escola</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao">
            <i class="fas fa-plus"></i> Cadastrar Novo Aluno
        </button>
    </div>
    
    <div id="tabelaContainer" class="table-container table-responsive">
        <table id="tabelaAlunos" class="table table-bordered table-hover align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Nascimento</th>
                    <th>Telefone</th>
                    <th>Classe</th>
                    <th class="text-center no-sort">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="5" class="text-center py-4 text-muted">Carregando dados...</td</tr>
            </tbody>
        </table>
    </div>
    
    <div id="cartoesContainer" class="row g-3"></div>
</main>

<!-- Modal de Cadastro/Edição -->
<div id="modalCadastroEdicao" class="modal fade" tabindex="-1" aria-labelledby="modalCadastroEdicaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroEdicaoLabel">Cadastrar Aluno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastroEdicao">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: João da Silva" required autocomplete="name">
                        </div>
                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" placeholder="(11) 99999-9999" required inputmode="tel" autocomplete="tel">
                            <div class="invalid-feedback">Telefone inválido</div>
                        </div>
                        <div class="col-md-6">
                            <label for="data_nascimento" class="form-label">Data de Nascimento <span class="text-danger">*</span></label>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required autocomplete="bday">
                        </div>
                        <div class="col-12">
                            <label for="classe_id" class="form-label">Classe <span class="text-danger">*</span></label>
                            <select id="classe_id" name="classe_id" class="form-select" required>
                                <option value="">Selecione uma classe</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvar">
                    <i class="fas fa-save"></i> Gravar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container para Toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
$(document).ready(function() {
    let tabela = null;
    
    // Inicializa DataTable se existir a tabela
    if ($('#tabelaAlunos').length && !$.fn.DataTable.isDataTable('#tabelaAlunos')) {
        tabela = $('#tabelaAlunos').DataTable({
            ajax: {
                url: '../../controllers/aluno.php?acao=listar',
                dataSrc: 'data',
                error: function() { exibirMensagem('erro', 'Erro ao carregar dados dos alunos.'); }
            },
            columns: [
                { data: 'nome' },
                { data: 'data_nascimento', render: data => data ? moment(data).format('DD/MM/YYYY') : '-' },
                { data: 'telefone' },
                { data: 'classe', render: data => data ? `<span class="badge-classe">${data}</span>` : '-' },
                {
                    data: 'id',
                    className: 'text-center',
                    orderable: false,
                    render: id => `
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-warning btn-sm btnEditar" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao" data-id="${id}" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm btnExcluir" data-id="${id}" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                        </div>`
                }
            ],
            responsive: true,
            language: {
                sEmptyTable: "Nenhum registro encontrado", sInfo: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                sInfoEmpty: "Nenhum registro", sInfoFiltered: "(filtrado de _MAX_)", sLengthMenu: "_MENU_ por página",
                sLoadingRecords: "Carregando...", sZeroRecords: "Sem resultados", sSearch: "Buscar:",
                oPaginate: { sNext: "Próximo", sPrevious: "Anterior" }
            },
            pageLength: 10, lengthMenu: [5, 10, 25, 50], order: [[0, 'asc']]
        });
    }

    // Carrega classes para o select
    function carregarClasses() {
        $.ajax({
            url: '../../controllers/classe.php',
            method: 'POST',
            dataType: 'json',
            data: { acao: 'listar' },
            success: function(res) {
                if (res.sucesso && Array.isArray(res.data)) {
                    const sel = $('#classe_id');
                    sel.empty().append('<option value="">Selecione uma classe</option>');
                    res.data.forEach(c => sel.append(`<option value="${c.id}">${c.nome}</option>`));
                } else { exibirMensagem('erro', res.mensagem || 'Erro ao carregar classes'); }
            },
            error: () => exibirMensagem('erro', 'Erro ao carregar classes')
        });
    }

    // Renderiza cards para mobile
    function renderizarCartoes(alunos) {
        const container = document.getElementById("cartoesContainer");
        container.innerHTML = '';
        if (!alunos || alunos.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">Nenhum aluno cadastrado</div>';
            return;
        }
        alunos.forEach(a => {
            container.innerHTML += `
                <div class="col-12">
                    <div class="aluno-card">
                        <div class="card-body">
                            <h5 class="card-title">${a.nome || '-'}</h5>
                            <div class="info-row"><strong><i class="far fa-calendar-alt"></i> Nasc.:</strong> ${a.data_nascimento ? moment(a.data_nascimento).format('DD/MM/YYYY') : '-'}</div>
                            <div class="info-row"><strong><i class="fas fa-phone"></i> Tel.:</strong> <a href="tel:${(a.telefone||'').replace(/\D/g,'')}">${a.telefone || '-'}</a></div>
                            <div class="info-row"><strong><i class="fas fa-chalkboard"></i> Classe:</strong> ${a.classe ? `<span class="badge-classe">${a.classe}</span>` : '-'}</div>
                            <div class="card-actions">
                                <button class="btn btn-warning btn-sm btnEditar flex-fill" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao" data-id="${a.id}"><i class="fas fa-edit me-1"></i> Editar</button>
                                <button class="btn btn-danger btn-sm btnExcluir flex-fill" data-id="${a.id}"><i class="fas fa-trash-alt me-1"></i> Excluir</button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
    }

    function carregarAlunosParaCartoes() {
        $.get('../../controllers/aluno.php?acao=listar', null, res => {
            if (res.status === 'success' && Array.isArray(res.data)) renderizarCartoes(res.data);
        }, 'json');
    }

    // Evento de edição
    $(document).on('click', '.btnEditar', function() {
        const id = $(this).data('id');
        $('#modalCadastroEdicaoLabel').text('Editar Aluno');
        $.get('../../controllers/aluno.php?acao=buscar', { id }, res => {
            if (res.status === 'success' && res.data) {
                $('#id').val(res.data.id); $('#nome').val(res.data.nome); $('#telefone').val(res.data.telefone);
                $('#data_nascimento').val(res.data.data_nascimento); $('#classe_id').val(res.data.classe_id);
            } else exibirMensagem('erro', 'Erro ao carregar dados');
        }, 'json');
    });

    // Reset do modal ao abrir para cadastro
    $('#modalCadastroEdicao').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btnEditar')) {
            $('#modalCadastroEdicaoLabel').text('Cadastrar Aluno');
            $('#formCadastroEdicao')[0].reset(); $('#id').val('');
        }
        carregarClasses();
    });

    // Salvar aluno
    $('#btnSalvar').on('click', function() {
        const nome = $('#nome').val().trim();
        const tel = $('#telefone').val().trim().replace(/\D/g, '');
        const nasc = $('#data_nascimento').val().trim();
        const classe = $('#classe_id').val();
        const id = $('#id').val();

        if (!nome) return exibirMensagem('erro', 'Informe o nome.');
        if (!tel || tel.length < 10 || tel.length > 11) return exibirMensagem('erro', 'Telefone inválido.');
        if (!nasc) return exibirMensagem('erro', 'Informe a data de nascimento.');
        if (!classe) return exibirMensagem('erro', 'Selecione uma classe.');

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');

        $.ajax({
            url: id ? '../../controllers/aluno.php?acao=editar' : '../../controllers/aluno.php?acao=salvar',
            method: 'POST',
            data: $('#formCadastroEdicao').serialize(),
            dataType: 'json',
            success: res => {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Gravar');
                if (res.status === 'success') {
                    $('#modalCadastroEdicao').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    carregarAlunosParaCartoes();
                    exibirMensagem('sucesso', res.message || 'Salvo com sucesso!');
                } else { exibirMensagem('erro', res.message || 'Erro ao salvar'); }
            },
            error: () => {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Gravar');
                exibirMensagem('erro', 'Erro de comunicação com o servidor.');
            }
        });
    });

    // Excluir aluno
    $(document).on('click', '.btnExcluir', function() {
        const id = $(this).data('id');
        if (confirm('Tem certeza que deseja excluir este aluno?')) {
            $.post('../../controllers/aluno.php?acao=excluir', { id }, res => {
                if (res.status === 'success') {
                    if (tabela) tabela.ajax.reload(null, false);
                    carregarAlunosParaCartoes();
                    exibirMensagem('sucesso', res.message || 'Excluído com sucesso!');
                } else { exibirMensagem('erro', res.message || 'Erro ao excluir'); }
            }, 'json');
        }
    });

    // Função de toast
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white show`;
        toast.innerHTML = `<div class="toast-body d-flex align-items-center gap-2"><span>${icon}</span><span class="flex-grow-1">${mensagem}</span><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
    }

    // Máscara de telefone
    $('#telefone').on('input', function() {
        let v = $(this).val().replace(/\D/g, ''), f = '';
        if (v.length) f = '(' + v.substring(0, 2);
        if (v.length > 2) f += ') ' + v.substring(2, 7);
        if (v.length > 7) f += '-' + v.substring(7, 11);
        $(this).val(f).toggleClass('is-invalid', v.length > 0 && (v.length < 10 || v.length > 11));
    });

    // Nome em maiúsculas
    $('#nome').on('input', function() {
        const pos = this.selectionStart;
        $(this).val($(this).val().toUpperCase());
        this.setSelectionRange(pos, pos);
    });

    // Fechar menu mobile ao clicar em link
    $('.navbar-nav .nav-link').on('click', function() {
        if ($('.navbar-toggler').is(':visible')) { $('.navbar-collapse').collapse('hide'); }
    });

    // Efeito de scroll na navbar
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 10) { $('.navbar').addClass('scrolled'); }
        else { $('.navbar').removeClass('scrolled'); }
    });

    // Carregar dados iniciais
    carregarAlunosParaCartoes();
});
</script>
</body>
</html>