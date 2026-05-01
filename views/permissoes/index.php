<?php require_once '../includes/header.php'; ?>

    <div class="container mt-5">
        <h2>Gerenciamento de Permissões</h2>
        <button class="btn btn-success mt-4" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
            <i class="fas fa-plus"></i> Nova Permissão
        </button><br><br>

        <table class="table table-striped" id="tabelaPermissoes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="listaPermissoes">
            </tbody>
        </table>
    </div>

    <!-- Modal Cadastrar -->
    <div class="modal" id="modalCadastrar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Permissão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCadastrarPermissao">
                        <div class="mb-3">
                            <label class="form-label">Nome da Permissão</label>
                            <input type="text" class="form-control" id="nome" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
    <script>
    $(document).ready(function() {
        carregarPermissoes();
        
        $("#formCadastrarPermissao").submit(function(e) {
            e.preventDefault();
            $.post('../../controllers/permissoes.php', { acao: 'cadastrar', nome: $("#nome").val() }, function(response) {
                if (response.sucesso) {
                    alert("Permissão cadastrada com sucesso!");
                    $("#modalCadastrar").modal("hide");
                    carregarPermissoes();
                } else {
                    alert("Erro ao cadastrar permissão.");
                }
            }, 'json');
        });
    });

    function carregarPermissoes() {
        $.post('../../controllers/permissoes.php', { acao: 'listar' }, function(response) {
            let table = $('#tabelaPermissoes').DataTable();
            table.clear();
            response.permissoes.forEach(permissao => {
                table.row.add([
                    permissao.id,
                    permissao.nome,
                    `<button class='btn btn-danger btn-sm' onclick='excluirPermissao(${permissao.id})'>
                        <i class='fas fa-trash'></i>
                    </button>`
                ]).draw();
            });
        }, 'json');
    }

    function excluirPermissao(id) {
        if (confirm("Tem certeza que deseja excluir esta permissão?")) {
            $.post('../../controllers/permissoes.php', { acao: 'excluir', id: id }, function(response) {
                if (response.sucesso) {
                    alert("Permissão excluída com sucesso!");
                    carregarPermissoes();
                } else {
                    alert("Erro ao excluir permissão.");
                }
            }, 'json');
        }
    }
    </script>
</body>
</html>
