<?php

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

if ($conn->connect_error) {
  echo json_encode(["status"=>"erro","mensagem"=>"Erro conexão"]);
  exit;
}

$nome = $_POST['nome'] ?? '';
$preco = $_POST['preco'] ?? 0;
$imagem = $_POST['imagem'] ?? '';
$descricao = $_POST['descricao'] ?? '';

$preco = str_replace(",", ".", $preco);

$stmt = $conn->prepare("INSERT INTO produtos (nome, preco, imagem, descricao) VALUES (?,?,?,?)");

if(!$stmt){
  echo json_encode(["status"=>"erro","mensagem"=>$conn->error]);
  exit;
}

$stmt->bind_param("sdss", $nome, $preco, $imagem, $descricao);

if(!$stmt->execute()){
  echo json_encode([
    "status"=>"erro",
    "mensagem"=>$stmt->error
  ]);
  exit;
}

echo json_encode([
  "status"=>"ok",
  "mensagem"=>"Produto salvo com sucesso"
]);

$stmt->close();
$conn->close();

?>
