<?php  
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Configurar título da página
$pageTitle = 'Gestão de Congregações';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-church me-3" style="color: var(--primary-600);"></i>
                Gestão de Congregações
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-church me-1"></i> Congregações
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Cadastre e gerencie as congregações do sistema
            </p>
        </div>
        <div>
            <button class="btn btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
                <i class="fas fa-plus me-2"></i> Nova Congregação
            </button>
        </div>
    </div>

    <!-- Tabela de Congregações (Desktop) -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Congregações
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="tabelaContainer" class="table-responsive">
                <table id="tabelaCongregacoes" class="custom-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 80px">ID</th>
                            <th>Nome da Congregação</th>
                            <th class="text-center" style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando dados...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cards de Congregações (Mobile) -->
    <div id="cartoesContainer" class="row g-3 mt-2" style="display: none;"></div>
</div>

<!-- Modal Cadastrar Congregação -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white" id="modalCadastrarLabel">
                    <i class="fas fa-plus-circle me-2"></i> Nova Congregação
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formCadastrarCongregacao">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">
                            <i class="fas fa-church text-primary me-1"></i> Nome da Congregação <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nome" name="nome" class="form-control" 
                               placeholder="Ex: Congregação Central" 
                               required pattern=".{3,100}" maxlength="100" autocomplete="off">
                        <div class="invalid-feedback">Nome inválido. Mínimo 3 caracteres.</div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Digite o nome completo da congregação
                        </small>
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

