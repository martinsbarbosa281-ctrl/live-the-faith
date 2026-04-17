<?php
$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

$nome = $_POST['nome'];
$preco = $_POST['preco'];
$imagem = $_POST['imagem'];
$descricao = $_POST['descricao'];

$stmt = $conn->prepare("INSERT INTO produtos (nome, preco, imagem, descricao) VALUES (?,?,?,?)");
$stmt->bind_param("sdss", $nome, $preco, $imagem, $descricao);
$stmt->execute();
?>
