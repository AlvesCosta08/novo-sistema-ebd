<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Listar Matrículas';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';

$usuario_id       = $_SESSION['usuario_id'] ?? null;
$nome_usuario     = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil           = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id   = $_SESSION['congregacao_id'] ?? null;

if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado.');
}
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-list me-3" style="color: var(--primary-600);"></i>
                Listar Matrículas
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php" style="color: var(--primary-600);">
                            <i class="fas fa-user-plus me-1"></i> Matrículas
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-list me-1"></i> Listar
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Visualize e gerencie todas as matrículas registradas no sistema
            </p>
        </div>
        <div>
            <a href="index.php" class="btn btn-modern btn-modern-primary">
                <i class="fas fa-plus me-2"></i> Nova Matrícula
            </a>
        </div>
    </div>

    <!-- Tabela de Matrículas -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Matrículas Registradas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table mb-0" id="tabelaMatriculas">
                    <thead>
                        <tr>
                            <th style="width: 60px">ID</th>
                            <th>Aluno</th>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th style="width: 100px">Trimestre</th>
                            <th style="width: 110px">Data</th>
                            <th style="width: 100px">Status</th>
                            <th style="width: 100px" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando matrículas...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização Rápida -->
<div class="modal fade" id="modalVisualizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-info-circle me-2"></i> Detalhes da Matrícula
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesMatricula">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Carregando...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
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
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir esta matrícula?</p>
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
/* Estilos específicos para a página de listar matrículas */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Badge de status */
.badge-status-ativo {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-status-inativo {
    background: linear-gradient(135deg, var(--gray-500) 0%, var(--gray-600) 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Botões de ação */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin: 0 2px;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.btn-view {
    background: var(--primary-100);
    color: var(--primary-700);
}

.btn-view:hover {
    background: var(--primary-200);
    color: var(--primary-800);
}

.btn-edit {
    background: var(--accent-100);
    color: var(--accent-700);
}

.btn-edit:hover {
    background: var(--accent-200);
    color: var(--accent-800);
}

.btn-delete {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.btn-delete:hover {
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

/* Detalhes da matrícula */
.detalhes-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.detalhes-item:last-child {
    border-bottom: none;
}

.detalhes-label {
    font-weight: 600;
    color: var(--gray-700);
}

.detalhes-value {
    color: var(--gray-800);
}

/* Responsividade */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: none;
        text-align: right;
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_length {
        float: none;
        margin-bottom: 1rem;
    }
}
</style>

<script>
$(document).ready(function() {
    let tabela = null;
    let matriculaIdParaExcluir = null;
    
    // Inicializar DataTable
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#tabelaMatriculas')) {
            tabela = $('#tabelaMatriculas').DataTable();
            tabela.ajax.reload(null, false);
            return;
        }
        
        tabela = $('#tabelaMatriculas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../../controllers/matricula.php',
                type: 'POST',
                data: { acao: 'listarMatriculas' },
                error: function(xhr) {
                    console.error('Erro AJAX:', xhr.responseText);
                    exibirMensagem('erro', 'Erro ao carregar matrículas.');
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
                    data: 'aluno',
                    render: function(data) {
                        return `<i class="fas fa-user-graduate me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'classe',
                    render: function(data) {
                        return `<i class="fas fa-chalkboard-user me-2" style="color: var(--success);"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'congregacao',
                    render: function(data) {
                        return `<i class="fas fa-church me-2" style="color: var(--primary-500);"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'trimestre',
                    render: function(data) {
                        return `<span class="badge-id">${escapeHtml(data)}</span>`;
                    }
                },
                { 
                    data: 'data_matricula',
                    render: function(data) {
                        if (!data) return '-';
                        const date = new Date(data);
                        return date.toLocaleDateString('pt-BR');
                    }
                },
                { 
                    data: 'status',
                    render: function(data) {
                        if (data === 'ativo') {
                            return '<span class="badge-status-ativo"><i class="fas fa-check-circle me-1"></i> Ativo</span>';
                        } else {
                            return '<span class="badge-status-inativo"><i class="fas fa-times-circle me-1"></i> Inativo</span>';
                        }
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data) {
                        return `
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn-action btn-view" onclick="visualizarMatricula(${data.id})" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="editar.php?id=${data.id}" class="btn-action btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn-action btn-delete" onclick="confirmarExclusao(${data.id})" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json',
                processing: "Carregando..."
            },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            responsive: true,
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
    
    // Função para visualizar matrícula
    window.visualizarMatricula = function(id) {
        $('#detalhesMatricula').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Carregando detalhes...</p>
            </div>
        `);
        
        $.ajax({
            url: '../../controllers/matricula.php',
            method: 'POST',
            data: { acao: 'buscarMatricula', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.sucesso && res.data) {
                    const data = res.data;
                    const statusClass = data.status === 'ativo' ? 'badge-status-ativo' : 'badge-status-inativo';
                    const statusIcon = data.status === 'ativo' ? 'fa-check-circle' : 'fa-times-circle';
                    const statusText = data.status === 'ativo' ? 'Ativo' : 'Inativo';
                    
                    $('#detalhesMatricula').html(`
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-hashtag me-2"></i> ID:</span>
                            <span class="detalhes-value">#${data.id}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-user-graduate me-2"></i> Aluno:</span>
                            <span class="detalhes-value">${escapeHtml(data.aluno)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-chalkboard-user me-2"></i> Classe:</span>
                            <span class="detalhes-value">${escapeHtml(data.classe)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-church me-2"></i> Congregação:</span>
                            <span class="detalhes-value">${escapeHtml(data.congregacao)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-chart-line me-2"></i> Trimestre:</span>
                            <span class="detalhes-value">${escapeHtml(data.trimestre)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-calendar-alt me-2"></i> Data Matrícula:</span>
                            <span class="detalhes-value">${data.data_matricula ? new Date(data.data_matricula).toLocaleDateString('pt-BR') : '-'}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label"><i class="fas fa-circle me-2"></i> Status:</span>
                            <span class="${statusClass}"><i class="fas ${statusIcon} me-1"></i> ${statusText}</span>
                        </div>
                    `);
                    $('#modalVisualizar').modal('show');
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao carregar detalhes');
                }
            },
            error: function() {
                exibirMensagem('erro', 'Erro ao carregar detalhes da matrícula');
            }
        });
    };
    
    // Função para confirmar exclusão
    window.confirmarExclusao = function(id) {
        matriculaIdParaExcluir = id;
        $('#modalExcluir').modal('show');
    };
    
    // Confirmar exclusão
    $('#btnConfirmarExcluir').on('click', function() {
        if (!matriculaIdParaExcluir) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...');
        
        $.ajax({
            url: '../../controllers/matricula.php',
            method: 'POST',
            data: { acao: 'excluirMatricula', id: matriculaIdParaExcluir },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                
                if (res.sucesso) {
                    $('#modalExcluir').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    exibirMensagem('sucesso', res.mensagem || 'Matrícula excluída com sucesso!');
                    matriculaIdParaExcluir = null;
                } else {
                    exibirMensagem('erro', res.mensagem || 'Erro ao excluir matrícula');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                exibirMensagem('erro', 'Erro ao excluir matrícula');
            }
        });
    });
    
    // Limpar ao fechar modais
    $('#modalExcluir').on('hidden.bs.modal', function() {
        matriculaIdParaExcluir = null;
        $('#btnConfirmarExcluir').prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
    });
    
    // Inicializar
    inicializarDataTable();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>