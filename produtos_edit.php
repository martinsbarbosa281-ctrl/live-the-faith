<?php
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost","root","SUA_SENHA","live_the_faith");

if($conn->connect_error){
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Erro conexão: " . $conn->connect_error
  ]);
  exit;
}

$id = $_POST['id'] ?? 0;
$nome = $_POST['nome'] ?? '';
$preco = $_POST['preco'] ?? 0;
$descricao = $_POST['descricao'] ?? '';

$preco = str_replace(",", ".", $preco);

if(!$id || !$nome || !$preco || !$descricao){
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Campos inválidos"
  ]);
  exit;
}

$stmt = $conn->prepare("
  UPDATE produtos 
  SET nome=?, preco=?, descricao=? 
  WHERE id=?
");

if(!$stmt){
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Erro prepare: " . $conn->error
  ]);
  exit;
}

$stmt->bind_param("sdsi", $nome, $preco, $descricao, $id);

if($stmt->execute()){
  echo json_encode([
    "status" => "ok",
    "mensagem" => "Produto atualizado com sucesso"
  ]);
} else {
  echo json_encode([
    "status" => "erro",
    "mensagem" => "Erro execute: " . $stmt->error
  ]);
}

$stmt->close();
$conn->close();
?>
