<?php
// ver_logs.php - Acesse via navegador para ver os logs
session_start();

// Verificar se usuário é admin (ajuste conforme sua lógica)
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    die('Acesso negado. Apenas administradores.');
}

$logDir = __DIR__ . '/logs';

// Criar diretório de logs se não existir
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Processar limpeza de logs via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'limpar') {
        $dias = isset($_POST['dias']) ? intval($_POST['dias']) : 30;
        $arquivos = glob($logDir . '/erros_*.log');
        $deletados = 0;
        
        foreach ($arquivos as $arquivo) {
            // Deletar logs mais antigos que X dias
            if (filemtime($arquivo) < strtotime("-$dias days")) {
                if (unlink($arquivo)) {
                    $deletados++;
                }
            }
        }
        
        // Também limpar o arquivo de log atual se solicitado
        if (isset($_POST['limpar_atual']) && $_POST['limpar_atual'] == '1') {
            $logAtual = $logDir . '/erros_' . date('Y-m-d') . '.log';
            if (file_exists($logAtual)) {
                file_put_contents($logAtual, '');
                echo json_encode(['status' => 'success', 'message' => "Logs limpos com sucesso! Arquivos antigos removidos: $deletados"]);
                exit;
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => "$deletados arquivos antigos removidos"]);
        exit;
    }
    
    if ($_POST['acao'] === 'exportar') {
        $arquivo = $_POST['arquivo'] ?? '';
        $caminho = $logDir . '/' . $arquivo;
        
        if (file_exists($caminho)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $arquivo . '"');
            readfile($caminho);
            exit;
        }
    }
}

$arquivos = glob($logDir . '/erros_*.log');
rsort($arquivos); // Mais recentes primeiro

// Estatísticas gerais
$stats = [
    'total_arquivos' => count($arquivos),
    'total_linhas' => 0,
    'erros' => 0,
    'warnings' => 0,
    'excecoes' => 0,
    'info' => 0
];

