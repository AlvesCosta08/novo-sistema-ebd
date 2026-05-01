// matricula.js - Gerenciamento de Matrículas

$(document).ready(function() {
    let dataTable = null;
    let modalMatricula = new bootstrap.Modal(document.getElementById('modalMatricula'));
    let modalExcluir = new bootstrap.Modal(document.getElementById('modalExcluir'));
    let modalMigracao = new bootstrap.Modal(document.getElementById('modalMigracao'));
    let isEditing = false;

    // Carregar selects
    carregarSelects();

    // Inicializar DataTable
    inicializarDataTable();

    // Eventos
    $('#btnSalvarMatricula').on('click', salvarMatricula);
    $('#btnConfirmarExcluir').on('click', excluirMatricula);
    $('#btnMigrar').on('click', migrarMatriculas);

    // Gerar trimestres sugeridos
    gerarTrimestres();

    function exibirToast(mensagem, tipo = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: tipo === 'success' ? 'success' : tipo === 'danger' ? 'error' : 'info',
                title: tipo === 'success' ? 'Sucesso' : tipo === 'danger' ? 'Erro' : 'Atenção',
                text: mensagem,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000
            });
        } else {
            alert(mensagem);
        }
    }

    function showLoading() {
        let overlay = document.getElementById('globalLoading');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'globalLoading';
            overlay.className = 'position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center';
            overlay.style.zIndex = '9999';
            overlay.innerHTML = '<div class="spinner-border text-light" style="width: 3rem; height: 3rem;"></div>';
            document.body.appendChild(overlay);
        }
        overlay.classList.remove('d-none');
    }

    function hideLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) overlay.classList.add('d-none');
    }

    function carregarSelects() {
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'carregarSelects' },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    const dados = response.dados;
                    
                    $('#alunoId').empty().append('<option value="">Selecione um aluno...</option>');
                    dados.alunos.forEach(aluno => {
                        $('#alunoId').append(`<option value="${aluno.id}">${aluno.nome}</option>`);
                    });
                    
                    $('#classeId').empty().append('<option value="">Selecione uma classe...</option>');
                    dados.classes.forEach(classe => {
                        $('#classeId').append(`<option value="${classe.id}">${classe.nome}</option>`);
                    });
                    
                    $('#congregacaoId').empty().append('<option value="">Selecione uma congregação...</option>');
                    dados.congregacoes.forEach(cong => {
                        $('#congregacaoId').append(`<option value="${cong.id}">${cong.nome}</option>`);
                        if (USUARIO_PERFIL !== 'admin' && cong.id == USUARIO_CONGR_ID) {
                            $('#congregacaoId').val(cong.id).prop('disabled', true);
                        }
                    });
                }
            },
            error: function() {
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
            success: function(response) {
                if (response.sucesso && response.dados) {
                    const select = $('#trimestre');
                    select.empty().append('<option value="">Selecione o trimestre...</option>');
                    response.dados.forEach(trim => {
                        select.append(`<option value="${trim}">${trim}</option>`);
                    });
                }
            }
        });
    }

    function inicializarDataTable() {
        dataTable = $('#tabelaMatriculas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL,
                type: 'POST',
                data: function(d) {
                    d.acao = 'listarMatriculas';
                    d.congregacao = USUARIO_PERFIL !== 'admin' ? USUARIO_CONGR_ID : '';
                }
            },
            columns: [
                { data: 'id' },
                { data: 'aluno' },
                { data: 'classe' },
                { data: 'congregacao' },
                { data: 'trimestre' },
                { data: 'data_matricula' },
                { 
                    data: 'status',
                    render: function(data) {
                        return data === 'ativo' ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>';
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-editar" data-id="${data.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-excluir" data-id="${data.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            order: [[0, 'desc']],
            drawCallback: function() {
                $('.btn-editar').off('click').on('click', function() {
                    const id = $(this).data('id');
                    editarMatricula(id);
                });
                $('.btn-excluir').off('click').on('click', function() {
                    matriculaIdParaExcluir = $(this).data('id');
                    modalExcluir.show();
                });
                atualizarEstatisticas();
            }
        });
    }

    function atualizarEstatisticas() {
        const data = dataTable.rows().data();
        let total = data.length;
        let ativas = 0;
        let inativas = 0;
        
        data.each(function(row) {
            if (row.status === 'ativo') ativas++;
            else inativas++;
        });
        
        $('#totalMatriculas').text(total);
        $('#matriculasAtivas').text(ativas);
        $('#matriculasInativas').text(inativas);
    }

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
            success: function(response) {
                hideLoading();
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    modalMatricula.hide();
                    dataTable.ajax.reload();
                    limparFormulario();
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function() {
                hideLoading();
                exibirToast('Erro ao salvar matrícula.', 'danger');
            }
        });
    }

    function editarMatricula(id) {
        showLoading();
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'buscarMatricula', id: id },
            dataType: 'json',
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
                    $('#dataMatricula').val(dados.data_matricula);
                    $('#modalTitle').text('Editar Matrícula');
                    modalMatricula.show();
                    isEditing = true;
                } else {
                    exibirToast('Matrícula não encontrada.', 'danger');
                }
            },
            error: function() {
                hideLoading();
                exibirToast('Erro ao buscar matrícula.', 'danger');
            }
        });
    }

    function excluirMatricula() {
        if (!matriculaIdParaExcluir) return;
        
        showLoading();
        
        $.ajax({
            url: BASE_URL,
            method: 'POST',
            data: { acao: 'excluirMatricula', id: matriculaIdParaExcluir },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    dataTable.ajax.reload();
                    modalExcluir.hide();
                    matriculaIdParaExcluir = null;
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function() {
                hideLoading();
                exibirToast('Erro ao excluir matrícula.', 'danger');
            }
        });
    }

    function migrarMatriculas() {
        const trimestreOrigem = $('#trimestreOrigem').val();
        const trimestreDestino = $('#trimestreDestino').val();
        const manterStatus = $('#manterStatus').is(':checked');
        
        if (!trimestreDestino) {
            exibirToast('Informe o trimestre de destino.', 'warning');
            return;
        }
        
        if (!trimestreDestino.match(/^\d{4}-T[1-4]$/)) {
            exibirToast('Formato de trimestre inválido. Use: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4', 'warning');
            return;
        }
        
        showLoading();
        
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
                hideLoading();
                if (response.sucesso) {
                    exibirToast(response.mensagem, 'success');
                    modalMigracao.hide();
                    dataTable.ajax.reload();
                    gerarTrimestres();
                } else {
                    exibirToast(response.mensagem, 'danger');
                }
            },
            error: function() {
                hideLoading();
                exibirToast('Erro ao migrar matrículas.', 'danger');
            }
        });
    }

    function limparFormulario() {
        $('#matriculaId').val('');
        $('#alunoId').val('');
        $('#classeId').val('');
        $('#congregacaoId').prop('disabled', false).val('');
        $('#trimestre').val('');
        $('#status').val('ativo');
        $('#dataMatricula').val(new Date().toISOString().split('T')[0]);
        $('#modalTitle').text('Nova Matrícula');
        isEditing = false;
    }

    $('#modalMatricula').on('hidden.bs.modal', function() {
        limparFormulario();
    });
});