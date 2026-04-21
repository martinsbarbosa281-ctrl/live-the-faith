<?php
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, nome, preco, imagem, descricao, quantidade_estoque FROM produtos";
$result = $conn->query($sql);

$produtos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}

echo json_encode($produtos);

$conn->close();
?>
