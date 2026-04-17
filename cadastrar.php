<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

if($conn->connect_error){
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro de conexão: " . $conn->connect_error
    ]);
    exit;
}

$nome = $_POST['nome'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if(!$nome || !$telefone || !$email || !$senha){
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Preencha todos os campos"
    ]);
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

/* 👑 ADMIN FIXO */
$is_admin = 0;
if($email === "admin@livefaith.com"){
    $is_admin = 1;
}

$stmt = $conn->prepare("
    INSERT INTO usuarios (nome, telefone, email, senha, is_admin)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssi", $nome, $telefone, $email, $senhaHash, $is_admin);

if($stmt->execute()){
    echo json_encode([
        "status" => "ok",
        "mensagem" => "Cadastro realizado com sucesso"
    ]);
} else {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
