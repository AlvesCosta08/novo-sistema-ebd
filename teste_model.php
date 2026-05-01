<?php
// teste_model.php (Localizado em: escola/teste_model.php)
// Arquivo temporário para testar o modelo Chamada isoladamente.

// Habilitar exibição de erros para este teste (NÃO para produção!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Testando Modelo Chamada...</h2>";

try {
    // 1. Incluir conexao.php
    echo "<p>Incluindo conexao.php...</p>";
    $conexaoPath = __DIR__ . '/config/conexao.php';
    if (!file_exists($conexaoPath)) {
        throw new Exception("Arquivo conexao.php não encontrado em: $conexaoPath");
    }
    require_once $conexaoPath;
    echo "<p style='color:green;'>conexao.php incluído com sucesso.</p>";

    // 2. Incluir modelo Chamada
    echo "<p>Incluindo models/Chamada.php...</p>";
    $modeloPath = __DIR__ . '/models/Chamada.php';
    if (!file_exists($modeloPath)) {
        throw new Exception("Arquivo models/Chamada.php não encontrado em: $modeloPath");
    }
    require_once $modeloPath;
    echo "<p style='color:green;'>models/Chamada.php incluído com sucesso.</p>";

    // 3. Instanciar modelo
    echo "<p>Instanciando Chamada(\$pdo)...</p>";
    $modelo = new Chamada($pdo);
    echo "<p style='color:green;'>Modelo Chamada instanciado com sucesso.</p>";

    // 4. Chamar o método problemático
    echo "<p>Executando \$modelo->getCongregacoesAtivas()...</p>";
    $dados = $modelo->getCongregacoesAtivas();
    echo "<p style='color:green;'>Método getCongregacoesAtivas() executado com sucesso.</p>";

    // 5. Exibir resultado
    echo "<h3>Dados retornados:</h3>";
    echo "<pre>" . print_r($dados, true) . "</pre>";

} catch (Exception $e) {
    echo "<h3 style='color:red;'>Erro encontrado:</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Rastro:</strong> <pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "<h4>Teste concluído.</h4>";