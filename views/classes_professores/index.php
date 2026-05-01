<?php
// ✅ CORREÇÃO: Caminhos ajustados para a estrutura correta
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/professorclasse.php';

// Buscar dados do banco
$professores = $pdo->query("SELECT * FROM professores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT * FROM classes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Buscar associações existentes (se a variável não estiver definida no model)
if (!isset($professor_classes)) {
    $stmt = $pdo->query("
        SELECT pc.id, p.nome as professor, c.nome as classe 
        FROM professores_classes pc
        JOIN professores p ON p.id = pc.professor_id
        JOIN classes c ON c.id = pc.classe_id
        ORDER BY p.nome, c.nome
    ");
    $professor_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D - Associação Professor-Classe</title>
    <link rel="icon" href="../../assets/images/biblia.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #3b82f6;
            --color-primary-dark: #2563eb;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-gray-50: #f8fafc;
            --color-gray-100: #f1f5f9;
            --color-gray-200: #e2e8f0;
            --color-gray-300: #cbd5e1;
            --color-gray-600: #475569;
            --color-gray-800: #1e293b;
            --color-white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --radius: 8px;
            --radius-lg: 12px;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--color-gray-50);
            color: var(--color-gray-800);
            line-height: 1.5;
            padding-top: 56px;
        }
        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--color-gray-800);
            margin: 0;
        }
        .card-custom {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-200);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card-header-custom {
            background-color: var(--color-primary);
            color: var(--color-white);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        .card-body-custom {
            padding: 1.25rem;
        }
        .btn-primary {
            background-color: var(--color-primary);
            border: none;
            border-radius: var(--radius);
            padding: 0.5rem 1.25rem;
            transition: var(--transition);
        }
        .btn-primary:hover {
            background-color: var(--color-primary-dark);
            transform: translateY(-1px);
        }
        .btn-danger {
            border-radius: var(--radius);
            transition: var(--transition);
        }
        .btn-danger:hover {
            transform: translateY(-1px);
        }
        .table-container {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-gray-200);
            overflow: hidden;
        }
        .table thead th {
            background-color: var(--color-gray-100);
            color: var(--color-gray-600);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.85rem 1rem;
            border-bottom: 2px solid var(--color-gray-200);
        }
        .table tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            border-color: var(--color-gray-200);
        }
        .table tbody tr:hover {
            background-color: var(--color-gray-50);
        }
        .form-select, .form-control {
            border-radius: var(--radius);
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--color-gray-300);
            transition: var(--transition);
        }
        .form-select:focus, .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1090;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }
        .toast-container > * { pointer-events: auto; }
        .custom-toast {
            min-width: 300px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            border: none;
        }
        .custom-toast.bg-success {
            background: linear-gradient(135deg, var(--color-success), #059669);
            color: white;
        }
        .custom-toast.bg-danger {
            background: linear-gradient(135deg, var(--color-danger), #dc2626);
            color: white;
        }
        @media (max-width: 767px) {
            .row-cols-form {
                flex-direction: column;
            }
            .row-cols-form .col {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .btn-submit {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Navbar CORRIGIDA (links com caminhos corretos) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">
            <img src="../../assets/images/biblia.png" alt="EBD" height="30" class="d-inline-block align-text-top">
            <span class="d-none d-sm-inline">Escola Bíblica</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="../dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../alunos/index.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="../classes/index.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="../professores/index.php">Professores</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php">Professor-Classe</a></li>
                <li class="nav-item"><a class="nav-link" href="../congregacao/index.php">Congregações</a></li>
                <li class="nav-item"><a class="nav-link" href="../matriculas/index.php">Matrículas</a></li>
                <li class="nav-item"><a class="nav-link" href="../usuario/index.php">Usuários</a></li>
                <li class="nav-item"><a class="nav-link" href="../relatorios/index.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-danger btn-sm" href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline ms-1">Sair</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Associação Professor-Classe</h1>
            <p class="text-muted mb-0">Gerencie quais professores lecionam em quais classes</p>
        </div>
    </div>

    <!-- Formulário de Associação -->
    <div class="card-custom">
        <div class="card-header-custom">
            <i class="fas fa-link me-2"></i> Nova Associação
        </div>
        <div class="card-body-custom">
            <form id="formAssociar" class="mb-0">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Professor <span class="text-danger">*</span></label>
                        <select name="professor_id" id="professor_id" class="form-select" required>
                            <option value="">Selecione um Professor</option>
                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= $professor['id'] ?>"><?= htmlspecialchars($professor['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Classe <span class="text-danger">*</span></label>
                        <select name="classe_id" id="classe_id" class="form-select" required>
                            <option value="">Selecione uma Classe</option>
                            <?php foreach ($classes as $classe): ?>
                                <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" id="btnAssociar">
                            <i class="fas fa-plus me-1"></i> Associar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Associações -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" id="tabelaAssociacoes">
                <thead>
                    <tr>
                        <th>Professor</th>
                        <th>Classe</th>
                        <th class="text-center" style="width: 100px;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($professor_classes) && count($professor_classes) > 0): ?>
                        <?php foreach ($professor_classes as $item): ?>
                            <tr data-id="<?= $item['id'] ?>">
                                <td><?= htmlspecialchars($item['professor']) ?></td>
                                <td><?= htmlspecialchars($item['classe']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm btnRemover" data-id="<?= $item['id'] ?>" data-professor="<?= htmlspecialchars($item['professor']) ?>" data-classe="<?= htmlspecialchars($item['classe']) ?>">
                                        <i class="fas fa-trash-alt"></i> Remover
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Nenhuma associação cadastrada</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    
    // Função para exibir mensagens toast
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white show`;
        toast.innerHTML = `<div class="toast-body d-flex align-items-center gap-2">
            <span>${icon}</span>
            <span class="flex-grow-1">${mensagem}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Associar professor à classe
    $('#formAssociar').on('submit', function(e) {
        e.preventDefault();
        
        const professorId = $('#professor_id').val();
        const classeId = $('#classe_id').val();
        
        if (!professorId) {
            exibirMensagem('erro', 'Selecione um professor.');
            return;
        }
        if (!classeId) {
            exibirMensagem('erro', 'Selecione uma classe.');
            return;
        }
        
        const btn = $('#btnAssociar');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Associando...');
        
        $.ajax({
            url: '../../controllers/professor_classe.php',
            method: 'POST',
            data: { acao: 'associar', professor_id: professorId, classe_id: classeId },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Associar');
                
                if (response.sucesso) {
                    exibirMensagem('sucesso', response.mensagem || 'Associação realizada com sucesso!');
                    $('#professor_id').val('');
                    $('#classe_id').val('');
                    
                    // Recarregar a página para mostrar a nova associação
                    setTimeout(() => location.reload(), 1500);
                } else {
                    exibirMensagem('erro', response.mensagem || 'Erro ao associar professor à classe.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Associar');
                let msg = 'Erro ao comunicar com o servidor.';
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.mensagem) msg = resp.mensagem;
                } catch(e) {}
                exibirMensagem('erro', msg);
            }
        });
    });
    
    // Remover associação
    $(document).on('click', '.btnRemover', function() {
        const id = $(this).data('id');
        const professor = $(this).data('professor');
        const classe = $(this).data('classe');
        
        Swal.fire({
            title: 'Confirmar remoção',
            html: `Deseja remover a associação do professor <strong>${professor}</strong> com a classe <strong>${classe}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../controllers/professor_classe.php',
                    method: 'POST',
                    data: { acao: 'remover', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.sucesso) {
                            exibirMensagem('sucesso', response.mensagem || 'Associação removida com sucesso!');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            exibirMensagem('erro', response.mensagem || 'Erro ao remover associação.');
                        }
                    },
                    error: function() {
                        exibirMensagem('erro', 'Erro ao comunicar com o servidor.');
                    }
                });
            }
        });
    });
    
    // Fechar menu mobile ao clicar em link
    $('.navbar-nav .nav-link').on('click', function() {
        if ($('.navbar-toggler').is(':visible')) {
            $('.navbar-collapse').collapse('hide');
        }
    });
});
</script>

</body>
</html>