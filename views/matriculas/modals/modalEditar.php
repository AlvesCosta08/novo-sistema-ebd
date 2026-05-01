<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <form id="formEditarMatricula" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Matrícula</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="id_edit">
        <?php include 'formularioBase.php'; ?>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
      </div>
    </form>
  </div>
</div>
