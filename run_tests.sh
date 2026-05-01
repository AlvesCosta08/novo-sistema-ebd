#!/bin/bash

echo "=========================================="
echo "🧪 Executando Testes Unitários"
echo "📦 Sistema de Chamadas - Escola Bíblica"
echo "=========================================="
echo ""

# Executa o PHPUnit diretamente sem o wrapper
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testdox --colors=always

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Todos os testes passaram com sucesso!"
else
    echo ""
    echo "❌ Alguns testes falharam!"
    exit 1
fi
