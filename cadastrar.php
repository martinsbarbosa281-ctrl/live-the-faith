<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost","usuario_db","senha_db","live_the_faith");
if($conn->connect_error){
    echo json_encode(["status"=>"erro","mensagem"=>$conn->connect_error]);
    exit;
}

$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$email = $_POST['email'];
$senha = $_POST['senha'];

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (nome, telefone, email, senha) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $telefone, $email, $senhaHash);

if($stmt->execute()){
    echo json_encode(["status"=>"ok"]);
} else {
    echo json_encode(["status"=>"erro","mensagem"=>$conn->error]);
}

$stmt->close();
$conn->close();
?>
