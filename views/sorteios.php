<?php  
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Chamada e Sorteio';

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../functions/funcoes_chamadas.php';

$estatisticas = obterEstatisticasChamadasMensais($pdo);
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-gift me-3" style="color: var(--success);"></i>
                Sorteio de Brindes
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-random me-1"></i> Sorteio
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Realize sorteios entre os alunos presentes nas classes, selecionando os participantes
            </p>
        </div>
        <div>
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2 text-danger"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Card Principal - Sorteio -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-random me-2"></i> Realizar Sorteio
            </h5>
        </div>
        <div class="card-body p-4">
            <form id="formSorteio">
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label">
                            <i class="fas fa-church me-1 text-primary"></i> Congregação
                        </label>
                        <select class="form-select" id="sorteio_congregacao" required>
                            <option value="">Selecione a Congregação</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">
                            <i class="fas fa-chalkboard-user me-1 text-primary"></i> Classe
                        </label>
                        <select class="form-select" id="sorteio_classe" required disabled>
                            <option value="">Selecione a Classe</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">
                            <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                        </label>
                        <select class="form-select" id="sorteio_trimestre" required>
                            <option value="">Selecione o Trimestre</option>
                            <option value="1">1º Trimestre</option>
                            <option value="2">2º Trimestre</option>
                            <option value="3">3º Trimestre</option>
                            <option value="4">4º Trimestre</option>
                        </select>
                    </div>
                </div>
                
                <!-- Container de Alunos -->
                <div id="alunosContainer" style="display: none;">
                    <div class="select-all-toolbar mb-3">
                        <button type="button" id="btnSelecionarTodos" class="btn btn-modern btn-modern-primary btn-sm">
                            <i class="fas fa-check-circle me-1"></i> Selecionar Todos
                        </button>
                        <button type="button" id="btnDesmarcarTodos" class="btn btn-modern btn-outline-secondary btn-sm ms-2">
                            <i class="fas fa-times-circle me-1"></i> Desmarcar Todos
                        </button>
                        <span id="contadorSelecionados" class="badge-ebd badge-primary ms-2">0 selecionados</span>
                    </div>
                    
                    <div class="alunos-list-container">
                        <h6 class="mb-3 fw-semibold">
                            <i class="fas fa-users me-2 text-primary"></i> Alunos da Classe
                        </h6>
                        <div id="alunosLista" class="alunos-list border rounded-3 p-2"></div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" id="btnRealizarSorteio" class="btn btn-modern btn-modern-success btn-lg px-5">
                            <i class="fas fa-random me-2"></i> Realizar Sorteio
                        </button>
                    </div>
                </div>
                
                <!-- Resultado do Sorteio -->
                <div id="sorteioResultados" class="mt-4" style="display: none;">
                    <div class="winner-card text-center">
                        <div class="sorteio-animacao" id="animacaoSorteio"></div>
                        <div class="winner-info" id="resultadoSorteio">
                            <div class="winner-name" id="ganhadorNome"></div>
                            <div class="winner-details" id="ganhadorDetalhes"></div>
                        </div>
                        <div class="winner-celebration" style="display: none;">
                            <i class="fas fa-trophy fa-3x text-warning"></i>
                            <i class="fas fa-star fa-2x text-warning"></i>
                            <i class="fas fa-crown fa-3x text-warning"></i>
                        </div>
                    </div>
                    
                    <div class="info-card mt-3">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="fas fa-info-circle"></i>
                            <span>Foram considerados todos os alunos selecionados na lista acima.</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estatísticas do Mês -->
    <?php if (!empty($estatisticas)): ?>
    <div class="modern-card mt-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-chart-bar me-2"></i> Estatísticas do Mês
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-6 col-md-3 text-center">
                    <div class="stat-circle bg-primary bg-opacity-10">
                        <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $estatisticas['total_chamadas'] ?? 0 ?></h3>
                    <small class="text-muted">Chamadas Realizadas</small>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="stat-circle bg-success bg-opacity-10">
                        <i class="fas fa-user-check fa-2x text-success"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $estatisticas['total_presencas'] ?? 0 ?></h3>
                    <small class="text-muted">Presenças</small>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="stat-circle bg-warning bg-opacity-10">
                        <i class="fas fa-chart-line fa-2x text-warning"></i>
                    </div>
                    <h3 class="mt-2 mb-0"><?= $estatisticas['media_frequencia'] ?? 0 ?>%</h3>
                    <small class="text-muted">Média de Frequência</small>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="stat-circle bg-info bg-opacity-10">
                        <i class="fas fa-dollar-sign fa-2x text-info"></i>
                    </div>
                    <h3 class="mt-2 mb-0">R$ <?= number_format($estatisticas['total_ofertas'] ?? 0, 2, ',', '.') ?></h3>
                    <small class="text-muted">Ofertas do Mês</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Dica de Utilização -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="300">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-lightbulb fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Dica:</strong>
                <span>Selecione os alunos participantes do sorteio e clique no botão "Realizar Sorteio". O sistema escolherá um ganhador aleatoriamente entre os selecionados.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para o sorteio */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

