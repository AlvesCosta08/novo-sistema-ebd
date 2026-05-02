<?php  
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Configurar título da página
$pageTitle = 'Gestão de Usuários';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-users-gear me-3" style="color: var(--primary-600);"></i>
                Gestão de Usuários
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-users-gear me-1"></i> Usuários
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Gerencie os usuários do sistema, seus perfis e permissões de acesso
            </p>
        </div>
        <div>
            <button class="btn btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
                <i class="fas fa-plus me-2"></i> Cadastrar Usuário
            </button>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="100">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalUsuarios">--</div>
                <div class="stat-label">Total de Usuários</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-purple bg-opacity-10 text-purple">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-value" id="totalAdmin">--</div>
                <div class="stat-label">Administradores</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div class="stat-value" id="totalProfessores">--</div>
                <div class="stat-label">Professores</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-value" id="totalUsuariosComum">--</div>
                <div class="stat-label">Usuários Comuns</div>
            </div>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form id="formFiltros" class="row g-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-search me-1 text-primary"></i> Buscar
                    </label>
                    <input type="text" id="filtroBusca" class="form-control" placeholder="Nome ou email...">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-user-tag me-1 text-primary"></i> Perfil
                    </label>
                    <select id="filtroPerfil" class="form-select">
                        <option value="">Todos os perfis</option>
                        <option value="admin">Administrador</option>
                        <option value="user">Usuário Comum</option>
                        <option value="professor">Professor</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <select id="filtroCongregacao" class="form-select">
                        <option value="">Todas as congregações</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" id="btnAplicarFiltros" class="btn btn-modern btn-modern-primary flex-grow-1">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                        <button type="button" id="btnLimparFiltros" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-eraser me-1"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Usuários (Desktop) -->
    <div class="modern-card d-none d-md-block" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Usuários
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaUsuarios" class="custom-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 70px">ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Perfil</th>
                            <th>Congregação</th>
                            <th class="text-center" style="width: 100px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando usuários...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cards de Usuários (Mobile) -->
    <div id="cartoesContainer" class="row g-3 d-md-none" data-aos="fade-up" data-aos-delay="300"></div>
</div>

<!-- Modal Cadastrar Usuário -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-plus me-2"></i> Cadastrar Usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCadastrarUsuario">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user me-1 text-primary"></i> Nome <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nome" name="nome" class="form-control" required autocomplete="name" placeholder="Nome completo">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1 text-primary"></i> Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-control" required autocomplete="email" placeholder="usuario@exemplo.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-key me-1 text-primary"></i> Senha <span class="text-danger">*</span>
                            </label>
                            <input type="password" id="senha" name="senha" class="form-control" required autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user-tag me-1 text-primary"></i> Perfil <span class="text-danger">*</span>
                            </label>
                            <select id="perfil" name="perfil" class="form-select" required>
                                <option value="admin">👑 Administrador</option>
                                <option value="user">👤 Usuário Comum</option>
                                <option value="professor">📚 Professor</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-church me-1 text-primary"></i> Congregação
                            </label>
                            <select id="congregacao_id" name="congregacao_id" class="form-select">
                                <option value="">Selecione uma congregação</option>
                            </select>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i> Opcional - pode ser definido posteriormente
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--gray-50);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-save me-1"></i> Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i> Editar Usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarUsuario">
                <div class="modal-body">
                    <input type="hidden" id="id_edit" name="id">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user me-1 text-primary"></i> Nome <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nome_edit" name="nome" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1 text-primary"></i> Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="email_edit" name="email" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-key me-1 text-primary"></i> Nova Senha
                            </label>
                            <input type="password" id="senha_edit" name="senha" class="form-control" autocomplete="new-password" placeholder="Deixe em branco para manter">
                            <small class="text-muted">Digite apenas se quiser alterar a senha</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user-tag me-1 text-primary"></i> Perfil <span class="text-danger">*</span>
                            </label>
                            <select id="perfil_edit" name="perfil" class="form-select" required>
                                <option value="admin">👑 Administrador</option>
                                <option value="user">👤 Usuário Comum</option>
                                <option value="professor">📚 Professor</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-church me-1 text-primary"></i> Congregação
                            </label>
                            <select id="congregacao_edit" name="congregacao_id" class="form-select">
                                <option value="">Selecione uma congregação</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--gray-50);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-modern" style="background: var(--warning); color: white;">
                        <i class="fas fa-save me-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
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
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir este usuário?</p>
                <p class="text-danger small mt-2">
                    <i class="fas fa-info-circle me-1"></i> Esta ação não pode ser desfeita.
                </p>
                <small class="text-muted">ID: <span id="id_excluir_display" class="fw-bold"></span></small>
            </div>
            <div class="modal-footer justify-content-center" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExcluir">
                    <i class="fas fa-trash-alt me-1"></i> Sim, Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container para Toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<style>
