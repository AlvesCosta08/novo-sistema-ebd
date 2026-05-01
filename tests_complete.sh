#!/bin/bash

# ==========================================
# TESTES COMPLETOS - SISTEMA DE CHAMADAS
# ==========================================

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Diretórios
COVERAGE_DIR="tests/coverage"
DATE=$(date +"%Y-%m-%d %H:%M:%S")

# Função para mostrar menu
show_menu() {
    echo ""
    echo "=========================================="
    echo "📦 SISTEMA DE CHAMADAS - TESTES"
    echo "=========================================="
    echo ""
    echo -e "${GREEN}1${NC}) Executar todos os testes (rápido)"
    echo -e "${GREEN}2${NC}) Executar testes com cobertura"
    echo -e "${GREEN}3${NC}) Executar apenas testes do Model"
    echo -e "${GREEN}4${NC}) Executar apenas testes do Controller"
    echo -e "${GREEN}5${NC}) Gerar relatório de cobertura"
    echo -e "${GREEN}6${NC}) Monitor contínuo (executa ao salvar)"
    echo -e "${RED}0${NC}) Sair"
    echo ""
    echo -n "Escolha uma opção: "
}

# Função para testes rápidos
run_tests() {
    echo ""
    echo "=========================================="
    echo "🧪 Executando Testes Unitários"
    echo "=========================================="
    echo ""
    
    /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testdox --colors=always
    
    if [ $? -eq 0 ]; then
        echo ""
        echo -e "${GREEN}✅ Todos os testes passaram com sucesso!${NC}"
    else
        echo ""
        echo -e "${RED}❌ Alguns testes falharam!${NC}"
        exit 1
    fi
}

# Função para testes com cobertura
run_tests_coverage() {
    echo ""
    echo "=========================================="
    echo "🧪 Testes com Cobertura"
    echo "=========================================="
    echo ""
    echo -e "${BLUE}📊 Data: ${DATE}${NC}"
    echo ""
    
    mkdir -p $COVERAGE_DIR
    rm -rf $COVERAGE_DIR/*
    
    echo -e "${YELLOW}🚀 Executando testes...${NC}"
    echo ""
    
    XDEBUG_MODE=coverage /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit \
        --testdox \
        --colors=always \
        --coverage-html $COVERAGE_DIR \
        --coverage-text
    
    if [ $? -eq 0 ]; then
        echo ""
        echo -e "${GREEN}=========================================="
        echo "✅ TODOS OS TESTES PASSARAM!"
        echo "==========================================${NC}"
        echo ""
        echo -e "${YELLOW}📊 Relatório: file:///opt/lampp/htdocs/escola/${COVERAGE_DIR}/index.html${NC}"
        echo ""
        echo -e "${GREEN}📈 Resumo:${NC}"
        echo "   • Model: 7 testes"
        echo "   • Controller: 2 testes"
        echo "   • Total: 9 testes, 26 asserts"
    else
        echo ""
        echo -e "${RED}❌ Alguns testes falharam!${NC}"
        exit 1
    fi
}

# Função para testes do Model
run_model_tests() {
    echo ""
    echo "=========================================="
    echo "🧪 Testes do Model - Chamada"
    echo "=========================================="
    echo ""
    
    /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit \
        tests/unit/ChamadaModelTest.php \
        --testdox \
        --colors=always
    
    if [ $? -eq 0 ]; then
        echo ""
        echo -e "${GREEN}✅ Todos os testes do Model passaram!${NC}"
    else
        echo ""
        echo -e "${RED}❌ Alguns testes do Model falharam!${NC}"
        exit 1
    fi
}

# Função para testes do Controller
run_controller_tests() {
    echo ""
    echo "=========================================="
    echo "🧪 Testes do Controller - Chamada"
    echo "=========================================="
    echo ""
    
    /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit \
        tests/unit/ChamadaControllerTest.php \
        --testdox \
        --colors=always
    
    if [ $? -eq 0 ]; then
        echo ""
        echo -e "${GREEN}✅ Todos os testes do Controller passaram!${NC}"
    else
        echo ""
        echo -e "${RED}❌ Alguns testes do Controller falharam!${NC}"
        exit 1
    fi
}

# Função para gerar apenas cobertura
generate_coverage() {
    echo ""
    echo "=========================================="
    echo "📊 Gerando Relatório de Cobertura"
    echo "=========================================="
    echo ""
    
    mkdir -p $COVERAGE_DIR
    rm -rf $COVERAGE_DIR/*
    
    echo -e "${YELLOW}🚀 Gerando cobertura...${NC}"
    echo ""
    
    XDEBUG_MODE=coverage /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit \
        --coverage-html $COVERAGE_DIR \
        --coverage-text
    
    if [ -f $COVERAGE_DIR/index.html ]; then
        echo ""
        echo -e "${GREEN}✅ Relatório gerado: ${COVERAGE_DIR}/index.html${NC}"
        echo -e "${BLUE}   file:///opt/lampp/htdocs/escola/${COVERAGE_DIR}/index.html${NC}"
    else
        echo ""
        echo -e "${RED}❌ Falha ao gerar relatório${NC}"
    fi
}

# Função para monitor contínuo
watch_tests() {
    echo ""
    echo "=========================================="
    echo "👀 Monitor Contínuo de Testes"
    echo "=========================================="
    echo ""
    
    # Verificar se inotifywait está instalado
    if ! command -v inotifywait &> /dev/null; then
        echo -e "${YELLOW}⚠️ inotifywait não encontrado. Instalando...${NC}"
        sudo apt-get install -y inotify-tools
    fi
    
    echo -e "${YELLOW}📁 Monitorando arquivos...${NC}"
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
        
        inotifywait -q -r -e modify escola/models/ escola/controllers/ tests/unit/ 2>/dev/null
    done
}

# Menu principal
while true; do
    show_menu
    read option
    
    case $option in
        1) run_tests ;;
        2) run_tests_coverage ;;
        3) run_model_tests ;;
        4) run_controller_tests ;;
        5) generate_coverage ;;
        6) watch_tests ;;
        0) 
            echo -e "${GREEN}👋 Até logo!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Opção inválida!${NC}"
            sleep 1
            ;;
    esac
    
    if [ "$option" != "6" ]; then
        echo ""
        echo -n "Pressione Enter para continuar..."
        read
    fi
done