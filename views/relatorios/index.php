<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="col-md-10 offset-md-1 mt-5 mb-5">
    <div class="list-group shadow rounded-3 overflow-hidden">
        <a href="./relatorio_consolidado.php" class="list-group-item list-group-item-action p-4 d-flex align-items-center gap-3 transition">
            <i class="fas fa-users fa-lg text-primary"></i>
            <span class="fw-semibold">Relatório Individual de Frequência</span>
        </a>
        <a href="./relatorio_geral.php" class="list-group-item list-group-item-action p-4 d-flex align-items-center gap-3 transition">
            <i class="fas fa-user-times fa-lg text-danger"></i>
            <span class="fw-semibold">Relatório Geral</span>
        </a>
        <a href="./aniversariantes.php" class="list-group-item list-group-item-action p-4 d-flex align-items-center gap-3 transition">
            <i class="fas fa-user-check fa-lg text-success"></i>
            <span class="fw-semibold">Aniversariantes do Mês</span>
        </a>
        <a href="./frequencias_geral.php" class="list-group-item list-group-item-action p-4 d-flex align-items-center gap-3 transition">
            <i class="fas fa-users-slash fa-lg text-warning"></i>
            <span class="fw-semibold">Relatório Total Faltas & Presenças</span>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

