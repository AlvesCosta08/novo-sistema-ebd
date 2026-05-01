$(document).ready(function () {
    carregarSelects();
    listarMatriculas();

    $('#formCadastrarMatricula').submit(e => {
        e.preventDefault();
        enviarFormulario('criarMatricula', '#formCadastrarMatricula', '#modalCadastrar');
    });

    $('#formEditarMatricula').submit(e => {
        e.preventDefault();
        enviarFormulario('editarMatricula', '#formEditarMatricula', '#modalEditar', $('#id_edit').val());
    });

    $(document).on('click', '.excluir', function () {
        $('#confirmarExcluir').data('id', $(this).data('id'));
        $('#modalExcluir').modal('show');
    });

    $('#confirmarExcluir').click(() => {
        let id = $('#confirmarExcluir').data('id');
        $.get(`../../controllers/matriculas.php?acao=excluirMatricula&id=${id}`, response => {
            alert(response.mensagem);
            $('#modalExcluir').modal('hide');
            listarMatriculas();
        }, 'json');
    });

    $(document).on('click', '.editar', function () {
        let id = $(this).data('id');
        $.getJSON(`../../controllers/matriculas.php?acao=buscarMatricula&id=${id}`, response => {
            if (response.sucesso) {
                const m = response.dados;
                $('#id_edit').val(m.id);
                $('#aluno').val(m.aluno_id);
                $('#classe').val(m.classe_id);
                $('#congregacao').val(m.congregacao_id);
                $('#professor').val(m.usuario_id);
                $('#trimestre').val(m.trimestre);
                $('#status').val(m.status);
                $('#modalEditar').modal('show');
            }
        });
    });
});

function carregarSelects() {
    $.getJSON('../../controllers/matriculas.php?acao=carregarSelects', response => {
        if (response.sucesso) {
            preencherSelect('#aluno', response.dados.alunos);
            preencherSelect('#classe', response.dados.classes);
            preencherSelect('#congregacao', response.dados.congregacoes);
            preencherSelect('#professor', response.dados.usuarios);
        }
    });
}

function preencherSelect(selector, items) {
    let options = '<option value="">Selecione</option>';
    items.forEach(i => options += `<option value="${i.id}">${i.nome}</option>`);
    $(selector).html(options);
}

function listarMatriculas() {
    $.getJSON('../../controllers/matriculas.php?acao=listarMatriculas', response => {
        if (response.sucesso) {
            const dados = response.dados.map(m => [
                m.id, m.aluno, m.classe, m.congregacao, m.usuario,
                m.trimestre, m.status,
                `<button class="btn btn-warning editar" data-id="${m.id}"><i class="fas fa-edit"></i></button>
                 <button class="btn btn-danger excluir" data-id="${m.id}"><i class="fas fa-trash"></i></button>`
            ]);
            $('#tabelaMatriculas').DataTable({
                data: dados,
                destroy: true,
                columns: [...Array(7)].map(() => ({ title: "" })).concat([{ title: "Ações" }]),
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' }
            });
        }
    });
}

function enviarFormulario(acao, formId, modalId, id = null) {
    const dados = {
        aluno_id: $(`${formId} #aluno`).val(),
        classe_id: $(`${formId} #classe`).val(),
        congregacao_id: $(`${formId} #congregacao`).val(),
        professor_id: $(`${formId} #professor`).val(),
        trimestre: $(`${formId} #trimestre`).val(),
        status: $(`${formId} #status`).val(),
        id: id || null,
        data_matricula: new Date().toISOString().split('T')[0]
    };

    $.ajax({
        url: `../../controllers/matriculas.php?acao=${acao}`,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(dados),
        dataType: 'json',
        success: function (response) {
            alert(response.mensagem);
            $(formId)[0].reset();
            bootstrap.Modal.getInstance(document.querySelector(modalId)).hide();
            listarMatriculas();
        }
    });
}
