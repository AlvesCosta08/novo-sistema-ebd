<?php
require_once '../includes/header.php';
?>

<div class="container mt-4">
  <h4><i class="fas fa-user-check me-2"></i>Editar Presença</h4>
  <form id="formPresenca">
    <input type="hidden" name="id" id="id" value="<?= $_GET['id'] ?? '' ?>">

    <div class="row mb-3">
      <div class="col-md-6">
        <label for="chamada_id" class="form-label">Data da Chamada</label>
        <select name="chamada_id" id="chamada_id" class="form-select" required></select>
      </div>

      <div class="col-md-6">
        <label for="aluno_id" class="form-label">Aluno</label>
        <select name="aluno_id" id="aluno_id" class="form-select" required></select>
      </div>
    </div>

    <div class="mb-3">
      <label for="presente" class="form-label">Status</label>
      <select name="presente" id="presente" class="form-select" required>
        <option value="">Selecione</option>
        <option value="presente">Presente</option>
        <option value="ausente">Ausente</option>
        <option value="justificado">Justificado</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save me-1"></i>Salvar Alterações
    </button>
    <a href="index.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-1"></i>Voltar
    </a>
  </form>
</div>

<script src="presencas.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const id = document.getElementById('id').value;

  carregarSelects();

  if (id) {
    fetchPresenca(id);
  }

  document.getElementById("formPresenca").addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append("acao", "salvar");

    fetch("presencas_helper.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(res => {
      if (res.sucesso) {
        Swal.fire("Sucesso!", res.mensagem, "success").then(() => {
          window.location.href = "index.php";
        });
      } else {
        Swal.fire("Erro!", res.mensagem, "error");
      }
    });
  });
});

function fetchPresenca(id) {
  const data = new FormData();
  data.append("acao", "buscar");
  data.append("id", id);

  fetch("presencas_helper.php", {
    method: "POST",
    body: data
  })
  .then(res => res.json())
  .then(res => {
    if (res.sucesso) {
      const presenca = res.dados;
      document.getElementById("chamada_id").value = presenca.chamada_id;
      document.getElementById("aluno_id").value = presenca.aluno_id;
      document.getElementById("presente").value = presenca.presente;
    } else {
      Swal.fire("Erro!", res.mensagem, "error");
    }
  });
}

function carregarSelects() {
  const data = new FormData();
  data.append("acao", "carregar_selects");

  fetch("presencas_helper.php", {
    method: "POST",
    body: data
  })
  .then(res => res.json())
  .then(res => {
    if (res.sucesso) {
      const chamadas = res.chamadas;
      const alunos = res.alunos;

      const chamadaSelect = document.getElementById("chamada_id");
      chamadas.forEach(c => {
        chamadaSelect.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
      });

      const alunoSelect = document.getElementById("aluno_id");
      alunos.forEach(a => {
        alunoSelect.innerHTML += `<option value="${a.id}">${a.nome}</option>`;
      });
    }
  });
}
</script>

<?php require_once '../includes/footer.php'; ?>

