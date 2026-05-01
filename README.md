### Estrutura de Pasta 

``
/escola/
├── views/chamada/
│   ├── index.php          # Registrar chamada
│   ├── listar.php         # Listar chamadas
│   ├── editar.php         # Editar chamada
│   └── js/
│       ├── chamada.js
│       ├── listar.js
│       └── editar.js
├── controllers/
│   └── chamada.php
└── models/
│   └── chamada.php
└── tests/                     # PASTA DE TESTES (NOVA)
    ├── bootstrap.php          # Configuração inicial
    ├── config/
    │   └── database.php       # Configuração do banco de testes
    ├── fixtures/              # Dados fixos para testes
    └── unit/
        ├── ChamadaModelTest.php
        ├── ChamadaControllerTest.php
        └── ConnectionTest.php


/escola/
├── views/matricula/
│   ├── index.php          # Gerenciar matrículas
│   ├── listar.php         # Listar matrículas
│   ├── editar.php         # Editar matrícula
│   └── js/
│       ├── matricula.js
│       ├── listar.js
│       └── editar.js
├── controllers/
│   └── matricula.php      # (já existe)
└── models/
│   └── matricula.php      # (já existe)        

     
📊 Funcionalidades Implementadas

Funcionalidade	Status	Arquivos

Registrar chamada	

✅index.php + chamada.js
Listar chamadas com filtros	
✅	listar.php + listar.js

Editar chamada existente	
✅	editar.php + editar.js

Excluir chamada	
✅	listar.js + chamada.php (controller)

Ver detalhes da chamada	
✅	listar.js + modal

Exportar para CSV	
✅	listar.js

Suporte a trimestre flexível	
✅	Todos os arquivos

Cards de estatísticas	
✅	listar.php + listar.js

Design responsivo	
✅	Todos os CSS

Testes unitários	
✅	tests/unit/

Cobertura de código	

✅	PHPUnit + Xdebug

🚀 Como Rodar os Testes

1. Primeira execução (configurar permissões)
chmod +x run_tests.sh
chmod +x run_tests_complete.sh
chmod +x coverage.sh
chmod +x test_model.sh
chmod +x test_controller.sh
chmod +x tests_complete.sh
chmod +x watch_tests.sh

2. Comandos para executar testes
# Executar testes rápidos (sem cobertura)
./run_tests.sh

# Executar todos os testes via PHPUnit
./vendor/bin/phpunit

# Executar apenas um arquivo de teste
./vendor/bin/phpunit tests/unit/ChamadaModelTest.php

# Executar com output detalhado
./vendor/bin/phpunit --testdox

# Executar com cobertura de código
./vendor/bin/phpunit --coverage-html tests/coverage

# Usar script helper
./run_tests.sh

# Executar testes com relatório de cobertura
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --coverage-html tests/coverage

# Executar com cobertura e mostrar no terminal
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --coverage-text

3. Comandos específicos (com PHP do LAMPP)

# Comando que SEMPRE funciona (sem coverage)
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testdox

# Executar testes diários (rápido)
./run_tests.sh

# Executar testes completos com cobertura
./run_tests_complete.sh

# Apenas gerar relatório de cobertura
./coverage.sh

# Apenas testes do Model
./test_model.sh

# Apenas testes do Controller
./test_controller.sh

# Monitor contínuo (executa ao salvar arquivos)
# sudo apt install inotify-tools  # se não tiver
./watch_tests.sh

# Recomendado: Menu completo interativo
./tests_complete.sh


bash
./tests_complete.sh
Menu:

1 - Executar todos os testes (rápido)

2 - Executar testes com cobertura

3 - Executar apenas testes do Model

4 - Executar apenas testes do Controller

5 - Gerar relatório de cobertura

6 - Monitor contínuo (executa ao salvar)

0 - Sair

📊 Resultados dos Testes
bash
✅ 9 testes executados
✅ 26 assertions verificadas
✅ Model: 7 testes passando
✅ Controller: 2 testes passando
✅ Cobertura de código gerada
🔗 Links Úteis
GitHub: https://github.com/AlvesCosta08/novo-sistema-ebd

Relatório de cobertura: tests/coverage/index.html

🛠️ Requisitos do Ambiente
PHP 8.2.12 (LAMPP)

MySQL (via socket /opt/lampp/var/mysql/mysql.sock)

PHPUnit 9.6.34

Xdebug 3.5.1 (para cobertura)

Composer

📝 Observações
O banco de testes escola_testes é recriado a cada execução

Os testes não afetam o banco de dados de produção

Os scripts assumem o caminho do PHP do LAMPP: /opt/lampp/bin/php

Sistema 100% funcional e testado! 🚀

