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
            <h1 class="display-5 fw-bold mb-2" style="color: #1f2937;">
                <i class="fas fa-list me-3" style="color: #4f46e5;"></i>
                Listar Matrículas
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: #4f46e5;">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php" style="color: #4f46e5;">
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
                <table class="custom-table mb-0" id="tabelaMatriculas" width="100%">
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
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: #4f46e5;"></i>
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
            <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
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
            <div class="modal-footer">
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
            <div class="modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-4x mb-3" style="color: #ef4444;"></i>
                <p class="mb-2 fs-5 fw-semibold">Tem certeza que deseja excluir esta matrícula?</p>
                <p class="text-danger small mt-2">
                    <i class="fas fa-info-circle me-1"></i> Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer justify-content-center">
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
<div id="toastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}
.spinner-custom {
    width: 50px;
    height: 50px;
    border: 4px solid #fff;
    border-top-color: #4f46e5;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.badge-status-ativo {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}
.badge-status-inativo {
    background: #6b7280;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}
.modern-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-header-modern {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}
.custom-table {
    width: 100%;
    margin-bottom: 0;
}
.custom-table th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}
.custom-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #e5e7eb;
}
.btn-modern {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-modern-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border: none;
}
.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin: 0 2px;
    border: none;
    cursor: pointer;
}
.btn-action:hover {
    transform: translateY(-2px);
}
.btn-view {
    background: #e0e7ff;
    color: #4f46e5;
}
.btn-view:hover {
    background: #c7d2fe;
    color: #4338ca;
}
.btn-edit {
    background: #fef3c7;
    color: #d97706;
}
.btn-edit:hover {
    background: #fde68a;
    color: #b45309;
}
.btn-delete {
    background: #fee2e2;
    color: #ef4444;
}
.btn-delete:hover {
    background: #fecaca;
    color: #dc2626;
}
.detalhes-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}
.detalhes-item:last-child {
    border-bottom: none;
}
.detalhes-label {
    font-weight: 600;
    color: #374151;
}
.detalhes-value {
    color: #1f2937;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    border: none !important;
    color: white !important;
}
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    let tabela = null;
    let matriculaIdParaExcluir = null;
    
    function exibirToast(mensagem, tipo = 'success') {
        const container = document.getElementById('toastContainer');
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${tipo} border-0 show`;
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '300px';
        toastEl.style.marginBottom = '10px';
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${mensagem}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toastEl);
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 300);
        }, 4000);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
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
                    exibirToast('Erro ao carregar matrículas.', 'danger');
                }
            },
            columns: [
                { 
                    data: 'id',
                    render: function(data) {
                        return `<span style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 8px;">#${data}</span>`;
                    }
                },
                { 
                    data: 'aluno',
                    render: function(data) {
                        return `<i class="fas fa-user-graduate me-2" style="color: #4f46e5;"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'classe',
                    render: function(data) {
                        return `<i class="fas fa-chalkboard-user me-2" style="color: #10b981;"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'congregacao',
                    render: function(data) {
                        return `<i class="fas fa-church me-2" style="color: #4f46e5;"></i>${escapeHtml(data)}`;
                    }
                },
                { 
                    data: 'trimestre',
                    render: function(data) {
                        return `<span style="background: #e0e7ff; padding: 0.25rem 0.5rem; border-radius: 8px;">${escapeHtml(data)}</span>`;
                    }
                },
                { 
                    data: 'data_matricula',
                    render: function(data) {
                        if (!data || data === '0000-00-00') return '-';
                        return data;
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
                                <button class="btn-action btn-edit" onclick="editarMatricula(${data.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
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
            lengthMenu: [5, 10, 25, 50, 100]
        });
    }
    
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
                if (res.sucesso && res.dados) {
                    const data = res.dados;
                    const statusClass = data.status === 'ativo' ? 'badge-status-ativo' : 'badge-status-inativo';
                    const statusIcon = data.status === 'ativo' ? 'fa-check-circle' : 'fa-times-circle';
                    const statusText = data.status === 'ativo' ? 'Ativo' : 'Inativo';
                    
                    $('#detalhesMatricula').html(`
                        <div class="detalhes-item">
                            <span class="detalhes-label">ID:</span>
                            <span class="detalhes-value">#${data.id}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label">Aluno:</span>
                            <span class="detalhes-value">${escapeHtml(data.aluno)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label">Classe:</span>
                            <span class="detalhes-value">${escapeHtml(data.classe)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label">Congregação:</span>
                            <span class="detalhes-value">${escapeHtml(data.congregacao)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label">Trimestre:</span>
                            <span class="detalhes-value">${escapeHtml(data.trimestre)}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label">Data Matrícula:</span>
                            <span class="detalhes-value">${data.data_matricula && data.data_matricula !== '0000-00-00' ? data.data_matricula : '-'}</span>
                        </div>
                        <div class="detalhes-item">
                            <span class="detalhes-label">Status:</span>
                            <span class="${statusClass}"><i class="fas ${statusIcon} me-1"></i> ${statusText}</span>
                        </div>
                    `);
                    $('#modalVisualizar').modal('show');
                } else {
                    exibirToast(res.mensagem || 'Erro ao carregar detalhes', 'danger');
                }
            },
            error: function() {
                exibirToast('Erro ao carregar detalhes da matrícula', 'danger');
            }
        });
    };
    
    window.editarMatricula = function(id) {
        window.location.href = `index.php?id=${id}`;
    };
    
    window.confirmarExclusao = function(id) {
        matriculaIdParaExcluir = id;
        $('#modalExcluir').modal('show');
    };
    
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
                    exibirToast(res.mensagem || 'Matrícula excluída com sucesso!', 'success');
                    matriculaIdParaExcluir = null;
                } else {
                    exibirToast(res.mensagem || 'Erro ao excluir matrícula', 'danger');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
                exibirToast('Erro ao excluir matrícula', 'danger');
            }
        });
    });
    
    $('#modalExcluir').on('hidden.bs.modal', function() {
        matriculaIdParaExcluir = null;
        $('#btnConfirmarExcluir').prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Sim, Excluir');
    });
    
    inicializarDataTable();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>