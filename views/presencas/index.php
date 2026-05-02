<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Controle de Presenças';

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-user-check me-3" style="color: var(--success);"></i>
                Controle de Presenças
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-user-check me-1"></i> Presenças
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Gerencie as presenças dos alunos nas chamadas realizadas
            </p>
        </div>
        <div>
            <button class="btn btn-modern btn-modern-success" data-bs-toggle="modal" data-bs-target="#modalPresenca" onclick="abrirModalCadastro()">
                <i class="fas fa-plus me-2"></i> Nova Presença
            </button>
        </div>
    </div>

    <!-- Tabela de Presenças -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-success">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Registro de Presenças
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaPresencas" class="custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Classe</th>
                            <th>Data da Chamada</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando presenças...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Criar/Editar Presença -->
<div class="modal fade" id="modalPresenca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formPresenca" class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-check me-2"></i> <span id="modalTitle">Cadastro de Presença</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="chamada_id" class="form-label">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Chamada
                        </label>
                        <select name="chamada_id" id="chamada_id" class="form-select" required>
                            <option value="">Selecione uma chamada...</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="aluno_id" class="form-label">
                            <i class="fas fa-user-graduate text-primary me-1"></i> Aluno
                        </label>
                        <select name="aluno_id" id="aluno_id" class="form-select" required>
                            <option value="">Selecione um aluno...</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="presente" class="form-label">
                            <i class="fas fa-circle text-primary me-1"></i> Status
                        </label>
                        <select name="presente" id="presente" class="form-select" required>
                            <option value="presente">✅ Presente</option>
                            <option value="ausente">❌ Ausente</option>
                            <option value="justificado">⏰ Justificado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-modern btn-modern-success">
                    <i class="fas fa-save me-1"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
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
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir esta presença?</p>
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
/* Estilos específicos para presenças */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Badge de status */
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-presente {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
}

.badge-ausente {
    background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
    color: white;
}

.badge-justificado {
    background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);
    color: white;
}

/* Botões de ação */
.btn-action-sm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin: 0 2px;
}

.btn-action-sm:hover {
    transform: translateY(-2px);
}

.btn-edit-presenca {
    background: var(--accent-100);
    color: var(--accent-700);
}

.btn-edit-presenca:hover {
    background: var(--accent-200);
    color: var(--accent-800);
}