<!-- Modal Editar Congregação -->
<div class="modal fade" id="modalEditarCongregacao" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
                <h5 class="modal-title text-white" id="modalEditarLabel">
                    <i class="fas fa-edit me-2"></i> Editar Congregação
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarCongregacao">
                <div class="modal-body">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="mb-3">
                        <label for="nomeEditar" class="form-label">
                            <i class="fas fa-church text-primary me-1"></i> Nome da Congregação <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nomeEditar" name="nome" class="form-control" 
                               required pattern=".{3,100}" maxlength="100">
                        <div class="invalid-feedback">Nome inválido. Mínimo 3 caracteres.</div>
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
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);">
                <h5 class="modal-title text-white" id="modalExcluirLabel">
                    <i class="fas fa-trash-alt me-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-4x mb-3" style="color: var(--warning);"></i>
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir esta congregação?</p>
                <small class="text-muted">ID: <span id="idExcluirDisplay" class="fw-bold"></span></small>
                <p class="text-danger small mt-3">
                    <i class="fas fa-alert me-1"></i> Esta ação não poderá ser desfeita!
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
/* Estilos específicos para a página de congregações */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Cards para mobile */
.congregacao-card {
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

.congregacao-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.congregacao-card .card-body {
    padding: 1.25rem;
    flex: 1;
}

.congregacao-card .card-title {
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

.congregacao-card .card-title i {
    color: var(--primary-600);
    font-size: 1.2rem;
}

.congregacao-card .info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.congregacao-card .info-row strong {
    color: var(--gray-700);
    font-weight: 600;
    min-width: 35px;
}

.congregacao-card .info-row .badge-id {
    background: var(--gray-100);
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    font-family: monospace;
    font-size: 0.8rem;
    color: var(--gray-700);
}

.congregacao-card .card-actions {
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
    
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#tabelaCongregacoes')) {
            tabela = $('#tabelaCongregacoes').DataTable();
            tabela.ajax.reload(null, false);
            return;
        }
        
        tabela = $('#tabelaCongregacoes').DataTable({
            ajax: {
                url: '../../controllers/congregacao.php',
                type: 'POST',
                data: { acao: 'listar' },
                dataType: 'json',
                dataSrc: 'data',
                error: function(xhr) {
                    console.error('Erro AJAX:', xhr.responseText);
                    exibirMensagem('erro', 'Erro ao carregar congregações.');
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
                        return `<i class="fas fa-church me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                    }
                },
                {
                    data: 'id',
                    className: 'text-center',
                    orderable: false,
                    render: function(id) {
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning-custom btn-sm btnEditar" 
                                        data-id="${id}" data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarCongregacao" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger-custom btn-sm btnExcluir" 
                                        data-id="${id}" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            responsive: true,
            language: {
                sEmptyTable: "Nenhuma congregação encontrada",
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
            }
        });
    }
    
    function renderizarCartoes(congregacoes) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!congregacoes || congregacoes.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-church fa-3x mb-3" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0">Nenhuma congregação cadastrada</p>
                </div>`;
            return;
        }
        
        congregacoes.forEach(c => {
            container.innerHTML += `
                <div class="col-12">
                    <div class="congregacao-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-church"></i>
                                ${escapeHtml(c.nome)}
                            </h5>
                            <div class="info-row">
                                <strong><i class="fas fa-hashtag"></i> ID:</strong>
                                <span class="badge-id">#${c.id}</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-warning-custom btn-sm btnEditar flex-fill" 
                                        data-id="${c.id}" data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarCongregacao">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-danger-custom btn-sm btnExcluir flex-fill" 
                                        data-id="${c.id}">
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
    
    function atualizarCards() {
        $.ajax({
            url: '../../controllers/congregacao.php',
            type: 'POST',
            data: { acao: 'listar' },
            dataType: 'json',
            success: function(res) {
                if (res.sucesso && Array.isArray(res.data)) {
                    renderizarCartoes(res.data);
                }
            },
            error: function() {
                console.error('Erro ao carregar cards');
            }
        });
    }
    
    // Cadastrar congregação
    $('#formCadastrarCongregacao').on('submit', function(e) {
        e.preventDefault();
        
        const nome = $('#nome').val().trim();
        
        if (!nome || nome.length < 3) {
            $('#nome').addClass('is-invalid');
            exibirMensagem('erro', 'Nome inválido. Mínimo 3 caracteres.');
            $('#nome').focus();
            return;
        }
        
        $('#nome').removeClass('is-invalid');
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/congregacao.php',
            method: 'POST',
            data: { acao: 'salvar', nome: nome },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Cadastrar');
                
                if (res.sucesso) {
                    $('#modalCadastrar').modal('hide');
                    $('#formCadastrarCongregacao')[0].reset();
                    
                    if (tabela) tabela.ajax.reload(null, false);
                    atualizarCards();
                    exibirMensagem('sucesso', res.mensagem || 'Cadastrada com sucesso!');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao cadastrar');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Cadastrar');
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Editar congregação
    $(document).on('click', '.btnEditar', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '../../controllers/congregacao.php',
            method: 'POST',
            data: { acao: 'buscar', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.sucesso && res.congregacao) {
                    $('#idEditar').val(res.congregacao.id);
                    $('#nomeEditar').val(res.congregacao.nome);
                    $('#modalEditarCongregacao').modal('show');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao buscar dados');
                }
            },
            error: function() {
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Salvar edição
    $('#formEditarCongregacao').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#idEditar').val();
        const nome = $('#nomeEditar').val().trim();
        
        if (!nome || nome.length < 3) {
            $('#nomeEditar').addClass('is-invalid');
            exibirMensagem('erro', 'Nome inválido. Mínimo 3 caracteres.');
            $('#nomeEditar').focus();
            return;
        }
        
        $('#nomeEditar').removeClass('is-invalid');
        
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/congregacao.php',
            method: 'POST',
            data: { acao: 'editar', id: id, nome: nome },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar Alterações');
                
                if (res.sucesso) {
                    $('#modalEditarCongregacao').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    atualizarCards();
                    exibirMensagem('sucesso', res.mensagem || 'Atualizada com sucesso!');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao atualizar');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar Alterações');
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Excluir congregação
    $(document).on('click', '.btnExcluir', function() {
        const id = $(this).data('id');
        $('#idExcluirDisplay').text(id);
        $('#btnConfirmarExcluir').data('id', id);
        $('#modalExcluir').modal('show');
    });
    
    $('#btnConfirmarExcluir').on('click', function() {
        const id = $(this).data('id');
        if (!id) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...');
        
        $.ajax({
            url: '../../controllers/congregacao.php',
            method: 'POST',
            data: { acao: 'excluir', id: id },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                
                if (res.sucesso) {
                    $('#modalExcluir').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    atualizarCards();
                    exibirMensagem('sucesso', res.mensagem || 'Excluída com sucesso!');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao excluir');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                exibirMensagem('erro', 'Erro de comunicação com o servidor');
            }
        });
    });
    
    // Limpar formulários ao fechar modais
    $('#modalCadastrar, #modalEditarCongregacao, #modalExcluir').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
    });
    
    // Inicializar DataTable e cards
    inicializarDataTable();
    atualizarCards();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>