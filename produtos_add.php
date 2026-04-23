<?php

header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
  echo json_encode(["status" => "erro", "mensagem" => "Erro conexão"]);
  exit;
}

$nome = trim($_POST['nome'] ?? '');
$preco = floatval(str_replace(",", ".", $_POST['preco'] ?? 0));
$imagem = trim($_POST['imagem'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$estoque = intval($_POST['quantidade_estoque'] ?? 0);

$tipo = $_POST['tipo'] ?? 'simples';
$tamanhos = $_POST['tamanhos'] ?? null;

// Regra
if ($tipo !== "roupa") {
  $tamanhos = null;
}

// Validação
if (empty($nome) || $preco <= 0) {
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Nome e preço obrigatórios"
  ]);
  exit;
}

// Prepare
$stmt = $conn->prepare("
  INSERT INTO produtos 
  (nome, preco, imagem, descricao, quantidade_estoque, tipo, tamanhos)
  VALUES (?,?,?,?,?,?,?)
");

if (!$stmt) {
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Erro no prepare: " . $conn->error
  ]);
  exit;
}

// Bind
$stmt->bind_param("sdssiss", $nome, $preco, $imagem, $descricao, $estoque, $tipo, $tamanhos);

// Execute
if ($stmt->execute()) {
  echo json_encode([
    "status" => "ok",
    "mensagem" => "Produto salvo com sucesso"
  ]);
} else {
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Erro ao salvar: " . $stmt->error
  ]);
}

$stmt->close();
$conn->close();

?>
