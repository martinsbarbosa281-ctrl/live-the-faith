<?php
// ==========================================
// Segurança de Sessão e API
// ==========================================
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

header('Content-Type: application/json; charset=utf-8');
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// ==========================================
// Rate Limiting / Força Bruta
// ==========================================
if (!isset($_SESSION['tentativas'])) {
    $_SESSION['tentativas'] = 0;
    $_SESSION['ultimo_acesso'] = time();
}

if ($_SESSION['tentativas'] >= 5 && (time() - $_SESSION['ultimo_acesso']) < 300) {
    echo json_encode(["status" => "erro", "code" => 429]);
    exit;
}

// ==========================================
// Sanitização e Validação
// ==========================================
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = filter_input(INPUT_POST, 'senha', FILTER_DEFAULT);

if (!$email || !$senha) {
    echo json_encode(["status" => "erro", "code" => 400]);
    exit;
}

// ==========================================
// 👑 VERIFICAÇÃO FIXA DE ADMIN
// ==========================================
if ($email === 'admin@livefaith.com' && $senha === 'admin123') {
    unset($_SESSION['tentativas']);

    $usuarioAdmin = [
        "id" => 1,
        "nome" => "Administrador",
        "email" => $email,
        "admin" => 1
    ];

    $_SESSION['usuario_temp'] = $usuarioAdmin;

    echo json_encode([
        "status" => "ok",
        "usuario" => $usuarioAdmin
    ]);
    exit;
}

// ==========================================
// CONEXÃO COM O BANCO DE DADOS (Para outros usuários)
// ==========================================
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
    echo json_encode(["status" => "erro", "code" => 500]);
    exit;
}

// Verifica no banco se a coluna se chama 'is_admin' ou 'admin'
$sqlCheck = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'is_admin'");
if ($sqlCheck && $sqlCheck->num_rows > 0) {
    $stmt = $conn->prepare("SELECT id, nome, senha, is_admin FROM usuarios WHERE email = ?");
} else {
    $stmt = $conn->prepare("SELECT id, nome, senha, admin FROM usuarios WHERE email = ?");
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {

    if (password_verify($senha, $row['senha'])) {

        session_regenerate_id(true);
        unset($_SESSION['tentativas']);

        $isAdmin = 0;
        if (isset($row['is_admin'])) {
            $isAdmin = (int)$row['is_admin'];
        } elseif (isset($row['admin'])) {
            $isAdmin = (int)$row['admin'];
        }

        $usuarioDados = [
            "id" => $row['id'],
            "nome" => htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'),
            "email" => $email,
            "admin" => $isAdmin
        ];

        $_SESSION['usuario_temp'] = $usuarioDados;

        echo json_encode([
            "status" => "ok",
            "usuario" => $usuarioDados
        ]);
        exit;
    }
}

// Registro de tentativa inválida
$_SESSION['tentativas']++;
$_SESSION['ultimo_acesso'] = time();

echo json_encode(["status" => "erro", "code" => 401]);
exit;
?>
