<?php  
// Iniciar sessão antes de qualquer output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Configurar título da página
$pageTitle = 'Gestão de Classes';

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
                Gestão de Classes
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-chalkboard-user me-1"></i> Classes
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Cadastre e gerencie as classes da escola bíblica
            </p>
        </div>
        <div>
            <button class="btn btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
                <i class="fas fa-plus me-2"></i> Cadastrar Classe
            </button>
        </div>
    </div>

    <!-- Tabela de Classes (Desktop) -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Classes
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="tabelaContainer" class="table-responsive">
                <table id="tabelaClasses" class="custom-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 80px">ID</th>
                            <th>Nome da Classe</th>
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

    <!-- Cards de Classes (Mobile) -->
    <div id="cartoesContainer" class="row g-3 mt-2" style="display: none;"></div>
</div>

<!-- Modal Cadastrar Classe -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white" id="modalCadastrarLabel">
                    <i class="fas fa-plus-circle me-2"></i> Cadastrar Classe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastrarClasse">
                    <div class="mb-3">
                        <label for="nome" class="form-label">
                            <i class="fas fa-tag text-primary me-1"></i> Nome da Classe <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               placeholder="Ex: Juvenis, Adolescentes, Adultos..." 
                               required autocomplete="off">
                        <div class="invalid-feedback">Por favor, informe o nome da classe.</div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Escolha um nome descritivo para a classe
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-modern btn-modern-primary" id="btnSalvarCadastrar">
                    <i class="fas fa-save me-1"></i> Cadastrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Classe -->
<div class="modal fade" id="modalEditarClasse" tabindex="-1" aria-labelledby="modalEditarClasseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
                <h5 class="modal-title text-white" id="modalEditarClasseLabel">
                    <i class="fas fa-edit me-2"></i> Editar Classe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarClasse">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="mb-3">
                        <label for="nomeEditar" class="form-label">
                            <i class="fas fa-tag text-primary me-1"></i> Nome da Classe <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nomeEditar" name="nome" required autocomplete="off">
                        <div class="invalid-feedback">Por favor, informe o nome da classe.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-modern" style="background: var(--warning); color: white;" id="btnSalvarEditar">
                    <i class="fas fa-save me-1"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container para Toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<style>
/* Estilos específicos para a página de classes */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Cards para mobile */
.classe-card {
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

.classe-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.classe-card .card-body {
    padding: 1.25rem;
    flex: 1;
}

.classe-card .card-title {
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

.classe-card .card-title i {
    color: var(--primary-600);
    font-size: 1.2rem;
}

.classe-card .info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.classe-card .info-row strong {
    color: var(--gray-700);
    font-weight: 600;
    min-width: 35px;
}

.classe-card .info-row .badge-id {
    background: var(--gray-100);
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    font-family: monospace;
    font-size: 0.8rem;
    color: var(--gray-700);
}

.classe-card .card-actions {
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
                                { 
                                    data: 'id',
                                    render: function(data) {
                                        return `<span class="badge-id" style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 8px; font-family: monospace;">#${data}</span>`;
                                    }
                                },
                                { 
                                    data: 'nome',
                                    render: function(data) {
                                        return `<i class="fas fa-chalkboard-user me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                                    }
                                },
                                {
                                    data: 'id',
                                    className: 'text-center',
                                    orderable: false,
                                    render: function(data) {
                                        return `
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-warning-custom btn-sm editar" 
                                                        data-id="${data}" data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarClasse" title="Editar">
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
                            language: {
                                sEmptyTable: "Nenhuma classe encontrada",
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
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-chalkboard-user fa-3x mb-3" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0">Nenhuma classe cadastrada</p>
                </div>`;
            return;
        }
        
        classes.forEach(classe => {
            container.innerHTML += `
                <div class="col-12">
                    <div class="classe-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chalkboard-user"></i>
                                ${escapeHtml(classe.nome)}
                            </h5>
                            <div class="info-row">
                                <strong><i class="fas fa-hashtag"></i> ID:</strong>
                                <span class="badge-id">#${classe.id}</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-warning-custom btn-sm editar flex-fill" 
                                        data-id="${classe.id}" data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarClasse">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-danger-custom btn-sm excluir flex-fill" 
                                        data-id="${classe.id}">
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
    
    // Cadastrar classe
    $('#btnSalvarCadastrar').on('click', function() {
        const nome = $('#nome').val().trim();
        
        if (!nome) {
            $('#nome').addClass('is-invalid');
            exibirMensagem('erro', 'Nome da classe é obrigatório.');
            $('#nome').focus();
            return;
        }
        
        $('#nome').removeClass('is-invalid');
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/classe.php',
            method: 'POST',
            data: { acao: 'salvar', nome: nome },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Cadastrar');
                
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
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Cadastrar');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
            }
        });
    });
    
    // Editar classe
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
    
    // Salvar edição
    $('#btnSalvarEditar').on('click', function() {
        const nome = $('#nomeEditar').val().trim();
        const id = $('#idEditar').val();
        
        if (!nome) {
            $('#nomeEditar').addClass('is-invalid');
            exibirMensagem('erro', 'Nome da classe é obrigatório.');
            $('#nomeEditar').focus();
            return;
        }
        
        $('#nomeEditar').removeClass('is-invalid');
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: '../../controllers/classe.php',
            method: 'POST',
            data: { acao: 'salvar', id: id, nome: nome },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar Alterações');
                
                if (response.sucesso) {
                    $('#modalEditarClasse').modal('hide');
                    carregarDados();
                    exibirMensagem('sucesso', 'Classe atualizada com sucesso!');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao atualizar classe.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar Alterações');
                exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
            }
        });
    });
    
    // Excluir classe
    $(document).on('click', '.excluir', function() {
        const id = $(this).data('id');
        
        if (confirm('⚠️ Tem certeza que deseja excluir esta classe?\n\nEsta ação pode afetar alunos vinculados e não poderá ser desfeita!')) {
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
    
    // Limpar formulários ao fechar modais
    $('#modalCadastrar').on('hidden.bs.modal', function() {
        $('#formCadastrarClasse')[0].reset();
        $('#nome').removeClass('is-invalid');
    });
    
    $('#modalEditarClasse').on('hidden.bs.modal', function() {
        $('#nomeEditar').removeClass('is-invalid');
    });
    
    // Carregar dados iniciais
    carregarDados();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>