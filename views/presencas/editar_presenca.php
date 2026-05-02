<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Editar Presença';

// Obter ID da presença
$presencaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-user-edit me-3" style="color: var(--warning);"></i>
                Editar Presença
                <?php if ($presencaId): ?>
                    <span class="fs-4 text-muted">#<?= $presencaId ?></span>
                <?php endif; ?>
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
                            <i class="fas fa-user-check me-1"></i> Presenças
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-edit me-1"></i> Editar
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Altere os dados da presença do aluno na chamada selecionada
            </p>
        </div>
        <div>
            <a href="index.php" class="btn btn-modern btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Formulário de Edição -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-pencil-alt me-2"></i> Formulário de Edição
            </h5>
        </div>
        <div class="card-body p-4">
            <form id="formPresenca">
                <input type="hidden" name="id" id="id" value="<?= $presencaId ?>">
                <input type="hidden" name="acao" value="salvar">

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label for="chamada_id" class="form-label">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Data da Chamada <span class="text-danger">*</span>
                        </label>
                        <select name="chamada_id" id="chamada_id" class="form-select" required>
                            <option value="">Selecione uma chamada...</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione uma chamada.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="aluno_id" class="form-label">
                            <i class="fas fa-user-graduate text-primary me-2"></i>
                            Aluno <span class="text-danger">*</span>
                        </label>
                        <select name="aluno_id" id="aluno_id" class="form-select" required>
                            <option value="">Selecione um aluno...</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um aluno.</div>
                    </div>

                    <div class="col-12">
                        <label for="presente" class="form-label">
                            <i class="fas fa-circle text-primary me-2"></i>
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="presente" id="presente" class="form-select" required>
                            <option value="">Selecione o status...</option>
                            <option value="presente">✅ Presente</option>
                            <option value="ausente">❌ Ausente</option>
                            <option value="justificado">⏰ Justificado</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um status.</div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-modern" style="background: var(--warning); color: white;">
                            <i class="fas fa-save me-2"></i> Salvar Alterações
                        </button>
                        <a href="index.php" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Informações Adicionais (opcional) -->
    <?php if ($presencaId): ?>
    <div class="modern-card mt-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-info-circle me-2"></i> Informações
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="alert-ebd alert-info-ebd mb-0">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Dica:</strong> Ao editar uma presença, os relatórios e estatísticas serão automaticamente atualizados com as novas informações.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Container para Toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<style>
/* Estilos específicos para edição de presença */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Alertas personalizados */
.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 8px;
    padding: 1rem;
}

/* Selects estilizados */
.form-select:focus {
    border-color: var(--warning);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}

/* Responsividade */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .btn-modern {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const id = document.getElementById('id').value;
    
    // Função para exibir mensagem toast (fallback)
    function exibirMensagem(tipo, mensagem) {
        if (tipo === 'sucesso') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: mensagem,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: mensagem,
                confirmButtonColor: '#dc2626'
            });
        }
    }
    
    // Carregar selects
    function carregarSelects() {
        const formData = new FormData();
        formData.append("acao", "carregar_selects");
        
        fetch("presencas_helper.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.sucesso) {
                const chamadas = res.chamadas || [];
                const alunos = res.alunos || [];
                
                const chamadaSelect = document.getElementById("chamada_id");
                chamadaSelect.innerHTML = '<option value="">Selecione uma chamada...</option>';
                chamadas.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = c.nome;
                    chamadaSelect.appendChild(option);
                });
                
                const alunoSelect = document.getElementById("aluno_id");
                alunoSelect.innerHTML = '<option value="">Selecione um aluno...</option>';
                alunos.forEach(a => {
                    const option = document.createElement('option');
                    option.value = a.id;
                    option.textContent = a.nome;
                    alunoSelect.appendChild(option);
                });
                
                // Se for edição, carregar os dados da presença
                if (id) {
                    fetchPresenca(id);
                }
            } else {
                exibirMensagem('erro', res.mensagem || 'Erro ao carregar dados');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            exibirMensagem('erro', 'Erro ao carregar os dados');
        });
    }
    
    // Buscar dados da presença para edição
    function fetchPresenca(id) {
        const formData = new FormData();
        formData.append("acao", "buscar");
        formData.append("id", id);
        
        fetch("presencas_helper.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.sucesso && res.dados) {
                const presenca = res.dados;
                document.getElementById("chamada_id").value = presenca.chamada_id;
                document.getElementById("aluno_id").value = presenca.aluno_id;
                document.getElementById("presente").value = presenca.presente;
            } else {
                exibirMensagem('erro', res.mensagem || 'Erro ao carregar dados da presença');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            exibirMensagem('erro', 'Erro ao carregar presença');
        });
    }
    
    // Validação do formulário
    function validarFormulario() {
        let valido = true;
        const chamadaId = document.getElementById('chamada_id').value;
        const alunoId = document.getElementById('aluno_id').value;
        const presente = document.getElementById('presente').value;
        
        if (!chamadaId) {
            document.getElementById('chamada_id').classList.add('is-invalid');
            valido = false;
        } else {
            document.getElementById('chamada_id').classList.remove('is-invalid');
        }
        
        if (!alunoId) {
            document.getElementById('aluno_id').classList.add('is-invalid');
            valido = false;
        } else {
            document.getElementById('aluno_id').classList.remove('is-invalid');
        }
        
        if (!presente) {
            document.getElementById('presente').classList.add('is-invalid');
            valido = false;
        } else {
            document.getElementById('presente').classList.remove('is-invalid');
        }
        
        return valido;
    }
    
    // Submissão do formulário
    document.getElementById("formPresenca").addEventListener("submit", (e) => {
        e.preventDefault();
        
        if (!validarFormulario()) {
            exibirMensagem('erro', 'Por favor, preencha todos os campos obrigatórios.');
            return;
        }
        
        const formData = new FormData(e.target);
        
        // Mostrar loading
        Swal.fire({
            title: 'Salvando...',
            text: 'Por favor, aguarde.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch("presencas_helper.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.sucesso) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: res.mensagem || 'Presença atualizada com sucesso!',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.href = "index.php";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: res.mensagem || 'Erro ao salvar presença',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao comunicar com o servidor',
                confirmButtonColor: '#dc2626'
            });
        });
    });
    
    // Remover classe is-invalid ao digitar
    document.getElementById('chamada_id').addEventListener('change', function() {
        this.classList.remove('is-invalid');
    });
    
    document.getElementById('aluno_id').addEventListener('change', function() {
        this.classList.remove('is-invalid');
    });
    
    document.getElementById('presente').addEventListener('change', function() {
        this.classList.remove('is-invalid');
    });
    
    // Inicializar AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
    
    // Carregar dados
    carregarSelects();
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>