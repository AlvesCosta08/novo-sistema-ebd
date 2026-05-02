// editar.js - Versão final corrigida

document.addEventListener('DOMContentLoaded', async function() {
    const formContainer = document.getElementById('formContainer');
    let classesDisponiveis = [];
    let confirmacaoModal = null;
    let sucessoModal = null;

    // Verificar se CHAMADA_ID está definido
    if (typeof CHAMADA_ID === 'undefined' || !CHAMADA_ID) {
        formContainer.innerHTML = `<div class="alert alert-danger m-4"><i class="fas fa-exclamation-triangle me-2"></i>ID da chamada não informado. <a href="listar.php">Voltar para lista</a></div>`;
        return;
    }

    // Inicializar modais com segurança
    const modalConfirmacaoEl = document.getElementById('modalConfirmacao');
    const modalSucessoEl = document.getElementById('modalSucesso');
    
    if (modalConfirmacaoEl) {
        confirmacaoModal = new bootstrap.Modal(modalConfirmacaoEl);
    }
    
    if (modalSucessoEl) {
        sucessoModal = new bootstrap.Modal(modalSucessoEl);
    }

    try {
        console.log('Carregando chamada ID:', CHAMADA_ID);
        console.log('BASE_URL:', BASE_URL);
        
        const res = await fetch(BASE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ acao: 'getChamada', chamada_id: CHAMADA_ID })
        });
        const data = await res.json();
        
        console.log('Resposta da API:', data);
        
        if (data.status !== 'success') {
            formContainer.innerHTML = `<div class="alert alert-danger m-4"><i class="fas fa-exclamation-triangle me-2"></i>${data.message || 'Não foi possível carregar a chamada'}</div>`;
            return;
        }
        
        const chamada = data.data;
        if (!chamada.alunos) chamada.alunos = [];
        
        await carregarClassesDisponiveis(chamada.congregacao_id);
        const html = montarFormulario(chamada);
        formContainer.innerHTML = html;
        
        // Configurar eventos após o DOM ser atualizado
        configurarEventos(chamada);

    } catch (e) {
        console.error('Erro ao carregar edição:', e);
        formContainer.innerHTML = `<div class="alert alert-danger m-4"><i class="fas fa-exclamation-triangle me-2"></i>Erro ao carregar dados da chamada. Tente novamente.<br><small>${e.message}</small></div>`;
    }

    function configurarEventos(chamada) {
        const btnSalvar = document.getElementById('btnSalvar');
        if (btnSalvar) {
            btnSalvar.addEventListener('click', () => {
                if (confirmacaoModal) {
                    // Remove listener antigo do botão de confirmação para evitar duplicação
                    const btnConfirmar = document.getElementById('btnConfirmarSalvar');
                    if (btnConfirmar) {
                        const newBtn = btnConfirmar.cloneNode(true);
                        btnConfirmar.parentNode.replaceChild(newBtn, btnConfirmar);
                        newBtn.addEventListener('click', () => {
                            salvarEdicao();
                        });
                    }
                    confirmacaoModal.show();
                } else {
                    salvarEdicao();
                }
            });
        }
        
        const btnMarcarTodos = document.getElementById('btnMarcarTodos');
        if (btnMarcarTodos) {
            btnMarcarTodos.addEventListener('click', () => {
                document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]').forEach(radio => radio.checked = true);
                showToast('Todos os alunos marcados como presentes.', 'info');
            });
        }
        
        const btnLimparTodos = document.getElementById('btnLimparTodos');
        if (btnLimparTodos) {
            btnLimparTodos.addEventListener('click', () => {
                document.querySelectorAll('#tabelaAlunos input[type="radio"]').forEach(radio => radio.checked = false);
                showToast('Todos os status foram limpos.', 'info');
            });
        }
        
        const classeSelect = document.getElementById('classeSelect');
        if (classeSelect && USUARIO_PERFIL === 'admin') {
            classeSelect.addEventListener('change', async function() {
                if (confirm('Alterar a classe recarregará a lista de alunos. Deseja continuar?')) {
                    const trimestre = document.getElementById('trimestre').value;
                    const congregacaoId = document.getElementById('congregacaoId').value;
                    await recarregarAlunosPorClasse(this.value, trimestre, congregacaoId);
                } else {
                    this.value = chamada.classe_id;
                }
            });
        }
        
        // Botão voltar do modal de sucesso
        const btnVoltar = document.getElementById('btnVoltarListagem');
        if (btnVoltar) {
            btnVoltar.addEventListener('click', () => {
                window.location.href = 'listar.php';
            });
        }
    }

    function showLoading() {
        let overlay = document.getElementById('globalLoading');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'globalLoading';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>';
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    function hideLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) overlay.style.display = 'none';
    }

    async function carregarClassesDisponiveis(congregacaoId) {
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'getClassesByCongregacao', congregacao_id: congregacaoId })
            });
            const data = await response.json();
            if (data.status === 'success' && data.data) {
                classesDisponiveis = data.data;
            }
        } catch (e) {
            console.error('Erro ao carregar classes:', e);
            classesDisponiveis = [];
        }
    }

    async function recarregarAlunosPorClasse(classeId, trimestre, congregacaoId) {
        if (!congregacaoId || !classeId) return;
        
        showLoading();
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    acao: 'getAlunosByClasse',
                    classe_id: parseInt(classeId),
                    congregacao_id: parseInt(congregacaoId),
                    trimestre: trimestre
                })
            });
            const data = await response.json();
            
            if (data.status === 'success' && data.data) {
                atualizarTabelaAlunos(data.data);
                showToast('Lista de alunos atualizada para a nova classe.', 'info');
            } else {
                showToast('Nenhum aluno encontrado para esta classe.', 'warning');
                atualizarTabelaAlunos([]);
            }
        } catch (e) {
            console.error('Erro ao recarregar alunos:', e);
            showToast('Erro ao recarregar alunos.', 'danger');
        } finally {
            hideLoading();
        }
    }

    function atualizarTabelaAlunos(alunos) {
        const tbody = document.getElementById('tabelaAlunos');
        if (!tbody) return;
        
        if (!alunos || alunos.length === 0) {
            tbody.innerHTML = `<td><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-users-slash fa-2x mb-2 d-block"></i>Nenhum aluno matriculado nesta classe.</td></tr>`;
            return;
        }
        
        let html = '';
        alunos.forEach((aluno, index) => {
            html += `
                <tr>
                    <td class="text-center"><span class="badge bg-secondary rounded-pill">${index + 1}</span></td>
                    <td><i class="fas fa-user-graduate text-primary me-2"></i>${escapeHtml(aluno.nome)}<input type="hidden" name="aluno_id" value="${aluno.id}"></td>
                    <td>
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
        tbody.innerHTML = html;
    }

    function montarFormulario(chamada) {
        let dataFormatada = chamada.data || '';
        
        let classeHtml = '';
        if (USUARIO_PERFIL === 'admin' && classesDisponiveis.length > 0) {
            classeHtml = `
                <label class="form-label fw-semibold"><i class="fas fa-users text-primary me-1"></i> Classe</label>
                <select id="classeSelect" class="form-select">
                    <option value="">Selecione uma classe...</option>
                    ${classesDisponiveis.map(c => `<option value="${c.id}" ${c.id == chamada.classe_id ? 'selected' : ''}>${escapeHtml(c.nome)}</option>`).join('')}
                </select>
                <small class="text-muted">Alterar a classe recarregará a lista de alunos.</small>
            `;
        } else {
            classeHtml = `
                <label class="form-label fw-semibold"><i class="fas fa-users text-primary me-1"></i> Classe</label>
                <input type="text" class="form-control bg-light" value="${escapeHtml(chamada.nome_classe || chamada.classe_id)}" readonly disabled>
                <input type="hidden" id="classeId" value="${chamada.classe_id}">
            `;
        }
        
        let alunosHtml = '';
        if (chamada.alunos && chamada.alunos.length > 0) {
            chamada.alunos.forEach((aluno, index) => {
                const alunoId = aluno.aluno_id || aluno.id;
                const presente = aluno.presente === 'presente' ? 'checked' : '';
                const ausente = aluno.presente === 'ausente' ? 'checked' : '';
                const justificado = aluno.presente === 'justificado' ? 'checked' : '';
                
                alunosHtml += `
                    <tr>
                        <td class="text-center"><span class="badge bg-secondary rounded-pill">${index + 1}</span></td>
                        <td><i class="fas fa-user-graduate text-primary me-2"></i>${escapeHtml(aluno.nome)}<input type="hidden" name="aluno_id" value="${alunoId}"></td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <label class="radio-option d-inline-flex align-items-center gap-1">
                                    <input type="radio" name="status_${alunoId}" value="presente" class="form-check-input" ${presente}>
                                    <span class="badge bg-success">Presente</span>
                                </label>
                                <label class="radio-option d-inline-flex align-items-center gap-1">
                                    <input type="radio" name="status_${alunoId}" value="ausente" class="form-check-input" ${ausente}>
                                    <span class="badge bg-danger">Ausente</span>
                                </label>
                                <label class="radio-option d-inline-flex align-items-center gap-1">
                                    <input type="radio" name="status_${alunoId}" value="justificado" class="form-check-input" ${justificado}>
                                    <span class="badge bg-warning text-dark">Justificado</span>
                                </label>
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            alunosHtml = `<tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-users-slash fa-2x mb-2 d-block"></i>Nenhum aluno registrado nesta chamada.</td></tr>`;
        }
        
        const podeEditarTrimestre = USUARIO_PERFIL === 'admin' ? '' : 'readonly disabled';
        
        return `
            <input type="hidden" id="chamadaId" value="${chamada.id}">
            <input type="hidden" id="congregacaoId" value="${chamada.congregacao_id}">
            
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fas fa-calendar-day text-primary me-1"></i> Data da Aula</label>
                    <input type="date" id="dataChamada" class="form-control" value="${dataFormatada}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fas fa-tag text-primary me-1"></i> Trimestre</label>
                    <input type="text" id="trimestre" class="form-control" value="${escapeHtml(chamada.trimestre || '')}" ${podeEditarTrimestre}>
                    <small class="text-muted">Formato: ANO-TRIMESTRE (ex: 2026-T2)</small>
                </div>
                <div class="col-md-4">
                    ${classeHtml}
                </div>
            </div>
            
            <hr>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-dollar-sign text-success me-1"></i> Oferta (R$)</label>
                    <input type="number" step="0.01" id="ofertaClasse" class="form-control" value="${chamada.oferta_classe || 0}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-user-plus text-info me-1"></i> Visitantes</label>
                    <input type="number" id="totalVisitantes" class="form-control" value="${chamada.total_visitantes || 0}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-book text-primary me-1"></i> Bíblias</label>
                    <input type="number" id="totalBiblias" class="form-control" value="${chamada.total_biblias || 0}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-magazine text-warning me-1"></i> Revistas</label>
                    <input type="number" id="totalRevistas" class="form-control" value="${chamada.total_revistas || 0}">
                </div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="mb-0"><i class="fas fa-users me-2 text-primary"></i> Lista de Presença (${chamada.alunos ? chamada.alunos.length : 0} alunos)</h6>
                <div class="d-flex gap-2">
                    <button type="button" id="btnMarcarTodos" class="btn btn-sm btn-outline-success"><i class="fas fa-check-double me-1"></i> Marcar Todos Presentes</button>
                    <button type="button" id="btnLimparTodos" class="btn btn-sm btn-outline-secondary"><i class="fas fa-undo-alt me-1"></i> Limpar Todos</button>
                </div>
            </div>
            
            <div class="table-wrapper border rounded">
                <table class="custom-table mb-0">
                    <thead><tr><th style="width: 60px">#</th><th>Aluno</th><th style="min-width: 250px">Status</th></tr></thead>
                    <tbody id="tabelaAlunos">${alunosHtml}</tbody>
                </table>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
                <a href="listar.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
                <button type="button" id="btnSalvar" class="btn btn-modern-primary btn-modern"><i class="fas fa-save me-2"></i> Atualizar Chamada</button>
                <span id="loadingSalvar" class="ms-2 d-none"><span class="spinner-border spinner-border-sm"></span> Salvando...</span>
            </div>
        `;
    }

    function coletarAlunos() {
        const alunos = [];
        const tbody = document.getElementById('tabelaAlunos');
        if (!tbody) return alunos;
        
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            const alunoIdInput = row.querySelector('input[name="aluno_id"]');
            if (!alunoIdInput) return;
            const alunoId = parseInt(alunoIdInput.value);
            const radioChecked = row.querySelector('input[type="radio"]:checked');
            if (radioChecked) {
                alunos.push({ id: alunoId, status: radioChecked.value });
            }
        });
        return alunos;
    }

    async function salvarEdicao() {
        const btnSalvar = document.getElementById('btnSalvar');
        const spinner = document.getElementById('loadingSalvar');
        
        if (btnSalvar) btnSalvar.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        
        const dataChamada = document.getElementById('dataChamada').value;
        if (!dataChamada) {
            showToast('A data da chamada é obrigatória.', 'danger');
            if (btnSalvar) btnSalvar.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (confirmacaoModal) confirmacaoModal.hide();
            return;
        }
        
        let trimestre = document.getElementById('trimestre').value;
        const classeSelect = document.getElementById('classeSelect');
        let classeId = classeSelect ? parseInt(classeSelect.value) : parseInt(document.getElementById('classeId')?.value || 0);
        
        if (!classeId) {
            showToast('Selecione uma classe válida.', 'danger');
            if (btnSalvar) btnSalvar.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (confirmacaoModal) confirmacaoModal.hide();
            return;
        }
        
        if (!trimestre.match(/^\d{4}-T[1-4]$/)) {
            if (trimestre.match(/^[1-4]$/)) {
                trimestre = `${new Date().getFullYear()}-T${trimestre}`;
            } else {
                showToast('Formato de trimestre inválido. Use ANO-TRIMESTRE (ex: 2026-T2)', 'warning');
                if (btnSalvar) btnSalvar.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (confirmacaoModal) confirmacaoModal.hide();
                return;
            }
        }
        
        const alunos = coletarAlunos();
        if (alunos.length === 0) {
            showToast('Nenhum aluno foi encontrado para salvar.', 'warning');
            if (btnSalvar) btnSalvar.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (confirmacaoModal) confirmacaoModal.hide();
            return;
        }
        
        const payload = {
            acao: 'atualizarChamada',
            chamada_id: CHAMADA_ID,
            data: dataChamada,
            trimestre: trimestre,
            classe: classeId,
            professor: USUARIO_ID,
            alunos: alunos,
            oferta_classe: parseFloat(document.getElementById('ofertaClasse')?.value) || 0,
            total_visitantes: parseInt(document.getElementById('totalVisitantes')?.value) || 0,
            total_biblias: parseInt(document.getElementById('totalBiblias')?.value) || 0,
            total_revistas: parseInt(document.getElementById('totalRevistas')?.value) || 0
        };
        
        console.log('Enviando payload:', payload);
        
        try {
            const res = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            
            console.log('Resposta do servidor:', result);
            
            if (confirmacaoModal) confirmacaoModal.hide();
            
            if (result.status === 'success') {
                const sucessoMensagem = document.getElementById('sucessoMensagem');
                if (sucessoMensagem) sucessoMensagem.textContent = result.message || 'Chamada atualizada com sucesso!';
                if (sucessoModal) sucessoModal.show();
                
                setTimeout(() => {
                    window.location.href = 'listar.php';
                }, 2000);
            } else {
                showToast(result.message || 'Erro ao atualizar chamada.', 'danger');
            }
        } catch (e) {
            console.error('Erro ao salvar:', e);
            showToast('Erro de conexão ao salvar. Tente novamente.', 'danger');
        } finally {
            if (btnSalvar) btnSalvar.disabled = false;
            if (spinner) spinner.classList.add('d-none');
        }
    }

    function showToast(message, type = 'success') {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '250px';
        toastEl.style.marginBottom = '10px';
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body"><i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i> ${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});