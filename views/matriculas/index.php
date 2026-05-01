<?php  
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    // ❌ PATH_BASE REMOVIDO - não é mais necessário
    require_once __DIR__ . '/../../auth/valida_sessao.php';
    require_once __DIR__ . '/../../config/conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D - Matrículas</title>
    <link rel="icon" href="../../assets/images/biblia.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <?php if (file_exists(__DIR__ . '/../../assets/css/dashboard.css')): ?><link rel="stylesheet" href="../../assets/css/dashboard.css"><?php endif; ?>
    <style>
        :root { --color-primary: #3b82f6; --color-primary-dark: #2563eb; --color-success: #10b981; --color-warning: #f59e0b; --color-danger: #ef4444; --color-gray-50: #f8fafc; --color-gray-100: #f1f5f9; --color-gray-200: #e2e8f0; --color-gray-300: #cbd5e1; --color-gray-600: #475569; --color-gray-800: #1e293b; --color-white: #ffffff; --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1); --radius: 8px; --radius-lg: 12px; --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--color-gray-50); color: var(--color-gray-800); line-height: 1.5; padding-top: 56px; overflow-x: hidden; }
        .page-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { font-size: 1.5rem; font-weight: 600; color: var(--color-gray-800); margin: 0; }
        .navbar { box-shadow: var(--shadow-sm); transition: var(--transition); }
        .navbar.scrolled { box-shadow: var(--shadow-md); }
        .navbar-brand { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; }
        .navbar-brand img { height: 30px; width: auto; }
        .navbar-nav .nav-link { font-weight: 500; color: var(--color-gray-600); transition: var(--transition); }
        .navbar-nav .nav-link:hover { color: var(--color-primary-dark); }
        .navbar-nav .nav-link.active { color: var(--color-primary-dark); font-weight: 600; }
        .navbar-toggler { border: 1px solid var(--color-gray-200); padding: 0.25rem 0.75rem; }
        .navbar-toggler:focus { box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); outline: none; }
        .navbar .btn-outline-danger { border-radius: var(--radius); font-weight: 500; transition: var(--transition); }
        .navbar .btn-outline-danger:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        @media (max-width: 991px) {
            .navbar-collapse { background: var(--color-white); padding: 1rem; border-radius: var(--radius-lg); margin-top: 0.75rem; box-shadow: var(--shadow-md); border: 1px solid var(--color-gray-200); }
            .navbar-nav .nav-link { padding: 0.75rem 1rem; border-radius: var(--radius); }
            .navbar-nav .nav-link.active { background-color: rgba(59, 130, 246, 0.08); }
            .navbar .d-flex { width: 100%; justify-content: center; padding-top: 0.75rem; border-top: 1px solid var(--color-gray-200); margin-top: 0.5rem; }
            .navbar .btn-outline-danger { width: 100%; justify-content: center; }
        }
        .btn { border-radius: var(--radius); font-weight: 500; padding: 0.5rem 1.25rem; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .btn-primary { background-color: var(--color-primary); border: none; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background-color: var(--color-primary-dark); box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .btn-warning { background-color: rgba(245, 158, 11, 0.1); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.2); }
        .btn-warning:hover { background-color: rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.4); }
        .btn-danger { background-color: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background-color: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); }
        .btn-info { background-color: #0d9488; border: none; }
        .btn-info:hover { background-color: #0f766e; transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.875rem; }
        .table-container { background: var(--color-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200); overflow: hidden; }
        #tabelaMatriculas { margin-bottom: 0; }
        #tabelaMatriculas thead th { background-color: var(--color-gray-100); color: var(--color-gray-600); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.85rem 1rem; border-bottom: 2px solid var(--color-gray-200); }
        #tabelaMatriculas tbody td { padding: 0.85rem 1rem; vertical-align: middle; border-color: var(--color-gray-200); color: var(--color-gray-800); }
        #tabelaMatriculas tbody tr:hover { background-color: var(--color-gray-50); }
        .matricula-card { background: var(--color-white); border-radius: var(--radius-lg); border: 1px solid var(--color-gray-200); box-shadow: var(--shadow-sm); transition: var(--transition); height: 100%; display: flex; flex-direction: column; }
        .matricula-card:hover { box-shadow: var(--shadow-md); border-color: var(--color-gray-300); }
        .matricula-card .card-body { padding: 1rem; flex: 1; }
        .matricula-card .card-title { font-size: 1.1rem; font-weight: 600; color: var(--color-gray-800); margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--color-gray-200); }
        .matricula-card .info-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; font-size: 0.9rem; color: var(--color-gray-600); }
        .matricula-card .info-row strong { color: var(--color-gray-800); font-weight: 500; min-width: 90px; }
        .matricula-card .card-actions { display: flex; gap: 0.5rem; margin-top: auto; padding-top: 0.75rem; }
        .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }
        .modal-header { background-color: var(--color-primary); color: var(--color-white); padding: 1rem 1.25rem; border: none; }
        .modal-header.bg-warning { background-color: var(--color-warning) !important; color: #000 !important; }
        .modal-header.bg-danger { background-color: var(--color-danger) !important; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { background-color: var(--color-gray-50); padding: 0.85rem 1.25rem; border-top: 1px solid var(--color-gray-200); }
        .form-control, .form-select { border-radius: var(--radius); padding: 0.6rem 0.85rem; border: 1px solid var(--color-gray-300); transition: var(--transition); }
        .form-control:focus, .form-select:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-label { font-weight: 500; color: var(--color-gray-800); margin-bottom: 0.4rem; }
        .form-control.is-invalid { border-color: var(--color-danger); }
        .invalid-feedback { font-size: 0.75rem; color: var(--color-danger); margin-top: 0.25rem; }
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 1090; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; }
        .toast-container > * { pointer-events: auto; }
        .custom-toast { min-width: 300px; border-radius: var(--radius); box-shadow: var(--shadow-md); border: none; }
        .custom-toast.bg-success { background: linear-gradient(135deg, var(--color-success), #059669); color: white; }
        .custom-toast.bg-danger { background: linear-gradient(135deg, var(--color-danger), #dc2626); color: white; }
        .filters-card { background: var(--color-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200); padding: 1rem; margin-bottom: 1.5rem; }
        .badge-classe { background-color: rgba(59, 130, 246, 0.1); color: var(--color-primary-dark); padding: 0.3rem 0.7rem; border-radius: 9999px; font-weight: 500; font-size: 0.8rem; }
        .badge-trimestre { background-color: rgba(13, 148, 136, 0.1); color: #0d9488; padding: 0.3rem 0.7rem; border-radius: 9999px; font-weight: 500; font-size: 0.8rem; }
        .badge-status-ativo { background-color: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-status-inativo { background-color: rgba(148, 163, 184, 0.2); color: #64748b; }
        @media (max-width: 767px) { #tabelaContainer { display: none !important; } #cartoesContainer { display: flex !important; } .page-header { flex-direction: column; align-items: flex-start; } .page-header .btn { width: 100%; } }
        @media (min-width: 768px) { #tabelaContainer { display: block !important; } #cartoesContainer { display: none !important; } }
        @media (max-width: 576px) { .navbar-brand span { display: none; } .modal-dialog { margin: 0.5rem; } .modal-footer { flex-direction: column-reverse; } .modal-footer .btn { width: 100%; } }
        :focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
    </style>
</head>
<body>

<!-- Navbar CORRIGIDA (PATH_BASE removido) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">
            <img src="../../assets/images/biblia.png" alt="EBD" height="30" class="d-inline-block align-text-top">
            <span class="d-none d-sm-inline">Escola Bíblica</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="../dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../alunos/index.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="../classes/index.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="../professores/index.php">Professores</a></li>
                <li class="nav-item"><a class="nav-link" href="../congregacao/index.php">Congregações</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="../matriculas/index.php">Matrículas</a></li>
                <li class="nav-item"><a class="nav-link" href="../usuario/index.php">Usuários</a></li>
                <li class="nav-item"><a class="nav-link" href="../relatorios/index.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-danger btn-sm" href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i><span class="d-none d-md-inline">Sair</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="page-header">
        <div><h1 class="page-title">Gestão de Matrículas</h1><p class="text-muted mb-0">Gerencie as matrículas de alunos por classe e congregação</p></div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar"><i class="fas fa-plus"></i> Nova Matrícula</button>
            <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalMatriculaMassa"><i class="fas fa-users"></i> Migrar em Massa</button>
            <button class="btn btn-secondary" id="btnRefresh"><i class="fas fa-sync-alt"></i> Atualizar</button>
        </div>
    </div>

    <div class="filters-card">
        <form id="formFiltros" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Buscar</label>
                <input type="text" id="filtroBusca" class="form-control form-control-sm" placeholder="Nome do aluno...">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Congregação</label>
                <select id="filtroCongregacao" class="form-select form-select-sm">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Trimestre</label>
                <input type="text" id="filtroTrimestre" class="form-control form-control-sm" placeholder="Ex: 2026-T1">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select id="filtroStatus" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" id="btnAplicarFiltros" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i> Filtrar</button>
            </div>
            <div class="col-md-1">
                <button type="button" id="btnLimparFiltros" class="btn btn-sm btn-outline-secondary w-100"><i class="fas fa-times"></i></button>
            </div>
        </form>
    </div>

    <div id="tabelaContainer" class="table-container table-responsive">
        <table id="tabelaMatriculas" class="table table-bordered table-hover align-middle" style="width:100%">
            <thead>
                <tr><th>ID</th><th>Aluno</th><th>Classe</th><th>Congregação</th><th>Professor</th><th>Trimestre</th><th>Status</th><th class="text-center no-sort">Ações</th></tr>
            </thead>
            <tbody><tr><td colspan="8" class="text-center py-4 text-muted">Carregando dados...</td</tr></tbody>
        </table>
    </div>
    <div id="cartoesContainer" class="row g-3"></div>
</main>

<!-- Modal Cadastrar -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formCadastrarMatricula">
                <div class="modal-header"><h5 class="modal-title">Nova Matrícula</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Aluno <span class="text-danger">*</span></label>
                            <select id="aluno" name="aluno_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Classe <span class="text-danger">*</span></label>
                            <select id="classe" name="classe_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Congregação <span class="text-danger">*</span></label>
                            <select id="congregacao" name="congregacao_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Professor <span class="text-danger">*</span></label>
                            <select id="professor" name="professor_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trimestre <span class="text-danger">*</span></label>
                            <input type="text" id="trimestre" name="trimestre" class="form-control" placeholder="Ex: 2026-T1" required pattern="^\d{4}-T[1-4]$">
                            <div class="invalid-feedback">Formato: AAAA-T1, T2, T3 ou T4</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formEditarMatricula">
                <div class="modal-header bg-warning text-dark"><h5 class="modal-title">Editar Matrícula</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="id_edit" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Aluno <span class="text-danger">*</span></label>
                            <select id="aluno_edit" name="aluno_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Classe <span class="text-danger">*</span></label>
                            <select id="classe_edit" name="classe_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Congregação <span class="text-danger">*</span></label>
                            <select id="congregacao_edit" name="congregacao_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Professor <span class="text-danger">*</span></label>
                            <select id="professor_edit" name="professor_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trimestre <span class="text-danger">*</span></label>
                            <input type="text" id="trimestre_edit" name="trimestre" class="form-control" placeholder="Ex: 2026-T1" required pattern="^\d{4}-T[1-4]$">
                            <div class="invalid-feedback">Formato: AAAA-T1, T2, T3 ou T4</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="status_edit" name="status" class="form-select">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirmar Exclusão</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <p class="mb-0">Tem certeza que deseja excluir esta matrícula?</p>
                <small class="text-muted">ID: <span id="id_excluir_display"></span></small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExcluir"><i class="fas fa-trash"></i> Sim, Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Migração em Massa -->
<div class="modal fade" id="modalMatriculaMassa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formMatriculaMassa">
                <div class="modal-header bg-info text-white"><h5 class="modal-title">Migrar Matrículas em Massa</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3"><i class="fas fa-info-circle me-1"></i> Esta ação criará novas matrículas no trimestre de destino para todos os alunos <strong>ativos</strong> da congregação selecionada.</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Trimestre Atual <span class="text-danger">*</span></label>
                            <input type="text" id="trimestre_atual" name="trimestre_atual" class="form-control" placeholder="Ex: 2026-T1" required pattern="^\d{4}-T[1-4]$">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Novo Trimestre <span class="text-danger">*</span></label>
                            <input type="text" id="novo_trimestre" name="novo_trimestre" class="form-control" placeholder="Ex: 2026-T2" required pattern="^\d{4}-T[1-4]$">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Congregação <span class="text-danger">*</span></label>
                            <select id="congregacao_massa" name="congregacao_id" class="form-select" required><option value="">Selecione</option></select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="manter_status" name="manter_status" checked>
                                <label class="form-check-label" for="manter_status">Manter o status atual das matrículas migradas</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white"><i class="fas fa-sync-alt"></i> Confirmar Migração</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    let tabela = null;
    
    if (!$.fn.DataTable.isDataTable('#tabelaMatriculas')) {
        tabela = $('#tabelaMatriculas').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '../../controllers/matriculas.php',
                type: 'POST',
                data: function(d) {
                    d.acao = 'listarMatriculas';
                    d.congregacao = $('#filtroCongregacao').val();
                    d.trimestre = $('#filtroTrimestre').val();
                    d.status = $('#filtroStatus').val();
                },
                dataType: 'json',
                dataSrc: 'dados',
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    let msg = 'Erro ao carregar matrículas.';
                    if (xhr.status === 404) msg = 'Controller não encontrado.';
                    else if (xhr.status === 500) msg = 'Erro interno no servidor.';
                    else if (xhr.responseText) {
                        try {
                            const resp = JSON.parse(xhr.responseText);
                            if (resp.mensagem) msg = resp.mensagem;
                        } catch(e) { msg = 'Resposta inválida'; }
                    }
                    exibirMensagem('erro', msg);
                }
            },
            columns: [
                { data: 'id', width: "5%" },
                { data: 'aluno' },
                { data: 'classe', render: function(d) { return d ? `<span class="badge-classe">${escapeHtml(d)}</span>` : '-'; } },
                { data: 'congregacao' },
                { data: 'usuario' },
                { data: 'trimestre', render: function(d) { return d ? `<span class="badge-trimestre">${escapeHtml(d)}</span>` : '-'; } },
                { data: 'status', render: function(d) {
                    const cls = d === 'ativo' ? 'badge-status-ativo' : 'badge-status-inativo';
                    return `<span class="badge ${cls}">${escapeHtml(d)}</span>`;
                }},
                {
                    data: 'id', className: 'text-center', orderable: false, width: "10%",
                    render: function(id) {
                        return `<div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-warning btn-sm btnEditar" data-id="${id}" data-bs-toggle="modal" data-bs-target="#modalEditar" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm btnExcluir" data-id="${id}" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                        </div>`;
                    }
                }
            ],
            responsive: true,
            language: {
                sEmptyTable: "Nenhuma matrícula encontrada", sInfo: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                sInfoEmpty: "Nenhum registro", sInfoFiltered: "(filtrado de _MAX_)", sLengthMenu: "_MENU_ por página",
                sLoadingRecords: "Carregando...", sZeroRecords: "Sem resultados", sSearch: "Buscar:",
                oPaginate: { sNext: "Próximo", sPrevious: "Anterior" }
            },
            pageLength: 10, 
            lengthMenu: [10, 25, 50, 100], 
            order: [[0, 'desc']]
        });
    } else {
        tabela = $('#tabelaMatriculas').DataTable();
    }
    
    function escapeHtml(text) { if (!text) return ''; const d = document.createElement('div'); d.textContent = text; return d.innerHTML; }
    
    function exibirMensagem(tipo, mensagem) {
        const container = document.getElementById('toastContainer');
        const bg = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const icon = tipo === 'sucesso' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';
        const toast = document.createElement('div');
        toast.className = `toast custom-toast ${bg} text-white show`;
        toast.innerHTML = `<div class="toast-body d-flex align-items-center gap-2"><span>${icon}</span><span class="flex-grow-1">${mensagem}</span><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
    }
    
    function renderizarCartoes(matriculas) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        container.innerHTML = '';
        if (!matriculas || !Array.isArray(matriculas) || matriculas.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">Nenhuma matrícula encontrada</div>';
            return;
        }
        matriculas.forEach(m => {
            const statusCls = m.status === 'ativo' ? 'badge-status-ativo' : 'badge-status-inativo';
            container.innerHTML += `<div class="col-12"><div class="matricula-card"><div class="card-body">
                <h5 class="card-title">${escapeHtml(m.aluno)}</h5>
                <div class="info-row"><strong><i class="fas fa-chalkboard"></i> Classe:</strong> ${escapeHtml(m.classe)}</div>
                <div class="info-row"><strong><i class="fas fa-building"></i> Congregação:</strong> ${escapeHtml(m.congregacao)}</div>
                <div class="info-row"><strong><i class="fas fa-user"></i> Professor:</strong> ${escapeHtml(m.usuario || '-')}</div>
                <div class="info-row"><strong><i class="fas fa-calendar"></i> Trimestre:</strong> <span class="badge-trimestre">${escapeHtml(m.trimestre)}</span></div>
                <div class="info-row"><strong><i class="fas fa-circle"></i> Status:</strong> <span class="badge ${statusCls}">${escapeHtml(m.status)}</span></div>
                <div class="card-actions">
                    <button class="btn btn-warning btn-sm btnEditar flex-fill" data-id="${m.id}" data-bs-toggle="modal" data-bs-target="#modalEditar"><i class="fas fa-edit me-1"></i> Editar</button>
                    <button class="btn btn-danger btn-sm btnExcluir flex-fill" data-id="${m.id}"><i class="fas fa-trash-alt me-1"></i> Excluir</button>
                </div>
            </div></div></div>`;
        });
    }
    
    function atualizarCards() {
        $.ajax({
            url: '../../controllers/matriculas.php',
            type: 'POST',
            data: { acao: 'listarMatriculas', length: 10, start: 0 },
            dataType: 'json',
            success: function(res) { if (res.sucesso && Array.isArray(res.dados)) renderizarCartoes(res.dados); },
            error: function(xhr) { console.error('Erro cards:', xhr.responseText); }
        });
    }
    
    function carregarSelects() {
        $.ajax({
            url: '../../controllers/matriculas.php',
            type: 'POST',
            data: { acao: 'carregarSelects' },
            dataType: 'json',
            success: function(res) {
                if (res.sucesso && res.dados) {
                    preencherSelect('#aluno,#aluno_edit', res.dados.alunos);
                    preencherSelect('#classe,#classe_edit', res.dados.classes);
                    preencherSelect('#congregacao,#congregacao_edit,#congregacao_massa', res.dados.congregacoes);
                    preencherSelect('#professor,#professor_edit', res.dados.usuarios);
                    const fc = $('#filtroCongregacao'); fc.find('option:not(:first)').remove();
                    res.dados.congregacoes.forEach(c => fc.append(`<option value="${c.id}">${escapeHtml(c.nome)}</option>`));
                }
            },
            error: function(xhr) { console.error('Erro selects:', xhr.responseText); }
        });
    }
    
    function preencherSelect(seletor, items) {
        $(seletor).each(function() {
            const cur = $(this).val();
            $(this).html('<option value="">Selecione</option>');
            if (Array.isArray(items)) items.forEach(i => $(this).append(`<option value="${i.id}">${escapeHtml(i.nome)}</option>`));
            if (cur) $(this).val(cur);
        });
    }
    
    function preencherTrimestresMassa() {
        const h = new Date(), m = h.getMonth()+1, a = h.getFullYear(), t = Math.ceil(m/3);
        let tp = t+1, ap = a; if (tp>4) { tp=1; ap++; }
        $('#trimestre_atual').val(`${a}-T${t}`); $('#novo_trimestre').val(`${ap}-T${tp}`);
    }
    
    function aplicarFiltros() {
        if (tabela) tabela.ajax.reload(null, true);
    }
    
    $('#formCadastrarMatricula').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { e.stopPropagation(); $(this).addClass('was-validated'); exibirMensagem('erro', 'Preencha os campos obrigatórios.'); return; }
        $(this).removeClass('was-validated');
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');
        $.ajax({
            url: '../../controllers/matriculas.php', method: 'POST',
            data: {
                acao: 'criarMatricula', aluno_id: $('#aluno').val(), classe_id: $('#classe').val(),
                congregacao_id: $('#congregacao').val(), professor_id: $('#professor').val(),
                trimestre: $('#trimestre').val(), status: $('#status').val(),
                data_matricula: new Date().toISOString().split('T')[0]
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Cadastrar');
                if (res.sucesso) { $('#modalCadastrar').modal('hide'); $('#formCadastrarMatricula')[0].reset(); if (tabela) tabela.ajax.reload(null, false); atualizarCards(); exibirMensagem('sucesso', res.mensagem || 'Cadastrada com sucesso!'); }
                else { exibirMensagem('erro', res.mensagem || 'Erro ao cadastrar'); }
            },
            error: function() { btn.prop('disabled', false).html('<i class="fas fa-save"></i> Cadastrar'); exibirMensagem('erro', 'Erro de comunicação com o servidor'); }
        });
    });
    
    $(document).on('click', '.btnEditar', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '../../controllers/matriculas.php', method: 'POST',
            data: { acao: 'buscarMatricula', id: id },
            dataType: 'json',
            success: function(res) {
                if (res.sucesso && res.dados) {
                    const m = res.dados;
                    $('#id_edit').val(m.id); $('#aluno_edit').val(m.aluno_id); $('#classe_edit').val(m.classe_id);
                    $('#congregacao_edit').val(m.congregacao_id); $('#professor_edit').val(m.usuario_id);
                    $('#trimestre_edit').val(m.trimestre); $('#status_edit').val(m.status);
                    $('#modalEditar').modal('show');
                } else { exibirMensagem('erro', res.mensagem || 'Erro ao buscar dados'); }
            },
            error: function() { exibirMensagem('erro', 'Erro de comunicação com o servidor'); }
        });
    });
    
    $('#formEditarMatricula').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { e.stopPropagation(); $(this).addClass('was-validated'); exibirMensagem('erro', 'Preencha os campos obrigatórios.'); return; }
        $(this).removeClass('was-validated');
        const id = $('#id_edit').val(), btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');
        $.ajax({
            url: '../../controllers/matriculas.php', method: 'POST',
            data: {
                acao: 'atualizarMatricula', id: id, aluno_id: $('#aluno_edit').val(), classe_id: $('#classe_edit').val(),
                congregacao_id: $('#congregacao_edit').val(), professor_id: $('#professor_edit').val(),
                trimestre: $('#trimestre_edit').val(), status: $('#status_edit').val(),
                data_matricula: new Date().toISOString().split('T')[0]
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar');
                if (res.sucesso) { $('#modalEditar').modal('hide'); if (tabela) tabela.ajax.reload(null, false); atualizarCards(); exibirMensagem('sucesso', res.mensagem || 'Atualizada com sucesso!'); }
                else { exibirMensagem('erro', res.mensagem || 'Erro ao atualizar'); }
            },
            error: function() { btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar'); exibirMensagem('erro', 'Erro de comunicação com o servidor'); }
        });
    });
    
    $(document).on('click', '.btnExcluir', function() {
        $('#id_excluir_display').text($(this).data('id'));
        $('#btnConfirmarExcluir').data('id', $(this).data('id'));
        $('#modalExcluir').modal('show');
    });
    
    $('#btnConfirmarExcluir').on('click', function() {
        const id = $(this).data('id'); if (!id) return;
        const btn = $(this); btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Excluindo...');
        $.ajax({
            url: '../../controllers/matriculas.php', method: 'POST',
            data: { acao: 'excluirMatricula', id: id },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Sim, Excluir');
                if (res.sucesso) { $('#modalExcluir').modal('hide'); if (tabela) tabela.ajax.reload(null, false); atualizarCards(); exibirMensagem('sucesso', res.mensagem || 'Excluída com sucesso!'); }
                else { exibirMensagem('erro', res.mensagem || 'Erro ao excluir'); }
            },
            error: function() { btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Sim, Excluir'); exibirMensagem('erro', 'Erro de comunicação com o servidor'); }
        });
    });
    
    $('#formMatriculaMassa').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { e.stopPropagation(); $(this).addClass('was-validated'); exibirMensagem('erro', 'Preencha os campos obrigatórios.'); return; }
        $(this).removeClass('was-validated');
        const ta = $('#trimestre_atual').val(), tn = $('#novo_trimestre').val();
        if (!confirm(`Confirmar migração de ${ta} para ${tn}?`)) return;
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Migrando...');
        $.ajax({
            url: '../../controllers/matriculas.php', method: 'POST',
            data: {
                acao: 'migrarMatriculas', trimestre_atual: ta, novo_trimestre: tn,
                congregacao_id: $('#congregacao_massa').val(), manter_status: $('#manter_status').is(':checked')
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Confirmar Migração');
                if (res.sucesso) { $('#modalMatriculaMassa').modal('hide'); $('#formMatriculaMassa')[0].reset(); if (tabela) tabela.ajax.reload(null, false); atualizarCards(); exibirMensagem('sucesso', res.mensagem || 'Migração concluída!'); }
                else { exibirMensagem('erro', res.mensagem || 'Erro na migração'); }
            },
            error: function() { btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Confirmar Migração'); exibirMensagem('erro', 'Erro de comunicação com o servidor'); }
        });
    });
    
    $('#btnRefresh').on('click', function() { if (tabela) tabela.ajax.reload(null, false); atualizarCards(); exibirMensagem('sucesso', 'Lista atualizada!'); });
    $('#btnAplicarFiltros').on('click', aplicarFiltros);
    $('#btnLimparFiltros').on('click', function() { $('#formFiltros')[0].reset(); aplicarFiltros(); });
    $('#filtroBusca').on('keyup', function(e) { if (e.key === 'Enter') aplicarFiltros(); });
    $('#modalMatriculaMassa').on('show.bs.modal', preencherTrimestresMassa);
    $('#modalCadastrar, #modalEditar, #modalExcluir, #modalMatriculaMassa').on('hidden.bs.modal', function() { $(this).find('form')[0]?.reset(); $(this).find('.is-invalid').removeClass('is-invalid'); $(this).find('.was-validated').removeClass('was-validated'); });
    $('.needs-validation, form').on('input', 'input, select', function() { if ($(this).hasClass('is-invalid')) $(this).removeClass('is-invalid'); });
    $('.navbar-nav .nav-link').on('click', function() { if ($('.navbar-toggler').is(':visible')) $('.navbar-collapse').collapse('hide'); });
    $(window).on('scroll', function() { if ($(window).scrollTop() > 10) $('.navbar').addClass('scrolled'); else $('.navbar').removeClass('scrolled'); });
    
    carregarSelects(); atualizarCards();
});
</script>
</body>
</html>