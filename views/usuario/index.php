<?php  
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    // ❌ PATH_BASE REMOVIDO - não é mais necessário
    require_once __DIR__ . '/../../auth/valida_sessao.php';
    require_once __DIR__ . '/../../config/conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D - Usuários</title>
    <link rel="icon" href="../../assets/images/biblia.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <?php if (file_exists(__DIR__ . '/../../assets/css/dashboard.css')): ?><link rel="stylesheet" href="../../assets/css/dashboard.css"><?php endif; ?>
    <?php if (file_exists(__DIR__ . '/../../assets/css/aluno.css')): ?><link rel="stylesheet" href="../../assets/css/aluno.css"><?php endif; ?>
    <style>
        :root { --color-primary: #3b82f6; --color-primary-dark: #2563eb; --color-success: #10b981; --color-warning: #f59e0b; --color-danger: #ef4444; --color-gray-50: #f8fafc; --color-gray-100: #f1f5f9; --color-gray-200: #e2e8f0; --color-gray-300: #cbd5e1; --color-gray-600: #475569; --color-gray-800: #1e293b; --color-white: #ffffff; --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1); --radius: 8px; --radius-lg: 12px; --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
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
        #tabelaUsuarios { margin-bottom: 0; }
        #tabelaUsuarios thead th { background-color: var(--color-gray-100); color: var(--color-gray-600); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.85rem 1rem; border-bottom: 2px solid var(--color-gray-200); }
        #tabelaUsuarios tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-color: var(--color-gray-200); color: var(--color-gray-800); }
        #tabelaUsuarios tbody tr:hover { background-color: var(--color-gray-50); }
        .usuario-card { background: var(--color-white); border-radius: var(--radius-lg); border: 1px solid var(--color-gray-200); box-shadow: var(--shadow-sm); transition: var(--transition); height: 100%; display: flex; flex-direction: column; }
        .usuario-card:hover { box-shadow: var(--shadow-md); border-color: var(--color-gray-300); }
        .usuario-card .card-body { padding: 1rem; flex: 1; }
        .usuario-card .card-title { font-size: 1.1rem; font-weight: 600; color: var(--color-gray-800); margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--color-gray-200); }
        .usuario-card .info-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; font-size: 0.9rem; color: var(--color-gray-600); }
        .usuario-card .info-row strong { color: var(--color-gray-800); font-weight: 500; min-width: 80px; }
        .usuario-card .card-actions { display: flex; gap: 0.5rem; margin-top: auto; padding-top: 0.75rem; }
        .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }
        .modal-header { background-color: var(--color-primary); color: var(--color-white); padding: 1rem 1.25rem; border: none; }
        .modal-header.bg-warning { background-color: var(--color-warning) !important; color: #000 !important; }
        .modal-header.bg-danger { background-color: var(--color-danger) !important; }
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
        .filters-card { background: var(--color-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200); padding: 1rem; margin-bottom: 1.5rem; }
        .badge-perfil-admin { background-color: #8b5cf6; color: white; padding: 0.3rem 0.7rem; border-radius: 9999px; font-weight: 500; font-size: 0.8rem; }
        .badge-perfil-user { background-color: #10b981; color: white; padding: 0.3rem 0.7rem; border-radius: 9999px; font-weight: 500; font-size: 0.8rem; }
        .badge-perfil-professor { background-color: #f59e0b; color: white; padding: 0.3rem 0.7rem; border-radius: 9999px; font-weight: 500; font-size: 0.8rem; }
        @media (max-width: 767px) { #tabelaContainer { display: none !important; } #cartoesContainer { display: flex !important; } .page-header { flex-direction: column; align-items: flex-start; } .page-header .btn { width: 100%; } }
        @media (min-width: 768px) { #tabelaContainer { display: block !important; } #cartoesContainer { display: none !important; } }
        @media (max-width: 576px) { .navbar-brand span { display: none; } .modal-dialog { margin: 0.5rem; } .modal-footer { flex-direction: column-reverse; } .modal-footer .btn { width: 100%; } }
        :focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
    </style>
</head>
<body>

<!-- Navbar CORRIGIDA (PATH_BASE removido) -->
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
                <li class="nav-item"><a class="nav-link" href="../alunos/index.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="../classes/index.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="../professores/index.php">Professores</a></li>
                <li class="nav-item"><a class="nav-link" href="../congregacao/index.php">Congregações</a></li>
                <li class="nav-item"><a class="nav-link" href="../matriculas/index.php">Matrículas</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="../usuario/index.php">Usuários</a></li>
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
        <div><h1 class="page-title">Gestão de Usuários</h1><p class="text-muted mb-0">Gerencie os usuários do sistema</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar"><i class="fas fa-plus"></i> Cadastrar Usuário</button>
    </div>

    <div class="filters-card">
        <form id="formFiltros" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Buscar</label>
                <input type="text" id="filtroBusca" class="form-control form-control-sm" placeholder="Nome ou email...">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Perfil</label>
                <select id="filtroPerfil" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="admin">Admin</option>
                    <option value="user">Usuário</option>
                    <option value="professor">Professor</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Congregação</label>
                <select id="filtroCongregacao" class="form-select form-select-sm">
                    <option value="">Todas</option>
                </select>
            </div>
            <!-- FILTRO DE STATUS REMOVIDO - A tabela usuarios NÃO tem coluna status -->
            <div class="col-md-2">
                <button type="button" id="btnAplicarFiltros" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <button type="button" id="btnLimparFiltros" class="btn btn-sm btn-outline-secondary w-100"><i class="fas fa-times"></i> Limpar</button>
            </div>
        </form>
    </div>

    <div id="tabelaContainer" class="table-container table-responsive">
        <table id="tabelaUsuarios" class="table table-bordered table-hover align-middle" style="width:100%">
            <thead>
                <tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil</th><th>Congregação</th><th class="text-center no-sort">Ações</th></tr>
            </thead>
            <tbody><tr><td colspan="6" class="text-center py-4 text-muted">Carregando dados...</td></tr></tbody>
        </table>
    </div>

    <div id="cartoesContainer" class="row g-3"></div>
</main>

<!-- Modal Cadastrar -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formCadastrarUsuario">
                <div class="modal-header"><h5 class="modal-title">Cadastrar Usuário</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-control" required autocomplete="name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha <span class="text-danger">*</span></label>
                            <input type="password" id="senha" name="senha" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perfil <span class="text-danger">*</span></label>
                            <select id="perfil" name="perfil" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="user">Usuário</option>
                                <option value="professor">Professor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Congregação</label>
                            <select id="congregacao_id" name="congregacao_id" class="form-select">
                                <option value="">Selecione uma congregação</option>
                            </select>
                            <small class="text-muted">Opcional - pode ser definido depois</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formEditarUsuario">
                <div class="modal-header bg-warning text-dark"><h5 class="modal-title">Editar Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="id_edit" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" id="nome_edit" name="nome" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email_edit" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha (deixe em branco para manter)</label>
                            <input type="password" id="senha_edit" name="senha" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perfil <span class="text-danger">*</span></label>
                            <select id="perfil_edit" name="perfil" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="user">Usuário</option>
                                <option value="professor">Professor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Congregação</label>
                            <select id="congregacao_edit" name="congregacao_id" class="form-select">
                                <option value="">Selecione uma congregação</option>
                            </select>
                            <small class="text-muted">Opcional - pode ser definido depois</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirmar Exclusão</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <p class="mb-0">Tem certeza que deseja excluir este usuário?</p>
                <small class="text-muted">ID: <span id="id_excluir_display"></span></small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExcluir"><i class="fas fa-trash"></i> Sim, Excluir</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    let tabela = null;
    
    function escapeHtml(text) { if (!text) return ''; const d = document.createElement('div'); d.textContent = text; return d.innerHTML; }
    
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
    
    function carregarCongregacoes(selectedId = '') {
        $('#congregacao_id, #congregacao_edit, #filtroCongregacao').html('<option value="">Carregando...</option>');
        $.ajax({
            url: '../../controllers/congregacao.php',
            type: 'POST',
            data: { acao: 'listar' },
            dataType: 'json',
            success: function(res) {
                if (res && res.sucesso === true && res.data) {
                    const congregacoes = res.data;
                    if (Array.isArray(congregacoes) && congregacoes.length > 0) {
                        let opts = '<option value="">Selecione uma congregação</option>';
                        let optsFiltro = '<option value="">Todas</option>';
                        congregacoes.forEach(c => {
                            const selected = (c.id == selectedId) ? 'selected' : '';
                            opts += `<option value="${c.id}" ${selected}>${escapeHtml(c.nome)}</option>`;
                            optsFiltro += `<option value="${c.id}">${escapeHtml(c.nome)}</option>`;
                        });
                        $('#congregacao_id, #congregacao_edit').html(opts);
                        $('#filtroCongregacao').html(optsFiltro);
                    } else {
                        $('#congregacao_id, #congregacao_edit, #filtroCongregacao').html('<option value="">Nenhuma congregação cadastrada</option>');
                    }
                } else {
                    $('#congregacao_id, #congregacao_edit, #filtroCongregacao').html('<option value="">Erro ao carregar congregações</option>');
                }
            },
            error: function() {
                $('#congregacao_id, #congregacao_edit, #filtroCongregacao').html('<option value="">Erro ao carregar congregações</option>');
                exibirMensagem('erro', 'Erro ao carregar congregações.');
            }
        });
    }
    
    function inicializarDataTable() {
        if (!$.fn.DataTable.isDataTable('#tabelaUsuarios')) {
            tabela = $('#tabelaUsuarios').DataTable({
                ajax: {
                    url: '../../controllers/usuario.php',
                    type: 'POST',
                    data: function(d) {
                        // Envia os filtros para o backend (sem o status)
                        return {
                            acao: 'listar',
                            busca: $('#filtroBusca').val(),
                            perfil: $('#filtroPerfil').val(),
                            congregacao: $('#filtroCongregacao').val()
                            // status foi REMOVIDO porque a tabela não tem essa coluna
                        };
                    },
                    dataType: 'json',
                    dataSrc: function(json) {
                        if (json.sucesso && json.data) return json.data;
                        else return [];
                    },
                    error: function() { exibirMensagem('erro', 'Erro ao carregar usuários.'); }
                },
                columns: [
                    { data: 'id' }, 
                    { data: 'nome' }, 
                    { data: 'email' },
                    { data: 'perfil', render: function(data) {
                        const cls = data === 'admin' ? 'badge-perfil-admin' : (data === 'professor' ? 'badge-perfil-professor' : 'badge-perfil-user');
                        return `<span class="badge ${cls}">${escapeHtml(data)}</span>`;
                    }},
                    { data: 'congregacao_nome', render: function(data) { return data ? escapeHtml(data) : '<span class="text-muted">-</span>'; } },
                    { data: 'id', className: 'text-center', orderable: false, render: function(id) {
                        return `<div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-warning btn-sm editar" data-id="${id}" data-bs-toggle="modal" data-bs-target="#modalEditar" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm excluir" data-id="${id}" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                        </div>`;
                    }}
                ],
                responsive: true,
                language: {
                    sEmptyTable: "Nenhum usuário encontrado", 
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
        } else { 
            tabela = $('#tabelaUsuarios').DataTable(); 
        }
    }
    
    function renderizarCartoes(usuarios) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        container.innerHTML = '';
        if (!usuarios || usuarios.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">Nenhum usuário encontrado</div>';
            return;
        }
        usuarios.forEach(u => {
            const perfilCls = u.perfil === 'admin' ? 'badge-perfil-admin' : (u.perfil === 'professor' ? 'badge-perfil-professor' : 'badge-perfil-user');
            container.innerHTML += `<div class="col-12"><div class="usuario-card"><div class="card-body">
                <h5 class="card-title">${escapeHtml(u.nome)}</h5>
                <div class="info-row"><strong><i class="fas fa-envelope"></i> Email:</strong> ${escapeHtml(u.email)}</div>
                <div class="info-row"><strong><i class="fas fa-user-tag"></i> Perfil:</strong> <span class="badge ${perfilCls}">${escapeHtml(u.perfil)}</span></div>
                <div class="info-row"><strong><i class="fas fa-building"></i> Congregação:</strong> ${escapeHtml(u.congregacao_nome || '-')}</div>
                <div class="card-actions">
                    <button class="btn btn-warning btn-sm editar flex-fill" data-id="${u.id}" data-bs-toggle="modal" data-bs-target="#modalEditar"><i class="fas fa-edit me-1"></i> Editar</button>
                    <button class="btn btn-danger btn-sm excluir flex-fill" data-id="${u.id}"><i class="fas fa-trash-alt me-1"></i> Excluir</button>
                </div>
            </div></div></div>`;
        });
    }
    
    function atualizarCards() {
        $.ajax({
            url: '../../controllers/usuario.php', 
            type: 'POST', 
            data: { 
                acao: 'listar',
                busca: $('#filtroBusca').val(),
                perfil: $('#filtroPerfil').val(),
                congregacao: $('#filtroCongregacao').val()
                // status foi REMOVIDO
            }, 
            dataType: 'json',
            success: function(res) { 
                if (res.sucesso && Array.isArray(res.data)) {
                    renderizarCartoes(res.data);
                }
            },
            error: function() { console.error('Erro ao atualizar cards'); }
        });
    }
    
    $('#formCadastrarUsuario').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { e.stopPropagation(); $(this).addClass('was-validated'); exibirMensagem('erro', 'Preencha todos os campos obrigatórios.'); return; }
        $(this).removeClass('was-validated');
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');
        $.ajax({
            url: '../../controllers/usuario.php', method: 'POST',
            data: { acao: 'salvar', nome: $('#nome').val(), email: $('#email').val(), senha: $('#senha').val(), perfil: $('#perfil').val(), congregacao_id: $('#congregacao_id').val() || '' },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Cadastrar');
                if (res.sucesso) {
                    $('#modalCadastrar').modal('hide'); 
                    $('#formCadastrarUsuario')[0].reset(); 
                    $('#formCadastrarUsuario').removeClass('was-validated');
                    if (tabela) tabela.ajax.reload(null, false); 
                    atualizarCards();
                    exibirMensagem('sucesso', res.mensagem || 'Usuário cadastrado com sucesso!');
                } else { 
                    exibirMensagem('erro', res.mensagem || 'Erro ao cadastrar usuário'); 
                }
            },
            error: function() { 
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Cadastrar'); 
                exibirMensagem('erro', 'Erro de comunicação com o servidor'); 
            }
        });
    });
    
    $(document).on('click', '.editar', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '../../controllers/usuario.php', method: 'POST', data: { acao: 'buscar', id: id }, dataType: 'json',
            success: function(res) {
                if (res.sucesso && res.data) {
                    const u = res.data;
                    $('#id_edit').val(u.id); 
                    $('#nome_edit').val(u.nome); 
                    $('#email_edit').val(u.email);
                    $('#perfil_edit').val(u.perfil); 
                    $('#senha_edit').val('');
                    carregarCongregacoes(u.congregacao_id || '');
                    $('#modalEditar').modal('show');
                } else { 
                    exibirMensagem('erro', res.mensagem || 'Erro ao buscar dados do usuário'); 
                }
            },
            error: function() { 
                exibirMensagem('erro', 'Erro de comunicação com o servidor'); 
            }
        });
    });
    
    $('#formEditarUsuario').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { e.stopPropagation(); $(this).addClass('was-validated'); exibirMensagem('erro', 'Preencha todos os campos obrigatórios.'); return; }
        $(this).removeClass('was-validated');
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');
        $.ajax({
            url: '../../controllers/usuario.php', method: 'POST',
            data: { acao: 'editar', id: $('#id_edit').val(), nome: $('#nome_edit').val(), email: $('#email_edit').val(), senha: $('#senha_edit').val(), perfil: $('#perfil_edit').val(), congregacao_id: $('#congregacao_edit').val() || '' },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar');
                if (res.sucesso) {
                    $('#modalEditar').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false); 
                    atualizarCards();
                    exibirMensagem('sucesso', res.mensagem || 'Usuário atualizado com sucesso!');
                } else { 
                    exibirMensagem('erro', res.mensagem || 'Erro ao atualizar usuário'); 
                }
            },
            error: function() { 
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar'); 
                exibirMensagem('erro', 'Erro de comunicação com o servidor'); 
            }
        });
    });
    
    $(document).on('click', '.excluir', function() {
        $('#id_excluir_display').text($(this).data('id'));
        $('#btnConfirmarExcluir').data('id', $(this).data('id'));
        $('#modalExcluir').modal('show');
    });
    
    $('#btnConfirmarExcluir').on('click', function() {
        const id = $(this).data('id'); 
        if (!id) return;
        const btn = $(this); 
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Excluindo...');
        $.ajax({
            url: '../../controllers/usuario.php', method: 'POST', data: { acao: 'excluir', id: id }, dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Sim, Excluir');
                if (res.sucesso) {
                    $('#modalExcluir').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false); 
                    atualizarCards();
                    exibirMensagem('sucesso', res.mensagem || 'Usuário excluído com sucesso!');
                } else { 
                    exibirMensagem('erro', res.mensagem || 'Erro ao excluir usuário'); 
                }
            },
            error: function() { 
                btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Sim, Excluir'); 
                exibirMensagem('erro', 'Erro de comunicação com o servidor'); 
            }
        });
    });
    
    // CORRIGIDO: Aplicar filtros sem o parâmetro status
    $('#btnAplicarFiltros').on('click', function() {
        const busca = $('#filtroBusca').val();
        const perfil = $('#filtroPerfil').val();
        const congregacao = $('#filtroCongregacao').val();
        // O filtro de status foi REMOVIDO porque a tabela usuarios não tem essa coluna
        
        if (tabela) {
            tabela.ajax.reload(null, false);
        }
        
        atualizarCards();
    });
    
    $('#btnLimparFiltros').on('click', function() { 
        $('#formFiltros')[0].reset(); 
        if (tabela) {
            tabela.ajax.reload(null, false);
        }
        atualizarCards(); 
    });
    
    function inicializar() { 
        inicializarDataTable(); 
        carregarCongregacoes(); 
        atualizarCards(); 
    }
    
    inicializar();
});
</script>
</body>
</html>