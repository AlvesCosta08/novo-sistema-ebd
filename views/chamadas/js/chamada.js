// chamada.js - Lógica do frontend de chamadas (versão moderna)

document.addEventListener('DOMContentLoaded', function() {
    // Elementos da interface
    const congregacaoSelect = document.getElementById('congregacaoSelect');
    const classeSelect = document.getElementById('classeSelect');
    const btnCarregarAlunos = document.getElementById('btnCarregarAlunos');
    const btnVerificarChamada = document.getElementById('btnVerificarChamada');
    const loadingAlunosSpinner = document.getElementById('loadingAlunos');
    const containerAlunos = document.getElementById('containerAlunos');
    const containerTotais = document.getElementById('containerTotais');
    const tabelaAlunos = document.getElementById('tabelaAlunos');
    const btnSalvarChamada = document.getElementById('btnSalvarChamada');
    const loadingSalvarSpinner = document.getElementById('loadingSalvar');
    const alertContainer = document.getElementById('alertContainer');
    const dataChamada = document.getElementById('dataChamada');
    const professorId = document.getElementById('professorId').value;

    // Configura data atual como padrão
    if (dataChamada && !dataChamada.value) {
        dataChamada.value = new Date().toISOString().split('T')[0];
    }

    // Inicialização: carrega congregações
    carregarCongregacoes();

    // Se o usuário não for admin, define a congregação da sessão e carrega as classes
    if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) {
        if (congregacaoSelect) {
            congregacaoSelect.value = USUARIO_CONGR_ID;
            carregarClasses(USUARIO_CONGR_ID);
        }
    }

    // Eventos
    if (congregacaoSelect) {
        congregacaoSelect.addEventListener('change', function() {
            const id = this.value;
            if (id) {
                carregarClasses(id);
            } else {
                limparClasses();
            }
        });
    }

    // Carrega alunos também quando mudar ano/trimestre
    const anoSelect = document.getElementById('anoSelect');
    const trimestreSelect = document.getElementById('trimestreSelect');
    
    if (anoSelect) {
        anoSelect.addEventListener('change', function() {
            if (congregacaoSelect && congregacaoSelect.value && classeSelect && classeSelect.value) {
                carregarAlunos();
            }
        });
    }
    
    if (trimestreSelect) {
        trimestreSelect.addEventListener('change', function() {
            if (congregacaoSelect && congregacaoSelect.value && classeSelect && classeSelect.value) {
                carregarAlunos();
            }
        });
    }

    if (btnCarregarAlunos) {
        btnCarregarAlunos.addEventListener('click', carregarAlunos);
    }
    
    if (btnVerificarChamada) {
        btnVerificarChamada.addEventListener('click', verificarChamadaExistente);
    }
    
    if (btnSalvarChamada) {
        btnSalvarChamada.addEventListener('click', salvarChamada);
    }

    // Botões de ação rápida
    const btnSelectAllPresentes = document.getElementById('btnSelectAllPresentes');
    const btnClearAll = document.getElementById('btnClearAll');
    
    if (btnSelectAllPresentes) {
        btnSelectAllPresentes.addEventListener('click', marcarTodosPresentes);
    }
    
    if (btnClearAll) {
        btnClearAll.addEventListener('click', limparTodosStatus);
    }

    // Função para obter trimestre formatado
    function getTrimestreFormatado() {
        const ano = document.getElementById('anoSelect')?.value || ANO_ATUAL;
        const trimestre = document.getElementById('trimestreSelect')?.value || TRIMESTRE_ATUAL;
        return `${ano}-T${trimestre}`;
    }

    // Função para mostrar loading overlay
    function showLoading() {
        let overlay = document.getElementById('globalLoading');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'globalLoading';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner-custom"></div>';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    function hideLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    // Função para exibir alerta
    function exibirAlerta(mensagem, tipo) {
        if (!alertContainer) return;
        
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.setAttribute('role', 'alert');
        alerta.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 5000);
    }

    async function carregarCongregacoes() {
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'getCongregacoes' })
            });
            const data = await response.json();
            
            if (data.status === 'success' && Array.isArray(data.data) && congregacaoSelect) {
                congregacaoSelect.innerHTML = '<option value="">Selecione uma congregação...</option>';
                data.data.forEach(cong => {
                    const option = document.createElement('option');
                    option.value = cong.id;
                    option.textContent = cong.nome;
                    if (USUARIO_PERFIL !== 'admin' && cong.id == USUARIO_CONGR_ID) {
                        option.selected = true;
                    }
                    congregacaoSelect.appendChild(option);
                });
                
                if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) {
                    carregarClasses(USUARIO_CONGR_ID);
                }
            } else {
                exibirAlerta('Erro ao carregar congregações.', 'danger');
            }
        } catch (error) {
            exibirAlerta('Falha na comunicação com o servidor.', 'danger');
            console.error(error);
        }
    }

    async function carregarClasses(congregacaoId) {
        if (!classeSelect) return;
        
        classeSelect.disabled = true;
        classeSelect.innerHTML = '<option value="">Carregando...</option>';
        
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    acao: 'getClassesByCongregacao',
                    congregacao_id: parseInt(congregacaoId)
                })
            });
            const data = await response.json();
            
            if (data.status === 'success' && Array.isArray(data.data)) {
                if (data.data.length === 0) {
                    classeSelect.innerHTML = '<option value="">Nenhuma classe encontrada</option>';
                } else {
                    classeSelect.innerHTML = '<option value="">Selecione uma classe...</option>';
                    data.data.forEach(classe => {
                        const option = document.createElement('option');
                        option.value = classe.id;
                        option.textContent = classe.nome;
                        classeSelect.appendChild(option);
                    });
                }
                classeSelect.disabled = false;
                limparAlunos();
            } else {
                exibirAlerta('Erro ao carregar classes.', 'danger');
                classeSelect.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        } catch (error) {
            exibirAlerta('Falha na comunicação.', 'danger');
            classeSelect.innerHTML = '<option value="">Erro ao carregar</option>';
            console.error(error);
        }
    }

    function limparClasses() {
        if (classeSelect) {
            classeSelect.innerHTML = '<option value="">Selecione uma congregação primeiro</option>';
            classeSelect.disabled = true;
        }
        limparAlunos();
    }

    function limparAlunos() {
        if (tabelaAlunos) {
            tabelaAlunos.innerHTML = '';
        }
        if (containerAlunos) {
            containerAlunos.classList.add('d-none');
        }
        if (containerTotais) {
            containerTotais.classList.add('d-none');
        }
    }

    async function carregarAlunos() {
        const congregacaoId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const trimestreCompleto = getTrimestreFormatado();

        if (!congregacaoId || !classeId) {
            exibirAlerta('Selecione a congregação e a classe.', 'warning');
            return;
        }

        if (loadingAlunosSpinner) {
            loadingAlunosSpinner.classList.remove('d-none');
        }
        if (btnCarregarAlunos) {
            btnCarregarAlunos.disabled = true;
        }
        limparAlunos();

        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    acao: 'getAlunosByClasse',
                    classe_id: parseInt(classeId),
                    congregacao_id: parseInt(congregacaoId),
                    trimestre: trimestreCompleto
                })
            });
            const data = await response.json();
            
            if (data.status === 'success' && Array.isArray(data.data)) {
                if (data.data.length === 0) {
                    exibirAlerta('Nenhum aluno encontrado para esta classe no trimestre selecionado.', 'info');
                    if (tabelaAlunos) {
                        tabelaAlunos.innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                    Nenhum aluno matriculado neste período.
                                </td>
                            </tr>
                        `;
                    }
                    if (containerAlunos) {
                        containerAlunos.classList.remove('d-none');
                    }
                } else {
                    montarTabelaAlunos(data.data);
                    if (containerAlunos) {
                        containerAlunos.classList.remove('d-none');
                    }
                    if (containerTotais) {
                        containerTotais.classList.remove('d-none');
                    }
                }
            } else {
                exibirAlerta('Erro ao buscar alunos: ' + (data.message || 'Erro desconhecido'), 'danger');
            }
        } catch (error) {
            exibirAlerta('Falha na comunicação.', 'danger');
            console.error(error);
        } finally {
            if (loadingAlunosSpinner) {
                loadingAlunosSpinner.classList.add('d-none');
            }
            if (btnCarregarAlunos) {
                btnCarregarAlunos.disabled = false;
            }
        }
    }

    function montarTabelaAlunos(alunos) {
        if (!tabelaAlunos) return;
        
        tabelaAlunos.innerHTML = '';
        
        alunos.forEach((aluno, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center" style="width: 50px">
                    <span class="badge bg-secondary rounded-pill">${index + 1}</span>
                </td>
                <td>
                    <i class="fas fa-user-graduate text-primary me-2"></i>
                    ${escapeHtml(aluno.nome)}
                </td>
                <td>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="status_${aluno.id}" value="presente" class="form-check-input" checked>
                            <span class="badge badge-presente">Presente</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="status_${aluno.id}" value="ausente" class="form-check-input">
                            <span class="badge badge-ausente">Ausente</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="status_${aluno.id}" value="justificado" class="form-check-input">
                            <span class="badge badge-justificado">Justificado</span>
                        </label>
                    </div>
                </td>
            `;
            tabelaAlunos.appendChild(tr);
        });
    }

    function marcarTodosPresentes() {
        const radios = document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]');
        radios.forEach(radio => {
            radio.checked = true;
        });
        exibirAlerta('Todos os alunos marcados como presentes.', 'info');
    }

    function limparTodosStatus() {
        const radios = document.querySelectorAll('#tabelaAlunos input[type="radio"]');
        radios.forEach(radio => {
            radio.checked = false;
        });
        exibirAlerta('Todos os status foram limpos.', 'info');
    }

    function coletarDadosAlunos() {
        const alunos = [];
        const radios = document.querySelectorAll('#tabelaAlunos input[type="radio"]:checked');
        radios.forEach(radio => {
            const name = radio.name;
            const alunoId = name.split('_')[1];
            const status = radio.value;
            alunos.push({ id: parseInt(alunoId), status: status });
        });
        return alunos;
    }

    async function verificarChamadaExistente() {
        const congregacaoId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const data = dataChamada?.value;
        
        if (!congregacaoId || !classeId) {
            exibirAlerta('Selecione congregação e classe.', 'warning');
            return;
        }
        
        if (!data) {
            exibirAlerta('Informe a data da aula.', 'warning');
            return;
        }
        
        showLoading();
        
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    acao: 'verificarChamadaExistente',
                    data: data,
                    classe_id: parseInt(classeId)
                })
            });
            const result = await response.json();
            
            const alertDiv = document.getElementById('chamadaExistenteAlert');
            const msgSpan = document.getElementById('chamadaExistenteMsg');
            
            if (alertDiv && msgSpan) {
                if (result.status === 'success' && result.data?.existe) {
                    msgSpan.innerHTML = `Já existe uma chamada para esta data (${formatarData(data)}) na classe selecionada.`;
                    alertDiv.classList.remove('d-none');
                    
                    const btnEditar = document.getElementById('btnEditarExistente');
                    if (btnEditar) {
                        btnEditar.onclick = () => {
                            window.location.href = `editar.php?id=${result.data.chamada.id}`;
                        };
                    }
                } else if (result.status === 'success' && !result.data?.existe) {
                    msgSpan.innerHTML = `Nenhuma chamada encontrada para esta data. Você pode registrar uma nova.`;
                    alertDiv.classList.remove('d-none');
                    setTimeout(() => {
                        alertDiv.classList.add('d-none');
                    }, 3000);
                } else {
                    exibirAlerta('Erro ao verificar chamada: ' + (result.message || 'Erro desconhecido'), 'danger');
                }
            }
        } catch (error) {
            exibirAlerta('Erro ao verificar chamada existente.', 'danger');
            console.error(error);
        } finally {
            hideLoading();
        }
    }

    function formatarData(dataISO) {
        if (!dataISO) return '';
        const [ano, mes, dia] = dataISO.split('-');
        return `${dia}/${mes}/${ano}`;
    }

    async function salvarChamada() {
        const congregacaoId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const trimestreCompleto = getTrimestreFormatado();
        const data = dataChamada?.value;
        const alunos = coletarDadosAlunos();

        if (!congregacaoId || !classeId) {
            exibirAlerta('Selecione congregação e classe.', 'warning');
            return;
        }
        if (!data) {
            exibirAlerta('Informe a data da aula.', 'warning');
            return;
        }
        if (alunos.length === 0) {
            exibirAlerta('Nenhum aluno carregado. Carregue os alunos primeiro.', 'warning');
            return;
        }

        const ofertaClasse = document.getElementById('ofertaClasse')?.value || 0;
        const totalVisitantes = document.getElementById('totalVisitantes')?.value || 0;
        const totalBiblias = document.getElementById('totalBiblias')?.value || 0;
        const totalRevistas = document.getElementById('totalRevistas')?.value || 0;

        const payload = {
            acao: 'salvarChamada',
            data: data,
            trimestre: trimestreCompleto,
            classe: parseInt(classeId),
            professor: parseInt(professorId),
            alunos: alunos,
            oferta_classe: parseFloat(ofertaClasse),
            total_visitantes: parseInt(totalVisitantes),
            total_biblias: parseInt(totalBiblias),
            total_revistas: parseInt(totalRevistas)
        };

        if (loadingSalvarSpinner) {
            loadingSalvarSpinner.classList.remove('d-none');
        }
        if (btnSalvarChamada) {
            btnSalvarChamada.disabled = true;
        }

        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.status === 'success') {
                exibirAlerta(result.message || 'Chamada salva com sucesso!', 'success');
                
                // Limpa campos complementares
                const ofertaInput = document.getElementById('ofertaClasse');
                const visitantesInput = document.getElementById('totalVisitantes');
                const bibliasInput = document.getElementById('totalBiblias');
                const revistasInput = document.getElementById('totalRevistas');
                
                if (ofertaInput) ofertaInput.value = '0.00';
                if (visitantesInput) visitantesInput.value = '0';
                if (bibliasInput) bibliasInput.value = '0';
                if (revistasInput) revistasInput.value = '0';
                
                // Recarrega alunos para nova chamada
                setTimeout(() => {
                    if (confirm('Deseja registrar outra chamada para esta mesma turma?')) {
                        document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]').forEach(radio => {
                            radio.checked = true;
                        });
                        if (dataChamada) {
                            dataChamada.value = new Date().toISOString().split('T')[0];
                        }
                        // Esconde o alerta de chamada existente
                        const alertDiv = document.getElementById('chamadaExistenteAlert');
                        if (alertDiv) alertDiv.classList.add('d-none');
                    } else {
                        window.location.href = 'listar.php';
                    }
                }, 500);
            } else {
                exibirAlerta('Erro: ' + (result.message || 'Falha ao salvar chamada.'), 'danger');
            }
        } catch (error) {
            exibirAlerta('Erro de conexão ao salvar.', 'danger');
            console.error(error);
        } finally {
            if (loadingSalvarSpinner) {
                loadingSalvarSpinner.classList.add('d-none');
            }
            if (btnSalvarChamada) {
                btnSalvarChamada.disabled = false;
            }
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});