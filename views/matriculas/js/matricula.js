// matricula.js - Gerenciamento de Matrículas (VERSÃO CORRIGIDA)

$(document).ready(function() {
    // Verificar se os modals existem antes de inicializar
    const modalMatriculaEl = document.getElementById('modalMatricula');
    const modalExcluirEl = document.getElementById('modalExcluir');
    const modalMigracaoEl = document.getElementById('modalMigracao');
    
    let modalMatricula = modalMatriculaEl ? new bootstrap.Modal(modalMatriculaEl) : null;
    let modalExcluir = modalExcluirEl ? new bootstrap.Modal(modalExcluirEl) : null;
    let modalMigracao = modalMigracaoEl ? new bootstrap.Modal(modalMigracaoEl) : null;
    let dataTable = null;
    let matriculaIdParaExcluir = null; // CORREÇÃO: Declarada globalmente

    // Carregar selects
    carregarSelects();

    // Inicializar DataTable
    inicializarDataTable();

    // Eventos - Verificar se os elementos existem
    $('#btnSalvarMatricula').on('click', salvarMatricula);
    $('#btnConfirmarExcluir').on('click', excluirMatricula);
    $('#btnMigrar').on('click', migrarMatriculas);
    
    $('#btnFiltrar').on('click', function() {
        if (dataTable) dataTable.ajax.reload();
    });
    
    $('#filtroBusca').on('keypress', function(e) {
        if (e.which === 13 && dataTable) {
            dataTable.ajax.reload();
        }
    });

    // ==================== FUNÇÕES AUXILIARES ====================
    
    function exibirToast(mensagem, tipo = 'success') {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${tipo} border-0 show`;
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '300px';
        toastEl.style.marginBottom = '10px';
        toastEl.style.borderRadius = '8px';
        
        const icon = tipo === 'success' ? 'check-circle' : 
                     tipo === 'danger' ? 'exclamation-triangle' : 'info-circle';
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icon} me-2"></i>
                    ${mensagem}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toastEl);
        
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 300);
        }, 4000);
    }

    function showLoading() {
        let overlay = document.getElementById('globalLoading');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'globalLoading';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner-custom"></div>';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    function hideLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) overlay.style.display = 'none';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==================== FUNÇÕES PRINCIPAIS ====================
    
    function carregarSelects() {
        showLoading();
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'carregarSelects' },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                hideLoading();
                if (response.sucesso) {
                    const dados = response.dados;
                    
                    // Alunos
                    if ($('#alunoId').length) {
                        $('#alunoId').empty().append('<option value="">Selecione um aluno...</option>');
                        if (dados.alunos && dados.alunos.length) {
                            dados.alunos.forEach(aluno => {
                                $('#alunoId').append(`<option value="${aluno.id}">${escapeHtml(aluno.nome)}</option>`);
                            });
                        }
                    }
                    
                    // Classes
                    if ($('#classeId').length) {
                        $('#classeId').empty().append('<option value="">Selecione uma classe...</option>');
                        if (dados.classes && dados.classes.length) {
                            dados.classes.forEach(classe => {
                                $('#classeId').append(`<option value="${classe.id}">${escapeHtml(classe.nome)}</option>`);
                            });
                        }
                    }
                    
                    // Congregações
                    if ($('#congregacaoId').length) {
                        $('#congregacaoId').empty().append('<option value="">Selecione uma congregação...</option>');
                        if (dados.congregacoes && dados.congregacoes.length) {
                            dados.congregacoes.forEach(cong => {
                                $('#congregacaoId').append(`<option value="${cong.id}">${escapeHtml(cong.nome)}</option>`);
                            });
                            
                            if (typeof USUARIO_PERFIL !== 'undefined' && USUARIO_PERFIL !== 'admin' && typeof USUARIO_CONGR_ID !== 'undefined' && USUARIO_CONGR_ID) {
                                $('#congregacaoId').val(USUARIO_CONGR_ID);
                                $('#congregacaoId').prop('disabled', true);
                            }
                        }
                    }
                    
                    console.log('Selects carregados com sucesso!');
                    
                    // Carregar trimestres após selects
                    gerarTrimestres();
                } else {
                    exibirToast('Erro ao carregar dados: ' + (response.mensagem || 'Erro desconhecido'), 'danger');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Erro carregarSelects:', status, error);
                exibirToast('Erro ao carregar dados dos selects.', 'danger');
            }
        });
    }

    function gerarTrimestres() {
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'getTrimestresSugeridos' },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                if (response.sucesso && response.dados) {
                    const select = $('#trimestre');
                    if (select.length) {
                        select.empty().append('<option value="">Selecione o trimestre...</option>');
                        response.dados.forEach(trim => {
                            select.append(`<option value="${trim.valor}">${trim.label}</option>`);
                        });
                        
                        // Selecionar trimestre atual se as variáveis existirem
                        if (typeof ANO_ATUAL !== 'undefined' && typeof TRIMESTRE_ATUAL !== 'undefined') {
                            const trimAtual = `${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`;
                            select.val(trimAtual);
                        }
                    }
                    
                    const selectFiltro = $('#filtroTrimestre');
                    if (selectFiltro.length) {
                        selectFiltro.empty().append('<option value="">Todos</option>');
                        response.dados.forEach(trim => {
                            selectFiltro.append(`<option value="${trim.valor}">${trim.label}</option>`);
                        });
                    }
                    
                    console.log('Trimestres carregados com sucesso!');
                } else {
                    exibirToast('Erro ao carregar trimestres.', 'warning');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro gerarTrimestres:', status, error);
                exibirToast('Erro ao carregar trimestres.', 'danger');
            }
        });
    }

    function inicializarDataTable() {
        // Verificar se a tabela existe
        if (!$('#tabelaMatriculas').length || !$.fn.DataTable) {
            console.error('Tabela ou DataTable não disponível');
            return;
        }
        
        if ($.fn.DataTable.isDataTable('#tabelaMatriculas')) {
            dataTable = $('#tabelaMatriculas').DataTable();
            dataTable.destroy();
        }
        
        dataTable = $('#tabelaMatriculas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL,
                type: 'POST',
                data: function(d) {
                    d.acao = 'listarMatriculas';
                    if (typeof USUARIO_PERFIL !== 'undefined' && USUARIO_PERFIL !== 'admin' && typeof USUARIO_CONGR_ID !== 'undefined') {
                        d.congregacao = USUARIO_CONGR_ID;
                    } else {
                        d.congregacao = '';
                    }
                    d.status = $('#filtroStatus').val() || '';
                    d.trimestre = $('#filtroTrimestre').val() || '';
                    if ($('#filtroBusca').val()) {
                        d.search = { value: $('#filtroBusca').val() };
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro DataTable AJAX:', status, error);
                    exibirToast('Erro ao carregar matrículas.', 'danger');
                }
            },
            columns: [
                { 
                    data: 'id',
                    render: function(data) {
                        return `<span style="background: #f0f0f0; padding: 0.25rem 0.5rem; border-radius: 8px;">#${data}</span>`;
                    }
                },
                { 
                    data: 'aluno',
                    render: function(data) {
                        return `<i class="fas fa-user-graduate me-2" style="color: #4f46e5;"></i>${escapeHtml(data)}</span>`;
                    }
                },
                { 
                    data: 'classe',
                    render: function(data) {
                        return `<i class="fas fa-chalkboard-user me-2" style="color: #10b981;"></i>${escapeHtml(data)}</span>`;
                    }
                },
                { 
                    data: 'congregacao',
                    render: function(data) {
                        return `<i class="fas fa-church me-2" style="color: #4f46e5;"></i>${escapeHtml(data)}</span>`;
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
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-editar" data-id="${data.id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-excluir" data-id="${data.id}" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
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
            drawCallback: function() {
                // CORREÇÃO: Usar event delegation ou re-attach após cada draw
                $('.btn-editar').off('click').on('click', function() {
                    const id = $(this).data('id');
                    if (id) editarMatricula(id);
                });
                $('.btn-excluir').off('click').on('click', function() {
                    matriculaIdParaExcluir = $(this).data('id');
                    if (modalExcluir) modalExcluir.show();
                });
                atualizarEstatisticas();
            }
        });
    }

    function atualizarEstatisticas() {
        if (!dataTable) return;
        
        const data = dataTable.rows().data();
        let total = data.length;
        let ativas = 0;
        
        data.each(function(row) {
            if (row.status === 'ativo') ativas++;
        });
        
        $('#totalMatriculas').text(total);
        $('#matriculasAtivas').text(ativas);
        $('#matriculasInativas').text(total - ativas);
    }

    function salvarMatricula() {
        const id = $('#matriculaId').val();
        const dados = {
            aluno_id: $('#alunoId').val(),
            classe_id: $('#classeId').val(),
            congregacao_id: $('#congregacaoId').val(),
            professor_id: typeof USUARIO_ID !== 'undefined' ? USUARIO_ID : 0,
            trimestre: $('#trimestre').val(),
            status: $('#status').val(),
            data_matricula: $('#dataMatricula').val() || new Date().toISOString().split('T')[0]
        };
        
        if (!dados.aluno_id || !dados.classe_id || !dados.congregacao_id || !dados.trimestre) {
            exibirToast('Preencha todos os campos obrigatórios.', 'warning');
            return;
        }
        
        const acao = id ? 'atualizarMatricula' : 'criarMatricula';
        if (id) dados.id = id;
        
        showLoading();
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { ...dados, acao: acao },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                hideLoading();
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    if (modalMatricula) modalMatricula.hide();
                    if (dataTable) dataTable.ajax.reload();
                    limparFormulario();
                } else {
                    exibirToast(response.mensagem || 'Erro ao salvar matrícula.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Erro salvarMatricula:', status, error);
                exibirToast('Erro ao salvar matrícula.', 'danger');
            }
        });
    }

    function editarMatricula(id) {
        if (!id) {
            exibirToast('ID da matrícula inválido.', 'warning');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'buscarMatricula', id: id },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                hideLoading();
                if (response.sucesso && response.dados) {
                    const dados = response.dados;
                    $('#matriculaId').val(dados.id);
                    $('#alunoId').val(dados.aluno_id);
                    $('#classeId').val(dados.classe_id);
                    $('#congregacaoId').val(dados.congregacao_id);
                    $('#trimestre').val(dados.trimestre);
                    $('#status').val(dados.status);
                    $('#dataMatricula').val(dados.data_matricula || new Date().toISOString().split('T')[0]);
                    $('#modalTitle').text('Editar Matrícula');
                    if (modalMatricula) modalMatricula.show();
                } else {
                    exibirToast(response.mensagem || 'Matrícula não encontrada.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Erro editarMatricula:', status, error);
                exibirToast('Erro ao buscar matrícula.', 'danger');
            }
        });
    }

    function excluirMatricula() {
        if (!matriculaIdParaExcluir) {
            exibirToast('Nenhuma matrícula selecionada para exclusão.', 'warning');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'excluirMatricula', id: matriculaIdParaExcluir },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                hideLoading();
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    if (dataTable) dataTable.ajax.reload();
                    if (modalExcluir) modalExcluir.hide();
                    matriculaIdParaExcluir = null;
                } else {
                    exibirToast(response.mensagem || 'Erro ao excluir matrícula.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Erro excluirMatricula:', status, error);
                exibirToast('Erro ao excluir matrícula.', 'danger');
            }
        });
    }

    function migrarMatriculas() {
        let trimestreOrigem = $('#trimestreOrigem').val();
        const trimestreDestino = $('#trimestreDestino').val();
        const manterStatus = $('#manterStatus').is(':checked') ? 1 : 0;
        
        if (!trimestreOrigem && typeof ANO_ATUAL !== 'undefined' && typeof TRIMESTRE_ATUAL !== 'undefined') {
            trimestreOrigem = `${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`;
        }
        
        if (!trimestreDestino) {
            exibirToast('Informe o trimestre de destino.', 'warning');
            return;
        }
        
        if (!trimestreDestino.match(/^\d{4}-T[1-4]$/)) {
            exibirToast('Formato de trimestre inválido. Use: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4', 'warning');
            return;
        }
        
        showLoading();
        
        let congregacaoId = '';
        if (typeof USUARIO_PERFIL !== 'undefined' && USUARIO_PERFIL !== 'admin' && typeof USUARIO_CONGR_ID !== 'undefined') {
            congregacaoId = USUARIO_CONGR_ID;
        } else {
            congregacaoId = $('#congregacaoId').val() || '';
        }
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: {
                acao: 'migrarMatriculas',
                trimestre_atual: trimestreOrigem,
                novo_trimestre: trimestreDestino,
                congregacao_id: congregacaoId,
                manter_status: manterStatus
            },
            dataType: 'json',
            timeout: 60000, // Timeout maior para migração
            success: function(response) {
                hideLoading();
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    if (modalMigracao) modalMigracao.hide();
                    if (dataTable) dataTable.ajax.reload();
                    gerarTrimestres();
                    $('#trimestreDestino').val('');
                } else {
                    exibirToast(response.mensagem || 'Erro ao migrar matrículas.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('Erro migrarMatriculas:', status, error);
                exibirToast('Erro ao migrar matrículas.', 'danger');
            }
        });
    }

    function limparFormulario() {
        $('#matriculaId').val('');
        $('#alunoId').val('');
        $('#classeId').val('');
        
        if (typeof USUARIO_PERFIL !== 'undefined' && USUARIO_PERFIL === 'admin') {
            if ($('#congregacaoId').length) {
                $('#congregacaoId').prop('disabled', false).val('');
            }
        } else if (typeof USUARIO_CONGR_ID !== 'undefined' && USUARIO_CONGR_ID) {
            if ($('#congregacaoId').length) {
                $('#congregacaoId').prop('disabled', true).val(USUARIO_CONGR_ID);
            }
        }
        
        if (typeof ANO_ATUAL !== 'undefined' && typeof TRIMESTRE_ATUAL !== 'undefined') {
            $('#trimestre').val(`${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`);
        }
        $('#status').val('ativo');
        $('#dataMatricula').val(new Date().toISOString().split('T')[0]);
        $('#modalTitle').text('Nova Matrícula');
    }

    // Eventos do modal
    if (modalMatricula) {
        $('#modalMatricula').on('show.bs.modal', function() {
            limparFormulario();
        }).on('hidden.bs.modal', function() {
            limparFormulario();
        });
    }
    
    console.log('Script matricula.js inicializado com sucesso!');
});