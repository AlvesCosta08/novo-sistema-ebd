<?php
require_once __DIR__ . '/../../auth/valida_sessao.php';

$usuario_id       = $_SESSION['usuario_id'] ?? null;
$nome_usuario     = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil           = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id   = $_SESSION['congregacao_id'] ?? null;

if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Listar Matrículas - Escola Bíblica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .modern-card { background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header-modern { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; padding: 1rem 1.5rem; }
        .btn-modern { border-radius: 50px; padding: 10px 24px; font-weight: 500; border: none; }
        .btn-modern-primary { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white"><i class="fas fa-users me-2"></i>Listar Matrículas</h1>
        <a href="index.php" class="btn btn-light btn-modern"><i class="fas fa-plus me-2"></i> Nova Matrícula</a>
    </div>
    
    <div class="modern-card">
        <div class="card-header-modern">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i> Matrículas Registradas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaMatriculas">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Aluno</th><th>Classe</th><th>Congregação</th><th>Trimestre</th><th>Data</th><th>Status</th><th>Ações</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    const USUARIO_PERFIL = '<?= $perfil ?>';
    const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    const BASE_URL = '/escola/controllers/matricula.php';
    
    $('#tabelaMatriculas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: BASE_URL,
            type: 'POST',
            data: { acao: 'listarMatriculas' }
        },
        columns: [
            { data: 'id' },
            { data: 'aluno' },
            { data: 'classe' },
            { data: 'congregacao' },
            { data: 'trimestre' },
            { data: 'data_matricula' },
            { data: 'status', render: (data) => `<span class="badge ${data === 'ativo' ? 'bg-success' : 'bg-secondary'}">${data}</span>` },
            { data: null, render: (data) => `<div class="btn-group btn-group-sm"><a href="editar.php?id=${data.id}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a></div>` }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json' },
        order: [[0, 'desc']]
    });
</script>
</body>
</html>