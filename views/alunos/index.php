<?php  
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Configurar título da página
$pageTitle = 'Gestão de Alunos';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-users me-3" style="color: var(--primary-600);"></i>
                Gestão de Alunos
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-users me-1"></i> Alunos
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Cadastre, edite e gerencie os alunos da escola
            </p>
        </div>
        <div>
            <button class="btn btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao">
                <i class="fas fa-plus me-2"></i> Cadastrar Novo Aluno
            </button>
        </div>
    </div>
    
    <!-- Tabela de Alunos (Desktop) -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Alunos
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="tabelaContainer" class="table-responsive">
                <table id="tabelaAlunos" class="custom-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Nascimento</th>
                            <th>Telefone</th>
                            <th>Classe</th>
                            <th class="text-center" style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="color: var(--primary-400);"></i>
                                <p class="text-muted mb-0">Carregando dados...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cards de Alunos (Mobile) -->
    <div id="cartoesContainer" class="row g-3 mt-2" style="display: none;"></div>
</div>

<!-- Modal de Cadastro/Edição -->
<div id="modalCadastroEdicao" class="modal fade" tabindex="-1" aria-labelledby="modalCadastroEdicaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary);">
                <h5 class="modal-title text-white" id="modalCadastroEdicaoLabel">
                    <i class="fas fa-user-plus me-2"></i> Cadastrar Aluno
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastroEdicao">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-4">
                        <div class="col-12">
                            <label for="nome" class="form-label">
                                <i class="fas fa-user text-primary me-1"></i> Nome Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nome" name="nome" class="form-control" 
                                   placeholder="Ex: João da Silva" required autocomplete="name" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label for="telefone" class="form-label">
                                <i class="fas fa-phone text-primary me-1"></i> Telefone <span class="text-danger">*</span>
                            </label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" 
                                   placeholder="(11) 99999-9999" required inputmode="tel" autocomplete="tel">
                            <div class="invalid-feedback">Telefone inválido (formato: (99) 99999-9999)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="data_nascimento" class="form-label">
                                <i class="fas fa-calendar-alt text-primary me-1"></i> Data de Nascimento <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required autocomplete="bday">
                        </div>
                        <div class="col-12">
                            <label for="classe_id" class="form-label">
                                <i class="fas fa-chalkboard-user text-primary me-1"></i> Classe <span class="text-danger">*</span>
                            </label>
                            <select id="classe_id" name="classe_id" class="form-select" required>
                                <option value="">Selecione uma classe</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: var(--gray-50);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-modern btn-modern-primary" id="btnSalvar">
                    <i class="fas fa-save me-1"></i> Gravar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container para Toasts -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<style>
/* Estilos específicos para a página de alunos */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

.badge-classe {
    background: linear-gradient(135deg, var(--primary-100) 0%, var(--primary-50) 100%);
    color: var(--primary-700);
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-block;
}

/* Cards para mobile */
.aluno-card {
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

.aluno-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.aluno-card .card-body {
    padding: 1.25rem;
    flex: 1;
}

.aluno-card .card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--gray-100);
}

.aluno-card .info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.aluno-card .info-row strong {
    color: var(--gray-700);
    font-weight: 600;
    min-width: 70px;
}

.aluno-card .info-row a {
    color: var(--primary-600);
    text-decoration: none;
    transition: color 0.2s;
}

.aluno-card .info-row a:hover {
    color: var(--primary-700);
    text-decoration: underline;
}

.aluno-card .card-actions {
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

/* DataTables personalizado para o tema */
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
    opacity: 0;
    transition: opacity 0.3s ease;
}

.custom-toast.show {
    opacity: 1;
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
    
    .btn-modern {
        width: 100%;
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
        display: none;
    }
    #cartoesContainer {
        display: flex !important;
    }
}

@media (min-width: 768px) {
    #tabelaContainer {
        display: block;
    }
    #cartoesContainer {
        display: none !important;
    }
}
</style>

