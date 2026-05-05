<?php

require_once __DIR__ . '/../config/conexao.php';

class Aluno {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function listar() {
        try {
            $stmt = $this->db->prepare("
                SELECT a.id, a.nome, a.data_nascimento, a.telefone, c.nome AS classe 
                FROM alunos a 
                JOIN classes c ON a.classe_id = c.id 
                WHERE a.status = 'ativo'
                ORDER BY a.nome ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar alunos: " . $e->getMessage());
            return [];
        }
    }

    public function buscar($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, c.nome AS classe 
                FROM alunos a 
                JOIN classes c ON a.classe_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($aluno) {
                return [
                    "status" => "success",
                    "data" => $aluno
                ];
            } else {
                return [
                    "status" => "error",
                    "message" => "Aluno não encontrado"
                ];
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno: " . $e->getMessage());
            return [
                "status" => "error",
                "message" => "Erro ao buscar aluno"
            ];
        }
    }

    public function salvar($dados) {
        try {
            // Validar se classe existe
            $stmt = $this->db->prepare("SELECT id FROM classes WHERE id = :classe_id");
            $stmt->execute([':classe_id' => $dados['classe_id']]);
            if (!$stmt->fetch()) {
                return ["status" => "error", "message" => "Classe não encontrada"];
            }
            
            // Verificar se já existe aluno com mesmo nome e telefone (opcional)
            $stmt = $this->db->prepare("
                SELECT id FROM alunos 
                WHERE nome = :nome AND telefone = :telefone AND status = 'ativo'
            ");
            $stmt->execute([
                ':nome' => $dados['nome'],
                ':telefone' => $dados['telefone']
            ]);
            
            if ($stmt->fetch()) {
                return ["status" => "error", "message" => "Aluno já cadastrado com este nome e telefone"];
            }
            
            // Inserir novo aluno
            $stmt = $this->db->prepare("
                INSERT INTO alunos (nome, data_nascimento, telefone, classe_id, status, created_at) 
                VALUES (:nome, :data_nascimento, :telefone, :classe_id, 'ativo', NOW())
            ");
            $stmt->execute([
                ':nome' => $dados['nome'],
                ':data_nascimento' => $dados['data_nascimento'],
                ':telefone' => $dados['telefone'],
                ':classe_id' => $dados['classe_id']
            ]);
    
            return ["status" => "success", "message" => "Aluno cadastrado com sucesso"];
            
        } catch (PDOException $e) {
            error_log("Erro ao salvar aluno: " . $e->getMessage());
            return ["status" => "error", "message" => "Erro ao salvar aluno: " . $e->getMessage()];
        }
    }

    public function editar($id, $dados) {
        try {
            // Verificar se aluno existe
            $stmt = $this->db->prepare("SELECT id FROM alunos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                return ["status" => "error", "message" => "Aluno não encontrado"];
            }
            
            // Validar se classe existe
            $stmt = $this->db->prepare("SELECT id FROM classes WHERE id = :classe_id");
            $stmt->execute([':classe_id' => $dados['classe_id']]);
            if (!$stmt->fetch()) {
                return ["status" => "error", "message" => "Classe não encontrada"];
            }
            
            // Atualizar dados
            $stmt = $this->db->prepare("
                UPDATE alunos 
                SET nome = :nome, 
                    data_nascimento = :data_nascimento, 
                    telefone = :telefone, 
                    classe_id = :classe_id
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $id,
                ':nome' => $dados['nome'],
                ':data_nascimento' => $dados['data_nascimento'],
                ':telefone' => $dados['telefone'],
                ':classe_id' => $dados['classe_id']
            ]);
    
            return ["status" => "success", "message" => "Aluno atualizado com sucesso"];
            
        } catch (PDOException $e) {
            error_log("Erro ao editar aluno: " . $e->getMessage());
            return ["status" => "error", "message" => "Erro ao editar aluno"];
        }
    }
    
    public function excluir($id) {
        try {
            // Verificar se aluno existe
            $stmt = $this->db->prepare("SELECT id FROM alunos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                return ["status" => "error", "message" => "Aluno não encontrado"];
            }
            
            // Verificar se há presenças vinculadas (soft delete é mais seguro)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM presencas WHERE aluno_id = :id");
            $stmt->execute([':id' => $id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                // Se houver presenças, apenas marcar como inativo
                $stmt = $this->db->prepare("UPDATE alunos SET status = 'inativo' WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return ["status" => "success", "message" => "Aluno marcado como inativo (possui registros de presença)"];
            } else {
                // Se não houver presenças, pode excluir
                $stmt = $this->db->prepare("DELETE FROM alunos WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return ["status" => "success", "message" => "Aluno excluído com sucesso"];
            }
            
        } catch (PDOException $e) {
            error_log("Erro ao excluir aluno: " . $e->getMessage());
            return ["status" => "error", "message" => "Erro ao excluir aluno"];
        }
    }
}
?>