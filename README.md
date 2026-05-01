### Estrutura de Pasta 

/escola/
├── modules/chamada/
│   ├── index.php          # Registrar nova chamada
│   ├── listar.php         # Histórico de chamadas
│   ├── editar.php         # Editar chamada existente
│   └── js/
│       ├── chamada.js     # Lógica do registro
│       ├── listar.js      # Lógica da listagem
│       └── editar.js      # Lógica da edição
├── controllers/
│   └── chamada.php        # API/Controller
├── models/
│   └── chamada.php        # Model com queries
└── tests/                 # (A SER CRIADO)
    └── unit/
        ├── ChamadaModelTest.php
        ├── ChamadaControllerTest.php
        └── bootstrap.php

# Github
https://github.com/AlvesCosta08/novo-sistema-ebd 

#### Funcionalidade	Status	Arquivo

# Registrar chamada	
✅	index.php + chamada.js

# Listar chamadas com filtros
✅	listar.php + listar.js

# Editar chamada existente	
✅	editar.php + editar.js

# Excluir chamada	
✅	listar.js + controller

# Ver detalhes da chamada	
✅	listar.js + modal

# Exportar para CSV	
✅	listar.js

# Suporte a trimestre flexível	
✅	Todos os arquivos

# Cards de estatísticas	
✅	listar.php + listar.js

# Design responsivo	
✅	Todos os CSS       # novo-sistema-ebd
