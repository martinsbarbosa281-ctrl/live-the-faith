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
            "id" => 0, // 🌟 Admin fixo pode ter ID 0
            "nome" => "Administrador",
            "email" => $email, 
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
// 🌟 ALTERAÇÃO AQUI: Adicionado o "id" no SELECT
$stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
// 🌟 ALTERAÇÃO AQUI: Adicionado a variável $id no bind_result
$stmt->bind_result($id, $nome, $senhaHash);

if($stmt->num_rows > 0){
    $stmt->fetch();

    if(password_verify($senha, $senhaHash)){
        echo json_encode([
            "status" => "ok",
            "id" => $id, // 🌟 ALTERAÇÃO AQUI: Agora enviamos o ID real do banco de dados!
            "nome" => $nome,
            "email" => $email, 
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
