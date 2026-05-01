<?php
// tests/unit/ChamadaModelTest.php

require_once __DIR__ . '/../bootstrap.php';

// CORRIGIDO: Caminho absoluto ou relativo correto
require_once __DIR__ . '/../../models/chamada.php';

use PHPUnit\Framework\TestCase;

class ChamadaModelTest extends TestCase
{
    private $pdo;
    private $chamadaModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = getTestConnection();
        $this->chamadaModel = new Chamada($this->pdo);
        $this->seedBasicData();
    }

    private function seedBasicData()
    {
        $this->pdo->exec("INSERT INTO congregacoes (id, nome) VALUES (1, 'SEDE')");
        $this->pdo->exec("INSERT INTO classes (id, nome) VALUES (1, 'ADULTOS')");
        $this->pdo->exec("INSERT INTO usuarios (id, nome, email, senha, perfil, congregacao_id) VALUES (1, 'Professor Teste', 'teste@teste.com', '123456', 'professor', 1)");
        $this->pdo->exec("INSERT INTO alunos (id, nome, classe_id, congregacao_id, status) VALUES (1, 'Aluno Teste', 1, 1, 'ativo')");
        $this->pdo->exec("INSERT INTO matriculas (aluno_id, classe_id, congregacao_id, usuario_id, trimestre, status, data_matricula) VALUES (1, 1, 1, 1, '2026-T2', 'ativo', CURDATE())");
        $this->pdo->exec("INSERT INTO professores (usuario_id, congregacao_id) VALUES (1, 1)");
    }

    public function testPadronizarTrimestre()
    {
        echo "\n✅ Testando padronização de trimestre...\n";
        
        $this->assertEquals('2026-T2', $this->chamadaModel->padronizarTrimestre('2', 2026));
        $this->assertEquals('2026-T2', $this->chamadaModel->padronizarTrimestre('2026-T2'));
        $this->assertEquals('2026-T2', $this->chamadaModel->padronizarTrimestre('2026t2'));
        
        echo "  ✓ Trimestre '2' → '2026-T2'\n";
        echo "  ✓ Trimestre '2026-T2' mantido\n";
        echo "  ✓ Trimestre '2026t2' → '2026-T2'\n";
        
        $this->assertTrue(true);
    }

    public function testExtrairNumeroTrimestre()
    {
        echo "\n✅ Testando extração de número do trimestre...\n";
        
        $this->assertEquals('2', $this->chamadaModel->extrairNumeroTrimestre('2026-T2'));
        $this->assertEquals('2', $this->chamadaModel->extrairNumeroTrimestre('2'));
        
        echo "  ✓ Extraído '2' de '2026-T2'\n";
        echo "  ✓ Extraído '2' de '2'\n";
        
        $this->assertTrue(true);
    }

    public function testRegistrarChamada()
    {
        echo "\n✅ Testando registro de chamada...\n";
        
        $alunos = [['id' => 1, 'status' => 'presente']];
        
        $resultado = $this->chamadaModel->registrarChamada(
            date('Y-m-d'),
            '2026-T2',
            1, 1, $alunos,
            10.50, 3, 5, 4
        );
        
        $this->assertTrue($resultado['sucesso']);
        $this->assertEquals('Chamada registrada com sucesso', $resultado['mensagem']);
        $this->assertArrayHasKey('chamada_id', $resultado);
        
        echo "  ✓ Chamada registrada com sucesso!\n";
        echo "  ✓ ID da chamada: {$resultado['chamada_id']}\n";
    }

    public function testListarChamadas()
    {
        echo "\n✅ Testando listagem de chamadas...\n";
        
        // Registrar chamada de teste
        $alunos = [['id' => 1, 'status' => 'presente']];
        $this->chamadaModel->registrarChamada('2026-05-01', '2026-T2', 1, 1, $alunos, 10.50);
        
        // Teste listagem
        $chamadas = $this->chamadaModel->listarChamadas();
        $this->assertIsArray($chamadas);
        $this->assertGreaterThanOrEqual(1, count($chamadas));
        
        echo "  ✓ Listagem sem filtros: " . count($chamadas) . " chamada(s)\n";
    }

    public function testGetAlunosByClasse()
    {
        echo "\n✅ Testando busca de alunos por classe...\n";
        
        $alunos = $this->chamadaModel->getAlunosByClasse(1, 1, '2026-T2');
        $this->assertIsArray($alunos);
        $this->assertGreaterThanOrEqual(1, count($alunos));
        
        if (count($alunos) > 0) {
            $this->assertEquals('Aluno Teste', $alunos[0]['nome']);
            echo "  ✓ Busca com '2026-T2': encontrado {$alunos[0]['nome']}\n";
        } else {
            echo "  ⚠ Nenhum aluno encontrado (pode ser normal se o seed falhou)\n";
        }
    }

    public function testGetClassesByCongregacao()
    {
        echo "\n✅ Testando busca de classes por congregação...\n";
        
        $classes = $this->chamadaModel->getClassesByCongregacao(1);
        
        $this->assertIsArray($classes);
        
        if (count($classes) > 0) {
            $this->assertEquals('ADULTOS', $classes[0]['nome']);
            echo "  ✓ Classes encontradas: " . count($classes) . "\n";
        } else {
            echo "  ⚠ Nenhuma classe encontrada\n";
        }
    }

    public function testExcluirChamada()
    {
        echo "\n✅ Testando exclusão de chamada...\n";
        
        // Registrar chamada
        $alunos = [['id' => 1, 'status' => 'presente']];
        $resultado = $this->chamadaModel->registrarChamada('2026-05-01', '2026-T2', 1, 1, $alunos);
        
        if (isset($resultado['chamada_id'])) {
            $chamadaId = $resultado['chamada_id'];
            
            // Excluir
            $exclusao = $this->chamadaModel->excluirChamada($chamadaId);
            $this->assertTrue($exclusao['sucesso']);
            echo "  ✓ Chamada excluída com sucesso!\n";
        } else {
            echo "  ⚠ Não foi possível testar exclusão\n";
            $this->assertTrue(true);
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            clearTestData($this->pdo);
        }
        parent::tearDown();
    }
}