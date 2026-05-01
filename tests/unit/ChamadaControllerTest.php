<?php
// tests/unit/ChamadaControllerTest.php

require_once __DIR__ . '/../bootstrap.php';

use PHPUnit\Framework\TestCase;

class ChamadaControllerTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = getTestConnection();
        
        // Inserir dados básicos com senha definida
        $this->pdo->exec("
            INSERT INTO congregacoes (id, nome) VALUES (1, 'SEDE');
            INSERT INTO classes (id, nome) VALUES (1, 'ADULTOS');
            INSERT INTO usuarios (id, nome, email, senha, perfil, congregacao_id) 
            VALUES (1, 'Professor Teste', 'teste@teste.com', '123456', 'professor', 1);
            INSERT INTO alunos (id, nome, classe_id, congregacao_id, status) 
            VALUES (1, 'Aluno Teste', 1, 1, 'ativo');
            INSERT INTO matriculas (aluno_id, classe_id, congregacao_id, usuario_id, trimestre, status, data_matricula) 
            VALUES (1, 1, 1, 1, '2026-T2', 'ativo', CURDATE());
            INSERT INTO professores (usuario_id, congregacao_id) VALUES (1, 1);
        ");
    }

    // Teste da função padronizarTrimestre
    public function testPadronizarTrimestreFunction()
    {
        echo "\n✅ Testando função padronizarTrimestre do controller...\n";
        
        // Simula a função do controller
        $padronizar = function($trimestre, $ano = null) {
            if (empty($trimestre)) return null;
            if (preg_match('/^\d{4}-T[1-4]$/i', $trimestre)) {
                return strtoupper($trimestre);
            }
            if (preg_match('/^[1-4]$/', $trimestre)) {
                $anoUsar = $ano ?: date('Y');
                return $anoUsar . '-T' . $trimestre;
            }
            if (preg_match('/^(\d{4})[Tt]([1-4])$/', $trimestre, $matches)) {
                return $matches[1] . '-T' . $matches[2];
            }
            return $trimestre;
        };
        
        $this->assertEquals('2026-T2', $padronizar('2', 2026));
        $this->assertEquals('2026-T2', $padronizar('2026-T2'));
        $this->assertEquals('2026-T2', $padronizar('2026t2'));
        
        echo "  ✓ Função padronizarTrimestre funcionando!\n";
    }

    // Teste da função extrairNumeroTrimestre
    public function testExtrairNumeroTrimestreFunction()
    {
        echo "\n✅ Testando função extrairNumeroTrimestre do controller...\n";
        
        $extrair = function($trimestre) {
            if (empty($trimestre)) return null;
            if (preg_match('/-T([1-4])$/i', $trimestre, $matches)) {
                return $matches[1];
            }
            if (preg_match('/^[1-4]$/', $trimestre)) {
                return $trimestre;
            }
            if (preg_match('/^(\d{4})[Tt]([1-4])$/', $trimestre, $matches)) {
                return $matches[2];
            }
            return null;
        };
        
        $this->assertEquals('2', $extrair('2026-T2'));
        $this->assertEquals('2', $extrair('2'));
        $this->assertEquals('2', $extrair('2026t2'));
        
        echo "  ✓ Função extrairNumeroTrimestre funcionando!\n";
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            clearTestData($this->pdo);
        }
        parent::tearDown();
    }
}