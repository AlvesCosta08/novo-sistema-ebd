#!/bin/bash

echo "=========================================="
echo "📊 Gerando Relatório de Cobertura"
echo "📦 Sistema de Chamadas - Escola Bíblica"
echo "=========================================="
echo ""

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

COVERAGE_DIR="tests/coverage"

mkdir -p $COVERAGE_DIR
rm -rf $COVERAGE_DIR/*

echo -e "${YELLOW}🚀 Gerando cobertura...${NC}"
echo ""

XDEBUG_MODE=coverage /opt/lampp/bin/php vendor/phpunit/phpunit/phpunit \
    --coverage-html $COVERAGE_DIR \
    --coverage-text

echo ""
echo -e "${GREEN}✅ Relatório gerado: ${COVERAGE_DIR}/index.html${NC}"
echo -e "${BLUE}   file:///opt/lampp/htdocs/escola/${COVERAGE_DIR}/index.html${NC}"