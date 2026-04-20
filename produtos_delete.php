<?php

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

if ($conn->connect_error) {
  echo json_encode(["status"=>"erro","mensagem"=>"Erro na conexão"]);
  exit;
}

$id = $_GET['id'] ?? 0;

// garante que é número
$id = intval($id);

$stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
  echo json_encode(["status"=>"ok","mensagem"=>"Produto deletado"]);
} else {
  echo json_encode(["status"=>"erro","mensagem"=>"Erro ao deletar"]);
}

$stmt->close();
$conn->close();

?>