<script>
$(document).ready(function() {
    let tabela = null;
    
    // Função para escapar HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Inicializa DataTable
    function inicializarDataTable() {
        if ($('#tabelaAlunos').length && !$.fn.DataTable.isDataTable('#tabelaAlunos')) {
            tabela = $('#tabelaAlunos').DataTable({
                ajax: {
                    url: '../../controllers/aluno.php?acao=listar',
                    dataSrc: function(json) {
                        if (json.status === 'success' && Array.isArray(json.data)) {
                            return json.data;
                        }
                        return [];
                    },
                    error: function(xhr, status, error) { 
                        console.error('Erro DataTable:', error);
                        exibirMensagem('erro', 'Erro ao carregar dados dos alunos.'); 
                    }
                },
                columns: [
                    { data: 'nome' },
                    { 
                        data: 'data_nascimento', 
                        render: function(data) { 
                            return data && data !== '0000-00-00' ? moment(data).format('DD/MM/YYYY') : '-'; 
                        } 
                    },
                    { 
                        data: 'telefone',
                        render: function(data) {
                            if (!data) return '-';
                            let tel = data.replace(/\D/g, '');
                            if (tel.length === 11) {
                                return `(${tel.substring(0,2)}) ${tel.substring(2,7)}-${tel.substring(7,11)}`;
                            } else if (tel.length === 10) {
                                return `(${tel.substring(0,2)}) ${tel.substring(2,6)}-${tel.substring(6,10)}`;
                            }
                            return data;
                        }
                    },
                    { 
                        data: 'classe', 
                        render: function(data) { 
                            return data ? `<span class="badge-classe"><i class="fas fa-chalkboard-user me-1"></i>${escapeHtml(data)}</span>` : '-'; 
                        } 
                    },
                    {
                        data: 'id',
                        className: 'text-center',
                        orderable: false,
                        render: function(id) {
                            return `
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-warning-custom btnEditar" 
                                            data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao" 
                                            data-id="${id}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger-custom btnExcluir" 
                                            data-id="${id}" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                        }
                    }
                ],
                responsive: true,
                language: {
                    sEmptyTable: "Nenhum registro encontrado",
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
                order: [[0, 'asc']]
            });
        }
    }
    
// Carrega classes para o select - Usando o controller aluno.php
function carregarClasses() {
    return new Promise((resolve, reject) => {
        // Usando o endpoint listar_classes do seu controller aluno.php
        $.ajax({
            url: '../../controllers/aluno.php?acao=listar_classes',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                console.log('Resposta classes:', res); // Debug
                
                let classes = [];
                
                // Seu controller retorna {status: "success", data: [...]}
                if (res.status === 'success' && Array.isArray(res.data)) {
                    classes = res.data;
                } else if (Array.isArray(res)) {
                    classes = res;
                }
                
                if (classes.length > 0) {
                    const sel = $('#classe_id');
                    sel.empty().append('<option value="">Selecione uma classe</option>');
                    classes.forEach(function(c) {
                        sel.append(`<option value="${c.id}">${escapeHtml(c.nome)}</option>`);
                    });
                    resolve();
                } else {
                    // Se não encontrou classes, tenta endpoint alternativo
                    console.warn('Nenhuma classe encontrada no primeiro endpoint');
                    
                    // Tenta listar diretamente do banco via PHP (fallback)
                    $.ajax({
                        url: '../../controllers/classe.php?acao=listar',
                        method: 'GET',
                        dataType: 'json',
                        success: function(res2) {
                            let classes2 = [];
                            if (res2.status === 'success' && Array.isArray(res2.data)) {
                                classes2 = res2.data;
                            } else if (Array.isArray(res2)) {
                                classes2 = res2;
                            }
                            
                            if (classes2.length > 0) {
                                const sel = $('#classe_id');
                                sel.empty().append('<option value="">Selecione uma classe</option>');
                                classes2.forEach(function(c) {
                                    sel.append(`<option value="${c.id}">${escapeHtml(c.nome)}</option>`);
                                });
                                resolve();
                            } else {
                                // Fallback manual - carrega classes diretamente
                                carregarClassesManual(resolve, reject);
                            }
                        },
                        error: function() {
                            carregarClassesManual(resolve, reject);
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('Erro ao carregar classes:', xhr);
                carregarClassesManual(resolve, reject);
            }
        });
    });
}

// Fallback: Carrega classes manualmente via PHP inline
function carregarClassesManual(resolve, reject) {
    // Tenta carregar via uma requisição AJAX para um arquivo PHP temporário
    // ou simplesmente exibe mensagem
    console.warn('Tentando carregar classes manualmente...');
    
    // Cria um elemento script para carregar dados do PHP
    $.ajax({
        url: '../../controllers/aluno.php?acao=listar_classes',
        method: 'GET',
        dataType: 'json',
        timeout: 3000,
        success: function(res) {
            if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
                const sel = $('#classe_id');
                sel.empty().append('<option value="">Selecione uma classe</option>');
                res.data.forEach(function(c) {
                    sel.append(`<option value="${c.id}">${escapeHtml(c.nome)}</option>`);
                });
                resolve();
            } else {
                // Se mesmo assim não conseguir, verifica se há classes no banco
                verificarClassesNoBanco(resolve, reject);
            }
        },
        error: function() {
            reject();
        }
    });
}


    function verificarClassesNoBanco(resolve, reject) {
        $.ajax({
            url: '../../controllers/aluno.php?acao=listar',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                // Se conseguiu listar alunos, mas não classes, pode ser que o select esteja vazio
                // Nesse caso, exibe mensagem para o admin
                exibirMensagem('aviso', 'Nenhuma classe encontrada. Cadastre classes no sistema.');
                reject();
            },
            error: function() {
                reject();
            }
        });
    }
        // Renderiza cards para mobile
    function renderizarCartoes(alunos) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!alunos || alunos.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-users-slash fa-3x mb-3" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0">Nenhum aluno cadastrado</p>
                </div>`;
            return;
        }
        
        alunos.forEach(function(a) {
            let telefoneFormatado = a.telefone || '-';
            let telefoneLink = '';
            if (a.telefone) {
                let tel = a.telefone.replace(/\D/g, '');
                telefoneLink = `href="tel:${tel}"`;
                if (tel.length === 11) {
                    telefoneFormatado = `(${tel.substring(0,2)}) ${tel.substring(2,7)}-${tel.substring(7,11)}`;
                } else if (tel.length === 10) {
                    telefoneFormatado = `(${tel.substring(0,2)}) ${tel.substring(2,6)}-${tel.substring(6,10)}`;
                }
            }
            
            let dataFormatada = '-';
            if (a.data_nascimento && a.data_nascimento !== '0000-00-00') {
                dataFormatada = moment(a.data_nascimento).format('DD/MM/YYYY');
            }
            
            container.innerHTML += `
                <div class="col-12">
                    <div class="aluno-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-graduate me-2" style="color: var(--primary-600);"></i>
                                ${escapeHtml(a.nome || '-')}
                            </h5>
                            <div class="info-row">
                                <strong><i class="far fa-calendar-alt"></i> Nasc.:</strong>
                                <span>${dataFormatada}</span>
                            </div>
                            <div class="info-row">
                                <strong><i class="fas fa-phone"></i> Tel.:</strong>
                                <a ${telefoneLink} class="text-decoration-none">${telefoneFormatado}</a>
                            </div>
                            <div class="info-row">
                                <strong><i class="fas fa-chalkboard-user"></i> Classe:</strong>
                                ${a.classe ? `<span class="badge-classe">${escapeHtml(a.classe)}</span>` : '-'}
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-warning-custom btn-sm btnEditar flex-fill" 
                                        data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao" 
                                        data-id="${a.id}">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-danger-custom btn-sm btnExcluir flex-fill" 
                                        data-id="${a.id}">
                                    <i class="fas fa-trash-alt me-1"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
    }
    
    function carregarAlunosParaCartoes() {
        $.ajax({
            url: '../../controllers/aluno.php?acao=listar',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' && Array.isArray(res.data)) {
                    renderizarCartoes(res.data);
                } else {
                    renderizarCartoes([]);
                }
            },
            error: function() {
                renderizarCartoes([]);
            }
        });
    }
    
    // Evento de edição
    $(document).on('click', '.btnEditar', function() {
        const id = $(this).data('id');
        $('#modalCadastroEdicaoLabel').html('<i class="fas fa-edit me-2"></i> Editar Aluno');
        
        $('#btnSalvar').prop('disabled', true);
        
        $.ajax({
            url: '../../controllers/aluno.php?acao=buscar',
            method: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' && res.data) {
                    $('#id').val(res.data.id);
                    $('#nome').val(res.data.nome);
                    $('#data_nascimento').val(res.data.data_nascimento);
                    $('#classe_id').val(res.data.classe_id);
                    
                    let tel = res.data.telefone || '';
                    if (tel) {
                        let telLimpo = tel.replace(/\D/g, '');
                        if (telLimpo.length === 11) {
                            $('#telefone').val(`(${telLimpo.substring(0,2)}) ${telLimpo.substring(2,7)}-${telLimpo.substring(7,11)}`);
                        } else if (telLimpo.length === 10) {
                            $('#telefone').val(`(${telLimpo.substring(0,2)}) ${telLimpo.substring(2,6)}-${telLimpo.substring(6,10)}`);
                        } else {
                            $('#telefone').val(tel);
                        }
                    } else {
                        $('#telefone').val('');
                    }
                } else {
                    exibirMensagem('erro', res.message || 'Erro ao carregar dados do aluno');
                }
            },
            error: function() {
                exibirMensagem('erro', 'Erro ao carregar dados do aluno');
            },
            complete: function() {
                $('#btnSalvar').prop('disabled', false);
            }
        });
    });
    
    // Reset do modal ao abrir para cadastro
    $('#modalCadastroEdicao').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btnEditar')) {
            $('#modalCadastroEdicaoLabel').html('<i class="fas fa-user-plus me-2"></i> Cadastrar Aluno');
            $('#formCadastroEdicao')[0].reset();
            $('#id').val('');
            $('#telefone').removeClass('is-invalid');
        }
        carregarClasses();
    });
    
    // Salvar aluno
    $('#btnSalvar').on('click', function() {
        const nome = $('#nome').val().trim();
        let tel = $('#telefone').val().trim();
        const nasc = $('#data_nascimento').val().trim();
        const classe = $('#classe_id').val();
        const id = $('#id').val();
        
        // Validações
        if (!nome) {
            exibirMensagem('erro', 'Informe o nome do aluno.');
            $('#nome').focus();
            return;
        }
        
        if (nome.length < 3) {
            exibirMensagem('erro', 'Nome deve ter pelo menos 3 caracteres.');
            $('#nome').focus();
            return;
        }
        
        const telLimpo = tel.replace(/\D/g, '');
        if (!telLimpo || (telLimpo.length !== 10 && telLimpo.length !== 11)) {
            exibirMensagem('erro', 'Telefone inválido. Use o formato (99) 99999-9999');
            $('#telefone').focus();
            return;
        }
        
        if (!nasc) {
            exibirMensagem('erro', 'Informe a data de nascimento.');
            $('#data_nascimento').focus();
            return;
        }
        
        // Valida idade
        const dataNasc = new Date(nasc);
        const hoje = new Date();
        let idade = hoje.getFullYear() - dataNasc.getFullYear();
        const mesDiff = hoje.getMonth() - dataNasc.getMonth();
        if (mesDiff < 0 || (mesDiff === 0 && hoje.getDate() < dataNasc.getDate())) {
            idade--;
        }
        
        if (idade < 0 || idade > 120) {
            exibirMensagem('erro', 'Data de nascimento inválida.');
            $('#data_nascimento').focus();
            return;
        }
        
        if (!classe) {
            exibirMensagem('erro', 'Selecione uma classe.');
            $('#classe_id').focus();
            return;
        }
        
        // Prepara dados para envio
        const dadosEnvio = {
            nome: nome,
            telefone: telLimpo,
            data_nascimento: nasc,
            classe_id: classe
        };
        
        if (id) {
            dadosEnvio.id = id;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');
        
        const url = id ? '../../controllers/aluno.php?acao=editar' : '../../controllers/aluno.php?acao=salvar';
        
        $.ajax({
            url: url,
            method: 'POST',
            data: dadosEnvio,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#modalCadastroEdicao').modal('hide');
                    
                    if (tabela) {
                        tabela.ajax.reload(null, false);
                    }
                    carregarAlunosParaCartoes();
                    exibirMensagem('sucesso', res.message || 'Operação realizada com sucesso!');
                } else {
                    exibirMensagem('erro', res.message || 'Erro ao salvar');
                }
            },
            error: function(xhr) {
                let msg = 'Erro de comunicação com o servidor.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                exibirMensagem('erro', msg);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Gravar');
            }
        });
    });
    
    // Excluir aluno
    $(document).on('click', '.btnExcluir', function() {
        const id = $(this).data('id');
        
        if (confirm('⚠️ Tem certeza que deseja excluir este aluno?\n\nEsta ação não poderá ser desfeita!')) {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: '../../controllers/aluno.php?acao=excluir',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        if (tabela) {
                            tabela.ajax.reload(null, false);
                        }
                        carregarAlunosParaCartoes();
                        exibirMensagem('sucesso', res.message || 'Excluído com sucesso!');
                    } else {
                        exibirMensagem('erro', res.message || 'Erro ao excluir');
                    }
                },
                error: function() {
                    exibirMensagem('erro', 'Erro ao excluir aluno');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                }
            });
        }
    });
    
    // Função de toast
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
        
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-body d-flex align-items-center gap-2">
                <span style="font-size: 1.2rem;">${icon}</span>
                <span class="flex-grow-1">${escapeHtml(mensagem)}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        container.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }
    
    // Máscara de telefone
    $('#telefone').on('input', function() {
        let v = $(this).val().replace(/\D/g, '');
        
        if (v.length === 0) {
            $(this).val('');
            $(this).removeClass('is-invalid');
            return;
        }
        
        let formatted = '';
        
        if (v.length <= 2) {
            formatted = '(' + v;
        } else if (v.length <= 7) {
            formatted = '(' + v.substring(0, 2) + ') ' + v.substring(2);
        } else if (v.length <= 11) {
            formatted = '(' + v.substring(0, 2) + ') ' + v.substring(2, 7) + '-' + v.substring(7);
        } else {
            formatted = '(' + v.substring(0, 2) + ') ' + v.substring(2, 7) + '-' + v.substring(7, 11);
        }
        
        $(this).val(formatted);
        
        if (v.length < 10 || v.length > 11) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Inicializa
    if (window.innerWidth >= 768) {
        inicializarDataTable();
    }
    
    carregarAlunosParaCartoes();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../../includes/footer.php';
?>