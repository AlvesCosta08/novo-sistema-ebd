// chamada.js - Lógica do frontend de chamadas (versão final)
document.addEventListener('DOMContentLoaded', function() {
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

    if (dataChamada && !dataChamada.value) dataChamada.value = new Date().toISOString().split('T')[0];

    carregarCongregacoes();

    if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID && congregacaoSelect) {
        congregacaoSelect.value = USUARIO_CONGR_ID;
        carregarClasses(USUARIO_CONGR_ID);
    }

    if (congregacaoSelect) {
        congregacaoSelect.addEventListener('change', function() {
            this.value ? carregarClasses(this.value) : limparClasses();
        });
    }

    const anoSelect = document.getElementById('anoSelect');
    const trimestreSelect = document.getElementById('trimestreSelect');
    if (anoSelect) anoSelect.addEventListener('change', () => { if (congregacaoSelect?.value && classeSelect?.value) carregarAlunos(); });
    if (trimestreSelect) trimestreSelect.addEventListener('change', () => { if (congregacaoSelect?.value && classeSelect?.value) carregarAlunos(); });

    if (btnCarregarAlunos) btnCarregarAlunos.addEventListener('click', carregarAlunos);
    if (btnVerificarChamada) btnVerificarChamada.addEventListener('click', verificarChamadaExistente);
    if (btnSalvarChamada) btnSalvarChamada.addEventListener('click', salvarChamada);

    const btnSelectAll = document.getElementById('btnSelectAllPresentes');
    const btnClear = document.getElementById('btnClearAll');
    if (btnSelectAll) btnSelectAll.addEventListener('click', marcarTodosPresentes);
    if (btnClear) btnClear.addEventListener('click', limparTodosStatus);

    function getTrimestreFormatado() {
        const ano = document.getElementById('anoSelect')?.value || ANO_ATUAL;
        const trim = document.getElementById('trimestreSelect')?.value || TRIMESTRE_ATUAL;
        return `${ano}-T${trim}`;
    }

    function showLoading() {
        let overlay = document.getElementById('globalLoading');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'globalLoading';
            overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>';
            overlay.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:9999;';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    function hideLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) overlay.style.display = 'none';
    }

    function exibirAlerta(mensagem, tipo) {
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
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2 fa-lg"></i>
                <span class="flex-grow-1">${mensagem}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        container.appendChild(alerta);
        setTimeout(() => alerta.remove(), 5000);
    }

    async function carregarCongregacoes() {
        try {
            const res = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'getCongregacoes' })
            });
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data) && congregacaoSelect) {
                congregacaoSelect.innerHTML = '<option value="">Selecione uma congregação...</option>';
                data.data.forEach(cong => {
                    const opt = document.createElement('option');
                    opt.value = cong.id;
                    opt.textContent = cong.nome;
                    if (USUARIO_PERFIL !== 'admin' && cong.id == USUARIO_CONGR_ID) opt.selected = true;
                    congregacaoSelect.appendChild(opt);
                });
                if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) carregarClasses(USUARIO_CONGR_ID);
            } else exibirAlerta('Erro ao carregar congregações.', 'danger');
        } catch (err) { exibirAlerta('Falha na comunicação.', 'danger'); console.error(err); }
    }

    async function carregarClasses(congregacaoId) {
        if (!classeSelect) return;
        classeSelect.disabled = true;
        classeSelect.innerHTML = '<option value="">Carregando...</option>';
        try {
            const res = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'getClassesByCongregacao', congregacao_id: parseInt(congregacaoId) })
            });
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data)) {
                if (data.data.length === 0) classeSelect.innerHTML = '<option value="">Nenhuma classe encontrada</option>';
                else {
                    classeSelect.innerHTML = '<option value="">Selecione uma classe...</option>';
                    data.data.forEach(cls => {
                        const opt = document.createElement('option');
                        opt.value = cls.id;
                        opt.textContent = cls.nome;
                        classeSelect.appendChild(opt);
                    });
                }
                classeSelect.disabled = false;
                limparAlunos();
            } else exibirAlerta('Erro ao carregar classes.', 'danger');
        } catch (err) { exibirAlerta('Falha na comunicação.', 'danger'); console.error(err); }
    }

    function limparClasses() {
        if (classeSelect) {
            classeSelect.innerHTML = '<option value="">Selecione uma congregação primeiro</option>';
            classeSelect.disabled = true;
        }
        limparAlunos();
    }

    function limparAlunos() {
        if (tabelaAlunos) tabelaAlunos.innerHTML = `<tr><td colspan="3" class="text-center py-5"><i class="fas fa-users-slash fa-3x mb-3 d-block"></i><p class="text-muted">Nenhum aluno carregado.<br>Selecione uma classe e clique em "Carregar Alunos".</p></td></tr>`;
        if (containerAlunos) containerAlunos.classList.add('d-none');
        if (containerTotais) containerTotais.classList.add('d-none');
    }

    async function carregarAlunos() {
        const congId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const trimestre = getTrimestreFormatado();
        if (!congId || !classeId) { exibirAlerta('Selecione congregação e classe.', 'warning'); return; }
        if (loadingAlunosSpinner) loadingAlunosSpinner.classList.remove('d-none');
        if (btnCarregarAlunos) btnCarregarAlunos.disabled = true;
        limparAlunos();
        try {
            const res = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'getAlunosByClasse', classe_id: parseInt(classeId), congregacao_id: parseInt(congId), trimestre })
            });
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data)) {
                if (data.data.length === 0) {
                    exibirAlerta('Nenhum aluno matriculado neste período.', 'info');
                    if (tabelaAlunos) tabelaAlunos.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-users-slash fa-2x mb-2"></i>Nenhum aluno matriculado.</td></tr>`;
                    if (containerAlunos) containerAlunos.classList.remove('d-none');
                } else {
                    montarTabelaAlunos(data.data);
                    if (containerAlunos) containerAlunos.classList.remove('d-none');
                    if (containerTotais) containerTotais.classList.remove('d-none');
                }
            } else exibirAlerta('Erro ao buscar alunos.', 'danger');
        } catch (err) { exibirAlerta('Falha na comunicação.', 'danger'); console.error(err); }
        finally {
            if (loadingAlunosSpinner) loadingAlunosSpinner.classList.add('d-none');
            if (btnCarregarAlunos) btnCarregarAlunos.disabled = false;
        }
    }

    function montarTabelaAlunos(alunos) {
        if (!tabelaAlunos) return;
        tabelaAlunos.innerHTML = '';
        alunos.forEach((aluno, idx) => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--gray-200)';
            tr.innerHTML = `
                <td class="text-center" style="width:50px; vertical-align:middle;"><span class="badge bg-secondary rounded-pill">${idx+1}</span></td>
                <td style="vertical-align:middle;"><i class="fas fa-user-graduate text-primary me-2"></i><span class="fw-medium">${escapeHtml(aluno.nome)}</span></td>
                <td style="vertical-align:middle;">
                    <div class="d-flex gap-2 flex-wrap">
                        <label class="radio-option d-inline-flex align-items-center gap-1"><input type="radio" name="status_${aluno.id}" value="presente" class="form-check-input" checked><span class="badge bg-success">Presente</span></label>
                        <label class="radio-option d-inline-flex align-items-center gap-1"><input type="radio" name="status_${aluno.id}" value="ausente" class="form-check-input"><span class="badge bg-danger">Ausente</span></label>
                        <label class="radio-option d-inline-flex align-items-center gap-1"><input type="radio" name="status_${aluno.id}" value="justificado" class="form-check-input"><span class="badge bg-warning text-dark">Justificado</span></label>
                    </div>
                </td>
            `;
            tabelaAlunos.appendChild(tr);
        });
    }

    function marcarTodosPresentes() {
        document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]').forEach(r => r.checked = true);
        exibirAlerta('Todos marcados como presentes.', 'success');
    }

    function limparTodosStatus() {
        document.querySelectorAll('#tabelaAlunos input[type="radio"]').forEach(r => r.checked = false);
        exibirAlerta('Status limpos.', 'info');
    }

    function coletarDadosAlunos() {
        const alunos = [];
        const ids = new Set();
        document.querySelectorAll('#tabelaAlunos tr').forEach(row => {
            const radio = row.querySelector('input[type="radio"]:checked');
            if (radio) {
                const id = radio.name.split('_')[1];
                if (id && !ids.has(id)) {
                    ids.add(id);
                    alunos.push({ id: parseInt(id), status: radio.value });
                }
            }
        });
        return alunos;
    }

    // ==================== FUNÇÃO CORRIGIDA (REDIRECIONAMENTO FUNCIONAL) ====================
    async function verificarChamadaExistente() {
        const congregacaoId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const data = dataChamada?.value;
        if (!congregacaoId || !classeId || !data) {
            exibirAlerta('Preencha todos os campos (congregação, classe e data).', 'warning');
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
                    classe_id: parseInt(classeId),
                    congregacao_id: parseInt(congregacaoId)
                })
            });
            const result = await response.json();
            console.log('Resposta raw:', result);

            const alertDiv = document.getElementById('chamadaExistenteAlert');
            const msgSpan = document.getElementById('chamadaExistenteMsg');
            const btnEditar = document.getElementById('btnEditarExistente');

            if (result.status === 'success' && result.data?.existe === true) {
                const dataFormatada = formatarData(data);
                // EXTRAÇÃO ROBUSTA DO ID
                let chamadaId = null;
                if (result.data.chamada?.id) chamadaId = result.data.chamada.id;
                else if (result.data.id) chamadaId = result.data.id;
                else if (result.chamada?.id) chamadaId = result.chamada.id;
                chamadaId = parseInt(chamadaId);
                console.log('ID extraído (verificado):', chamadaId);

                if (isNaN(chamadaId) || chamadaId <= 0) {
                    console.error('ID inválido. Objeto chamada:', result.data.chamada);
                    exibirAlerta('ID da chamada inválido. Contate o administrador.', 'danger');
                    msgSpan.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> Já existe chamada para ${dataFormatada}, mas erro no ID.`;
                    alertDiv.classList.remove('d-none');
                    if (btnEditar) btnEditar.style.display = 'none';
                    hideLoading();
                    return;
                }

                msgSpan.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> Já existe uma chamada para ${dataFormatada} nesta classe.`;
                alertDiv.classList.remove('d-none');
                if (btnEditar) {
                    btnEditar.style.display = 'inline-flex';
                    const editUrl = `editar.php?id=${chamadaId}`;
                    btnEditar.onclick = (e) => {
                        e.preventDefault();
                        window.location.href = editUrl;
                    };
                }
            } 
            else if (result.status === 'success' && result.data?.existe === false) {
                msgSpan.innerHTML = `<i class="fas fa-check-circle me-2"></i> Nenhuma chamada encontrada para esta data. Pode registrar nova.`;
                alertDiv.classList.remove('d-none');
                if (btnEditar) btnEditar.style.display = 'none';
                setTimeout(() => alertDiv.classList.add('d-none'), 3000);
            } 
            else {
                exibirAlerta('Erro na verificação: ' + (result.message || 'Resposta inesperada'), 'danger');
            }
        } catch (error) {
            console.error('Erro na verificação:', error);
            exibirAlerta('Erro de conexão.', 'danger');
        } finally {
            hideLoading();
        }
    }

    function formatarData(dataISO) {
        if (!dataISO) return '';
        const [a, m, d] = dataISO.split('-');
        return `${d}/${m}/${a}`;
    }

    async function salvarChamada() {
        const congId = congregacaoSelect?.value;
        const classeId = classeSelect?.value;
        const trimestre = getTrimestreFormatado();
        const data = dataChamada?.value;
        const alunos = coletarDadosAlunos();
        if (!congId || !classeId || !data) { exibirAlerta('Preencha todos os campos obrigatórios.', 'warning'); return; }
        const tabelaBody = document.getElementById('tabelaAlunos');
        const temAlunos = tabelaBody && tabelaBody.querySelectorAll('tr').length > 0 && !tabelaBody.querySelector('td[colspan]');
        if (!temAlunos || alunos.length === 0) { exibirAlerta('Nenhum aluno carregado ou selecionado.', 'warning'); return; }

        const payload = {
            acao: 'salvarChamada',
            data, trimestre,
            classe: parseInt(classeId),
            professor: parseInt(professorId),
            alunos,
            oferta_classe: parseFloat(document.getElementById('ofertaClasse')?.value || 0),
            total_visitantes: parseInt(document.getElementById('totalVisitantes')?.value || 0),
            total_biblias: parseInt(document.getElementById('totalBiblias')?.value || 0),
            total_revistas: parseInt(document.getElementById('totalRevistas')?.value || 0)
        };

        if (loadingSalvarSpinner) loadingSalvarSpinner.classList.remove('d-none');
        if (btnSalvarChamada) btnSalvarChamada.disabled = true;

        try {
            const res = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                exibirAlerta(result.message || 'Chamada salva!', 'success');
                // limpa ofertas, visitantes etc.
                const oferta = document.getElementById('ofertaClasse');
                const visit = document.getElementById('totalVisitantes');
                const biblias = document.getElementById('totalBiblias');
                const revistas = document.getElementById('totalRevistas');
                if (oferta) oferta.value = '0.00';
                if (visit) visit.value = '0';
                if (biblias) biblias.value = '0';
                if (revistas) revistas.value = '0';
                setTimeout(() => {
                    if (confirm('✅ Chamada salva!\n\nDeseja registrar outra para esta mesma turma?')) {
                        document.querySelectorAll('#tabelaAlunos input[type="radio"][value="presente"]').forEach(r => r.checked = true);
                        if (dataChamada) dataChamada.value = new Date().toISOString().split('T')[0];
                        const alertDiv = document.getElementById('chamadaExistenteAlert');
                        if (alertDiv) alertDiv.classList.add('d-none');
                    } else window.location.href = 'listar.php';
                }, 500);
            } else exibirAlerta('Erro: ' + (result.message || 'Falha ao salvar.'), 'danger');
        } catch (err) { exibirAlerta('Erro de conexão.', 'danger'); console.error(err); }
        finally {
            if (loadingSalvarSpinner) loadingSalvarSpinner.classList.add('d-none');
            if (btnSalvarChamada) btnSalvarChamada.disabled = false;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});