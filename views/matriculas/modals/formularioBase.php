<?php require_once '../../views/includes/header.php'; ?>
<div class="mb-3">
  <label for="aluno" class="form-label">Aluno</label>
  <select id="aluno" class="form-select" required></select>
</div>
<div class="mb-3">
  <label for="classe" class="form-label">Classe</label>
  <select id="classe" class="form-select" required></select>
</div>
<div class="mb-3">
  <label for="congregacao" class="form-label">Congregação</label>
  <select id="congregacao" class="form-select" required></select>
</div>
<div class="mb-3">
  <label for="professor" class="form-label">Professor</label>
  <select id="professor" class="form-select" required></select>
</div>
<div class="mb-3">
  <label for="trimestre" class="form-label">Trimestre</label>
  <input type="text" id="trimestre" class="form-control" required>
</div>
<div class="mb-3">
  <label for="status" class="form-label">Status</label>
  <select id="status" class="form-select" required>
    <option value="Ativo">Ativo</option>
    <option value="Inativo">Inativo</option>
  </select>
</div>
