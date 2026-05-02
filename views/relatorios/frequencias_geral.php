<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Relatório de Presenças por Aluno';

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';

// Conexão com o banco de dados
require_once __DIR__ . '/../../config/conexao.php';

// --- Funções ---
function calcularPeriodoTrimestre(int $trimestre): array {
    $ano = date('Y');
    $mes_inicio = ($trimestre - 1) * 3 + 1;
    $mes_fim = $mes_inicio + 2;
    $data_inicio = "$ano-" . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . "-01";
    $ultimo_dia = date("t", strtotime("$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-01"));
    $data_fim = "$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-$ultimo_dia";
    return [$data_inicio, $data_fim];
}

// --- Filtros ---
$congregacao_id = $_GET['congregacao_id'] ?? '';
$classe_id = $_GET['classe_id'] ?? '';
$trimestre = $_GET['trimestre'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Verifica se o formulário foi submetido
$form_submetido = !empty($_GET);

// Inicializa variáveis
$alunos = [];
$top_presencas = [];
$top_faltas = [];
$trimestre_sem_dados = false;

// Definir período
if (!empty($trimestre)) {
    [$data_inicio, $data_fim] = calcularPeriodoTrimestre($trimestre);
    
    // Verificar se existe alguma chamada para este trimestre
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM chamadas WHERE data BETWEEN ? AND ?");
    $stmt->execute([$data_inicio, $data_fim]);
    $result = $stmt->fetch();
    
    if ($result['total'] == 0) {
        $trimestre_sem_dados = true;
    }
} else {
    $data_inicio = $data_inicio ?: date('Y-m-01');
    $data_fim = $data_fim ?: date('Y-m-d');
}

// --- Consulta principal (com cálculos corrigidos) ---
if (!$trimestre_sem_dados && $form_submetido) {
    $sql = "
        SELECT 
            a.id,
            a.nome AS aluno,
            c.nome AS classe,
            cg.nome AS congregacao,
            COALESCE(total_chamadas.total_aulas, 0) AS total_aulas,
            COALESCE(presencas_aluno.total_presencas, 0) AS presencas,
            (COALESCE(total_chamadas.total_aulas, 0) - COALESCE(presencas_aluno.total_presencas, 0)) AS faltas,
            CASE 
                WHEN COALESCE(total_chamadas.total_aulas, 0) > 0 THEN
                    ROUND((COALESCE(presencas_aluno.total_presencas, 0) * 100.0) / total_chamadas.total_aulas, 1)
                ELSE 0
            END AS frequencia
        FROM alunos a
        INNER JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
        INNER JOIN classes c ON c.id = m.classe_id
        INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
        LEFT JOIN (
            SELECT ch.classe_id, COUNT(*) AS total_aulas
            FROM chamadas ch
            WHERE ch.data BETWEEN :inicio AND :fim
            GROUP BY ch.classe_id
        ) total_chamadas ON total_chamadas.classe_id = c.id
        LEFT JOIN (
            SELECT p.aluno_id, SUM(CASE WHEN p.presente IN ('presente', 'justificado') THEN 1 ELSE 0 END) AS total_presencas
            FROM presencas p
            INNER JOIN chamadas ch ON ch.id = p.chamada_id
            WHERE ch.data BETWEEN :inicio AND :fim
            GROUP BY p.aluno_id
        ) presencas_aluno ON presencas_aluno.aluno_id = a.id
        WHERE 1=1";

    $params = [':inicio' => $data_inicio, ':fim' => $data_fim];

    if (!empty($congregacao_id)) {
        $sql .= " AND cg.id = :congregacao";
        $params[':congregacao'] = $congregacao_id;
    }

    if (!empty($classe_id)) {
        $sql .= " AND c.id = :classe";
        $params[':classe'] = $classe_id;
    }

    $sql .= " ORDER BY frequencia DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rankings
    if (count($alunos) > 0) {
        $top_presencas = array_slice($alunos, 0, 5);
        $top_faltas = array_reverse(array_slice($alunos, -5, 5));
    }
}

// Dropdowns
$congs = $pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome")->fetchAll();
$classes = $pdo->query("SELECT id, nome FROM classes ORDER BY nome")->fetchAll();

// Valores padrão para os filtros
$data_inicio = $data_inicio ?: date('Y-m-01');
$data_fim = $data_fim ?: date('Y-m-d');
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-user-graduate me-3" style="color: var(--primary-600);"></i>
                Relatório de Presenças por Aluno
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php" style="color: var(--primary-600);">
                            <i class="fas fa-chart-line me-1"></i> Relatórios
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-user-check me-1"></i> Presenças por Aluno
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Análise detalhada de frequência individual por aluno, classe e congregação
            </p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-modern btn-outline-secondary">
                <i class="fas fa-print me-2"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="get" class="row g-4">
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <select name="congregacao_id" class="form-select">
                        <option value="">Todas as congregações</option>
                        <?php foreach ($congs as $c) : ?>
                            <option value="<?= $c['id'] ?>" <?= ($congregacao_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-users me-1 text-primary"></i> Classe
                    </label>
                    <select name="classe_id" class="form-select">
                        <option value="">Todas as classes</option>
                        <?php foreach ($classes as $cl) : ?>
                            <option value="<?= $cl['id'] ?>" <?= ($classe_id == $cl['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                    </label>
                    <select name="trimestre" id="trimestre" class="form-select">
                        <option value="">Personalizado</option>
                        <?php for ($i = 1; $i <= 4; $i++) : ?>
                            <option value="<?= $i ?>" <?= ($trimestre == $i) ? 'selected' : '' ?>><?= $i ?>º Trimestre</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Data Início
                    </label>
                    <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" <?= $trimestre ? 'readonly' : '' ?> />
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar-check me-1 text-primary"></i> Data Fim
                    </label>
                    <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" <?= $trimestre ? 'readonly' : '' ?> />
                </div>
                
                <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-modern btn-modern-primary">
                            <i class="fas fa-search me-2"></i> Filtrar
                        </button>
                        <a href="?" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Conteúdo Condicional -->
    <?php if (!$form_submetido): ?>
        <!-- Estado inicial - sem filtros -->
        <div class="modern-card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body text-center py-5">
                <i class="fas fa-filter fa-4x mb-3" style="color: var(--gray-400);"></i>
                <h5 class="text-muted">Nenhum dado para exibir</h5>
                <p class="text-muted">Por favor, selecione os filtros desejados e clique em "Filtrar".</p>
            </div>
        </div>
    <?php elseif ($trimestre_sem_dados): ?>
        <!-- Trimestre sem dados -->
        <div class="alert-ebd alert-warning-ebd" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-exclamation-triangle me-2"></i>
            O trimestre selecionado (<?= $trimestre ?>º) ainda não possui registros de chamadas.
        </div>
    <?php elseif (empty($alunos)): ?>
        <!-- Nenhum resultado -->
        <div class="modern-card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x mb-3" style="color: var(--gray-400);"></i>
                <h5 class="text-muted">Nenhum resultado encontrado</h5>
                <p class="text-muted">Não foram encontrados registros com os filtros selecionados.</p>
            </div>
        </div>
    <?php else: ?>
        <?php
        $total_alunos = count($alunos);
        $total_presencas = array_sum(array_column($alunos, 'presencas'));
        $total_faltas = array_sum(array_column($alunos, 'faltas'));
        $media_frequencia = $total_alunos > 0 ? round(array_sum(array_column($alunos, 'frequencia')) / $total_alunos, 1) : 0;
        ?>

        <!-- Cards de Resumo Geral -->
        <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="200">
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= $total_alunos ?></div>
                    <div class="stat-label">Total de Alunos</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?= $total_presencas ?></div>
                    <div class="stat-label">Total Presenças</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-value"><?= $total_faltas ?></div>
                    <div class="stat-label">Total Faltas</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value"><?= $media_frequencia ?>%</div>
                    <div class="stat-label">Média Frequência</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value fs-6"><?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?></div>
                    <div class="stat-label">Período Analisado</div>
                </div>
            </div>
        </div>

        <!-- Rankings Top 5 Presenças e Faltas -->
        <?php if (!empty($top_presencas) || !empty($top_faltas)): ?>
        <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="250">
            <div class="col-12 col-md-6">
                <div class="modern-card h-100">
                    <div class="card-header-modern bg-success">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-trophy me-2"></i> Top 5 Maiores Presenças
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach($top_presencas as $i => $aluno): ?>
                            <div class="ranking-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="ranking-position bg-success text-white"><?= $i+1 ?></span>
                                    <div>
                                        <strong class="d-block"><?= htmlspecialchars($aluno['aluno']) ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars($aluno['classe']) ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success"><?= $aluno['presencas'] ?> presenças</span>
                                    <br>
                                    <small><?= $aluno['frequencia'] ?>% de frequência</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6">
                <div class="modern-card h-100">
                    <div class="card-header-modern" style="background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i> Top 5 Maiores Faltas
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach($top_faltas as $i => $aluno): ?>
                            <div class="ranking-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="ranking-position bg-danger text-white"><?= $i+1 ?></span>
                                    <div>
                                        <strong class="d-block"><?= htmlspecialchars($aluno['aluno']) ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars($aluno['classe']) ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-danger"><?= $aluno['faltas'] ?> faltas</span>
                                    <br>
                                    <small><?= $aluno['frequencia'] ?>% de frequência</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela Completa de Alunos -->
        <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
            <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-table me-2"></i> Relação Completa de Alunos
                </h5>
                <div class="d-flex gap-2">
                    <button id="exportExcel" class="btn btn-sm" style="background: #27ae60; color: white;">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                    <button id="exportPdf" class="btn btn-sm" style="background: #e74c3c; color: white;">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelaAlunos" class="custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Classe</th>
                                <th>Congregação</th>
                                <th class="text-center">Presenças</th>
                                <th class="text-center">Faltas</th>
                                <th class="text-center">Frequência</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($alunos as $aluno): ?>
                                <tr>
                                    <td><i class="fas fa-user-graduate me-2" style="color: var(--primary-500);"></i><?= htmlspecialchars($aluno['aluno']) ?></td>
                                    <td><i class="fas fa-chalkboard-user me-2" style="color: var(--success);"></i><?= htmlspecialchars($aluno['classe']) ?></td>
                                    <td><i class="fas fa-church me-2" style="color: var(--primary-500);"></i><?= htmlspecialchars($aluno['congregacao']) ?></td>
                                    <td class="text-center fw-bold" style="color: var(--success);"><?= $aluno['presencas'] ?></td>
                                    <td class="text-center fw-bold" style="color: var(--danger);"><?= $aluno['faltas'] ?></td>
                                    <td class="text-center">
                                        <div class="progress-container">
                                            <div class="progress-bar-custom" style="width: <?= $aluno['frequencia'] ?>%;">
                                                <span><?= $aluno['frequencia'] ?>%</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-footer">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Totais Gerais:</td>
                                <td class="text-center fw-bold" style="color: var(--success);"><?= $total_presencas ?></td>
                                <td class="text-center fw-bold" style="color: var(--danger);"><?= $total_faltas ?></td>
                                <td class="text-center fw-bold" style="color: var(--info);"><?= $media_frequencia ?>%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dica de Análise -->
        <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="350">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <i class="fas fa-chart-line fa-2x" style="color: var(--info);"></i>
                <div class="flex-grow-1">
                    <strong class="d-block mb-1">Análise de Frequência:</strong>
                    <span>Alunos com frequência abaixo de 75% merecem atenção especial. Utilize este relatório para acompanhamento pastoral e ações de incentivo.</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Estilos específicos para o relatório de presenças */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Badge de trimestre */
.badge-trimestre {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Rankings */
.ranking-position {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
}

.ranking-item {
    transition: background-color 0.2s ease;
}

.ranking-item:hover {
    background-color: var(--gray-50);
}

/* Barra de progresso personalizada */
.progress-container {
    width: 100%;
    background-color: var(--gray-200);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}

.progress-bar-custom {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    border-radius: 20px;
    padding: 0.25rem 0.5rem;
    text-align: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    transition: width 0.5s ease;
}

.progress-bar-custom span {
    display: inline-block;
}

/* Alertas personalizados */
.alert-warning-ebd {
    background: linear-gradient(135deg, var(--accent-50) 0%, white 100%);
    border-left: 4px solid var(--warning);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

/* DataTables personalizado */
.dataTables_wrapper .dataTables_filter input {
    border-radius: 10px;
    border: 1.5px solid var(--gray-200);
    padding: 0.5rem 0.75rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Rodapé da tabela */
.table-footer {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 2px solid var(--gray-200);
    padding: 1rem;
}

/* Print styles */
@media print {
    .navbar, .breadcrumb, .btn-modern, .alert-ebd, .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }
    
    body {
        padding: 0;
        margin: 0;
    }
    
    .modern-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .stat-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}

/* Responsividade */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .stat-card .stat-value {
        font-size: 1.25rem;
    }
    
    .progress-bar-custom {
        min-width: 50px;
    }
}
</style>

<script>
$(document).ready(function() {
    <?php if (!empty($alunos)): ?>
    // Inicializar DataTable
    var table = $('#tabelaAlunos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        order: [[5, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        responsive: true,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    
    // Configurar botões de exportação
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn-excel',
                title: 'Relatorio_Presencas_Alunos',
                exportOptions: { columns: [0,1,2,3,4,5] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn-pdf',
                title: 'Relatorio_Presencas_Alunos',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5] },
                customize: function(doc) {
                    doc.styles.tableHeader = {
                        bold: true,
                        fontSize: 10,
                        color: 'white',
                        fillColor: '#3b82f6',
                        alignment: 'center'
                    };
                    doc.defaultStyle.fontSize = 9;
                }
            }
        ]
    });
    
    table.buttons().container().appendTo('.card-header-modern .d-flex');
    
    // Eventos para os botões personalizados
    $('#exportExcel').click(function() {
        table.button('.buttons-excel').trigger();
    });
    
    $('#exportPdf').click(function() {
        table.button('.buttons-pdf').trigger();
    });
    <?php endif; ?>
    
    // Atualiza datas ao mudar trimestre
    $('select[name="trimestre"]').change(function() {
        const trimestre = $(this).val();
        const ano = new Date().getFullYear();
        let inicio, fim;
        
        if (trimestre) {
            switch(trimestre) {
                case '1': inicio = `${ano}-01-01`; fim = `${ano}-03-31`; break;
                case '2': inicio = `${ano}-04-01`; fim = `${ano}-06-30`; break;
                case '3': inicio = `${ano}-07-01`; fim = `${ano}-09-30`; break;
                case '4': inicio = `${ano}-10-01`; fim = `${ano}-12-31`; break;
            }
            $('input[name="data_inicio"]').val(inicio).prop('readonly', true);
            $('input[name="data_fim"]').val(fim).prop('readonly', true);
        } else {
            $('input[name="data_inicio"], input[name="data_fim"]').prop('readonly', false);
        }
    });
    
    // Inicializar AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>