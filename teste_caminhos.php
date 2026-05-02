<?php
echo "<h1>Verificando Estrutura de Diretórios</h1>";
echo "<pre>";

$base = '/home1/adtc2m99/public_html/sistemas/escola/views/';

$pastas = [
    'alunos' => $base . 'alunos/',
    'classes' => $base . 'classes/',
    'professores' => $base . 'professores/',
    'congregacao' => $base . 'congregacao/',
    'matriculas' => $base . 'matriculas/',
    'usuarios' => $base . 'usuarios/',
    'relatorios' => $base . 'relatorios/',
    'chamada' => $base . 'chamada/',
];

foreach ($pastas as $nome => $caminho) {
    echo "$nome: ";
    if (is_dir($caminho)) {
        echo "✅ PASTA EXISTE<br>";
        // Verificar se index.php existe
        if (file_exists($caminho . 'index.php')) {
            echo "   └─ ✅ index.php existe<br>";
        } else {
            echo "   └─ ❌ index.php NÃO existe<br>";
        }
    } else {
        echo "❌ PASTA NÃO EXISTE<br>";
    }
    echo "<br>";
}

echo "</pre>";
?>