.alunos-list {
    max-height: 400px;
    overflow-y: auto;
    background: white;
}

.aluno-item {
    cursor: pointer;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--gray-200);
    transition: all 0.2s ease;
    border-radius: 8px;
    margin: 0 2px;
}

.aluno-item:hover {
    background-color: var(--gray-50);
    transform: translateX(4px);
}

.aluno-item.selected {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 3px solid var(--primary-600);
}

.aluno-item .fa-user-check {
    color: var(--success);
    font-size: 1.1rem;
}

/* Select all toolbar */
.select-all-toolbar {
    background: var(--gray-50);
    padding: 0.75rem 1rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Winner card */
.winner-card {
    background: linear-gradient(135deg, var(--accent-50) 0%, white 100%);
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    border: 2px solid var(--accent-200);
    animation: winnerGlow 1s ease-in-out infinite alternate;
}

@keyframes winnerGlow {
    from { box-shadow: 0 0 10px rgba(245, 158, 11, 0.2); }
    to { box-shadow: 0 0 30px rgba(245, 158, 11, 0.6); }
}

.winner-name {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--warning);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.winner-details {
    font-size: 0.9rem;
    color: var(--gray-600);
}

.sorteio-animacao {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--warning);
    margin-bottom: 1rem;
}

/* Info card */
.info-card {
    background: var(--gray-50);
    border-radius: 12px;
    padding: 0.75rem 1rem;
}

/* Stat circle */
.stat-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

/* Animations */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.animate-pulse {
    animation: pulse 0.5s ease-in-out;
}

