<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

if($conn->connect_error){
    echo json_encode([
        "status"=>"erro",
        "mensagem"=>$conn->connect_error
    ]);
    exit;
}

/* 🔒 EVITA ERRO DE VARIÁVEL NÃO DEFINIDA */
$email = $_POST['email'] ?? "";
$senha = $_POST['senha'] ?? "";

/* 🔴 VALIDAÇÃO */
if(empty($email) || empty($senha)){
    echo json_encode([
        "status"=>"erro",
        "mensagem"=>"Preencha todos os campos"
    ]);
    exit;
}

/* 👑 ADMIN FIXO */
$admin_email = "admin@livefaith.com";
$admin_senha = "admin123";

/* VERIFICA SE É ADMIN */
if($email === $admin_email){

    if($senha === $admin_senha){
        echo json_encode([
            "status" => "ok",
            "nome" => "Administrador",
            "email" => $email, // ✔ importante
            "admin" => 1
        ]);
    } else {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Senha incorreta"
        ]);
    }

    exit;
}

/* 👤 USUÁRIO NORMAL */
$stmt = $conn->prepare("SELECT nome, senha FROM usuarios WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nome, $senhaHash);

if($stmt->num_rows > 0){
    $stmt->fetch();

    if(password_verify($senha, $senhaHash)){
        echo json_encode([
            "status" => "ok",
            "nome" => $nome,
            "email" => $email, // ✔ ESSENCIAL PRO TROCAR SENHA
            "admin" => 0
        ]);
    } else {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Senha incorreta"
        ]);
    }

} else {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Usuário não encontrado"
    ]);
}

$stmt->close();
$conn->close();
?>
