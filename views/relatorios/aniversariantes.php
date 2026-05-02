<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Aniversariantes - E.B.D';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php';

// Conectando ao banco de dados
require_once __DIR__ . '/../../config/conexao.php';

// Definir a localidade para português (Brasil)
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

// --- Captura e Validação dos Filtros ---
$congregacao_selecionada = $_GET['congregacao_id'] ?? '';
$classe_selecionada = $_GET['classe_id'] ?? '';
$mes_selecionado = $_GET['mes'] ?? date('m');
$ano_selecionado = $_GET['ano'] ?? date('Y');

// Determinar o trimestre atual baseado no mês selecionado
$trimestre_atual = 1;
if ($mes_selecionado >= 1 && $mes_selecionado <= 3) {
    $trimestre_atual = 1;
} elseif ($mes_selecionado >= 4 && $mes_selecionado <= 6) {
    $trimestre_atual = 2;
} elseif ($mes_selecionado >= 7 && $mes_selecionado <= 9) {
    $trimestre_atual = 3;
} elseif ($mes_selecionado >= 10 && $mes_selecionado <= 12) {
    $trimestre_atual = 4;
}

$nome_congregacao_selecionada = '';
$nome_classe_selecionada = '';

// Consulta para obter as congregações
$query_congs = "SELECT id, nome FROM congregacoes ORDER BY nome";
$result_congs = $pdo->query($query_congs);
$congregacoes = $result_congs->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obter as classes
$query_classes = "SELECT id, nome FROM classes ORDER BY nome";
$result_classes = $pdo->query($query_classes);
$classes = $result_classes->fetchAll(PDO::FETCH_ASSOC);

// Array com os meses em português
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// Gerar lista de anos (5 anos atrás até 2 anos à frente)
$ano_atual = date('Y');
$anos = range($ano_atual - 5, $ano_atual + 2);

// --- Query Principal com filtros e DISTINCT ---
$query = "SELECT DISTINCT 
            a.nome, 
            DAY(a.data_nascimento) AS dia,
            c.nome AS congregacao_nome,
            cl.nome AS classe_nome
          FROM alunos a
          INNER JOIN matriculas m ON a.id = m.aluno_id 
              AND m.status = 'ativo' 
              AND m.trimestre = :trimestre
          INNER JOIN congregacoes c ON m.congregacao_id = c.id
          INNER JOIN classes cl ON m.classe_id = cl.id
          WHERE MONTH(a.data_nascimento) = :mes 
            AND YEAR(a.data_nascimento) <= :ano
            AND a.data_nascimento != '0000-00-00'";

if ($congregacao_selecionada) {
    $query .= " AND m.congregacao_id = :congregacao_id";
}
if ($classe_selecionada) {
    $query .= " AND m.classe_id = :classe_id";
}
$query .= " ORDER BY DAY(a.data_nascimento), a.nome";

$result = $pdo->prepare($query);
$result->bindParam(':mes', $mes_selecionado, PDO::PARAM_INT);
$result->bindParam(':ano', $ano_selecionado, PDO::PARAM_INT);
$result->bindParam(':trimestre', $trimestre_atual, PDO::PARAM_INT);

if ($congregacao_selecionada) {
    $result->bindParam(':congregacao_id', $congregacao_selecionada, PDO::PARAM_INT);
    foreach ($congregacoes as $cong) {
        if ($cong['id'] == $congregacao_selecionada) {
            $nome_congregacao_selecionada = $cong['nome'];
            break;
        }
    }
}
if ($classe_selecionada) {
    $result->bindParam(':classe_id', $classe_selecionada, PDO::PARAM_INT);
    foreach ($classes as $classe) {
        if ($classe['id'] == $classe_selecionada) {
            $nome_classe_selecionada = $classe['nome'];
            break;
        }
    }
}
$result->execute();

