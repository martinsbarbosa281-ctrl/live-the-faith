<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost","usuario_db","senha_db","live_the_faith");
if($conn->connect_error){
    echo json_encode(["status"=>"erro","mensagem"=>$conn->connect_error]);
    exit;
}

$email = $_POST['email'];
$senha = $_POST['senha'];

$stmt = $conn->prepare("SELECT nome, senha FROM usuarios WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nome, $senhaHash);

if($stmt->num_rows > 0){
    $stmt->fetch();
    if(password_verify($senha, $senhaHash)){
        echo json_encode(["status"=>"ok","nome"=>$nome]);
    } else {
        echo json_encode(["status"=>"erro","mensagem"=>"Senha incorreta"]);
    }
} else {
    echo json_encode(["status"=>"erro","mensagem"=>"Usuário não encontrado"]);
}

$stmt->close();
$conn->close();
?>
