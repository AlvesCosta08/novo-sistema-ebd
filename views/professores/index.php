<?php  
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Configurar título da página
$pageTitle = 'Gestão de Professores';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-chalkboard-user me-3" style="color: var(--primary-600);"></i>
                Gestão de Professores
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-chalkboard-user me-1"></i> Professores
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Cadastre e gerencie os professores da escola bíblica
            </p>
        </div>
        <div>
            <button class="btn btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
                <i class="fas fa-plus me-2"></i> Cadastrar Professor
            </button>
        </div>
    </div>

    <!-- Tabela de Professores (Desktop) -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Professores
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="tabelaContainer" class="table-responsive">
                <table id="tabelaProfessores" class="custom-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 80px">ID</th>
                            <th>Nome do Professor</th>
                            <th class="text-center" style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando professores...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cards de Professores (Mobile) -->
    <div id="cartoesContainer" class="row g-3 mt-2" style="display: none;"></div>
</div>

<!-- Modal Cadastrar Professor -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white" id="modalCadastrarLabel">
                    <i class="fas fa-user-plus me-2"></i> Cadastrar Professor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formCadastrarProfessor">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="usuario_id" class="form-label">
                            <i class="fas fa-user text-primary me-1"></i> Usuário <span class="text-danger">*</span>
                        </label>
                        <select id="usuario_id" name="usuario_id" class="form-select" required>
                            <option value="">Selecione um usuário</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um usuário.</div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Apenas usuários que ainda não são professores serão listados
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--gray-50);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-save me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Professor -->
<div class="modal fade" id="modalEditarProfessor" tabindex="-1" aria-labelledby="modalEditarProfessorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
                <h5 class="modal-title text-white" id="modalEditarProfessorLabel">
                    <i class="fas fa-edit me-2"></i> Editar Professor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarProfessor">
                <div class="modal-body">
                    <input type="hidden" id="idEditar" name="idEditar">
                    <div class="mb-3">
                        <label for="usuario_idEditar" class="form-label">
                            <i class="fas fa-user text-primary me-1"></i> Usuário <span class="text-danger">*</span>
                        </label>
                        <select id="usuario_idEditar" name="usuario_idEditar" class="form-select" required>
                            <option value="">Selecione um usuário</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um usuário.</div>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--gray-50);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-modern" style="background: var(--warning); color: white;">
                        <i class="fas fa-save me-1"></i> Atualizar
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
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir este professor?</p>
                <p class="text-danger small mt-2">
                    <i class="fas fa-info-circle me-1"></i> Esta ação não pode ser desfeita.
                </p>
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
/* Estilos específicos para a página de professores */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Cards para mobile */
.professor-card {
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

.professor-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.professor-card .card-body {
    padding: 1.25rem;
    flex: 1;
}

.professor-card .card-title {
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

.professor-card .card-title i {
    color: var(--primary-600);
    font-size: 1.2rem;
}

.professor-card .info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.professor-card .info-row strong {
    color: var(--gray-700);
    font-weight: 600;
    min-width: 35px;
}

.professor-card .info-row .badge-id {
    background: var(--gray-100);
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    font-family: monospace;
    font-size: 0.8rem;
    color: var(--gray-700);
}

.professor-card .card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--gray-100);
}

/* Botões específicos */
.btn-warning-custom {
    background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);
    color: white;
    border: none;
    transition: all 0.2s ease;
}

.btn-warning-custom:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    color: white;
}

.btn-danger-custom {
    background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
    color: white;
    border: none;
    transition: all 0.2s ease;
}

.btn-danger-custom:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    color: white;
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
    color: white;
}

.custom-toast.bg-danger {
    background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
    color: white;
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

@media (max-width: 767px) {
    #tabelaContainer {
        display: none !important;
    }
    #cartoesContainer {
        display: flex !important;
    }
}

@media (min-width: 768px) {
    #tabelaContainer {
        display: block !important;
    }
    #cartoesContainer {
        display: none !important;
    }
}
</style>

