<?php
// views/relatorios/aniversariantes.php
// Relatório de aniversariantes do mês

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$relatorioController = new RelatorioController();

// Processar filtros
$mes = isset($_GET['mes']) && $_GET['mes'] ? (int)$_GET['mes'] : (int)date('m');
$classe_id = isset($_GET['classe_id']) && $_GET['classe_id'] ? (int)$_GET['classe_id'] : null;

// Buscar aniversariantes usando o controller
$aniversariantes = $relatorioController->getAniversariantes($mes, $classe_id);

// Buscar classes para filtro usando o controller
$classes = $relatorioController->getClasses();

$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

$pageTitle = 'Aniversariantes - ' . $meses[$mes];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Cabeçalho -->
    <div class="mb-4" data-aos="fade-down">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                    <i class="fas fa-birthday-cake me-3" style="color: var(--success);"></i>
                    Aniversariantes
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php" style="color: var(--primary-600);">Relatórios</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Aniversariantes
                        </li>
                    </ol>
                </nav>
                <p class="text-muted mt-2 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Aniversariantes do mês organizados por dia
                </p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="exportarCSV()" class="btn btn-modern btn-success">
                    <i class="fas fa-file-csv me-2"></i> Exportar CSV
                </button>
                <button onclick="window.print()" class="btn btn-modern btn-primary">
                    <i class="fas fa-print me-2"></i> Imprimir
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up">
        <div class="card-header-modern bg-gray-100">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i> Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3" id="formFiltros">
                <div class="col-md-4">
                    <label class="form-label">Mês</label>
                    <select name="mes" class="form-select">
                        <?php foreach ($meses as $num => $nome): ?>
                            <option value="<?= $num ?>" <?= $mes == $num ? 'selected' : '' ?>>
                                <?= $nome ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Classe</label>
                    <select name="classe_id" class="form-select">
                        <option value="">Todas as classes</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= $classe['id'] ?>" <?= $classe_id == $classe['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($classe['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-modern btn-modern-primary w-100">
                        <i class="fas fa-search me-2"></i> Buscar Aniversariantes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumo -->
    <div class="alert-ebd alert-success-ebd mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-gift fa-2x"></i>
            <div>
                <strong class="d-block mb-1">Aniversariantes do mês de <?= $meses[$mes] ?></strong>
                <span>Encontramos <strong><?= count($aniversariantes) ?></strong> aniversariante(s) neste mês.</span>
            </div>
        </div>
    </div>

    <!-- Cards de Aniversariantes -->
    <?php if (empty($aniversariantes)): ?>
        <div class="text-center py-5" data-aos="fade-up">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Nenhum aniversariante encontrado para este mês</h5>
            <p class="text-muted">Tente selecionar outro mês ou verifique se há alunos cadastrados.</p>
        </div>
    <?php else: ?>
        <div class="row g-4" data-aos="fade-up" data-aos-delay="200">
            <?php 
            $dias = [];
            foreach ($aniversariantes as $aniversariante) {
                $dia = (int)date('j', strtotime($aniversariante['data_nascimento']));
                if (!isset($dias[$dia])) {
                    $dias[$dia] = [];
                }
                $dias[$dia][] = $aniversariante;
            }
            ksort($dias);
            
            foreach ($dias as $dia => $aniversariantesDia): 
            ?>
                <div class="col-12">
                    <div class="modern-card">
                        <div class="card-header-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-calendar-day me-2"></i>
                                Dia <?= $dia ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Data Nascimento</th>
                                            <th>Idade</th>
                                            <th>Classe</th>
                                            <th>Congregação</th>
                                            <th>Telefone</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($aniversariantesDia as $aniversariante): ?>
                                            <tr>
                                                <td class="fw-semibold">
                                                    <i class="fas fa-user-circle text-success me-2"></i>
                                                    <?= htmlspecialchars($aniversariante['nome']) ?>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($aniversariante['data_nascimento'])) ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $aniversariante['idade'] ?> anos</span>
                                                </td>
                                                <td><?= htmlspecialchars($aniversariante['classe_nome']) ?></td>
                                                <td><?= htmlspecialchars($aniversariante['congregacao_nome']) ?></td>
                                                <td>
                                                    <?php if ($aniversariante['telefone']): ?>
                                                        <a href="tel:<?= $aniversariante['telefone'] ?>" class="text-decoration-none">
                                                            <i class="fas fa-phone me-1"></i> <?= $aniversariante['telefone'] ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Não informado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="enviarParabens('<?= htmlspecialchars($aniversariante['nome']) ?>', '<?= $aniversariante['telefone'] ?>')">
                                                        <i class="fas fa-gift me-1"></i> Enviar Parabéns
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.alert-success-ebd {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-left: 4px solid var(--success);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

.btn-modern {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.btn-modern:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
}

.btn-modern-primary {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
}

.btn-modern-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

@media (max-width: 768px) {
    .btn-modern {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
    
    .alert-success-ebd {
        font-size: 0.85rem;
    }
}

@media print {
    .btn, .btn-modern, .form-filters, .breadcrumb, .alert-success-ebd .btn,
    .card-header-modern .btn, .d-flex.gap-2 .btn {
        display: none !important;
    }
    
    .modern-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    
    .card-header-modern {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<script>
function exportarCSV() {
    const formData = new FormData(document.getElementById('formFiltros'));
    const params = new URLSearchParams(formData).toString();
    window.location.href = 'exportar_relatorio.php?tipo=aniversariantes&' + params;
}

function enviarParabens(nome, telefone) {
    if (!telefone) {
        alert('Este aluno não possui telefone cadastrado!');
        return;
    }
    
    if (confirm(`Enviar mensagem de parabéns para ${nome}?`)) {
        const mensagem = `Feliz Aniversário, ${nome}! 🎉🎂 Que Deus abençoe sua vida abundantemente! Parabéns! 🎈🎁`;
        const telefoneLimpo = telefone.replace(/\D/g, '');
        window.open(`https://wa.me/55${telefoneLimpo}?text=${encodeURIComponent(mensagem)}`, '_blank');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>