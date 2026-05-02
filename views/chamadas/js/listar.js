// listar.js - Listagem e gerenciamento de chamadas (versão corrigida)

document.addEventListener('DOMContentLoaded', function() {
    // Elementos da interface
    const filtroCongregacao = document.getElementById('filtroCongregacao');
    const filtroClasse = document.getElementById('filtroClasse');
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimparFiltros = document.getElementById('btnLimparFiltros');
    const btnExportarCSV = document.getElementById('btnExportarCSV');
    const tabelaResultados = document.getElementById('tabelaResultados');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const resultCount = document.getElementById('resultCount');
    
    // Modais
    let modalExcluir = null;
    let modalDetalhes = null;
    const modalExcluirEl = document.getElementById('modalExcluir');
    const modalDetalhesEl = document.getElementById('modalDetalhes');
    
    if (modalExcluirEl) modalExcluir = new bootstrap.Modal(modalExcluirEl);
    if (modalDetalhesEl) modalDetalhes = new bootstrap.Modal(modalDetalhesEl);
    
    const btnConfirmaExcluir = document.getElementById('btnConfirmaExcluir');
    let chamadaIdParaExcluir = null;
    let dadosChamadas = [];

    // Inicialização
    carregarCongregacoes();
    
    if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID && filtroCongregacao) {
        filtroCongregacao.value = USUARIO_CONGR_ID;
        carregarClasses(USUARIO_CONGR_ID);
    }

    // Eventos
    if (filtroCongregacao) {
        filtroCongregacao.addEventListener('change', function() {
            const id = this.value;
            if (id) {
                carregarClasses(id);
                if (filtroClasse) filtroClasse.value = '';
            } else {
                limparClasses();
            }
        });
    }

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', executarFiltro);
    }
    
    if (btnLimparFiltros) {
        btnLimparFiltros.addEventListener('click', limparFiltros);
    }
    
    if (btnExportarCSV) {
        btnExportarCSV.addEventListener('click', exportarCSV);
    }

    if (btnConfirmaExcluir) {
        btnConfirmaExcluir.addEventListener('click', function() {
            if (chamadaIdParaExcluir) {
                excluirChamada(chamadaIdParaExcluir);
            }
            if (modalExcluir) modalExcluir.hide();
        });
    }

    // Carrega dados iniciais
    executarFiltro();

    function showLoading() {
        if (loadingIndicator) loadingIndicator.classList.remove('d-none');
        if (tabelaResultados) {
            tabelaResultados.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="spinner-border text-primary mb-2" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="text-muted mb-0">Carregando chamadas...</p>
                    </td>
                </tr>
            `;
        }
    }

    function hideLoading() {
        if (loadingIndicator) loadingIndicator.classList.add('d-none');
    }

    function exibirToast(mensagem, tipo = 'success') {
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
        toastEl.className = `toast align-items-center text-white bg-${tipo} border-0 show`;
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '250px';
        toastEl.style.marginBottom = '10px';
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${mensagem}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toastEl);
        
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 300);
        }, 4000);
    }

    async function carregarCongregacoes() {
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'getCongregacoes' })
            });
            const data = await response.json();
            
            if (data.status === 'success' && filtroCongregacao) {
                filtroCongregacao.innerHTML = '<option value="">Todas as congregações</option>';
                if (Array.isArray(data.data)) {
                    data.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.nome;
                        if (USUARIO_PERFIL !== 'admin' && item.id == USUARIO_CONGR_ID) {
                            opt.selected = true;
                        }
                        filtroCongregacao.appendChild(opt);
                    });
                }
                
                if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) {
                    carregarClasses(USUARIO_CONGR_ID);
                }
            }
        } catch (e) {
            console.error('Erro ao carregar congregações:', e);
            exibirToast('Erro ao carregar congregações', 'danger');
        }
    }

    async function carregarClasses(congregacaoId) {
        if (!filtroClasse) return;
        
        filtroClasse.disabled = true;
        filtroClasse.innerHTML = '<option value="">Carregando...</option>';
        
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
            
            if (data.status === 'success' && data.data) {
                filtroClasse.innerHTML = '<option value="">Todas as classes</option>';
                if (Array.isArray(data.data)) {
                    data.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.nome;
                        filtroClasse.appendChild(opt);
                    });
                }
                filtroClasse.disabled = false;
            } else {
                filtroClasse.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        } catch (e) {
            console.error('Erro ao carregar classes:', e);
            filtroClasse.innerHTML = '<option value="">Erro ao carregar</option>';
        }
    }

    function limparClasses() {
        if (filtroClasse) {
            filtroClasse.innerHTML = '<option value="">Todas as classes</option>';
            filtroClasse.disabled = true;
        }
    }

    function limparFiltros() {
        if (filtroCongregacao && USUARIO_PERFIL === 'admin') filtroCongregacao.value = '';
        if (filtroClasse) limparClasses();
        
        const filtroAno = document.getElementById('filtroAno');
        const filtroTrimestre = document.getElementById('filtroTrimestre');
        const filtroDataInicio = document.getElementById('filtroDataInicio');
        const filtroDataFim = document.getElementById('filtroDataFim');
        
        if (filtroAno) filtroAno.value = ANO_ATUAL;
        if (filtroTrimestre) filtroTrimestre.value = '';
        if (filtroDataInicio) filtroDataInicio.value = '';
        if (filtroDataFim) filtroDataFim.value = '';
        
        executarFiltro();
    }

    async function executarFiltro() {
        showLoading();
        
        const payload = { acao: 'listarChamadas' };
        
        if (filtroCongregacao && filtroCongregacao.value) {
            payload.congregacao_id = parseInt(filtroCongregacao.value);
        } else if (USUARIO_PERFIL !== 'admin' && USUARIO_CONGR_ID) {
            payload.congregacao_id = parseInt(USUARIO_CONGR_ID);
        }
        
        if (filtroClasse && filtroClasse.value) {
            payload.classe_id = parseInt(filtroClasse.value);
        }
        
        const dataInicio = document.getElementById('filtroDataInicio');
        const dataFim = document.getElementById('filtroDataFim');
        
        if (dataInicio && dataInicio.value) payload.data_inicio = dataInicio.value;
        if (dataFim && dataFim.value) payload.data_fim = dataFim.value;
        
        const ano = document.getElementById('filtroAno');
        const trimestre = document.getElementById('filtroTrimestre');
        
        if (trimestre && trimestre.value) {
            payload.trimestre_numero = trimestre.value;
            if (ano && ano.value) {
                payload.ano = ano.value;
            }
        }

        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                dadosChamadas = data.data || [];
                preencherTabela(dadosChamadas);
                atualizarEstatisticas(dadosChamadas);
                if (resultCount) {
                    resultCount.textContent = `${dadosChamadas.length} registro${dadosChamadas.length !== 1 ? 's' : ''}`;
                }
            } else {
                if (tabelaResultados) {
                    tabelaResultados.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                                ${data.message || 'Erro ao carregar chamadas'}
                            </td>
                        </tr>
                    `;
                }
                exibirToast(data.message || 'Erro ao carregar chamadas', 'danger');
            }
        } catch (e) {
            console.error('Erro ao filtrar:', e);
            if (tabelaResultados) {
                tabelaResultados.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center text-danger py-5">
                            <i class="fas fa-wifi fa-2x mb-2 d-block"></i>
                            Erro de conexão com o servidor
                        </td>
                    </tr>
                `;
            }
            exibirToast('Erro de conexão com o servidor', 'danger');
        } finally {
            hideLoading();
        }
    }

    function preencherTabela(lista) {
        if (!tabelaResultados) return;
        
        if (!lista || lista.length === 0) {
            tabelaResultados.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3 d-block"></i>
                        <p class="mb-0">Nenhuma chamada encontrada para os filtros selecionados.</p>
                        <small>Altere os filtros ou cadastre uma nova chamada.</small>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        lista.forEach(chamada => {
            let dataFormatada = chamada.data;
            try {
                const [ano, mes, dia] = chamada.data.split('-');
                dataFormatada = `${dia}/${mes}/${ano}`;
            } catch(e) {}
            
            let trimestreExibicao = chamada.trimestre;
            if (chamada.trimestre && chamada.trimestre.match(/^\d{4}-T[1-4]$/)) {
                const [ano, t] = chamada.trimestre.split('-T');
                trimestreExibicao = `${ano} - ${t}º Trim.`;
            }
            
            html += `
                <tr>
                    <td><span class="badge bg-light text-dark">${escapeHtml(dataFormatada)}</span></td>
                    <td>${escapeHtml(chamada.nome_congregacao || '-')}</td>
                    <td><span class="badge bg-info bg-opacity-25 text-dark">${escapeHtml(chamada.nome_classe || '-')}</span></td>
                    <td>${escapeHtml(chamada.nome_professor || '-')}</td>
                    <td class="text-center"><span class="badge bg-secondary">${escapeHtml(trimestreExibicao)}</span></td>
                    <td class="text-center"><span class="badge bg-success">${chamada.total_presentes || 0}</span></td>
                    <td class="text-center"><span class="badge bg-danger">${chamada.total_ausentes || 0}</span></td>
                    <td class="text-center"><span class="badge bg-warning">${chamada.total_justificados || 0}</span></td>
                    <td class="text-end"><strong>R$ ${parseFloat(chamada.oferta_classe || 0).toFixed(2)}</strong></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="editar.php?id=${chamada.id}" class="btn btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-outline-danger btn-excluir" data-id="${chamada.id}" data-descricao="${escapeHtml(dataFormatada)} - ${escapeHtml(chamada.nome_classe)}" title="Excluir">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button class="btn btn-outline-info btn-detalhes" data-id="${chamada.id}" title="Detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tabelaResultados.innerHTML = html;
        
        // Eventos dos botões
        document.querySelectorAll('.btn-excluir').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                chamadaIdParaExcluir = this.dataset.id;
                const descricao = this.dataset.descricao;
                const msgConfirmacao = document.getElementById('msgConfirmacaoExclusao');
                if (msgConfirmacao) {
                    msgConfirmacao.textContent = `Tem certeza que deseja excluir a chamada de ${descricao}?`;
                }
                if (modalExcluir) modalExcluir.show();
            });
        });
        
        document.querySelectorAll('.btn-detalhes').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                carregarDetalhesChamada(id);
            });
        });
    }

    function atualizarEstatisticas(dados) {
        if (!dados || dados.length === 0) {
            const totalChamadas = document.getElementById('totalChamadas');
            const totalPresencas = document.getElementById('totalPresencas');
            const mediaPresenca = document.getElementById('mediaPresenca');
            const totalOfertas = document.getElementById('totalOfertas');
            
            if (totalChamadas) totalChamadas.textContent = '0';
            if (totalPresencas) totalPresencas.textContent = '0';
            if (mediaPresenca) mediaPresenca.innerHTML = '0<small class="fs-6">%</small>';
            if (totalOfertas) totalOfertas.textContent = 'R$ 0,00';
            return;
        }
        
        let totalPresentes = 0, totalAusentes = 0, totalJustificados = 0, totalOfertasValor = 0;
        
        dados.forEach(chamada => {
            totalPresentes += parseInt(chamada.total_presentes) || 0;
            totalAusentes += parseInt(chamada.total_ausentes) || 0;
            totalJustificados += parseInt(chamada.total_justificados) || 0;
            totalOfertasValor += parseFloat(chamada.oferta_classe) || 0;
        });
        
        const totalMarcacoes = totalPresentes + totalAusentes + totalJustificados;
        const mediaPresencaValor = totalMarcacoes > 0 ? ((totalPresentes / totalMarcacoes) * 100).toFixed(1) : 0;
        
        const totalChamadas = document.getElementById('totalChamadas');
        const totalPresencas = document.getElementById('totalPresencas');
        const mediaPresenca = document.getElementById('mediaPresenca');
        const totalOfertas = document.getElementById('totalOfertas');
        
        if (totalChamadas) totalChamadas.textContent = dados.length;
        if (totalPresencas) totalPresencas.textContent = totalPresentes;
        if (mediaPresenca) mediaPresenca.innerHTML = mediaPresencaValor + '<small class="fs-6">%</small>';
        if (totalOfertas) totalOfertas.textContent = 'R$ ' + totalOfertasValor.toFixed(2);
    }

    async function carregarDetalhesChamada(id) {
        const modalBody = document.getElementById('modalDetalhesBody');
        if (!modalBody) return;
        
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-info" role="status"></div>
                <p class="mt-2">Carregando detalhes...</p>
            </div>
        `;
        
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type':application/json' },
                body: JSON.stringify({ acao: 'getChamada', chamada_id: parseInt(id) })
            });
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                exibirDetalhesModal(result.data);
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${result.message || 'Erro ao carregar detalhes'}
                    </div>
                `;
            }
            if (modalDetalhes) modalDetalhes.show();
        } catch (e) {
            console.error('Erro ao carregar detalhes:', e);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-wifi me-2"></i>
                    Erro de conexão ao carregar detalhes
                </div>
            `;
            if (modalDetalhes) modalDetalhes.show();
        }
    }

    function exibirDetalhesModal(chamada) {
        const modalBody = document.getElementById('modalDetalhesBody');
        if (!modalBody) return;
        
        let alunosHtml = '';
        if (chamada.alunos && chamada.alunos.length > 0) {
            alunosHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Aluno</th><th>Status</th></tr></thead><tbody>';
            chamada.alunos.forEach(aluno => {
                let badgeClass = '', statusText = '';
                switch(aluno.presente) {
                    case 'presente':
                        badgeClass = 'bg-success';
                        statusText = 'Presente';
                        break;
                    case 'ausente':
                        badgeClass = 'bg-danger';
                        statusText = 'Ausente';
                        break;
                    case 'justificado':
                        badgeClass = 'bg-warning text-dark';
                        statusText = 'Justificado';
                        break;
                    default:
                        badgeClass = 'bg-secondary';
                        statusText = aluno.presente;
                }
                alunosHtml += `<tr><td>${escapeHtml(aluno.nome)}</td><td><span class="badge ${badgeClass}">${statusText}</span></td></tr>`;
            });
            alunosHtml += '</tbody></table></div>';
        } else {
            alunosHtml = '<p class="text-muted text-center">Nenhum aluno registrado nesta chamada.</p>';
        }
        
        let dataFormatada = chamada.data;
        try {
            const [ano, mes, dia] = chamada.data.split('-');
            dataFormatada = `${dia}/${mes}/${ano}`;
        } catch(e) {}
        
        let trimestreExibicao = chamada.trimestre;
        if (chamada.trimestre && chamada.trimestre.match(/^\d{4}-T[1-4]$/)) {
            const [ano, t] = chamada.trimestre.split('-T');
            trimestreExibicao = `${ano} - ${t}º Trimestre`;
        }
        
        modalBody.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-6"><strong><i class="fas fa-calendar me-1"></i> Data:</strong> ${dataFormatada}</div>
                <div class="col-md-6"><strong><i class="fas fa-tag me-1"></i> Trimestre:</strong> <span class="badge bg-secondary">${escapeHtml(trimestreExibicao)}</span></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><strong><i class="fas fa-church me-1"></i> Congregação:</strong> ${escapeHtml(chamada.nome_congregacao || '-')}</div>
                <div class="col-md-6"><strong><i class="fas fa-users me-1"></i> Classe:</strong> ${escapeHtml(chamada.nome_classe || '-')}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><strong><i class="fas fa-chalkboard-user me-1"></i> Professor:</strong> ${escapeHtml(chamada.nome_professor || '-')}</div>
                <div class="col-md-6"><strong><i class="fas fa-dollar-sign me-1"></i> Oferta:</strong> R$ ${parseFloat(chamada.oferta_classe || 0).toFixed(2)}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong><i class="fas fa-user-plus me-1"></i> Visitantes:</strong> ${chamada.total_visitantes || 0}</div>
                <div class="col-md-4"><strong><i class="fas fa-book me-1"></i> Bíblias:</strong> ${chamada.total_biblias || 0}</div>
                <div class="col-md-4"><strong><i class="fas fa-magazine me-1"></i> Revistas:</strong> ${chamada.total_revistas || 0}</div>
            </div>
            <hr>
            <h6 class="mb-3"><i class="fas fa-users me-2"></i> Lista de Presença (${chamada.alunos?.length || 0} alunos)</h6>
            ${alunosHtml}
        `;
    }

    async function excluirChamada(id) {
        try {
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'excluirChamada', chamada_id: parseInt(id) })
            });
            const result = await response.json();
            
            if (result.status === 'success') {
                exibirToast(result.message || 'Chamada excluída com sucesso!', 'success');
                executarFiltro();
            } else {
                exibirToast('Erro: ' + (result.message || 'Falha ao excluir chamada'), 'danger');
            }
        } catch (e) {
            console.error('Erro ao excluir:', e);
            exibirToast('Erro de conexão ao excluir chamada', 'danger');
        }
    }

    function exportarCSV() {
        if (!dadosChamadas || dadosChamadas.length === 0) {
            exibirToast('Nenhum dado para exportar.', 'warning');
            return;
        }
        
        const headers = ['Data', 'Congregação', 'Classe', 'Professor', 'Trimestre', 'Presentes', 'Ausentes', 'Justificados', 'Oferta'];
        const rows = dadosChamadas.map(chamada => {
            let dataFormatada = chamada.data;
            try {
                const [ano, mes, dia] = chamada.data.split('-');
                dataFormatada = `${dia}/${mes}/${ano}`;
            } catch(e) {}
            
            let trimestreExibicao = chamada.trimestre;
            if (chamada.trimestre && chamada.trimestre.match(/^\d{4}-T[1-4]$/)) {
                const [ano, t] = chamada.trimestre.split('-T');
                trimestreExibicao = `${ano} - ${t}º Trim.`;
            }
            return [
                dataFormatada,
                chamada.nome_congregacao,
                chamada.nome_classe,
                chamada.nome_professor,
                trimestreExibicao,
                chamada.total_presentes || 0,
                chamada.total_ausentes || 0,
                chamada.total_justificados || 0,
                parseFloat(chamada.oferta_classe || 0).toFixed(2)
            ];
        });
        
        const csvContent = [headers, ...rows].map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.href = url;
        link.setAttribute('download', `chamadas_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        exibirToast('CSV exportado com sucesso!', 'success');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});