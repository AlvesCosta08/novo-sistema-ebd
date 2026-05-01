<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Registrar Chamada';

// Incluir header
require_once __DIR__ . '/../../includes/header.php';

// Recupera dados do usuário logado
$usuario_id       = $_SESSION['usuario_id'] ?? null;
$nome_usuario     = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil           = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id   = $_SESSION['congregacao_id'] ?? null;

// Verificar se a congregação está definida para não-admin
if (empty($congregacao_id) && $perfil !== 'admin') {
    die('Acesso não autorizado: congregação não definida para este usuário.');
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

<div class="main-container">
    <!-- Cabeçalho Moderno -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="text-white mb-2">
                <i class="fas fa-clipboard-list me-2"></i>
                Registro de Chamada
            </h1>
            <p class="text-white-50 mb-0">
                <i class="fas fa-church me-1"></i> Escola Bíblica Dominical
            </p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-light btn-modern dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nome_usuario) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text"><small><?= ucfirst($perfil) ?></small></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="listar.php"><i class="fas fa-history me-2"></i> Histórico</a></li>
                    <li><a class="dropdown-item text-danger" href="/escola/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Filtros da Aula
                </h5>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Formulário de filtros -->
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-church text-primary me-1"></i> Congregação
                    </label>
                    <select id="congregacaoSelect" class="form-select" <?= $perfil !== 'admin' ? 'disabled' : '' ?>>
                        <option value="">Selecione uma congregação...</option>
                    </select>
                    <?php if ($perfil !== 'admin' && $congregacao_id): ?>
                        <input type="hidden" id="congregacaoHidden" value="<?= $congregacao_id ?>">
                        <small class="text-muted">Filtrado pela sua congregação</small>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-users text-primary me-1"></i> Classe
                    </label>
                    <select id="classeSelect" class="form-select" disabled>
                        <option value="">Selecione uma classe primeiro</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar text-primary me-1"></i> Ano
                    </label>
                    <select id="anoSelect" class="form-select">
                        <?php
                        for ($ano = $anoAtual - 1; $ano <= $anoAtual + 1; $ano++) {
                            $selected = $ano == $anoAtual ? 'selected' : '';
                            echo "<option value=\"$ano\" $selected>$ano</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-chart-line text-primary me-1"></i> Trimestre
                    </label>
                    <select id="trimestreSelect" class="form-select">
                        <option value="1" <?= $trimestreAtual == 1 ? 'selected' : '' ?>>1º Trimestre</option>
                        <option value="2" <?= $trimestreAtual == 2 ? 'selected' : '' ?>>2º Trimestre</option>
                        <option value="3" <?= $trimestreAtual == 3 ? 'selected' : '' ?>>3º Trimestre</option>
                        <option value="4" <?= $trimestreAtual == 4 ? 'selected' : '' ?>>4º Trimestre</option>
                    </select>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="button" id="btnCarregarAlunos" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-users me-2"></i> Carregar Alunos
                    </button>
                    <button type="button" id="btnVerificarChamada" class="btn btn-modern btn-modern-primary bg-info border-0 ms-2">
                        <i class="fas fa-search me-2"></i> Verificar Chamada Existente
                    </button>
                    <span id="loadingAlunos" class="ms-2 d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span> Carregando...
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data da Chamada -->
    <div class="modern-card mt-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-secondary">
            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Informações da Aula</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar-day me-1 text-primary"></i> Data da Aula
                    </label>
                    <input type="date" id="dataChamada" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-tag me-1 text-primary"></i> Trimestre de Registro
                    </label>
                    <div class="alert alert-info mb-0 py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="trimestreFormatadoDisplay"><?= $anoAtual ?>-T<?= $trimestreAtual ?></span>
                        <small class="text-muted d-block">Formato salvo: ANO-TRIMESTRE</small>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-chalkboard-user me-1 text-primary"></i> Professor
                    </label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($nome_usuario) ?>" readonly disabled>
                    <input type="hidden" id="professorId" value="<?= $usuario_id ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta de chamada existente -->
    <div id="chamadaExistenteAlert" class="alert alert-warning d-none mt-3" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="chamadaExistenteMsg"></span>
            </div>
            <button type="button" id="btnEditarExistente" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i> Editar Chamada Existente
            </button>
        </div>
    </div>

    <!-- Tabela de Alunos -->
    <div id="containerAlunos" class="modern-card mt-4 d-none" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-success">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i> Alunos Matriculados
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" id="btnSelectAllPresentes" class="btn btn-light btn-sm">
                        <i class="fas fa-check-double text-success me-1"></i> Marcar Todos
                    </button>
                    <button type="button" id="btnClearAll" class="btn btn-light btn-sm">
                        <i class="fas fa-undo-alt text-warning me-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Nome do Aluno</th>
                            <th style="min-width: 200px">Status de Presença</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaAlunos">
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                Nenhum aluno carregado. Selecione uma classe e clique em "Carregar Alunos".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totais e Ofertas -->
    <div id="containerTotais" class="modern-card mt-4 d-none" data-aos="fade-up" data-aos-delay="400">
        <div class="card-header-modern bg-info">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Resumo da Aula</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign text-success me-1"></i> Oferta (R$)
                    </label>
                    <input type="number" step="0.01" min="0" id="ofertaClasse" class="form-control" value="0.00">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-user-plus text-info me-1"></i> Visitantes
                    </label>
                    <input type="number" min="0" id="totalVisitantes" class="form-control" value="0">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-book text-primary me-1"></i> Bíblias
                    </label>
                    <input type="number" min="0" id="totalBiblias" class="form-control" value="0">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-magazine text-warning me-1"></i> Revistas
                    </label>
                    <input type="number" min="0" id="totalRevistas" class="form-control" value="0">
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="button" id="btnSalvarChamada" class="btn btn-modern btn-modern-success w-100 w-md-auto">
                        <i class="fas fa-save me-2"></i> Salvar Chamada
                    </button>
                    <span id="loadingSalvar" class="ms-2 d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span> Salvando...
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button para Mobile -->
<button id="fabSalvar" class="fab-mobile" style="display: none;">
    <i class="fas fa-save fa-lg"></i>
</button>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Inicializa AOS animations
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

    // Função para obter trimestre formatado
    function getTrimestreFormatado() {
        const ano = document.getElementById('anoSelect')?.value || ANO_ATUAL;
        const trimestre = document.getElementById('trimestreSelect')?.value || TRIMESTRE_ATUAL;
        return `${ano}-T${trimestre}`;
    }

    // Atualiza display do trimestre
    document.addEventListener('DOMContentLoaded', function() {
        const anoSelect = document.getElementById('anoSelect');
        const trimestreSelect = document.getElementById('trimestreSelect');
        const trimestreDisplay = document.getElementById('trimestreFormatadoDisplay');
        
        if (anoSelect && trimestreSelect && trimestreDisplay) {
            const updateTrimestreDisplay = () => {
                trimestreDisplay.textContent = getTrimestreFormatado();
            };
            anoSelect.addEventListener('change', updateTrimestreDisplay);
            trimestreSelect.addEventListener('change', updateTrimestreDisplay);
        }
        
        // Show FAB only on mobile when totais container is visible
        const observer = new MutationObserver(function() {
            const totaisContainer = document.getElementById('containerTotais');
            const fab = document.getElementById('fabSalvar');
            if (totaisContainer && fab) {
                fab.style.display = totaisContainer.classList.contains('d-none') ? 'none' : 'flex';
            }
        });
        
        observer.observe(document.getElementById('containerTotais') || document.body, { 
            attributes: true, 
            attributeFilter: ['class'] 
        });
        
        // FAB click event
        const fabSalvar = document.getElementById('fabSalvar');
        if (fabSalvar) {
            fabSalvar.addEventListener('click', function() {
                const btnSalvar = document.getElementById('btnSalvarChamada');
                if (btnSalvar) btnSalvar.click();
            });
        }
    });
</script>
<script src="js/chamada.js"></script>
</body>
</html>