<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");
$user_id = $_SESSION['usuario_id'];

$sql = "SELECT f.id, p.nome, p.preco, p.imagem 
        FROM favoritos f 
        JOIN produtos p ON f.produto_id = p.id 
        WHERE f.usuario_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head><title>Meus Favoritos</title></head>
<body>
    <h1>Seus Favoritos ❤️</h1>
    <?php while($fav = $resultado->fetch_assoc()): ?>
        <div>
            <img src="<?= $fav['imagem'] ?>" width="100">
            <h3><?= $fav['nome'] ?></h3>
        </div>
    <?php endwhile; ?>
</body>
</html>
