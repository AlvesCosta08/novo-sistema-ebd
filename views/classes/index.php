<?php  
// Iniciar sessão antes de qualquer output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ❌ PATH_BASE REMOVIDO - não é mais necessário

// Incluir validação e conexão
require_once __DIR__ . '/../../auth/valida_sessao.php';
require_once __DIR__ . '/../../config/conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D - Classes</title>
    <link rel="icon" href="../../assets/images/biblia.png" type="image/x-icon">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Externos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Locais -->
    <?php if (file_exists(__DIR__ . '/../../assets/css/dashboard.css')): ?>
        <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <?php endif; ?>
    
    <style>
        :root { --color-primary: #3b82f6; --color-primary-dark: #2563eb; --color-success: #10b981; --color-warning: #f59e0b; --color-danger: #ef4444; --color-gray-50: #f8fafc; --color-gray-100: #f1f5f9; --color-gray-200: #e2e8f0; --color-gray-300: #cbd5e1; --color-gray-600: #475569; --color-gray-800: #1e293b; --color-white: #ffffff; --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1); --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1); --radius: 8px; --radius-lg: 12px; --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background-color: var(--color-gray-50); color: var(--color-gray-800); line-height: 1.5; padding-top: 56px; overflow-x: hidden; }
        .page-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { font-size: 1.5rem; font-weight: 600; color: var(--color-gray-800); margin: 0; }
        .navbar { box-shadow: var(--shadow-sm); transition: var(--transition); background-color: var(--color-white) !important; }
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
        #tabelaClasses { margin-bottom: 0; }
        #tabelaClasses thead th { background-color: var(--color-gray-100); color: var(--color-gray-600); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.85rem 1rem; border-bottom: 2px solid var(--color-gray-200); }
        #tabelaClasses tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-color: var(--color-gray-200); color: var(--color-gray-800); }
        #tabelaClasses tbody tr:hover { background-color: var(--color-gray-50); }
        .classe-card { background: var(--color-white); border-radius: var(--radius-lg); border: 1px solid var(--color-gray-200); box-shadow: var(--shadow-sm); transition: var(--transition); height: 100%; display: flex; flex-direction: column; }
        .classe-card:hover { box-shadow: var(--shadow-md); border-color: var(--color-gray-300); }
        .classe-card .card-body { padding: 1rem; flex: 1; }
        .classe-card .card-title { font-size: 1.1rem; font-weight: 600; color: var(--color-gray-800); margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--color-gray-200); }
        .classe-card .info-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; font-size: 0.9rem; color: var(--color-gray-600); }
        .classe-card .info-row strong { color: var(--color-gray-800); font-weight: 500; min-width: 30px; }
        .classe-card .card-actions { display: flex; gap: 0.5rem; margin-top: auto; padding-top: 0.75rem; }
        .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }
        .modal-header { background-color: var(--color-primary); color: var(--color-white); padding: 1rem 1.25rem; border: none; }
        .modal-header.bg-info { background-color: var(--color-gray-600) !important; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { background-color: var(--color-gray-50); padding: 0.85rem 1.25rem; border-top: 1px solid var(--color-gray-200); }
        .form-control { border-radius: var(--radius); padding: 0.6rem 0.85rem; border: 1px solid var(--color-gray-300); transition: var(--transition); }
        .form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-label { font-weight: 500; color: var(--color-gray-800); margin-bottom: 0.4rem; }
        .form-control.is-invalid { border-color: var(--color-danger); }
        .invalid-feedback { font-size: 0.8rem; margin-top: 0.25rem; }
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

<!-- Navbar Padrão Bootstrap 5.0 - CORRIGIDA (PATH_BASE removido) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">
            <img src="../../assets/images/biblia.png" alt="EBD" height="30" class="d-inline-block align-text-top">
            <span class="d-none d-sm-inline">Escola Bíblica</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" 
                aria-controls="navbarNav" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="../dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../alunos/index.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="../classes/index.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="../professores/index.php">Professores</a></li>
                <li class="nav-item"><a class="nav-link" href="../congregacao/index.php">Congregações</a></li>
                <li class="nav-item"><a class="nav-link" href="../matriculas/index.php">Matrículas</a></li>
                <li class="nav-item"><a class="nav-link" href="../usuario/index.php">Usuários</a></li>
                <li class="nav-item"><a class="nav-link" href="../relatorios/index.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-danger btn-sm" href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    <span class="d-none d-md-inline">Sair</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Conteúdo Principal -->
<main class="container py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestão de Classes</h1>
            <p class="text-muted mb-0">Cadastre e gerencie as classes da escola bíblica</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
            <i class="fas fa-plus"></i> Cadastrar Classe
        </button>
    </div>

    <!-- Tabela (Desktop) -->
    <div id="tabelaContainer" class="table-container table-responsive">
        <table id="tabelaClasses" class="table table-bordered table-hover align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th class="text-center no-sort">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="3" class="text-center py-4 text-muted">Carregando dados......</td</tr>
            </tbody>
        </table>
    </div>

    <!-- Cards (Mobile) -->
    <div id="cartoesContainer" class="row g-3"></div>
</main>

<!-- Modal Cadastrar -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastrarLabel">Cadastrar Classe</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastrarClasse">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Classe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Juvenis, Adolescentes..." required autocomplete="off">
                        <div class="invalid-feedback">Por favor, informe o nome da classe.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarCadastrar">
                    <i class="fas fa-save"></i> Cadastrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditarClasse" tabindex="-1" aria-labelledby="modalEditarClasseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title" id="modalEditarClasseLabel">Editar Classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarClasse">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="mb-3">
                        <label for="nomeEditar" class="form-label">Nome da Classe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nomeEditar" name="nome" required autocomplete="off">
                        <div class="invalid-feedback">Por favor, informe o nome da classe.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarEditar">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    let tabela = null;
    
    function carregarDados() {
        $.ajax({
            url: '../../controllers/classe.php',
            type: 'POST',
            dataType: 'json',
            data: { acao: 'listar' },
            success: function(response) {
                if (response.sucesso && Array.isArray(response.data)) {
                    if ($.fn.DataTable.isDataTable('#tabelaClasses')) {
                        tabela = $('#tabelaClasses').DataTable();
                        tabela.clear();
                        tabela.rows.add(response.data);
                        tabela.draw();
                    } else {
                        tabela = $('#tabelaClasses').DataTable({
                            responsive: true,
                            data: response.data,
                            columns: [
                                { data: 'id' },
                                { data: 'nome' },
                                {
                                    data: 'id',
                                    className: 'text-center',
                                    orderable: false,
                                    render: function(data) {
                                        return `
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-warning btn-sm editar" data-id="${data}" data-bs-toggle="modal" data-bs-target="#modalEditarClasse" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm excluir" data-id="${data}" title="Excluir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>`;
                                    }
                                }
                            ],
                            language: {
                                sEmptyTable: "Nenhuma classe encontrada",
                                sInfo: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                                sInfoEmpty: "Nenhum registro",
                                sInfoFiltered: "(filtrado de _MAX_)",
                                sLengthMenu: "_MENU_ por página",
                                sLoadingRecords: "Carregando...",
                                sZeroRecords: "Sem resultados",
                                sSearch: "Buscar:",
                                oPaginate: { sNext: "Próximo", sPrevious: "Anterior" }
                            },
                            pageLength: 10,
                            lengthMenu: [5, 10, 25, 50],
                            order: [[0, 'asc']]
                        });
                    }
                    renderizarCartoes(response.data);
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao carregar dados');
                }
            },
            error: function(xhr, error) {
                console.error('Erro AJAX:', error);
                exibirMensagem('erro', 'Erro ao carregar dados: ' + (xhr.responseText || error));
            }
        });
    }
    
    function renderizarCartoes(classes) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        container.innerHTML = '';
        if (!classes || classes.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">Nenhuma classe encontrada</div>';
            return;
        }
        classes.forEach(classe => {
            container.innerHTML += `
                <div class="col-12">
                    <div class="classe-card">
                        <div class="card-body">
                            <h5 class="card-title">${escapeHtml(classe.nome)}</h5>
                            <div class="info-row"><strong>ID:</strong> <span class="text-secondary">#${classe.id}</span></div>
                            <div class="card-actions">
                                <button class="btn btn-warning btn-sm editar flex-fill" data-id="${classe.id}" data-bs-toggle="modal" data-bs-target="#modalEditarClasse">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-danger btn-sm excluir flex-fill" data-id="${classe.id}">
                                    <i class="fas fa-trash-alt me-1"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white show`;
        toast.innerHTML = `
            <div class="toast-body d-flex align-items-center gap-2">
                <span>${icon}</span>
                <span class="flex-grow-1">${mensagem}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
    
    $('#btnSalvarCadastrar').on('click', function() {
        const nome = $('#nome').val().trim();
        if (!nome) {
            $('#nome').addClass('is-invalid');
            exibirMensagem('erro', 'Nome da classe é obrigatório.');
            return;
        }
        $('#nome').removeClass('is-invalid');
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');
        $.ajax({
            url: '../../controllers/classe.php',
            method: 'POST',
            data: { acao: 'salvar', nome: nome },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Cadastrar');
                if (response.sucesso) {
                    $('#modalCadastrar').modal('hide');
                    $('#formCadastrarClasse')[0].reset();
                    carregarDados();
                    exibirMensagem('sucesso', 'Classe cadastrada com sucesso!');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao cadastrar classe.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Cadastrar');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
            }
        });
    });
    
    $(document).on('click', '.editar', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '../../controllers/classe.php',
            method: 'POST',
            data: { acao: 'buscar', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso && response.data) {
                    $('#idEditar').val(response.data.id);
                    $('#nomeEditar').val(response.data.nome);
                    $('#modalEditarClasse').modal('show');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao buscar dados da classe.');
                }
            },
            error: function() {
                exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
            }
        });
    });
    
    $('#btnSalvarEditar').on('click', function() {
        const nome = $('#nomeEditar').val().trim();
        const id = $('#idEditar').val();
        if (!nome) {
            $('#nomeEditar').addClass('is-invalid');
            exibirMensagem('erro', 'Nome da classe é obrigatório.');
            return;
        }
        $('#nomeEditar').removeClass('is-invalid');
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');
        $.ajax({
            url: '../../controllers/classe.php',
            method: 'POST',
            data: { acao: 'salvar', id: id, nome: nome },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar');
                if (response.sucesso) {
                    $('#modalEditarClasse').modal('hide');
                    carregarDados();
                    exibirMensagem('sucesso', 'Classe atualizada com sucesso!');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao atualizar classe.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
            }
        });
    });
    
    $(document).on('click', '.excluir', function() {
        const id = $(this).data('id');
        if (confirm('Tem certeza que deseja excluir esta classe?\nEsta ação pode afetar alunos vinculados.')) {
            $.ajax({
                url: '../../controllers/classe.php',
                method: 'POST',
                data: { acao: 'excluir', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.sucesso) {
                        carregarDados();
                        exibirMensagem('sucesso', 'Classe excluída com sucesso!');
                    } else {
                        exibirMensagem('erro', response.mensagem || 'Erro ao excluir classe.');
                    }
                },
                error: function() {
                    exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
                }
            });
        }
    });
    
    $('#modalCadastrar').on('hidden.bs.modal', function() {
        $('#formCadastrarClasse')[0].reset();
        $('#nome').removeClass('is-invalid');
    });
    
    $('#modalEditarClasse').on('hidden.bs.modal', function() {
        $('#nomeEditar').removeClass('is-invalid');
    });
    
    $('.navbar-nav .nav-link').on('click', function() {
        if ($('.navbar-toggler').is(':visible')) {
            $('.navbar-collapse').collapse('hide');
        }
    });
    
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 10) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });
    
    carregarDados();
});
</script>

</body>
</html>