.btn-delete-presenca {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.btn-delete-presenca:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #b91c1c;
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
    let presencaIdParaExcluir = null;
    
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
    
    // Função para obter badge de status
    function getStatusBadge(status) {
        if (status === 'presente') {
            return '<span class="badge-status badge-presente"><i class="fas fa-check-circle me-1"></i> Presente</span>';
        } else if (status === 'ausente') {
            return '<span class="badge-status badge-ausente"><i class="fas fa-times-circle me-1"></i> Ausente</span>';
        } else {
            return '<span class="badge-status badge-justificado"><i class="fas fa-clock me-1"></i> Justificado</span>';
        }
    }
    
    // Inicializar DataTable
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#tabelaPresencas')) {
            tabela = $('#tabelaPresencas').DataTable();
            tabela.ajax.reload(null, false);
            return;
        }
        
        tabela = $('#tabelaPresencas').DataTable({
            ajax: {
                url: 'presencas_helper.php',
                method: 'POST',
                data: { acao: 'listar' },
                dataSrc: 'data',
                error: function(xhr) {
                    console.error('Erro AJAX:', xhr.responseText);
                    exibirMensagem('erro', 'Erro ao carregar presenças.');
                }
            },
            columns: [
                { 
                    data: 'aluno_nome',
                    render: function(data) {
                        return `<i class="fas fa-user-graduate me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'classe_nome',
                    render: function(data) {
                        return `<i class="fas fa-chalkboard-user me-2" style="color: var(--success);"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'data_chamada',
                    render: function(data) {
                        if (!data) return '-';
                        const date = new Date(data);
                        return `<i class="fas fa-calendar-alt me-2" style="color: var(--info);"></i>${date.toLocaleDateString('pt-BR')}`;
                    }
                },
                { 
                    data: 'presente',
                    render: function(data) {
                        return getStatusBadge(data);
                    }
                },
                {
                    data: 'id',
                    className: 'text-center',
                    orderable: false,
                    render: function(id) {
                        return `
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn-action-sm btn-edit-presenca" onclick="editarPresenca(${id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action-sm btn-delete-presenca" onclick="confirmarExclusao(${id})" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            language: {
                sEmptyTable: "Nenhuma presença encontrada",
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
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[2, 'desc']],
            drawCallback: function() {
                $('.dataTables_paginate').addClass('mt-3');
            }
        });
    }
    
    // Função para escapar HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Função para carregar os selects
    function carregarSelects() {
        $.ajax({
            url: 'presencas_helper.php',
            method: 'POST',
            data: { acao: 'carregar_selects' },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    const chamadas = response.chamadas || [];
                    const alunos = response.alunos || [];
                    
                    $('#chamada_id').html('<option value="">Selecione uma chamada...</option>' + 
                        chamadas.map(c => `<option value="${c.id}">${escapeHtml(c.nome)}</option>`).join(''));
                    
                    $('#aluno_id').html('<option value="">Selecione um aluno...</option>' + 
                        alunos.map(a => `<option value="${a.id}">${escapeHtml(a.nome)}</option>`).join(''));
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao carregar dados');
                }
            },
            error: function() {
                exibirMensagem('erro', 'Erro ao carregar selects');
            }
        });
    }
    
    // Função para abrir modal de cadastro
    window.abrirModalCadastro = function() {
        $('#modalTitle').text('Cadastro de Presença');
        $('#formPresenca')[0].reset();
        $('#id').val('');
        carregarSelects();
    };
    
    // Função para editar presença
    window.editarPresenca = function(id) {
        $.ajax({
            url: 'presencas_helper.php',
            method: 'POST',
            data: { acao: 'buscar', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso && response.dados) {
                    const presenca = response.dados;
                    $('#modalTitle').text('Editar Presença');
                    $('#id').val(presenca.id);
                    $('#chamada_id').val(presenca.chamada_id);
                    $('#aluno_id').val(presenca.aluno_id);
                    $('#presente').val(presenca.presente);
                    $('#modalPresenca').modal('show');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao carregar dados');
                }
            },
            error: function() {
                exibirMensagem('erro', 'Erro ao buscar presença');
            }
        });
    };
    
    // Função para confirmar exclusão
    window.confirmarExclusao = function(id) {
        presencaIdParaExcluir = id;
        $('#modalExcluir').modal('show');
    };
    
    // Salvar ou atualizar presença
    $('#formPresenca').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const btn = $(this).find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        $.ajax({
            url: 'presencas_helper.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar');
                
                if (response.sucesso) {
                    $('#modalPresenca').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', response.mensagem || 'Presença salva com sucesso!');
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao salvar presença');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Salvar');
                exibirMensagem('erro', 'Erro ao salvar presença');
            }
        });
    });
    
    // Confirmar exclusão
    $('#btnConfirmarExcluir').on('click', function() {
        if (!presencaIdParaExcluir) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...');
        
        $.ajax({
            url: 'presencas_helper.php',
            method: 'POST',
            data: { acao: 'excluir', id: presencaIdParaExcluir },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                
                if (response.sucesso) {
                    $('#modalExcluir').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', response.mensagem || 'Presença excluída com sucesso!');
                    presencaIdParaExcluir = null;
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao excluir presença');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                exibirMensagem('erro', 'Erro ao excluir presença');
            }
        });
    });
    
    // Limpar ao fechar modais
    $('#modalExcluir').on('hidden.bs.modal', function() {
        presencaIdParaExcluir = null;
        $('#btnConfirmarExcluir').prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
    });
    
    // Inicializar AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });
    
    // Inicializar DataTable
    inicializarDataTable();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>
