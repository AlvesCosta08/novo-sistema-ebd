<?php
require_once 'config/conexao.php';
require_once 'models/chamada.php';

$chamada = new Chamada($pdo);

echo "=== TESTE DE CHAMADAS ===\n\n";

// Teste 1: Listar congregações
echo "1. Congregações:\n";
$cong = $chamada->getCongregacoes();
print_r($cong);

// Teste 2: Listar classes
echo "\n2. Classes da congregação 7:\n";
$classes = $chamada->getClassesByCongregacao(7);
print_r($classes);

// Teste 3: Buscar alunos
echo "\n3. Alunos da classe ADOLESCENTES (id=6) no trimestre 2026-T2:\n";
$alunos = $chamada->getAlunosByClasse(6, 7, '2026-T2');
foreach($alunos as $aluno) {
    echo " - {$aluno['nome']} (ID: {$aluno['id']})\n";
}

echo "\n✅ Teste concluído!\n";
?>