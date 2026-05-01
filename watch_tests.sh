#!/bin/bash

echo "=========================================="
echo "👀 Monitor de Testes"
echo "📦 Executa automaticamente ao salvar"
echo "=========================================="
echo ""

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}📁 Monitorando arquivos em escola/models/ e escola/controllers/${NC}"
echo -e "${YELLOW}🔍 Pressione Ctrl+C para parar${NC}"
echo ""

while true; do
    clear
    echo "=========================================="
    echo "🔄 Executando testes em $(date +"%H:%M:%S")"
    echo "=========================================="
    echo ""
    
    /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testdox --colors=always
    
    echo ""
    echo -e "${GREEN}✅ Aguardando alterações...${NC}"
    echo ""
    
    # Aguarda mudanças nos arquivos
    inotifywait -q -r -e modify escola/models/ escola/controllers/ tests/unit/
done