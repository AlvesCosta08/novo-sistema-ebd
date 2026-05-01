<?php
require_once __DIR__ . '/../../auth/valida_sessao.php';
$usuario_id      = $_SESSION['usuario_id'] ?? null;
$nome_usuario    = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil          = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id  = $_SESSION['congregacao_id'] ?? null;

if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado.');
}

// Função para obter o trimestre atual
function getTrimestreAtual() {
    $mes = date('n');
    if ($mes >= 1 && $mes <= 3) return 1;
    if ($mes >= 4 && $mes <= 6) return 2;
    if ($mes >= 7 && $mes <= 9) return 3;
    return 4;
}

$anoAtual = date('Y');
$trimestreAtual = getTrimestreAtual();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="theme-color" content="#0d6efd">
    <title>Histórico de Chamadas - Escola Bíblica</title>
    <!-- Bootstrap 5 CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* ===== VARIÁVEIS ===== */
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --success: #198754;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #0dcaf0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-radius: 12px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Container principal */
        .main-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Cards modernos */
        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .modern-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -12px rgba(0,0,0,0.2);
        }

        .card-header-modern {
            padding: 1rem 1.5rem;
            border: none;
        }

        .card-header-gradient-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .card-header-gradient-success {
            background: linear-gradient(135deg, var(--success) 0%, #146c43 100%);
            color: white;
        }

        .card-header-gradient-info {
            background: linear-gradient(135deg, var(--info) 0%, #0aa2c0 100%);
            color: white;
        }

        /* Botões modernos */
        .btn-modern {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-modern-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-modern-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
            color: white;
        }

        .btn-modern-success {
            background: linear-gradient(135deg, var(--success), #146c43);
            color: white;
        }

        .btn-modern-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
            color: white;
        }

        .btn-modern-danger {
            background: linear-gradient(135deg, var(--danger), #b02a37);
            color: white;
        }

        .btn-modern-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            color: white;
        }

        /* Cards de estatísticas */
        .stats-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: none;
            border-radius: var(--border-radius);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stats-card .card-body {
            padding: 1.25rem;
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
        }

        /* Tabela moderna */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 50px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white !important;
            border: none;
            border-radius: 50px;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-presente {
            background: linear-gradient(135deg, var(--success), #146c43);
            color: white;
        }

        .badge-ausente {
            background: linear-gradient(135deg, var(--danger), #b02a37);
            color: white;
        }

        .badge-justificado {
            background: linear-gradient(135deg, var(--warning), #d39e00);
            color: #000;
        }

        /* Animação de loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .spinner-custom {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Toast notifications */
        .toast-custom {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 280px;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }
            
            .card-header-modern {
                padding: 0.75rem 1rem;
            }
            
            .stats-card .card-body {
                padding: 0.875rem;
            }
            
            .stats-card h3 {
                font-size: 1.25rem;
            }
            
            .stats-card h6 {
                font-size: 0.7rem;
            }
            
            .btn-modern {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .stats-icon {
                width: 35px;
                height: 35px;
            }
            
            .stats-icon i {
                font-size: 1rem;
            }
        }

        /* Utility classes */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="text-white mb-2">
                <i class="fas fa-history me-2"></i>
                Histórico de Chamadas
            </h1>
            <p class="text-white-50 mb-0">
                <i class="fas fa-church me-1"></i> Consulte e gerencie todas as chamadas registradas
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="dropdown">
                <button class="btn btn-light btn-modern dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nome_usuario) ?>
                    <span class="badge bg-secondary ms-1"><?= ucfirst($perfil) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="index.php"><i class="fas fa-plus-circle me-2"></i> Nova Chamada</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/escola/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Rápidas -->
    <div class="row mb-4 g-3" id="statsContainer" data-aos="fade-up" data-aos-delay="100">
        <div class="col-6 col-md-3">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Chamadas</h6>
                            <h3 class="mb-0" id="totalChamadas">--</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Presenças</h6>
                            <h3 class="mb-0" id="totalPresencas">--</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stats-card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Média Presença</h6>
                            <h3 class="mb-0" id="mediaPresenca">--<small class="fs-6">%</small></h3>
                        </div>
                        <div class="stats-icon bg-dark bg-opacity-25">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Ofertas</h6>
                            <h3 class="mb-0" id="totalOfertas">--</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern card-header-gradient-primary">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filtros de Busca</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-church text-primary me-1"></i> Congregação
                    </label>
                    <select id="filtroCongregacao" class="form-select" <?= $perfil !== 'admin' ? 'disabled' : '' ?>>
                        <option value="">Todas as congregações</option>
                    </select>
                    <?php if ($perfil !== 'admin' && $congregacao_id): ?>
                        <input type="hidden" id="congregacaoHidden" value="<?= $congregacao_id ?>">
                        <small class="text-muted">Filtrado pela sua congregação</small>
                    <?php endif; ?>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-users text-primary me-1"></i> Classe
                    </label>
                    <select id="filtroClasse" class="form-select" disabled>
                        <option value="">Todas as classes</option>
                    </select>
                </div>
                
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar text-primary me-1"></i> Ano
                    </label>
                    <select id="filtroAno" class="form-select">
                        <?php
                        for ($ano = $anoAtual - 2; $ano <= $anoAtual + 1; $ano++) {
                            $selected = $ano == $anoAtual ? 'selected' : '';
                            echo "<option value=\"$ano\" $selected>$ano</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-chart-simple text-primary me-1"></i> Trimestre
                    </label>
                    <select id="filtroTrimestre" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= $trimestreAtual == 1 ? 'selected' : '' ?>>1º Trimestre</option>
                        <option value="2" <?= $trimestreAtual == 2 ? 'selected' : '' ?>>2º Trimestre</option>
                        <option value="3" <?= $trimestreAtual == 3 ? 'selected' : '' ?>>3º Trimestre</option>
                        <option value="4" <?= $trimestreAtual == 4 ? 'selected' : '' ?>>4º Trimestre</option>
                    </select>
                    <small class="text-muted">Ex: <?= $anoAtual ?>-T<?= $trimestreAtual ?></small>
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar-week text-primary me-1"></i> Período
                    </label>
                    <input type="date" id="filtroDataInicio" class="form-control mb-1" placeholder="Data inicial">
                    <input type="date" id="filtroDataFim" class="form-control" placeholder="Data final">
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button id="btnFiltrar" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-search me-2"></i> Filtrar
                    </button>
                    <button id="btnLimparFiltros" class="btn btn-modern btn-outline-secondary">
                        <i class="fas fa-eraser me-2"></i> Limpar
                    </button>
                    <button id="btnExportarCSV" class="btn btn-modern btn-modern-success">
                        <i class="fas fa-file-csv me-2"></i> Exportar CSV
                    </button>
                    <span id="loadingIndicator" class="ms-2 d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span> Carregando...
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern card-header-gradient-success d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fas fa-table me-2"></i> Chamadas Encontradas</span>
            <span id="resultCount" class="badge bg-light text-dark">0 registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaChamadas" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px">Data</th>
                            <th>Congregação</th>
                            <th>Classe</th>
                            <th>Professor</th>
                            <th class="text-center">Trimestre</th>
                            <th class="text-center"><i class="fas fa-user-check text-success"></i> Presentes</th>
                            <th class="text-center"><i class="fas fa-user-times text-danger"></i> Ausentes</th>
                            <th class="text-center"><i class="fas fa-user-clock text-warning"></i> Justif.</th>
                            <th class="text-end"><i class="fas fa-dollar-sign"></i> Oferta</th>
                            <th class="text-center" style="width: 130px">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaResultados">
                        <tr>
                            <td colspan="10" class="text-muted text-center py-5">
                                <i class="fas fa-search fa-3x mb-3 d-block text-secondary"></i>
                                <p class="mb-0">Utilize os filtros acima e clique em <strong>"Filtrar"</strong> para visualizar as chamadas.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação para exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="msgConfirmacaoExclusao">Tem certeza que deseja excluir esta chamada?</p>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Esta ação não pode ser desfeita. Todos os registros de presença serão perdidos.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmaExcluir" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalhes da Chamada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetalhesBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2">Carregando detalhes...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Inicializa AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });

    // Variáveis globais
    const USUARIO_PERFIL = '<?= $perfil ?>';
    const USUARIO_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    const USUARIO_ID = <?= (int)$usuario_id ?>;
    const BASE_URL = '/escola/controllers/chamada.php';
    const ANO_ATUAL = <?= $anoAtual ?>;
    const TRIMESTRE_ATUAL = <?= $trimestreAtual ?>;
    
    let dataTable = null;
    
    // Função auxiliar para obter trimestre formatado
    function getTrimestreParaFiltro() {
        const ano = document.getElementById('filtroAno')?.value;
        const trimestre = document.getElementById('filtroTrimestre')?.value;
        if (ano && trimestre) {
            return `${ano}-T${trimestre}`;
        }
        return null;
    }
    
    // Função para atualizar estatísticas
    function atualizarEstatisticas(dados) {
        if (!dados) return;
        
        const totalChamadas = dados.length || 0;
        let totalPresentes = 0;
        let totalAusentes = 0;
        let totalJustificados = 0;
        let totalOfertas = 0;
        
        dados.forEach(chamada => {
            totalPresentes += parseInt(chamada.total_presentes) || 0;
            totalAusentes += parseInt(chamada.total_ausentes) || 0;
            totalJustificados += parseInt(chamada.total_justificados) || 0;
            totalOfertas += parseFloat(chamada.oferta_classe) || 0;
        });
        
        const totalMarcacoes = totalPresentes + totalAusentes + totalJustificados;
        const mediaPresenca = totalMarcacoes > 0 ? ((totalPresentes / totalMarcacoes) * 100).toFixed(1) : 0;
        
        document.getElementById('totalChamadas').textContent = totalChamadas;
        document.getElementById('totalPresencas').textContent = totalPresentes;
        document.getElementById('mediaPresenca').innerHTML = mediaPresenca + '<small class="fs-6">%</small>';
        document.getElementById('totalOfertas').textContent = 'R$ ' + totalOfertas.toFixed(2);
    }
</script>
<script src="js/listar.js"></script>
</body>
</html>