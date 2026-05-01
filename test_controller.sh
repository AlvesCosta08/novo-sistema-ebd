#!/bin/bash

echo "=========================================="
echo "🧪 Testes do Controller - Chamada"
echo "=========================================="
echo ""

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

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