/* Responsividade */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .winner-name {
        font-size: 1.2rem;
    }
    
    .stat-circle {
        width: 55px;
        height: 55px;
    }
    
    .stat-circle i {
        font-size: 1.5rem !important;
    }
    
    .select-all-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .select-all-toolbar .btn {
        width: 100%;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Inicializar AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
    
    function exibirMensagem(tipo, titulo, mensagem) {
        Swal.fire({
            icon: tipo,
            title: titulo,
            text: mensagem,
            confirmButtonColor: tipo === 'success' ? '#10b981' : '#ef4444'
        });
    }

    function carregarCongregacoes() {
        $.ajax({
            url: '../controllers/chamada.php',
            type: 'POST',
            data: { acao: 'getCongregacoes' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Selecione a Congregação</option>';
                    response.data.forEach(c => {
                        options += `<option value="${c.id}">${c.nome}</option>`;
                    });
                    $('#sorteio_congregacao').html(options);
                } else {
                    exibirMensagem('error', 'Erro', 'Não foi possível carregar as congregações');
                }
            },
            error: function() {
                exibirMensagem('error', 'Erro', 'Erro ao carregar congregações');
            }
        });
    }

    $('#sorteio_congregacao').change(function() {
        const congregacaoId = $(this).val();
        $('#sorteio_classe').prop('disabled', true).html('<option value="">Selecione a Classe</option>');
        $('#alunosContainer').hide();
        $('#sorteioResultados').hide();
        
        if (congregacaoId) {
            $.ajax({
                url: '../controllers/chamada.php',
                type: 'POST',
                data: { 
                    acao: 'getClassesByCongregacao',
                    congregacao_id: congregacaoId 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let options = '<option value="">Selecione a Classe</option>';
                        response.data.forEach(classe => {
                            options += `<option value="${classe.id}">${classe.nome}</option>`;
                        });
                        $("#sorteio_classe").html(options).prop('disabled', false);
                    } else {
                        $("#sorteio_classe").html('<option value="">Nenhuma classe disponível</option>').prop('disabled', true);
                        exibirMensagem('info', 'Aviso', response.message || 'Nenhuma classe encontrada');
                    }
                },
                error: function() {
                    exibirMensagem('error', 'Erro', 'Erro ao carregar classes');
                }
            });
        }
    });

    $('#sorteio_classe, #sorteio_trimestre').change(function() {
        const classeId = $('#sorteio_classe').val();
        const congregacaoId = $('#sorteio_congregacao').val();
        const trimestre = $('#sorteio_trimestre').val();
        
        $('#alunosContainer').hide();
        $('#sorteioResultados').hide();
        
        if (!classeId || !congregacaoId || !trimestre) { return; }

        $.ajax({
            url: '../controllers/sorteio.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                acao: 'getAlunosParaSorteio',
                classe_id: classeId,
                congregacao_id: congregacaoId,
                trimestre: trimestre
            }),
            dataType: 'json',
            beforeSend: function() {
                $('#alunosLista').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Carregando alunos...</p></div>');
            },
            success: function(response) {
                if (response.status === 'success') {
                    if (response.data && response.data.length > 0) {
                        let alunosHtml = '';
                        response.data.forEach(aluno => {
                            alunosHtml += 
                                `<div class="aluno-item d-flex justify-content-between align-items-center" data-id="${aluno.id}">
                                    <div>
                                        <strong>${aluno.nome}</strong>
                                        <div class="small text-muted">${aluno.classe_nome || 'Sem classe'}</div>
                                    </div>
                                    <i class="fas fa-user-check fa-lg" style="display: none;"></i>
                                </div>`;
                        });
                        
                        $('#alunosLista').html(alunosHtml);
                        $('#alunosContainer').fadeIn();
                        
                        $('.aluno-item').click(function() {
                            $(this).toggleClass('selected');
                            $(this).find('.fa-user-check').toggle();
                            atualizarContadorSelecionados();
                        });
                        
                        atualizarContadorSelecionados();
                    } else {
                        $('#alunosLista').html(`<div class="alert alert-info m-3"><i class="fas fa-info-circle me-2"></i> ${response.message || 'Nenhum aluno encontrado com os critérios atuais'}</div>`);
                        $('#alunosContainer').fadeIn();
                    }
                } else {
                    $('#alunosLista').html(`<div class="alert alert-danger m-3"><i class="fas fa-exclamation-triangle me-2"></i> ${response.message || 'Erro ao carregar alunos'}</div>`);
                }
            },
            error: function() {
                $('#alunosLista').html(`<div class="alert alert-danger m-3"><i class="fas fa-exclamation-triangle me-2"></i> Erro ao carregar alunos</div>`);
            }
        });
    });
    
    function atualizarContadorSelecionados() {
        const totalSelecionados = $('.aluno-item.selected').length;
        $('#contadorSelecionados').text(totalSelecionados + ' selecionado' + (totalSelecionados !== 1 ? 's' : ''));
    }

    $('#btnSelecionarTodos').click(function() {
        $('.aluno-item').addClass('selected');
        $('.aluno-item .fa-user-check').fadeIn();
        atualizarContadorSelecionados();
    });

    $('#btnDesmarcarTodos').click(function() {
        $('.aluno-item').removeClass('selected');
        $('.aluno-item .fa-user-check').fadeOut();
        atualizarContadorSelecionados();
    });

    $('#btnRealizarSorteio').click(function() {
        const alunosSelecionados = $('.aluno-item.selected');
        
        if (alunosSelecionados.length === 0) {
            exibirMensagem('warning', 'Atenção', 'Selecione pelo menos um aluno para o sorteio');
            return;
        }

        const alunosIds = [];
        alunosSelecionados.each(function() { alunosIds.push($(this).data('id')); });

        const classeId = $('#sorteio_classe').val();
        const congregacaoId = $('#sorteio_congregacao').val();

        if (!classeId || !congregacaoId) {
            exibirMensagem('warning', 'Atenção', 'Selecione uma congregação e uma classe válidas');
            return;
        }

        $('#sorteioResultados').fadeIn();
        $('#resultadoSorteio').hide();
        $('#animacaoSorteio').html('<div class="animate-pulse">🎲 Sorteando... 🎲</div>');

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sorteando...');

        $.ajax({
            url: '../controllers/sorteio.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                acao: 'realizarSorteio',
                alunos_ids: alunosIds,
                classe_id: classeId,
                congregacao_id: congregacaoId
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const ganhador = response.data.ganhador;
                    $('#ganhadorNome').html(`<i class="fas fa-trophy me-2 text-warning"></i>${ganhador.nome}`);
                    $('#ganhadorDetalhes').html(`
                        <div class="mt-2">
                            <span class="badge-ebd badge-primary"><i class="fas fa-chalkboard-user me-1"></i> ${ganhador.classe_nome || 'Classe não informada'}</span>
                            <span class="badge-ebd badge-success ms-2"><i class="fas fa-calendar me-1"></i> ${response.data.data_sorteio}</span>
                        </div>
                        <p class="mt-3 mb-0"><i class="fas fa-star text-warning me-1"></i> Parabéns ao ganhador! <i class="fas fa-star text-warning ms-1"></i></p>
                    `);
                    $('#resultadoSorteio').fadeIn(800);
                    $('#animacaoSorteio').empty();
                    
                    // Efeito de confete (opcional)
                    Swal.fire({
                        icon: 'success',
                        title: 'Sorteio Realizado!',
                        html: `<strong class="text-warning">${ganhador.nome}</strong> foi o ganhador!`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    exibirMensagem('error', 'Erro', response.message || 'Erro desconhecido');
                    $('#sorteioResultados').fadeOut();
                }
            },
            error: function() {
                exibirMensagem('error', 'Erro', 'Erro ao conectar com o servidor');
                $('#sorteioResultados').fadeOut();
            },
            complete: function() { 
                $btn.prop('disabled', false).html('<i class="fas fa-random me-2"></i> Realizar Sorteio'); 
            }
        });
    });

    carregarCongregacoes();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>