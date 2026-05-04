// views/chamada/js/chamada.js - VERSÃO CORRIGIDA

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== chamada.js iniciado ===');
    console.log('API_URL:', typeof API_URL !== 'undefined' ? API_URL : 'NÃO DEFINIDO');
    console.log('USUARIO_PERFIL:', typeof USUARIO_PERFIL !== 'undefined' ? USUARIO_PERFIL : 'NÃO DEFINIDO');

    // Verificar API_URL
    if (typeof API_URL === 'undefined' || !API_URL) {
        console.error('API_URL não definida! Verifique se as variáveis globais foram carregadas.');
        exibirAlerta('Erro de configuração: URL da API não definida. Contate o administrador.', 'danger');
        return;
    }

    // Elementos DOM
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
    const dataChamada = document.getElementById('dataChamada');
    const professorId = document.getElementById('professorId')?.value;
    const btnEditarExistente = document.getElementById('btnEditarExistente');

    let chamadaEditandoId = null;

    // Função para formatar trimestre
    function getTrimestreFormatado() {
        const ano = document.getElementById('anoSelect')?.value || new Date().getFullYear();
        const trim = document.getElementById('trimestreSelect')?.value || Math.ceil(new Date().getMonth() / 3) + 1;
        return `${ano}-T${trim}`;
    }

    function formatarDataBR(dataISO) {
        if (!dataISO) return '';
        const [ano, mes, dia] = dataISO.split('-');
        return `${dia}/${mes}/${ano}`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function exibirAlerta(mensagem, tipo = 'info') {
        let container = document.getElementById('alertContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'alertContainer';
            container.style.cssText = 'position:fixed; top:20px; right:20px; z-index:9999; max-width:400px;';
            document.body.appendChild(container);
        }
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.style.cssText = 'margin-bottom:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); border-radius:12px;';
        const icone = tipo === 'success' ? 'check-circle' : (tipo === 'danger' ? 'exclamation-circle' : 'info-circle');
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icone} me-2 fa-lg"></i>
                <span class="flex-grow-1">${mensagem}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        container.appendChild(alerta);
        setTimeout(() => alerta.remove(), 5000);
    }

    // Funções de API
    async function apiRequest(action, data = {}) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: action, ...data })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            if (result.status === 'error') {
                throw new Error(result.message || 'Erro desconhecido');
            }
            return result;
        } catch (err) {
            console.error(`Erro na API (${action}):`, err);
            throw err;
        }
    }

    // Carregar congregações
    async function carregarCongregacoes() {
        try {
            const result = await apiRequest('getCongregacoes');
            if (congregacaoSelect && result.data) {
                congregacaoSelect.innerHTML = '<option value="">Selecione uma congregação...</option>';
                result.data.forEach(cong => {
                    const option = document.createElement('option');
                    option.value = cong.id;
                    option.textContent = cong.nome;
                    if (typeof USUARIO_PERFIL !== 'undefined' && USUARIO_PERFIL !== 'admin' && 
                        typeof USUARIO_CONGR_ID !== 'undefined' && cong.id == USUARIO_CONGR_ID) {
                        option.selected = true;
                    }
                    congregacaoSelect.appendChild(option);
                });
                
                // Se for perfil não-admin e tem congregação, carregar classes automaticamente
                if (typeof USUARIO_PERFIL !== 'undefined' && USUARIO_PERFIL !== 'admin' && 
                    typeof USUARIO_CONGR_ID !== 'undefined' && USUARIO_CONGR_ID) {
                    await carregarClasses(USUARIO_CONGR_ID);
                }
            }
        } catch (err) {
            console.error('Erro ao carregar congregações:', err);
            exibirAlerta('Erro ao carregar congregações: ' + err.message, 'danger');
        }
    }

    // Carregar classes
    async function carregarClasses(congregacaoId) {
        if (!classeSelect) return;
        
        classeSelect.disabled = true;
        classeSelect.innerHTML = '<option value="">Carregando...</option>';
        
        try {
            const result = await apiRequest('getClassesByCongregacao', { congregacao_id: parseInt(congregacaoId) });
            
            if (result.data && result.data.length > 0) {
                classeSelect.innerHTML = '<option value="">Selecione uma classe...</option>';
                result.data.forEach(cls => {
                    const option = document.createElement('option');
                    option.value = cls.id;
                    option.textContent = cls.nome;
                    classeSelect.appendChild(option);
                });
                classeSelect.disabled = false;
            } else {
                classeSelect.innerHTML = '<option value="">Nenhuma classe encontrada</option>';
                exibirAlerta('Nenhuma classe encontrada para esta congregação.', 'warning');
            }
        } catch (err) {
            console.error('Erro ao carregar classes:', err);
            classeSelect.innerHTML = '<option value="">Erro ao carregar classes</option>';
            exibirAlerta('Erro ao carregar classes: ' + err.message, 'danger');
        }
    }

    // Limpar áreas
    function limparAlunos() {
        if (tabelaAlunos) {
            tabelaAlunos.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <i class="fas fa-users-slash fa-3x mb-3 d-block text-muted"></i>
                        <p class="text-muted">Nenhum aluno carregado.<br>Selecione uma classe e clique em "Carregar Alunos".</p>
                    </td>
                </tr>
            `;
        }
        if (containerAlunos) containerAlunos.classList.add('d-none');
        if (containerTotais) containerTotais.classList.add('d-none');
    }

    // Montar tabela de alunos
    function montarTabelaAlunos(alunos) {
        if (!tabelaAlunos) return;
        
        if (!alunos || alunos.length === 0) {
            tabelaAlunos.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <i class="fas fa-users-slash fa-3x mb-3 d-block text-muted"></i>
                        <p class="text-muted">Nenhum aluno matriculado nesta classe.<br>Verifique as matrículas.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        alunos.forEach((aluno, idx) => {
            html += `
                <tr data-aluno-id="${aluno.id}">
                    <td class="text-center" style="width:50px; vertical-align:middle;">
                        <span class="badge bg-secondary rounded-pill">${idx + 1}</span>
                    </td>
                    <td style="vertical-align:middle;">
                        <i class="fas fa-user-graduate text-primary me-2"></i>
                        <span class="fw-medium">${escapeHtml(aluno.nome)}</span>
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="d-flex gap-2 flex-wrap">
                            <label class="radio-option d-inline-flex align-items-center gap-1">
                                <input type="radio" name="status_${aluno.id}" value="presente" class="form-check-input" checked>
                                <span class="badge bg-success">Presente</span>
                            </label>
                            <label class="radio-option d-inline-flex align-items-center gap-1">
                                <input type="radio" name="status_${aluno.id}" value="ausente" class="form-check-input">
                                <span class="badge bg-danger">Ausente</span>
                            </label>
                            <label class="radio-option d-inline-flex align-items-center gap-1">
                                <input type="radio" name="status_${aluno.id}" value="justificado" class="form-check-input">
                                <span class="badge bg-warning text-dark">Justificado</span>
                            </label>
                        </div>
                    </td>
                </tr>
            `;
        });
        tabelaAlunos.innerHTML = html;
    }

    // Carregar alunos
    async function carregarAlunos() {
        const congId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        
        if (!congId || !classeId) {
            exibirAlerta('Selecione congregação e classe primeiro.', 'warning');
            return;
        }
        
        if (loadingAlunosSpinner) loadingAlunosSpinner.classList.remove('d-none');
        if (btnCarregarAlunos) btnCarregarAlunos.disabled = true;
        
        limparAlunos();
        
        try {
            const trimestre = getTrimestreFormatado();
            const result = await apiRequest('getAlunosByClasse', {
                classe_id: parseInt(classeId),
                congregacao_id: parseInt(congId),
                trimestre: trimestre
            });
            
            if (result.data && result.data.length > 0) {
                montarTabelaAlunos(result.data);
                if (containerAlunos) containerAlunos.classList.remove('d-none');
                if (containerTotais) containerTotais.classList.remove('d-none');
                exibirAlerta(`${result.data.length} alunos carregados com sucesso!`, 'success');
            } else {
                montarTabelaAlunos([]);
                if (containerAlunos) containerAlunos.classList.remove('d-none');
                exibirAlerta('Nenhum aluno encontrado para esta classe. Verifique as matrículas.', 'warning');
            }
        } catch (err) {
            console.error('Erro ao carregar alunos:', err);
            exibirAlerta('Erro ao carregar alunos: ' + err.message, 'danger');
        } finally {
            if (loadingAlunosSpinner) loadingAlunosSpinner.classList.add('d-none');
            if (btnCarregarAlunos) btnCarregarAlunos.disabled = false;
        }
    }

    function marcarTodosPresentes() {
        document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]').forEach(radio => radio.checked = true);
        exibirAlerta('Todos os alunos marcados como presentes.', 'success');
    }

    function limparTodosStatus() {
        document.querySelectorAll('#tabelaAlunos input[type="radio"]').forEach(radio => radio.checked = false);
        exibirAlerta('Todos os status foram limpos.', 'info');
    }

    function coletarDadosAlunos() {
        const alunos = [];
        const rows = document.querySelectorAll('#tabelaAlunos tr[data-aluno-id]');
        
        rows.forEach(row => {
            const alunoId = row.getAttribute('data-aluno-id');
            if (alunoId) {
                const selectedRadio = row.querySelector('input[type="radio"]:checked');
                if (selectedRadio) {
                    alunos.push({
                        id: parseInt(alunoId),
                        status: selectedRadio.value
                    });
                } else {
                    // Se nenhum radio selecionado, marca como ausente
                    alunos.push({
                        id: parseInt(alunoId),
                        status: 'ausente'
                    });
                }
            }
        });
        
        return alunos;
    }

    async function verificarChamadaExistente() {
        const congregacaoId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const data = dataChamada?.value;

        if (!congregacaoId || !classeId || !data) {
            exibirAlerta('Preencha congregação, classe e data.', 'warning');
            return;
        }

        try {
            const result = await apiRequest('verificarChamadaExistente', {
                data: data,
                classe_id: parseInt(classeId),
                congregacao_id: parseInt(congregacaoId)
            });

            const alertDiv = document.getElementById('chamadaExistenteAlert');
            const msgSpan = document.getElementById('chamadaExistenteMsg');

            if (result.data?.existe === true && result.data.chamada) {
                chamadaEditandoId = result.data.chamada.id;
                if (msgSpan) {
                    msgSpan.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> Já existe uma chamada para ${formatarDataBR(data)}. Clique em "Editar" para modificar.`;
                }
                if (alertDiv) alertDiv.classList.remove('d-none');
                exibirAlerta('Já existe uma chamada para esta data e classe!', 'warning');
            } else {
                chamadaEditandoId = null;
                if (msgSpan) {
                    msgSpan.innerHTML = `<i class="fas fa-check-circle me-2"></i> Nenhuma chamada encontrada para ${formatarDataBR(data)}. Você pode registrar uma nova.`;
                }
                if (alertDiv) alertDiv.classList.remove('d-none');
                setTimeout(() => {
                    if (alertDiv) alertDiv.classList.add('d-none');
                }, 3000);
            }
        } catch (err) {
            console.error('Erro ao verificar chamada:', err);
            exibirAlerta('Erro ao verificar chamada existente.', 'danger');
        }
    }

    function editarChamadaExistente() {
        if (!chamadaEditandoId) {
            exibirAlerta('Nenhuma chamada selecionada para edição.', 'warning');
            return;
        }
        window.location.href = `editar.php?id=${chamadaEditandoId}`;
    }

    async function salvarChamada() {
        const congId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const data = dataChamada?.value;
        
        if (!congId || !classeId || !data) {
            exibirAlerta('Preencha congregação, classe e data.', 'warning');
            return;
        }
        
        const alunos = coletarDadosAlunos();
        if (alunos.length === 0) {
            exibirAlerta('Nenhum aluno carregado. Clique em "Carregar Alunos" primeiro.', 'warning');
            return;
        }
        
        const trimestre = getTrimestreFormatado();
        
        const payload = {
            data: data,
            trimestre: trimestre,
            classe: parseInt(classeId),
            professor: typeof USUARIO_ID !== 'undefined' ? USUARIO_ID : 0,
            alunos: alunos,
            oferta_classe: parseFloat(document.getElementById('ofertaClasse')?.value || 0),
            total_visitantes: parseInt(document.getElementById('totalVisitantes')?.value || 0),
            total_biblias: parseInt(document.getElementById('totalBiblias')?.value || 0),
            total_revistas: parseInt(document.getElementById('totalRevistas')?.value || 0)
        };
        
        console.log('Enviando payload:', payload);
        
        if (loadingSalvarSpinner) loadingSalvarSpinner.classList.remove('d-none');
        if (btnSalvarChamada) btnSalvarChamada.disabled = true;
        
        try {
            let result;
            if (chamadaEditandoId) {
                result = await apiRequest('atualizarChamada', { chamada_id: chamadaEditandoId, ...payload });
                exibirAlerta(result.message || 'Chamada atualizada com sucesso!', 'success');
            } else {
                result = await apiRequest('salvarChamada', payload);
                exibirAlerta(result.message || 'Chamada registrada com sucesso!', 'success');
            }
            
            // Limpar formulário após salvar
            document.getElementById('ofertaClasse').value = '0.00';
            document.getElementById('totalVisitantes').value = '0';
            document.getElementById('totalBiblias').value = '0';
            document.getElementById('totalRevistas').value = '0';
            chamadaEditandoId = null;
            document.getElementById('chamadaExistenteAlert')?.classList.add('d-none');
            
            setTimeout(() => {
                if (confirm('✅ Chamada salva!\n\nDeseja registrar outra para esta mesma turma?')) {
                    document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]').forEach(r => r.checked = true);
                    if (dataChamada) dataChamada.value = new Date().toISOString().split('T')[0];
                } else {
                    window.location.href = 'listar.php';
                }
            }, 500);
        } catch (err) {
            console.error('Erro ao salvar chamada:', err);
            exibirAlerta('Erro ao salvar: ' + err.message, 'danger');
        } finally {
            if (loadingSalvarSpinner) loadingSalvarSpinner.classList.add('d-none');
            if (btnSalvarChamada) btnSalvarChamada.disabled = false;
        }
    }

    // Event Listeners
    if (dataChamada && !dataChamada.value) {
        dataChamada.value = new Date().toISOString().split('T')[0];
    }

    carregarCongregacoes();

    if (congregacaoSelect) {
        congregacaoSelect.addEventListener('change', function() {
            if (this.value) {
                carregarClasses(this.value);
            } else {
                if (classeSelect) {
                    classeSelect.innerHTML = '<option value="">Selecione uma congregação primeiro</option>';
                    classeSelect.disabled = true;
                }
                limparAlunos();
            }
        });
    }

    if (btnCarregarAlunos) btnCarregarAlunos.addEventListener('click', carregarAlunos);
    if (btnVerificarChamada) btnVerificarChamada.addEventListener('click', verificarChamadaExistente);
    if (btnSalvarChamada) btnSalvarChamada.addEventListener('click', salvarChamada);
    if (btnEditarExistente) btnEditarExistente.addEventListener('click', editarChamadaExistente);

    const btnSelectAll = document.getElementById('btnSelectAllPresentes');
    const btnClearAll = document.getElementById('btnClearAll');
    if (btnSelectAll) btnSelectAll.addEventListener('click', marcarTodosPresentes);
    if (btnClearAll) btnClearAll.addEventListener('click', limparTodosStatus);

    console.log('=== chamada.js inicializado com sucesso ===');
});