<?php
require_once '../includes/header.php'; // inclui Bootstrap, Font Awesome e JS base
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-user-check"></i> Controle de Presenças</h4>
    </div>

    <table id="tabelaPresencas" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Aluno</th>
                <th>Classe</th>
                <th>Data da Chamada</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal para criar/editar -->
<div class="modal fade" id="modalPresenca" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formPresenca" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastro de Presença</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="id">
        <div class="mb-3">
          <label for="chamada_id" class="form-label">Chamada</label>
          <select name="chamada_id" id="chamada_id" class="form-select" required></select>
        </div>
        <div class="mb-3">
          <label for="aluno_id" class="form-label">Aluno</label>
          <select name="aluno_id" id="aluno_id" class="form-select" required></select>
        </div>
        <div class="mb-3">
          <label for="presente" class="form-label">Status</label>
          <select name="presente" id="presente" class="form-select" required>
            <option value="presente">Presente</option>
            <option value="ausente">Ausente</option>
            <option value="justificado">Justificado</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Salvar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
<script>
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
          url: 'presencas_helper.php',
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
          url: 'presencas_helper.php',
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
              url: 'presencas_helper.php',
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
          url: 'presencas_helper.php',
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
</script>