// Organizando os aniversariantes por dia
$aniversariantes = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $aniversariantes[$row['dia']][] = [
        'nome' => $row['nome'],
        'congregacao' => $row['congregacao_nome'] ?? 'N/A',
        'classe' => $row['classe_nome'] ?? 'N/A'
    ];
}
$pdo = null;
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-birthday-cake me-3" style="color: var(--success);"></i>
                Calendário de Aniversariantes
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
                        <i class="fas fa-birthday-cake me-1"></i> Aniversariantes
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Acompanhe os aniversariantes do mês por classe e congregação
            </p>
        </div>
    </div>

    <!-- Badges de Informação -->
    <div class="d-flex flex-wrap gap-2 mb-4" data-aos="fade-up" data-aos-delay="100">
        <span class="badge-ebd badge-primary">
            <i class="far fa-calendar-alt me-1"></i> <?= $meses[$mes_selecionado] . ' ' . $ano_selecionado ?>
        </span>
        <?php if ($nome_congregacao_selecionada): ?>
            <span class="badge-ebd" style="background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);">
                <i class="fas fa-church me-1"></i> <?= htmlspecialchars($nome_congregacao_selecionada) ?>
            </span>
        <?php endif; ?>
        <?php if ($nome_classe_selecionada): ?>
            <span class="badge-ebd" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
                <i class="fas fa-users me-1"></i> <?= htmlspecialchars($nome_classe_selecionada) ?>
            </span>
        <?php endif; ?>
        <span class="badge-ebd badge-success">
            <i class="fas fa-layer-group me-1"></i> <?= $trimestre_atual ?>º Trimestre
        </span>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="get" class="row g-4">
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Mês
                    </label>
                    <select name="mes" class="form-select">
                        <?php foreach ($meses as $num => $nome_mes): ?>
                            <option value="<?= $num ?>" <?= $num == $mes_selecionado ? 'selected' : ?>><?= $nome_mes ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar me-1 text-primary"></i> Ano
                    </label>
                    <select name="ano" class="form-select">
                        <?php foreach ($anos as $ano): ?>
                            <option value="<?= $ano ?>" <?= $ano == $ano_selecionado ? 'selected' : ?>><?= $ano ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <select name="congregacao_id" class="form-select">
                        <option value="">Todas as congregações</option>
                        <?php foreach ($congregacoes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $congregacao_selecionada ? 'selected' : ?>><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-users me-1 text-primary"></i> Classe
                    </label>
                    <select name="classe_id" class="form-select">
                        <option value="">Todas as classes</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= $classe['id'] ?>" <?= $classe['id'] == $classe_selecionada ? 'selected' : ?>><?= htmlspecialchars($classe['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
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

    <!-- Tabela de Aniversariantes -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-success d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Aniversariantes
            </h5>
            <?php if (!empty($aniversariantes)): ?>
            <div class="d-flex gap-2">
                <button id="exportExcel" class="btn btn-sm" style="background: #27ae60; color: white;">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button>
                <button id="exportPdf" class="btn btn-sm" style="background: #e74c3c; color: white;">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaAniversariantes" class="custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px">Dia</th>
                            <th>Nome do Aluno</th>
                            <th>Classe</th>
                            <th>Congregação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_aniversariantes = 0;
                        if (!empty($aniversariantes)) {
                            foreach ($aniversariantes as $dia => $dados) {
                                foreach ($dados as $item) {
                                    $dia_formatado = str_pad($dia, 2, '0', STR_PAD_LEFT);
                                    echo "<tr>";
                                    echo "<td data-label='Dia' class='text-center'><span class='birthday-day'>{$dia_formatado}</span></td>";
                                    echo "<td data-label='Nome'><i class='fas fa-user-graduate me-2' style='color: var(--success);'></i>" . htmlspecialchars($item['nome']) . "</td>";
                                    echo "<td data-label='Classe'><i class='fas fa-chalkboard-user me-2' style='color: var(--primary-500);'></i>" . htmlspecialchars($item['classe']) . "</td>";
                                    echo "<td data-label='Congregação'><i class='fas fa-church me-2' style='color: var(--primary-500);'></i>" . htmlspecialchars($item['congregacao']) . "</td>";
                                    echo "</tr>";
                                    $total_aniversariantes++;
                                }
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-5'>";
                            echo "<i class='fas fa-calendar-times fa-3x mb-3 d-block' style='color: var(--gray-400);'></i>";
                            echo "<p class='text-muted mb-0'>Nenhum aniversariante encontrado</p>";
                            echo "<small class='text-muted'>Ajuste os filtros para visualizar os resultados</small>";
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                    <?php if ($total_aniversariantes > 0): ?>
                    <tfoot>
                        <tr class="table-footer">
                            <td colspan="4" class="text-center py-3">
                                <i class="fas fa-users me-2"></i>
                                Total de Aniversariantes: 
                                <strong class="fs-5" style="color: var(--success);"><?= $total_aniversariantes ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Dica de Aniversariantes -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="400">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-gift fa-2x" style="color: var(--success);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Dica Pastoral:</strong>
                <span>Utilize esta lista para planejar ações de carinho e celebração com os aniversariantes do mês, fortalecendo os laços comunitários.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para aniversariantes */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Badge do dia do aniversário */
.birthday-day {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1.25rem;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    transition: all 0.2s ease;
}

.birthday-day:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4);
}

/* Rodapé da tabela */
.table-footer {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 2px solid var(--gray-200);
}

/* Alertas personalizados */
.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--success);
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

.dataTables_wrapper .dataTables_length select {
    border-radius: 10px;
    border: 1.5px solid var(--gray-200);
    padding: 0.25rem 0.5rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--gradient-primary) !important;
    border: none !important;
    color: white !important;
}

/* Responsividade Mobile */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .birthday-day {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    /* Card responsivo para mobile */
    .custom-table thead {
        display: none;
    }
    
    .custom-table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid var(--gray-200);
        border-radius: 16px;
        padding: 1rem;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .custom-table tbody tr td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0;
        border: none;
        border-bottom: 1px dashed var(--gray-200);
    }
    
    .custom-table tbody tr td:last-child {
        border-bottom: none;
    }
    
    .custom-table tbody tr td::before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--gray-700);
        margin-right: 1rem;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        min-width: 100px;
    }
    
    .custom-table tbody tr td.text-center {
        justify-content: center;
    }
    
    .custom-table tbody tr td.text-center::before {
        display: none;
    }
}
</style>

<script>
$(document).ready(function() {
    <?php if (!empty($aniversariantes)): ?>
    // Inicializa DataTable
    var table = $('#tabelaAniversariantes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        order: [[0, 'asc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        responsive: false,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    
    // Configura botões de exportação (usando DataTable buttons)
    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn-excel',
                title: 'Aniversariantes_<?= $meses[$mes_selecionado] . '_' . $ano_selecionado ?>',
                exportOptions: { columns: [0,1,2,3] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn-pdf',
                title: 'Aniversariantes_<?= $meses[$mes_selecionado] . '_' . $ano_selecionado ?>',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3] },
                customize: function(doc) {
                    doc.styles.tableHeader = {
                        bold: true,
                        fontSize: 10,
                        color: 'white',
                        fillColor: '#059669',
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
require_once __DIR__ . '/../../includes/footer.php';
?>