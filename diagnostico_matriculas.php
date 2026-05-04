<?php
/**
 * diagnostico_matriculas.php
 * Coloque este arquivo temporariamente em: escola/diagnostico_matriculas.php
 * Acesse via navegador e veja o resultado.
 * REMOVA após uso — contém informações sensíveis.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Carregar conexão do projeto
$base_path = __DIR__;
require_once $base_path . '/config/conexao.php';

// Iniciar sessão para verificar dados do usuário logado
if (session_status() === PHP_SESSION_NONE) session_start();

echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}
h2{background:#4f46e5;color:#fff;padding:8px 12px;border-radius:6px;}
.ok{color:green;font-weight:bold;} .erro{color:red;font-weight:bold;}
.aviso{color:orange;font-weight:bold;}
table{border-collapse:collapse;width:100%;margin-bottom:20px;}
th,td{border:1px solid #ccc;padding:6px 10px;text-align:left;}
th{background:#e0e7ff;}
pre{background:#fff;padding:10px;border:1px solid #ddd;border-radius:4px;}
</style>";

echo "<h1>🔍 Diagnóstico — Matrículas</h1>";

// ── 1. Sessão ────────────────────────────────────────────────────────────────
echo "<h2>1. Dados da Sessão (usuário logado)</h2>";
if (empty($_SESSION)) {
    echo "<p class='aviso'>⚠️ Sessão vazia ou usuário não logado. Os filtros do controller usam a sessão.</p>";
} else {
    echo "<table><tr><th>Chave</th><th>Valor</th></tr>";
    $chaves_relevantes = ['usuario_id','nome','perfil','usuario_perfil','congregacao_id'];
    foreach ($chaves_relevantes as $k) {
        $val = $_SESSION[$k] ?? '<span class="erro">NÃO DEFINIDO</span>';
        echo "<tr><td>$k</td><td>$val</td></tr>";
    }
    echo "</table>";

    $perfil = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
    $cong_id = $_SESSION['congregacao_id'] ?? null;
    echo "<p><strong>Perfil detectado:</strong> <span class='ok'>$perfil</span></p>";
    echo "<p><strong>congregacao_id na sessão:</strong> ";
    if ($cong_id) {
        echo "<span class='ok'>$cong_id</span>";
    } else {
        echo "<span class='erro'>NULO ou não definido</span> — isso faz o controller filtrar NADA e retornar 0 registros!";
    }
    echo "</p>";
}

// ── 2. Conexão com banco ─────────────────────────────────────────────────────
echo "<h2>2. Conexão com o Banco</h2>";
if (!isset($pdo) || !$pdo) {
    echo "<p class='erro'>❌ Variável \$pdo não está disponível após o require da conexão!</p>";
    exit;
}
echo "<p class='ok'>✅ Conexão OK</p>";

// ── 3. Estrutura da tabela ───────────────────────────────────────────────────
echo "<h2>3. Estrutura da Tabela matriculas</h2>";
try {
    $cols = $pdo->query("DESCRIBE matriculas")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th><th>Observação</th></tr>";
    foreach ($cols as $c) {
        $obs = '';
        if ($c['Field'] === 'id' && $c['Extra'] !== 'auto_increment') {
            $obs = "<span class='erro'>❌ Sem AUTO_INCREMENT — execute corrigir_tabela.sql</span>";
        }
        if ($c['Field'] === 'id' && $c['Key'] !== 'PRI') {
            $obs .= " <span class='erro'>❌ Sem PRIMARY KEY</span>";
        }
        if ($c['Field'] === 'status' && strpos($c['Type'], 'varchar(5)') !== false) {
            $obs = "<span class='aviso'>⚠️ varchar(5) — 'inativo' tem 6 chars, será truncado! Altere para varchar(10)</span>";
        }
        if ($c['Field'] === 'data_matricula' && strpos($c['Type'], 'varchar') !== false) {
            $obs = "<span class='aviso'>⚠️ varchar — recomendado usar DATE para ordenação correta</span>";
        }
        echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td><td>{$c['Null']}</td><td>{$c['Key']}</td><td>{$c['Default']}</td><td>{$c['Extra']}</td><td>$obs</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='erro'>❌ Erro ao descrever tabela: " . $e->getMessage() . "</p>";
}

// ── 4. Total de registros sem filtro ─────────────────────────────────────────
echo "<h2>4. Total de Registros (sem filtro)</h2>";
try {
    $total = $pdo->query("SELECT COUNT(*) FROM matriculas")->fetchColumn();
    echo "<p>Total na tabela: <strong class='ok'>$total registros</strong></p>";

    $por_status = $pdo->query("SELECT status, COUNT(*) as qtd FROM matriculas GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table><tr><th>Status</th><th>Quantidade</th></tr>";
    foreach ($por_status as $r) {
        echo "<tr><td>{$r['status']}</td><td>{$r['qtd']}</td></tr>";
    }
    echo "</table>";

    $por_cong = $pdo->query("SELECT congregacao_id, COUNT(*) as qtd FROM matriculas GROUP BY congregacao_id ORDER BY congregacao_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Registros por congregacao_id</h3>";
    echo "<table><tr><th>congregacao_id</th><th>Quantidade</th></tr>";
    foreach ($por_cong as $r) {
        echo "<tr><td>{$r['congregacao_id']}</td><td>{$r['qtd']}</td></tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<p class='erro'>❌ " . $e->getMessage() . "</p>";
}

// ── 5. Simular a query do controller ─────────────────────────────────────────
echo "<h2>5. Simulação da Query do Controller (com filtros da sessão)</h2>";
try {
    $perfil  = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
    $cong_id = $_SESSION['congregacao_id'] ?? null;

    $sql = "SELECT COUNT(*) FROM matriculas m
            JOIN alunos a       ON m.aluno_id       = a.id
            JOIN classes c      ON m.classe_id      = c.id
            JOIN congregacoes cg ON m.congregacao_id = cg.id
            WHERE 1=1";
    $params = [];

    if ($perfil !== 'admin' && $cong_id) {
        $sql .= " AND m.congregacao_id = :congregacao_id";
        $params[':congregacao_id'] = $cong_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total_filtrado = $stmt->fetchColumn();

    echo "<pre>$sql\n\nParâmetros: " . json_encode($params) . "</pre>";
    echo "<p>Registros retornados com este filtro: <strong " . ($total_filtrado > 0 ? "class='ok'" : "class='erro'") . ">$total_filtrado</strong></p>";

    if ($total_filtrado == 0 && $perfil !== 'admin') {
        echo "<p class='erro'>❌ PROBLEMA IDENTIFICADO: A sessão tem congregacao_id = <strong>" . ($cong_id ?? 'NULL') . "</strong> mas os dados na tabela têm congregacao_id = 7.<br>
        Se esses valores não baterem, nenhum registro aparece.<br><br>
        <strong>Solução:</strong> Verifique se o usuário logado pertence à congregação 7, ou faça login com um usuário admin.</p>";
    }

} catch (Exception $e) {
    echo "<p class='erro'>❌ Erro na join: " . $e->getMessage() . "</p>";
    echo "<p>Isso significa que uma das tabelas (alunos, classes, congregacoes) não existe ou está vazia. Verifique as foreign keys.</p>";
}

// ── 6. Verificar JOINs separadamente ─────────────────────────────────────────
echo "<h2>6. Verificação das Tabelas Relacionadas</h2>";
$tabelas = ['alunos', 'classes', 'congregacoes', 'usuarios'];
echo "<table><tr><th>Tabela</th><th>Existe?</th><th>Registros</th></tr>";
foreach ($tabelas as $t) {
    try {
        $qtd = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $cor = $qtd > 0 ? 'ok' : 'aviso';
        echo "<tr><td>$t</td><td class='ok'>✅ Existe</td><td class='$cor'>$qtd</td></tr>";
    } catch (Exception $e) {
        echo "<tr><td>$t</td><td class='erro'>❌ NÃO EXISTE ou erro</td><td>-</td></tr>";
    }
}
echo "</table>";

// ── 7. Teste direto da query do DataTable ────────────────────────────────────
echo "<h2>7. Query Completa (igual ao DataTable — sem filtros)</h2>";
try {
    $stmt = $pdo->query("SELECT m.id, a.nome AS aluno, c.nome AS classe,
                                cg.nome AS congregacao, m.status, m.trimestre
                         FROM matriculas m
                         JOIN alunos a       ON m.aluno_id       = a.id
                         JOIN classes c      ON m.classe_id      = c.id
                         JOIN congregacoes cg ON m.congregacao_id = cg.id
                         LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo "<p class='ok'>✅ Query funcionou. Primeiros 5 registros:</p>";
        echo "<table><tr><th>ID</th><th>Aluno</th><th>Classe</th><th>Congregação</th><th>Status</th><th>Trimestre</th></tr>";
        foreach ($rows as $r) {
            echo "<tr><td>{$r['id']}</td><td>{$r['aluno']}</td><td>{$r['classe']}</td><td>{$r['congregacao']}</td><td>{$r['status']}</td><td>{$r['trimestre']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='erro'>❌ Query retornou 0 registros — provavelmente alguma tabela do JOIN está vazia.</p>";
    }
} catch (Exception $e) {
    echo "<p class='erro'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr><p style='color:#999;font-size:12px;'>⚠️ Remova este arquivo após o diagnóstico.</p>";