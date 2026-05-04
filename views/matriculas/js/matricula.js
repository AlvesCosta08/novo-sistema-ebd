// matricula.js - Versão corrigida com estatísticas corretas

$(document).ready(function() {
    console.log('matricula.js carregado - jQuery versão:', $.fn.jquery);
    
    // Verificar se os elementos do modal existem
    let modalMatricula = null;
    let modalExcluir = null;
    let modalMigracao = null;
    
    if (document.getElementById('modalMatricula')) {
        modalMatricula = new bootstrap.Modal(document.getElementById('modalMatricula'));
    } else {
        console.error('Modal modalMatricula não encontrado');
    }
    
    if (document.getElementById('modalExcluir')) {
        modalExcluir = new bootstrap.Modal(document.getElementById('modalExcluir'));
    } else {
        console.error('Modal modalExcluir não encontrado');
    }
    
    if (document.getElementById('modalMigracao')) {
        modalMigracao = new bootstrap.Modal(document.getElementById('modalMigracao'));
    } else {
        console.error('Modal modalMigracao não encontrado');
    }
    
    let matriculaIdParaExcluir = null;
    let dataTable = null;
    
    // Função de toast
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
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
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
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ==================== FUNÇÃO PARA BUSCAR ESTATÍSTICAS REAIS ====================
    function buscarEstatisticas() {
        console.log('Buscando estatísticas do servidor...');
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { 
                acao: 'getEstatisticas'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Estatísticas recebidas:', response);
                if (response.sucesso && response.dados) {
                    $('#totalMatriculas').text(response.dados.total_matriculas || 0);
                    $('#matriculasAtivas').text(response.dados.ativos || 0);
                    $('#matriculasInativas').text((response.dados.total_matriculas || 0) - (response.dados.ativos || 0));
                } else {
                    console.error('Erro ao buscar estatísticas:', response.mensagem);
                }
            },
            error: function(xhr) {
                console.error('Erro na requisição de estatísticas:', xhr);
            }
        });
    }
    
    // Carregar selects
    function carregarSelects() {
        console.log('Carregando selects...');
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'carregarSelects' },
            dataType: 'json',
            success: function(response) {
                console.log('Selects carregados:', response);
                if (response.sucesso) {
                    const dados = response.dados;
                    
                    $('#alunoId').empty().append('<option value="">Selecione um aluno...</option>');
                    if (dados.alunos && dados.alunos.length) {
                        dados.alunos.forEach(aluno => {
                            $('#alunoId').append(`<option value="${aluno.id}">${escapeHtml(aluno.nome)}</option>`);
                        });
                    }
                    
                    $('#classeId').empty().append('<option value="">Selecione uma classe...</option>');
                    if (dados.classes && dados.classes.length) {
                        dados.classes.forEach(classe => {
                            $('#classeId').append(`<option value="${classe.id}">${escapeHtml(classe.nome)}</option>`);
                        });
                    }
                    
                    $('#congregacaoId').empty().append('<option value="">Selecione uma congregação...</option>');
                    if (dados.congregacoes && dados.congregacoes.length) {
                        dados.congregacoes.forEach(cong => {
                            $('#congregacaoId').append(`<option value="${cong.id}">${escapeHtml(cong.nome)}</option>`);
                        });
                    }
                    
                    if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) {
                        $('#congregacaoId').val(USUARIO_CONGR_ID).prop('disabled', true);
                    }
                }
            },
            error: function(xhr) {
                console.error('Erro carregarSelects:', xhr);
            }
        });
    }
    
    // Carregar trimestres
    function carregarTrimestres() {
        console.log('Carregando trimestres...');
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'getTrimestresSugeridos' },
            dataType: 'json',
            success: function(response) {
                console.log('Trimestres carregados:', response);
                if (response.sucesso && response.dados) {
                    const select = $('#trimestre');
                    select.empty().append('<option value="">Selecione o trimestre...</option>');
                    response.dados.forEach(trim => {
                        select.append(`<option value="${trim.valor}">${trim.label}</option>`);
                    });
                    
                    const selectFiltro = $('#filtroTrimestre');
                    selectFiltro.empty().append('<option value="">Todos</option>');
                    response.dados.forEach(trim => {
                        selectFiltro.append(`<option value="${trim.valor}">${trim.label}</option>`);
                    });
                    
                    const trimAtual = `${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`;
                    select.val(trimAtual);
                }
            },
            error: function(xhr) {
                console.error('Erro carregarTrimestres:', xhr);
            }
        });
    }
    
    // Inicializar DataTable
    function inicializarDataTable() {
        console.log('Inicializando DataTable...');
        
        // Verificar se a tabela existe
        if ($('#tabelaMatriculas').length === 0) {
            console.error('Tabela #tabelaMatriculas não encontrada');
            return;
        }
        
        // Destruir DataTable existente se houver
        if ($.fn.DataTable.isDataTable('#tabelaMatriculas')) {
            $('#tabelaMatriculas').DataTable().destroy();
        }
        
        dataTable = $('#tabelaMatriculas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL,
                type: 'POST',
                data: function(d) {
                    d.acao = 'listarMatriculas';
                    d.congregacao = USUARIO_PERFIL !== 'admin' ? USUARIO_CONGR_ID : '';
                    d.status = $('#filtroStatus').val();
                    d.trimestre = $('#filtroTrimestre').val();
                    if ($('#filtroBusca').val()) {
                        d.search = { value: $('#filtroBusca').val() };
                    }
                },
                dataSrc: function(json) {
                    console.log('DataTable response:', json);
                    if (json.sucesso && json.dados) {
                        return json.dados;
                    }
                    return [];
                },
                error: function(xhr) {
                    console.error('Erro DataTable AJAX:', xhr);
                    exibirToast('Erro ao carregar matrículas', 'danger');
                }
            },
            columns: [
                { data: 'id' },
                { data: 'aluno' },
                { data: 'classe' },
                { data: 'congregacao' },
                { data: 'trimestre' },
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
                        return data === 'ativo' 
                            ? '<span class="badge-status-ativo"><i class="fas fa-check-circle me-1"></i> Ativo</span>'
                            : '<span class="badge-status-inativo"><i class="fas fa-times-circle me-1"></i> Inativo</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-editar me-1" data-id="${data.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir" data-id="${data.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/2.2.2/i18n/pt-BR.json'
            },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            drawCallback: function() {
                // Não atualizar estatísticas aqui - usar dados do servidor
                console.log('DrawCallback executado');
            }
        });
        
        // Event delegation para os botões (funciona mesmo após redraw)
        $('#tabelaMatriculas').off('click', '.btn-editar').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            editarMatricula(id);
        });
        
        $('#tabelaMatriculas').off('click', '.btn-excluir').on('click', '.btn-excluir', function() {
            matriculaIdParaExcluir = $(this).data('id');
            if (modalExcluir) modalExcluir.show();
        });
        
        // Buscar estatísticas após carregar os dados
        dataTable.on('xhr.dt', function(e, settings, json, xhr) {
            console.log('DataTable XHR completo');
            buscarEstatisticas();
        });
    }
    
    // Funções CRUD
    function salvarMatricula() {
        const id = $('#matriculaId').val();
        const dados = {
            aluno_id: $('#alunoId').val(),
            classe_id: $('#classeId').val(),
            congregacao_id: $('#congregacaoId').val(),
            professor_id: USUARIO_ID,
            trimestre: $('#trimestre').val(),
            status: $('#status').val(),
            data_matricula: $('#dataMatricula').val()
        };
        
        if (!dados.aluno_id || !dados.classe_id || !dados.congregacao_id || !dados.trimestre) {
            exibirToast('Preencha todos os campos obrigatórios', 'warning');
            return;
        }
        
        const acao = id ? 'atualizarMatricula' : 'criarMatricula';
        if (id) dados.id = id;
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { ...dados, acao: acao },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    if (modalMatricula) modalMatricula.hide();
                    if (dataTable) dataTable.ajax.reload();
                    buscarEstatisticas(); // Atualizar estatísticas
                    limparFormulario();
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function(xhr) {
                console.error('Erro salvarMatricula:', xhr);
                exibirToast('Erro ao salvar matrícula', 'danger');
            }
        });
    }
    
    function editarMatricula(id) {
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'buscarMatricula', id: id },
            dataType: 'json',
            success: function(response) {
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
                    exibirToast(response.mensagem || 'Matrícula não encontrada', 'danger');
                }
            },
            error: function(xhr) {
                console.error('Erro editarMatricula:', xhr);
                exibirToast('Erro ao buscar matrícula', 'danger');
            }
        });
    }
    
    function excluirMatricula() {
        if (!matriculaIdParaExcluir) return;
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'excluirMatricula', id: matriculaIdParaExcluir },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    if (dataTable) dataTable.ajax.reload();
                    buscarEstatisticas(); // Atualizar estatísticas
                    if (modalExcluir) modalExcluir.hide();
                    matriculaIdParaExcluir = null;
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function(xhr) {
                console.error('Erro excluirMatricula:', xhr);
                exibirToast('Erro ao excluir matrícula', 'danger');
            }
        });
    }
    
    function migrarMatriculas() {
        const trimestreOrigem = $('#trimestreOrigem').val();
        const trimestreDestino = $('#trimestreDestino').val();
        const manterStatus = $('#manterStatus').is(':checked');
        
        if (!trimestreDestino) {
            exibirToast('Informe o trimestre de destino', 'warning');
            return;
        }
        
        if (!trimestreDestino.match(/^\d{4}-T[1-4]$/)) {
            exibirToast('Formato inválido. Use: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4', 'warning');
            return;
        }
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: {
                acao: 'migrarMatriculas',
                trimestre_atual: trimestreOrigem,
                novo_trimestre: trimestreDestino,
                congregacao_id: USUARIO_PERFIL !== 'admin' ? USUARIO_CONGR_ID : $('#congregacaoId').val(),
                manter_status: manterStatus ? 1 : 0
            },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    if (modalMigracao) modalMigracao.hide();
                    if (dataTable) dataTable.ajax.reload();
                    buscarEstatisticas(); // Atualizar estatísticas
                    $('#trimestreDestino').val('');
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function(xhr) {
                console.error('Erro migrarMatriculas:', xhr);
                exibirToast('Erro ao migrar matrículas', 'danger');
            }
        });
    }
    
    function limparFormulario() {
        $('#matriculaId').val('');
        $('#alunoId').val('');
        $('#classeId').val('');
        if (USUARIO_PERFIL === 'admin') {
            $('#congregacaoId').prop('disabled', false).val('');
        } else {
            $('#congregacaoId').prop('disabled', true).val(USUARIO_CONGR_ID);
        }
        $('#trimestre').val(`${ANO_ATUAL}-T${TRIMESTRE_ATUAL}`);
        $('#status').val('ativo');
        $('#dataMatricula').val(new Date().toISOString().split('T')[0]);
        $('#modalTitle').text('Nova Matrícula');
    }
    
    // Inicialização
    carregarSelects();
    carregarTrimestres();
    inicializarDataTable();
    buscarEstatisticas(); // Buscar estatísticas iniciais
    
    // Eventos
    $('#btnSalvarMatricula').on('click', salvarMatricula);
    $('#btnConfirmarExcluir').on('click', excluirMatricula);
    $('#btnMigrar').on('click', migrarMatriculas);
    $('#btnFiltrar').on('click', function() {
        if (dataTable) dataTable.ajax.reload();
        buscarEstatisticas(); // Atualizar estatísticas ao filtrar
    });
    $('#filtroBusca').on('keypress', function(e) {
        if (e.which === 13 && dataTable) {
            dataTable.ajax.reload();
            buscarEstatisticas(); // Atualizar estatísticas ao buscar
        }
    });
    
    if (modalMatricula) {
        $('#modalMatricula').on('hidden.bs.modal', limparFormulario);
    }
    
    console.log('Inicialização completa!');
});