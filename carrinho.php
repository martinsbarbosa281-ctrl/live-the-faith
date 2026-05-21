<?php
session_start(); // Essencial para ler o que o login salvou

// Se não houver sessão, ele não pode ver o carrinho
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html"); // Ou sua página de login
    exit;
}

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");
$user_id = $_SESSION['usuario_id'];

// O filtro WHERE garante que um usuário não veja o carrinho do outro
$sql = "SELECT c.id, p.nome, p.preco, p.imagem, c.quantidade, c.tamanho 
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id 
        WHERE c.usuario_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head><title>Meu Carrinho</title></head>
<body>
    <h1>Seu Carrinho</h1>
    <?php while($item = $resultado->fetch_assoc()): ?>
        <div>
            <img src="<?= $item['imagem'] ?>" width="50">
            <p><?= $item['nome'] ?> - R$ <?= $item['preco'] ?> (Qtd: <?= $item['quantidade'] ?>)</p>
        </div>
    <?php endwhile; ?>
</body>
</html>
