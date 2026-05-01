$(document).ready(function () {
  // Inicializa a tabela
  var tabela = $('#tabelaPresencas').DataTable({
      ajax: {
          url: 'presencas_helper.php',
          method: 'POST',
          data: { acao: 'listar' },
          dataSrc: 'data'
      },
      columns: [
          { data: 'aluno_nome', title: 'Aluno' },
          { data: 'classe_nome', title: 'Classe' },
          { data: 'data_chamada', title: 'Data da Chamada' },
          {
              data: 'presente',
              title: 'Status',
              render: function (data) {
                  return data === 'presente' ? 'Presente' : (data === 'ausente' ? 'Ausente' : 'Justificado');
              }
          },
          {
              data: 'id',
              title: 'Ações',
              render: function (id) {
                  return `
                      <button class="btn btn-sm btn-warning editar" data-id="${id}">
                          <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-danger excluir" data-id="${id}">
                          <i class="fas fa-trash-alt"></i>
                      </button>
                  `;
              }
          }
      ]
  });

  // Função para carregar os selects
  function carregarSelects() {
      $.ajax({
          url: 'presencas_action.php',
          method: 'POST',
          data: { acao: 'carregar_selects' },
          success: function (response) {
              const data = JSON.parse(response);
              if (data.sucesso) {
                  // Preencher selects de chamada e aluno
                  const chamadas = data.chamadas;
                  const alunos = data.alunos;
                  
                  $('#chamada_id').html(chamadas.map(c => `<option value="${c.id}">${c.nome}</option>`).join(''));
                  $('#aluno_id').html(alunos.map(a => `<option value="${a.id}">${a.nome}</option>`).join(''));
              }
          }
      });
  }

  // Ação para abrir o modal de edição/criação
  $(document).on('click', '.editar', function () {
      const id = $(this).data('id');
      $.ajax({
          url: 'presencas_action.php',
          method: 'POST',
          data: { acao: 'buscar', id: id },
          success: function (response) {
              const data = JSON.parse(response);
              if (data.sucesso) {
                  const presenca = data.dados;
                  $('#id').val(presenca.id);
                  $('#chamada_id').val(presenca.chamada_id);
                  $('#aluno_id').val(presenca.aluno_id);
                  $('#presente').val(presenca.presente);
                  $('#modalPresenca').modal('show');
              } else {
                  alert(data.mensagem);
              }
          }
      });
  });

  // Ação para excluir presença
  $(document).on('click', '.excluir', function () {
      const id = $(this).data('id');
      if (confirm('Tem certeza que deseja excluir esta presença?')) {
          $.ajax({
              url: 'presencas_action.php',
              method: 'POST',
              data: { acao: 'excluir', id: id },
              success: function (response) {
                  const data = JSON.parse(response);
                  alert(data.mensagem);
                  tabela.ajax.reload();
              }
          });
      }
  });

  // Salvar ou atualizar presença
  $('#formPresenca').on('submit', function (e) {
      e.preventDefault();
      const formData = $(this).serialize();
      $.ajax({
          url: 'presencas_action.php',
          method: 'POST',
          data: formData,
          success: function (response) {
              const data = JSON.parse(response);
              alert(data.mensagem);
              if (data.sucesso) {
                  $('#modalPresenca').modal('hide');
                  tabela.ajax.reload();
              }
          }
      });
  });

  // Carregar os selects ao iniciar
  carregarSelects();
});

  