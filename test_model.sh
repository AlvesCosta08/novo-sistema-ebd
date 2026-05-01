#!/bin/bash

echo "=========================================="
echo "🧪 Testes do Model - Chamada"
echo "=========================================="
echo ""

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

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