foreach ($arquivos as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    $stats['total_linhas'] += substr_count($conteudo, PHP_EOL);
    $stats['erros'] += substr_count($conteudo, '[ERROR]');
    $stats['warnings'] += substr_count($conteudo, '[WARNING]');
    $stats['excecoes'] += substr_count($conteudo, '[EXCEPTION]');
    $stats['info'] += substr_count($conteudo, '[INFO]');
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador de Logs de Erro - EBD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .stat-card.error .number { color: #dc3545; }
        .stat-card.warning .number { color: #ffc107; }
        .stat-card.exception .number { color: #dc3545; }
        .stat-card.info .number { color: #28a745; }
        .stat-card.total .number { color: #17a2b8; }
        
        .filters {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-warning { background: #ffc107; color: #333; }
        .btn-error { background: #dc3545; color: white; }
        .btn-notice { background: #17a2b8; color: white; }
        .btn-deprecated { background: #6f42c1; color: white; }
        .btn-info { background: #28a745; color: white; }
        .btn-all { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-primary { background: #007bff; color: white; }
        
        button:hover {
            opacity: 0.8;
            transform: scale(1.02);
        }
        
        .search-box {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 250px;
            font-size: 14px;
        }
        
        .log-file {
            background: white;
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .log-header {
            background: #2c3e50;
            color: white;
            padding: 12px 20px;
            cursor: pointer;
            user-select: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .log-header:hover {
            background: #34495e;
        }
        
        .log-header .file-info {
            display: flex;
            gap: 15px;
            font-size: 14px;
        }
        
        .log-header .file-info span {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
        }
        
        .log-content {
            display: none;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .log-content pre {
            margin: 0;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.5;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        
        .log-line {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 4px 15px;
            border-bottom: 1px solid #333;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .log-line:hover {
            background: #2d2d2d;
        }
        
        .WARNING { color: #ffc107; }
        .NOTICE { color: #17a2b8; }
        .ERROR { color: #dc3545; font-weight: bold; }
        .EXCEPTION { color: #dc3545; font-weight: bold; background: rgba(220,53,69,0.1); }
        .DEPRECATED { color: #6f42c1; }
        .INFO { color: #28a745; }
        .DEBUG { color: #6c757d; }
        
        .loading {
            text-align: center;
            padding: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .log-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 Sistema de Logs - EBD</h1>
        <p>Monitoramento de erros e atividades do sistema</p>
    </div>
    
    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="number"><?= $stats['total_arquivos'] ?></div>
            <div class="label">Arquivos de Log</div>
        </div>
        <div class="stat-card total">
            <div class="number"><?= number_format($stats['total_linhas'], 0, ',', '.') ?></div>
            <div class="label">Total de Linhas</div>
        </div>
        <div class="stat-card error">
            <div class="number"><?= number_format($stats['erros'] + $stats['excecoes'], 0, ',', '.') ?></div>
            <div class="label">❌ Erros + Exceções</div>
        </div>
        <div class="stat-card warning">
            <div class="number"><?= number_format($stats['warnings'], 0, ',', '.') ?></div>
            <div class="label">⚠️ Warnings</div>
        </div>
        <div class="stat-card info">
            <div class="number"><?= number_format($stats['info'], 0, ',', '.') ?></div>
            <div class="label">ℹ️ Informações</div>
        </div>
    </div>
    
    <!-- Filtros e Ações -->
    <div class="filters">
        <div class="filter-buttons">
            <button onclick="filtrar('todos')" class="btn-all">📋 Todos</button>
            <button onclick="filtrar('ERROR')" class="btn-error">❌ Errors</button>
            <button onclick="filtrar('EXCEPTION')" class="btn-error">💥 Exceções</button>
            <button onclick="filtrar('WARNING')" class="btn-warning">⚠️ Warnings</button>
            <button onclick="filtrar('NOTICE')" class="btn-notice">ℹ️ Notices</button>
            <button onclick="filtrar('DEBUG')" class="btn-info">🐛 Debug</button>
            <button onclick="filtrar('INFO')" class="btn-info">📝 Info</button>
        </div>
        <div>
            <input type="text" id="searchInput" class="search-box" placeholder="🔍 Buscar nos logs..." onkeyup="buscarTexto()">
        </div>
    </div>
    
    <div class="filters">
        <div class="filter-buttons">
            <button onclick="expandirTodos()">📂 Expandir Todos</button>
            <button onclick="recolherTodos()">📁 Recolher Todos</button>
            <button onclick="limparLogsAntigos()" class="btn-warning">🗑️ Limpar Logs Antigos (30 dias)</button>
            <button onclick="exportarTodosLogs()" class="btn-primary">📥 Exportar Todos</button>
        </div>
    </div>
    
    <div id="logsContainer">
        <?php if (empty($arquivos)): ?>
            <div class="log-file">
                <div class="log-header">
                    📄 Nenhum arquivo de log encontrado
                </div>
                <div class="log-content" style="display: block;">
                    <pre>Nenhum log registrado até o momento.</pre>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($arquivos as $arquivo): 
                $nomeArquivo = basename($arquivo);
                $dataArquivo = date('d/m/Y H:i:s', filemtime($arquivo));
                $tamanho = round(filesize($arquivo) / 1024, 2);
                $linhasArquivo = count(file($arquivo));
            ?>
            <div class="log-file" data-filename="<?= $nomeArquivo ?>">
                <div class="log-header" onclick="toggleLog(this)">
                    <div class="file-info">
                        <strong>📄 <?= $nomeArquivo ?></strong>
                        <span>📅 <?= $dataArquivo ?></span>
                        <span>📊 <?= $linhasArquivo ?> linhas</span>
                        <span>💾 <?= $tamanho ?> KB</span>
                    </div>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="log-content">
                    <pre class="log-lines"><?php 
                    $conteudo = file($arquivo);
                    foreach ($conteudo as $linha) {
                        $linhaHtml = htmlspecialchars($linha);
                        // Adicionar classe para cada tipo de log
                        if (strpos($linha, '[ERROR]') !== false) {
                            echo '<div class="log-line ERROR">❌ ' . $linhaHtml . '</div>';
                        } elseif (strpos($linha, '[EXCEPTION]') !== false) {
                            echo '<div class="log-line EXCEPTION">💥 ' . $linhaHtml . '</div>';
                        } elseif (strpos($linha, '[WARNING]') !== false) {
                            echo '<div class="log-line WARNING">⚠️ ' . $linhaHtml . '</div>';
                        } elseif (strpos($linha, '[NOTICE]') !== false) {
                            echo '<div class="log-line NOTICE">ℹ️ ' . $linhaHtml . '</div>';
                        } elseif (strpos($linha, '[DEBUG]') !== false) {
                            echo '<div class="log-line DEBUG">🐛 ' . $linhaHtml . '</div>';
                        } elseif (strpos($linha, '[INFO]') !== false) {
                            echo '<div class="log-line INFO">📝 ' . $linhaHtml . '</div>';
                        } else {
                            echo '<div class="log-line">' . $linhaHtml . '</div>';
                        }
                    }
                    ?></pre>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Variável para armazenar o tipo de filtro atual
let filtroAtual = 'todos';
let textoBusca = '';

// Funções de toggle
function toggleLog(element) {
    const content = element.nextElementSibling;
    const icon = element.querySelector('.toggle-icon');
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        if (icon) icon.innerHTML = '▲';
    } else {
        content.style.display = 'none';
        if (icon) icon.innerHTML = '▼';
    }
}

function expandirTodos() {
    document.querySelectorAll('.log-content').forEach(content => {
        content.style.display = 'block';
        const header = content.previousElementSibling;
        const icon = header?.querySelector('.toggle-icon');
        if (icon) icon.innerHTML = '▲';
    });
}

function recolherTodos() {
    document.querySelectorAll('.log-content').forEach(content => {
        content.style.display = 'none';
        const header = content.previousElementSibling;
        const icon = header?.querySelector('.toggle-icon');
        if (icon) icon.innerHTML = '▼';
    });
}

// Filtrar logs por tipo
function filtrar(tipo) {
    filtroAtual = tipo;
    const todosLogs = document.querySelectorAll('.log-file');
    
    todosLogs.forEach(logFile => {
        const linhas = logFile.querySelectorAll('.log-line');
        let temLinhaVisivel = false;
        
        linhas.forEach(linha => {
            if (tipo === 'todos') {
                linha.style.display = 'block';
                temLinhaVisivel = true;
            } else {
                if (linha.classList.contains(tipo)) {
                    linha.style.display = 'block';
                    temLinhaVisivel = true;
                } else {
                    linha.style.display = 'none';
                }
            }
        });
        
        // Ocultar arquivo sem linhas visíveis
        if (!temLinhaVisivel && tipo !== 'todos') {
            logFile.style.display = 'none';
        } else {
            logFile.style.display = 'block';
        }
    });
    
    // Aplicar busca se houver texto
    if (textoBusca) {
        buscarTexto();
    }
}

// Buscar texto nos logs
function buscarTexto() {
    textoBusca = document.getElementById('searchInput').value.toLowerCase();
    const todosLogs = document.querySelectorAll('.log-file');
    
    todosLogs.forEach(logFile => {
        const linhas = logFile.querySelectorAll('.log-line');
        let temMatch = false;
        
        linhas.forEach(linha => {
            const texto = linha.innerText.toLowerCase();
            if (texto.includes(textoBusca)) {
                linha.style.display = 'block';
                temMatch = true;
            } else if (filtroAtual === 'todos' || linha.classList.contains(filtroAtual)) {
                linha.style.display = 'none';
            }
        });
        
        logFile.style.display = temMatch ? 'block' : 'none';
    });
}

// Limpar logs antigos
function limparLogsAntigos() {
    const dias = prompt('Remover logs com mais de quantos dias?', '30');
    if (dias && confirm(`Tem certeza que deseja remover logs com mais de ${dias} dias?`)) {
        fetch('ver_logs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `acao=limpar&dias=${dias}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao limpar logs. Tente novamente.');
        });
    }
}

// Exportar todos os logs
function exportarTodosLogs() {
    let todosLogs = '';
    document.querySelectorAll('.log-line').forEach(linha => {
        // Remover ícones e formatar
        let texto = linha.innerText;
        texto = texto.replace(/^[❌💥⚠️ℹ️🐛📝]\s*/, '');
        todosLogs += texto + '\n';
    });
    
    const blob = new Blob([todosLogs], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `logs_completos_${new Date().toISOString().slice(0,19)}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Auto-expandir o primeiro log após carregar
document.addEventListener('DOMContentLoaded', () => {
    const firstHeader = document.querySelector('.log-header');
    if (firstHeader) {
        setTimeout(() => {
            firstHeader.click();
        }, 500);
    }
});

// Atualizar estatísticas a cada 30 segundos
setInterval(() => {
    location.reload();
}, 30000);
</script>
</body>
</html>