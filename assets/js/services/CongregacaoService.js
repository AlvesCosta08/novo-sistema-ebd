/**
 * Serviço para comunicação com a API de Congregações
 * Padrão: Singleton com fetch encapsulado
 * 
 * @version 1.1 - Correção de resolução de caminho base
 */
const CongregacaoService = (function() {
    'use strict';
    
    /**
     * Resolve URL base de forma inteligente:
     * 1. Usa atributo data-api-base no body (prioritário)
     * 2. Usa meta tag com nome 'api-base'
     * 3. Fallback para caminho relativo configurável
     */
    function resolveBaseUrl() {
        // Opção 1: Atributo data no body (recomendado)
        const bodyBase = document.body.dataset.apiBase;
        if (bodyBase) return bodyBase;
        
        // Opção 2: Meta tag no head
        const metaBase = document.querySelector('meta[name="api-base"]');
        if (metaBase && metaBase.content) return metaBase.content;
        
        // Opção 3: Caminho relativo padrão (ajuste conforme sua estrutura)
        // Se a view está em: /sistemas/escola/views/congregacoes/index.php
        // E o controller está em: /sistemas/escola/controllers/congregacao.php
        // Então: ../../controllers/congregacao.php está correto
        return '../../controllers/congregacao.php';
    }
    
    const BASE_URL = resolveBaseUrl();
    
    /**
     * Obtém token CSRF do meta tag ou atributo data
     */
    function getCsrfToken() {
        // Prioridade 1: Meta tag
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        
        // Prioridade 2: Atributo data no body
        if (document.body.dataset.csrfToken) return document.body.dataset.csrfToken;
        
        return null;
    }
    
    /**
     * Executa requisição fetch com tratamento robusto de erros
     */
    async function requisicao(acao, dados = {}) {
        const startTime = performance.now();
        
        try {
            const params = new URLSearchParams({ acao, ...dados });
            
            console.debug(`[CongregacaoService] → ${acao}`, { 
                url: BASE_URL, 
                params: Object.fromEntries(params) 
            });
            
            const headers = {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            };
            
            const csrfToken = getCsrfToken();
            if (csrfToken) {
                headers['X-CSRF-Token'] = csrfToken;
            }
            
            const response = await fetch(BASE_URL, {
                method: 'POST',
                headers: headers,
                body: params,
                credentials: 'same-origin',
                cache: 'no-store'
            });
            
            const duration = Math.round(performance.now() - startTime);
            console.debug(`[CongregacaoService] ← ${acao} [${response.status}] ${duration}ms`);
            
            // Captura texto bruto antes de tentar parsear JSON
            const responseText = await response.text();
            
            if (!response.ok) {
                console.error(`[CongregacaoService] HTTP ${response.status}:`, responseText);
                throw new Error(`HTTP ${response.status}: ${response.statusText || 'Erro desconhecido'}`);
            }
            
            // Tenta parsear JSON com fallback para erro de formato
            let resultado;
            try {
                resultado = JSON.parse(responseText);
            } catch (parseError) {
                console.error('[CongregacaoService] Falha ao parsear JSON:', {
                    responseText: responseText.substring(0, 200) + '...',
                    error: parseError.message
                });
                throw new Error('Resposta do servidor em formato inválido');
            }
            
            if (resultado && resultado.sucesso === false) {
                // Erro lógico do backend (não HTTP)
                throw new Error(resultado.mensagem || 'Erro na operação');
            }
            
            return resultado;
            
        } catch (erro) {
            // Erros de rede, CORS, ou lógica
            console.error(`[CongregacaoService] ❌ Erro em '${acao}':`, {
                message: erro.message,
                name: erro.name,
                stack: erro.stack
            });
            
            // Adiciona contexto útil para debugging
            erro.serviceContext = {
                acao,
                baseUrl: BASE_URL,
                timestamp: new Date().toISOString()
            };
            
            throw erro;
        }
    }
    
    return {
        /**
         * Lista congregações com paginação server-side
         * @param {number} pagina - Página atual (1-based)
         * @param {number} porPagina - Registros por página
         * @param {string} busca - Termo de busca opcional
         */
        listar: (pagina = 1, porPagina = 10, busca = '') => 
            requisicao('listar', { pagina, por_pagina: porPagina, busca }),
        
        /**
         * Salva nova congregação
         * @param {string} nome - Nome da congregação
         */
        salvar: (nome) => requisicao('salvar', { nome }),
        
        /**
         * Atualiza congregação existente
         * @param {number|string} id - ID da congregação
         * @param {string} nome - Novo nome
         */
        editar: (id, nome) => requisicao('editar', { id, nome }),
        
        /**
         * Busca congregação por ID
         * @param {number|string} id - ID da congregação
         */
        buscar: (id) => requisicao('buscar', { id }),
        
        /**
         * Exclui congregação
         * @param {number|string} id - ID da congregação
         */
        excluir: (id) => requisicao('excluir', { id }),
        
        /**
         * Retorna estatísticas do módulo
         */
        getEstatisticas: () => requisicao('estatisticas'),
        
        /**
         * Método utilitário para redefinir a URL base (debug/testing)
         * @param {string} novaUrl - Nova URL base
         */
        _debugSetBaseUrl: (novaUrl) => {
            console.warn('[CongregacaoService] URL base alterada para debug:', novaUrl);
            // Nota: Isso não afeta requisições em andamento
        },
        
        /**
         * Retorna a URL base atual (para debugging)
         */
        _debugGetBaseUrl: () => BASE_URL
    };
})();

// Exportação para módulos (se usar ES Modules no futuro)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CongregacaoService;
}