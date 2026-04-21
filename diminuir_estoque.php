<?php

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
  echo json_encode(["status" => "erro", "mensagem" => "Erro conexão"]);
  exit;
}

$id = $_POST['id'] ?? 0;
$quantidade = $_POST['quantidade'] ?? 1;

/* 🔥 VERIFICA ESTOQUE PRIMEIRO */
$check = $conn->prepare("SELECT quantidade_estoque FROM produtos WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if($result->num_rows === 0){
  echo json_encode(["status" => "erro", "mensagem" => "Produto não encontrado"]);
  exit;
}

$row = $result->fetch_assoc();

if($row['quantidade_estoque'] < $quantidade){
  echo json_encode(["status" => "erro", "mensagem" => "Sem estoque suficiente"]);
  exit;
}

/* 🔥 DIMINUI ESTOQUE */
$stmt = $conn->prepare("
  UPDATE produtos
  SET quantidade_estoque = quantidade_estoque - ?
  WHERE id = ?
");

$stmt->bind_param("ii", $quantidade, $id);

if($stmt->execute()){
  echo json_encode(["status" => "ok", "mensagem" => "Estoque atualizado"]);
}else{
  echo json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar"]);
}

$stmt->close();
$conn->close();

?>
