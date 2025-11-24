
<?php
// index.php - Arquivo principal do sistema
require_once 'db.php';

// --- LÓGICA PHP (BACKEND) ---

// 1. DELETAR
if (isset($_GET['deletar'])) {
    $id = $_GET['deletar'];
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php"); // Recarrega a página limpa
    exit();
}

// 2. INSERIR OU ATUALIZAR (SALVAR)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $qtd = $_POST['quantidade'];
    $preco = $_POST['preco'];
    $desc = $_POST['descricao'];
    $id = $_POST['id_produto']; // Se tiver ID, é edição. Se não, é novo.

    if ($id) {
        // Atualizar existente
        $sql = "UPDATE produtos SET nome=?, quantidade=?, preco=?, descricao=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $qtd, $preco, $desc, $id]);
    } else {
        // Criar novo
        $sql = "INSERT INTO produtos (nome, quantidade, preco, descricao) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $qtd, $preco, $desc]);
    }
    header("Location: index.php");
    exit();
}

// 3. LISTAR (LER)
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque - Projeto Prático</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📦 Controle de Estoque v1.0</h1>
        <button class="btn btn-primary" onclick="abrirModal()">
            + Novo Produto
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Qtd.</th>
                        <th>Preço (R$)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p): ?>
                        <tr class="<?= $p['quantidade'] < 5 ? 'table-warning' : '' ?>">
                            <td><?= $p['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($p['descricao']) ?></small>
                            </td>
                            <td><?= $p['quantidade'] ?></td>
                            <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" 
                                    onclick="editarProduto(
                                        <?= $p['id'] ?>, 
                                        '<?= addslashes($p['nome']) ?>', 
                                        <?= $p['quantidade'] ?>, 
                                        <?= $p['preco'] ?>, 
                                        '<?= addslashes($p['descricao']) ?>'
                                    )">
                                    Editar
                                </button>
                                
                                <a href="index.php?deletar=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir este item?');">
                                    Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($produtos) == 0): ?>
                <p class="text-center mt-3 text-muted">Nenhum produto cadastrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProduto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_produto" id="id_produto">

                    <div class="mb-3">
                        <label class="form-label">Nome do Produto</label>
                        <input type="text" name="nome" id="nome" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantidade" id="quantidade" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço</label>
                            <input type="number" step="0.01" name="preco" id="preco" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Instância do Modal do Bootstrap
    const modalElement = document.getElementById('modalProduto');
    const modal = new bootstrap.Modal(modalElement);

    // Função para abrir modal limpo (Novo Cadastro)
    function abrirModal() {
        document.getElementById('modalTitulo').innerText = 'Novo Produto';
        document.getElementById('id_produto').value = ''; // Limpa ID
        document.getElementById('nome').value = '';
        document.getElementById('quantidade').value = '';
        document.getElementById('preco').value = '';
        document.getElementById('descricao').value = '';
        modal.show();
    }

    // Função para abrir modal preenchido (Edição)
    function editarProduto(id, nome, qtd, preco, desc) {
        document.getElementById('modalTitulo').innerText = 'Editar Produto';
        document.getElementById('id_produto').value = id; // Define o ID
        document.getElementById('nome').value = nome;
        document.getElementById('quantidade').value = qtd;
        document.getElementById('preco').value = preco;
        document.getElementById('descricao').value = desc;
        modal.show();
    }
</script>

</body>
</html>
