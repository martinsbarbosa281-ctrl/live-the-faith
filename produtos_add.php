<?php

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
  echo json_encode(["status" => "erro", "mensagem" => "Erro conexão"]);
  exit;
}

$nome = $_POST['nome'] ?? '';
$preco = $_POST['preco'] ?? 0;
$imagem = $_POST['imagem'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$estoque = $_POST['quantidade_estoque'] ?? 0;

$tipo = $_POST['tipo'] ?? 'simples';
$tamanhos = $_POST['tamanhos'] ?? null;

$preco = str_replace(",", ".", $preco);

/* 🔥 CORRIGIDO AQUI */
if ($tipo === "roupa") {
  // não zera mais o estoque
} else {
  $tamanhos = null;
}

$stmt = $conn->prepare("
  INSERT INTO produtos 
  (nome, preco, imagem, descricao, quantidade_estoque, tipo, tamanhos)
  VALUES (?,?,?,?,?,?,?)
");

$stmt->bind_param("sdssiss", $nome, $preco, $imagem, $descricao, $estoque, $tipo, $tamanhos);

$stmt->execute();

echo json_encode([
  "status" => "ok",
  "mensagem" => "Produto salvo com sucesso"
]);

$stmt->close();
$conn->close();

?>