/* Estilos específicos para usuários */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Cores personalizadas */
.text-purple {
    color: #8b5cf6;
}
.bg-purple {
    background-color: #8b5cf6;
}
.bg-opacity-10 {
    opacity: 0.1;
}

/* Badges de perfil */
.badge-perfil-admin {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-perfil-professor {
    background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-perfil-user {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Cards para mobile */
.usuario-card {
    background: white;
    border-radius: 16px;
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.usuario-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.usuario-card .card-body {
    padding: 1.25rem;
    flex: 1;
}

.usuario-card .card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--gray-100);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.usuario-card .card-title i {
    color: var(--primary-600);
    font-size: 1.2rem;
}

.usuario-card .info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.usuario-card .info-row strong {
    color: var(--gray-700);
    font-weight: 600;
    min-width: 90px;
}

.usuario-card .card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--gray-100);
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

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--gradient-primary) !important;
    border: none !important;
    color: white !important;
}

/* Toast personalizado */
.custom-toast {
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border: none;
    min-width: 320px;
}

.custom-toast.bg-success {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
}

.custom-toast.bg-danger {
    background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
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
}
</style>

<script>
$(document).ready(function() {
    let tabela = null;
    let usuarioIdParaExcluir = null;
    
    function escapeHtml(text) { 
        if (!text) return ''; 
        const d = document.createElement('div'); 
        d.textContent = text; 
        return d.innerHTML; 
    }
    
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white show`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `<div class="toast-body d-flex align-items-center gap-2">
            <span style="font-size: 1.2rem;">${icon}</span>
            <span class="flex-grow-1">${mensagem}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
    }
    
    function carregarCongregacoes(selectedId = '') {
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
                        let optsFiltro = '<option value="">Todas as congregações</option>';
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
    
    function atualizarEstatisticas(dados) {
        if (!dados || !Array.isArray(dados)) return;
        
        const total = dados.length;
        const admin = dados.filter(u => u.perfil === 'admin').length;
        const professor = dados.filter(u => u.perfil === 'professor').length;
        const user = dados.filter(u => u.perfil === 'user').length;
        
        $('#totalUsuarios').text(total);
        $('#totalAdmin').text(admin);
        $('#totalProfessores').text(professor);
        $('#totalUsuariosComum').text(user);
    }
    
    function renderizarCartoes(usuarios) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        container.innerHTML = '';
        
        if (!usuarios || usuarios.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-5"><i class="fas fa-users-slash fa-3x mb-3" style="color: var(--gray-400);"></i><p class="text-muted mb-0">Nenhum usuário encontrado</p></div>';
            return;
        }
        
        usuarios.forEach(u => {
            const perfilCls = u.perfil === 'admin' ? 'badge-perfil-admin' : (u.perfil === 'professor' ? 'badge-perfil-professor' : 'badge-perfil-user');
            const perfilIcon = u.perfil === 'admin' ? '👑' : (u.perfil === 'professor' ? '📚' : '👤');
            container.innerHTML += `
                <div class="col-12">
                    <div class="usuario-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-circle"></i>
                                ${escapeHtml(u.nome)}
                            </h5>
                            <div class="info-row">
                                <strong><i class="fas fa-envelope"></i> Email:</strong>
                                <span>${escapeHtml(u.email)}</span>
                            </div>
                            <div class="info-row">
                                <strong><i class="fas fa-user-tag"></i> Perfil:</strong>
                                <span class="${perfilCls}">${perfilIcon} ${escapeHtml(u.perfil)}</span>
                            </div>
                            <div class="info-row">
                                <strong><i class="fas fa-church"></i> Congregação:</strong>
                                <span>${escapeHtml(u.congregacao_nome || '-')}</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-warning-custom btn-sm editar flex-fill" data-id="${u.id}" data-bs-toggle="modal" data-bs-target="#modalEditar">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-danger-custom btn-sm excluir flex-fill" data-id="${u.id}">
                                    <i class="fas fa-trash-alt me-1"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
    }
    
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#tabelaUsuarios')) {
            tabela = $('#tabelaUsuarios').DataTable();
            tabela.ajax.reload(null, false);
            return;
        }
        
        tabela = $('#tabelaUsuarios').DataTable({
            ajax: {
                url: '../../controllers/usuario.php',
                type: 'POST',
                data: function(d) {
                    return {
                        acao: 'listar',
                        busca: $('#filtroBusca').val(),
                        perfil: $('#filtroPerfil').val(),
                        congregacao: $('#filtroCongregacao').val()
                    };
                },
                dataType: 'json',
                dataSrc: function(json) {
                    if (json.sucesso && json.data) {
                        atualizarEstatisticas(json.data);
                        renderizarCartoes(json.data);
                        return json.data;
                    }
                    return [];
                },
                error: function() { 
                    exibirMensagem('erro', 'Erro ao carregar usuários.'); 
                }
            },
            columns: [
                { 
                    data: 'id',
                    render: function(data) {
                        return `<span class="badge-id" style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 8px; font-family: monospace;">#${data}</span>`;
                    }
                },
                { 
                    data: 'nome',
                    render: function(data) {
                        return `<i class="fas fa-user-circle me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                    }
                },
                { data: 'email' },
                { 
                    data: 'perfil',
                    render: function(data) {
                        const cls = data === 'admin' ? 'badge-perfil-admin' : (data === 'professor' ? 'badge-perfil-professor' : 'badge-perfil-user');
                        const icon = data === 'admin' ? '👑' : (data === 'professor' ? '📚' : '👤');
                        return `<span class="${cls}">${icon} ${escapeHtml(data)}</span>`;
                    }
                },
                { 
                    data: 'congregacao_nome',
                    render: function(data) { 
                        return data ? `<i class="fas fa-church me-1" style="color: var(--primary-500);"></i>${escapeHtml(data)}` : '<span class="text-muted">-</span>'; 
                    } 
                },
                {
                    data: 'id',
                    className: 'text-center',
                    orderable: false,
                    render: function(id) {
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning-custom btn-sm editar" data-id="${id}" data-bs-toggle="modal" data-bs-target="#modalEditar" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger-custom btn-sm excluir" data-id="${id}" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>`;
                    }
                }
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
            order: [[0, 'asc']],
            drawCallback: function() {
                $('.dataTables_paginate').addClass('mt-3');
            }
        });
    }
    
    // Cadastrar usuário
    $('#formCadastrarUsuario').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            exibirMensagem('erro', 'Preencha todos os campos obrigatórios.');
            return;
        }
        $(this).removeClass('was-validated');
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/usuario.php',
            method: 'POST',
            data: {
                acao: 'salvar',
                nome: $('#nome').val(),
                email: $('#email').val(),
                senha: $('#senha').val(),
                perfil: $('#perfil').val(),
                congregacao_id: $('#congregacao_id').val() || ''
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Cadastrar');
                if (res.sucesso) {
                    $('#modalCadastrar').modal('hide');
                    $('#formCadastrarUsuario')[0].reset();
                    $('#formCadastrarUsuario').removeClass('was-validated');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', res.mensagem || 'Usuário cadastrado com sucesso!');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao cadastrar usuário');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Cadastrar');
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Editar usuário
    $(document).on('click', '.editar', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '../../controllers/usuario.php',
            method: 'POST',
            data: { acao: 'buscar', id: id },
            dataType: 'json',
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
    
    // Salvar edição
    $('#formEditarUsuario').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            exibirMensagem('erro', 'Preencha todos os campos obrigatórios.');
            return;
        }
        $(this).removeClass('was-validated');
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/usuario.php',
            method: 'POST',
            data: {
                acao: 'editar',
                id: $('#id_edit').val(),
                nome: $('#nome_edit').val(),
                email: $('#email_edit').val(),
                senha: $('#senha_edit').val(),
                perfil: $('#perfil_edit').val(),
                congregacao_id: $('#congregacao_edit').val() || ''
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar');
                if (res.sucesso) {
                    $('#modalEditar').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', res.mensagem || 'Usuário atualizado com sucesso!');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao atualizar usuário');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar');
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Excluir usuário
    $(document).on('click', '.excluir', function() {
        usuarioIdParaExcluir = $(this).data('id');
        $('#id_excluir_display').text(usuarioIdParaExcluir);
        $('#modalExcluir').modal('show');
    });
    
    $('#btnConfirmarExcluir').on('click', function() {
        if (!usuarioIdParaExcluir) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...');
        
        $.ajax({
            url: '../../controllers/usuario.php',
            method: 'POST',
            data: { acao: 'excluir', id: usuarioIdParaExcluir },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                if (res.sucesso) {
                    $('#modalExcluir').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', res.mensagem || 'Usuário excluído com sucesso!');
                    usuarioIdParaExcluir = null;
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao excluir usuário');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Aplicar filtros
    $('#btnAplicarFiltros').on('click', function() {
        if (tabela) {
            tabela.ajax.reload(null, false);
        }
    });
    
    $('#btnLimparFiltros').on('click', function() {
        $('#filtroBusca').val('');
        $('#filtroPerfil').val('');
        $('#filtroCongregacao').val('');
        if (tabela) {
            tabela.ajax.reload(null, false);
        }
    });
    
    // Limpar modais ao fechar
    $('#modalCadastrar').on('hidden.bs.modal', function() {
        $('#formCadastrarUsuario')[0].reset();
        $('#formCadastrarUsuario').removeClass('was-validated');
    });
    
    $('#modalEditar').on('hidden.bs.modal', function() {
        $('#formEditarUsuario').removeClass('was-validated');
    });
    
    $('#modalExcluir').on('hidden.bs.modal', function() {
        usuarioIdParaExcluir = null;
        $('#btnConfirmarExcluir').prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
    });
    
    // Inicializar AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });
    
    // Inicializar
    carregarCongregacoes();
    inicializarDataTable();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>