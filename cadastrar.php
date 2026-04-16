<?php
$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

$msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, telefone, email, senha) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $telefone, $email, $senhaHash);

    if($stmt->execute()){
        $msg = "Cadastro realizado com sucesso!";
    } else {
        $msg = "Erro: " . $conn->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastro</title>
</head>
<body>

<h2>Cadastro</h2>

<?php if($msg != "") echo "<p>$msg</p>"; ?>

<form method="POST">
  <input type="text" name="nome" placeholder="Nome" required><br>
  <input type="text" name="telefone" placeholder="Telefone" required><br>
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="senha" placeholder="Senha" required><br>
  <button type="submit">Cadastrar</button>
</form>

</body>
</html>