<script>
$(document).ready(function() {
    let tabela = null;
    let professorIdParaExcluir = null;
    
    // Função para exibir mensagem toast
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
        
        toast.innerHTML = `
            <div class="toast-body d-flex align-items-center gap-2">
                <span style="font-size: 1.2rem;">${icon}</span>
                <span class="flex-grow-1">${mensagem}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>`;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
    
    // Função para escapar HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Carregar usuários para os selects
    function carregarUsuarios(selectedId = '', selectId = 'usuario_id') {
        $.ajax({
            url: '../../controllers/usuario.php',
            method: 'POST',
            data: { acao: 'listar' },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso && Array.isArray(response.data)) {
                    let options = '<option value="">Selecione um usuário</option>';
                    response.data.forEach(u => {
                        options += `<option value="${u.id}" ${u.id == selectedId ? 'selected' : ''}>${escapeHtml(u.nome)}</option>`;
                    });
                    $('#' + selectId).html(options);
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao carregar usuários');
                }
            },
            error: function() { 
                exibirMensagem('erro', 'Erro ao carregar usuários'); 
            }
        });
    }
    
    // Renderizar cards para mobile
    function renderizarCartoes(professores) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!professores || professores.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-chalkboard-user fa-3x mb-3" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0">Nenhum professor cadastrado</p>
                </div>`;
            return;
        }
        
        professores.forEach(prof => {
            container.innerHTML += `
                <div class="col-12">
                    <div class="professor-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chalkboard-user"></i>
                                ${escapeHtml(prof.usuario_nome)}
                            </h5>
                            <div class="info-row">
                                <strong><i class="fas fa-hashtag"></i> ID:</strong>
                                <span class="badge-id">#${prof.id}</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-warning-custom btn-sm editar flex-fill" 
                                        data-id="${prof.id}" data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarProfessor">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-danger-custom btn-sm excluir flex-fill" 
                                        data-id="${prof.id}">
                                    <i class="fas fa-trash-alt me-1"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
    }
    
    // Inicializar DataTable
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#tabelaProfessores')) {
            tabela = $('#tabelaProfessores').DataTable();
            tabela.ajax.reload(null, false);
            return;
        }
        
        tabela = $('#tabelaProfessores').DataTable({
            ajax: {
                url: '../../controllers/professores.php',
                type: 'POST',
                data: { acao: 'listar' },
                dataType: "json",
                dataSrc: 'data',
                error: function(xhr) {
                    console.error("Erro no AJAX:", xhr.responseText);
                    exibirMensagem('erro', 'Erro ao carregar dados dos professores.');
                }
            },
            columns: [
                { 
                    data: "id",
                    render: function(data) {
                        return `<span class="badge-id" style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 8px; font-family: monospace;">#${data}</span>`;
                    }
                },
                { 
                    data: "usuario_nome",
                    render: function(data) {
                        return `<i class="fas fa-chalkboard-user me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                    }
                },
                {
                    data: "id",
                    className: 'text-center',
                    orderable: false,
                    render: function(data) {
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning-custom btn-sm editar" 
                                        data-id="${data}" data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarProfessor" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger-custom btn-sm excluir" 
                                        data-id="${data}" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            responsive: true,
            language: {
                sEmptyTable: "Nenhum professor encontrado",
                sInfo: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                sInfoEmpty: "Nenhum registro",
                sInfoFiltered: "(filtrado de _MAX_)",
                sLengthMenu: "_MENU_ por página",
                sLoadingRecords: "Carregando...",
                sZeroRecords: "Sem resultados",
                sSearch: "Buscar:",
                oPaginate: { 
                    sNext: "Próximo", 
                    sPrevious: "Anterior" 
                }
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [[0, 'asc']],
            drawCallback: function() {
                $('.dataTables_paginate').addClass('mt-3');
                const data = tabela.rows().data().toArray();
                renderizarCartoes(data);
            }
        });
    }
    
    // Cadastrar professor
    $("#formCadastrarProfessor").submit(function(e) {
        e.preventDefault();
        
        const usuarioId = $('#usuario_id').val();
        if (!usuarioId) {
            $('#usuario_id').addClass('is-invalid');
            exibirMensagem('erro', 'Selecione um usuário.');
            return;
        }
        $('#usuario_id').removeClass('is-invalid');
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/professores.php',
            method: 'POST',
            data: { acao: 'salvar', usuario_id: usuarioId },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar');
                
                if (response.sucesso) {
                    $('#modalCadastrar').modal('hide');
                    $('#formCadastrarProfessor')[0].reset();
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', response.mensagem || 'Professor cadastrado com sucesso!');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao cadastrar professor');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor');
            }
        });
    });
    
    // Editar professor
    $(document).on('click', '.editar', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '../../controllers/professores.php',
            method: 'POST',
            data: { acao: 'buscar', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso && response.data) {
                    $('#idEditar').val(response.data.id);
                    carregarUsuarios(response.data.usuario_id, 'usuario_idEditar');
                    $('#modalEditarProfessor').modal('show');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao buscar dados do professor');
                }
            },
            error: function() { 
                exibirMensagem('erro', 'Erro ao comunicar com o servidor'); 
            }
        });
    });
    
    // Salvar edição
    $("#formEditarProfessor").submit(function(e) {
        e.preventDefault();
        
        const id = $('#idEditar').val();
        const usuarioId = $('#usuario_idEditar').val();
        
        if (!usuarioId) {
            $('#usuario_idEditar').addClass('is-invalid');
            exibirMensagem('erro', 'Selecione um usuário.');
            return;
        }
        $('#usuario_idEditar').removeClass('is-invalid');
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/professores.php',
            method: 'POST',
            data: { acao: 'editar', id: id, usuario_id: usuarioId },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Atualizar');
                
                if (response.sucesso) {
                    $('#modalEditarProfessor').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', response.mensagem || 'Professor atualizado com sucesso!');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao atualizar professor');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Atualizar');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor');
            }
        });
    });
    
    // Excluir professor
    $(document).on('click', '.excluir', function() {
        professorIdParaExcluir = $(this).data('id');
        $('#modalExcluir').modal('show');
    });
    
    $('#btnConfirmarExcluir').on('click', function() {
        if (!professorIdParaExcluir) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...');
        
        $.ajax({
            url: '../../controllers/professores.php',
            method: 'POST',
            data: { acao: 'excluir', id: professorIdParaExcluir },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                
                if (response.sucesso) {
                    $('#modalExcluir').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', response.mensagem || 'Professor excluído com sucesso!');
                    professorIdParaExcluir = null;
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao excluir professor');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor');
            }
        });
    });
    
    // Limpar modais ao fechar
    $('#modalCadastrar').on('hidden.bs.modal', function() { 
        $('#formCadastrarProfessor')[0].reset(); 
        $('#usuario_id').removeClass('is-invalid');
    });
    
    $('#modalEditarProfessor').on('hidden.bs.modal', function() { 
        $('#formEditarProfessor')[0].reset(); 
        $('#usuario_idEditar').removeClass('is-invalid');
    });
    
    $('#modalExcluir').on('hidden.bs.modal', function() {
        professorIdParaExcluir = null;
        $('#btnConfirmarExcluir').prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
    });
    
    // Inicializar AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });
    
    // Carregar usuários e inicializar
    carregarUsuarios('', 'usuario_id');
    inicializarDataTable();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>