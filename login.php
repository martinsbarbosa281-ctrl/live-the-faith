<?php
session_start();

header('Content-Type: application/json');

$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

if($conn->connect_error){
    echo json_encode([
        "status"=>"erro",
        "mensagem"=>$conn->connect_error
    ]);
    exit;
}

$email = $_POST['email'] ?? "";
$senha = $_POST['senha'] ?? "";

if(empty($email) || empty($senha)){
    echo json_encode([
        "status"=>"erro",
        "mensagem"=>"Preencha todos os campos"
    ]);
    exit;
}

$admin_email = "admin@livefaith.com";
$admin_senha = "admin123";

if($email === $admin_email){

    if($senha === $admin_senha){

        $_SESSION['usuario_id'] = 0;
        $_SESSION['usuario_nome'] = "Administrador";
        $_SESSION['admin'] = 1;

        echo json_encode([
            "status" => "ok",
            "id" => 0,
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

$stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

$stmt->bind_result($id, $nome, $senhaHash);

if($stmt->num_rows > 0){

    $stmt->fetch();

    if(password_verify($senha, $senhaHash)){

        /* SALVA SESSÃO */
        $_SESSION['usuario_id'] = $id;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['admin'] = 0;

        echo json_encode([
            "status" => "ok",
            "id" => $id,
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
