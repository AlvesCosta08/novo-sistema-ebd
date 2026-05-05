<?php
// views/relatorios/exportar_relatorio.php
// Script para exportar relatórios em CSV

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$tipo = $_GET['tipo'] ?? '';
$relatorioController = new RelatorioController();

switch ($tipo) {
    case 'aniversariantes':
        $mes = $_GET['mes'] ?? date('m');
        $classe_id = $_GET['classe_id'] ?? null;
        $dados = $relatorioController->getAniversariantes($mes, $classe_id);
        $nomeArquivo = "aniversariantes_" . date('Y-m') . ".csv";
        $cabecalho = ['ID', 'Nome', 'Data Nascimento', 'Idade', 'Classe', 'Congregação', 'Telefone'];
        $linhas = array_map(function($row) {
            return [
                $row['id'],
                $row['nome'],
                date('d/m/Y', strtotime($row['data_nascimento'])),
                $row['idade'],
                $row['classe_nome'],
                $row['congregacao_nome'],
                $row['telefone']
            ];
        }, $dados);
        break;
        
    case 'consolidado':
        $trimestre = $_GET['trimestre'] ?? '';
        $congregacao = $_GET['congregacao'] ?? '';
        $dados = $relatorioController->getRelatorioConsolidado($trimestre, $congregacao);
        $nomeArquivo = "relatorio_consolidado.csv";
        $cabecalho = ['Congregação', 'Classe', 'Trimestre', 'Matriculados', 'Presentes', 'Ausentes', 'Justificados', 'Bíblias', 'Revistas', 'Visitantes', 'Oferta'];
        $linhas = $dados;
        break;
        
    case 'presencas_aluno':
        $congregacao_id = $_GET['congregacao_id'] ?? '';
        $classe_id = $_GET['classe_id'] ?? '';
        $trimestre = $_GET['trimestre'] ?? '';
        $data_inicio = $_GET['data_inicio'] ?? '';
        $data_fim = $_GET['data_fim'] ?? '';
        $resultado = $relatorioController->getPresencasPorAluno($congregacao_id, $classe_id, $data_inicio, $data_fim, $trimestre);
        $dados = $resultado['dados'];
        $nomeArquivo = "presencas_aluno.csv";
        $cabecalho = ['Aluno', 'Classe', 'Congregação', 'Presenças', 'Faltas', 'Frequência(%)'];
        $linhas = array_map(function($row) {
            return [
                $row['aluno'],
                $row['classe'],
                $row['congregacao'],
                $row['presencas'],
                $row['faltas'],
                $row['frequencia']
            ];
        }, $dados);
        break;
        
    case 'frequencia_alunos':
        $dados = $relatorioController->getFrequenciaAlunos();
        $nomeArquivo = "frequencia_alunos.csv";
        $cabecalho = ['ID', 'Aluno', 'Presenças', 'Faltas', 'Classe', 'Congregação', 'Trimestre'];
        $linhas = array_map(function($row) {
            return [
                $row['aluno_id'],
                $row['aluno_nome'],
                $row['total_presentes'],
                $row['total_ausentes'],
                $row['classe_nome'],
                $row['congregacao_nome'],
                $row['trimestre']
            ];
        }, $dados);
        break;
        
    default:
        die('Tipo de relatório inválido');
}

// Gerar CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

$output = fopen('php://output', 'w');
fputcsv($output, $cabecalho, ';');

foreach ($linhas as $linha) {
    fputcsv($output, $linha, ';');
}

fclose($output);
exit();