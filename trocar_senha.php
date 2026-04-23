<?php

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro na conexão"]);
    exit;
}

$email = $_POST["email"] ?? "";
$novaSenha = $_POST["novaSenha"] ?? "";

/* 🔴 VALIDAÇÃO CORRETA */
if (empty($email) || empty($novaSenha)) {
    echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos"]);
    exit;
}

/* 🔍 VERIFICA SE USUÁRIO EXISTE */
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "erro", "mensagem" => "Usuário não encontrado"]);
    exit;
}

/* 🔒 NOVA SENHA */
$novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

/* 🔄 ATUALIZA */
$stmt2 = $conn->prepare("UPDATE usuarios SET senha=? WHERE email=?");
$stmt2->bind_param("ss", $novaSenhaHash, $email);

if ($stmt2->execute()) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar"]);
}

$conn->close();

?>