<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Sessão expirada ou usuário não logado."]);
    exit;
}

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");
$user_id = $_SESSION['usuario_id'];

$acao = $_POST['acao'] ?? '';
$carrinho_id = intval($_POST['carrinho_id'] ?? 0);

if ($acao === 'atualizar_quantidade') {
    $nova_qtd = intval($_POST['nova_quantidade'] ?? 1);
    
    // Atualiza apenas se o registro do carrinho pertencer ao usuário logado
    $sql = "UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $nova_qtd, $carrinho_id, $user_id);
    
    if ($stmt->execute()) {
        // Recalcula os novos valores buscando o preço e o frete reais no banco
        $sqlPreco = "SELECT p.preco, IFNULL(c.valor_frete, 0) as valor_frete 
                     FROM carrinho c JOIN produtos p ON c.produto_id = p.id WHERE c.id = ?";
        $stmtP = $conn->prepare($sqlPreco);
        $stmtP->bind_param("i", $carrinho_id);
        $stmtP->execute();
        $res = $stmtP->get_result()->fetch_assoc();
        
        $precoUnitario = floatval($res['preco']);
        $freteItem = floatval($res['valor_frete']);
        
        $novoSubtotal = $precoUnitario * $nova_qtd;
        $novoTotalItem = $novoSubtotal + $freteItem;
        
        echo json_encode([
            "sucesso" => true,
            "novo_subtotal" => $novoSubtotal,
            "novo_subtotal_formatado" => number_format($novoSubtotal, 2, ',', '.'),
            "novo_total_item_formatado" => number_format($novoTotalItem, 2, ',', '.')
        ]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar banco de dados."]);
    }

} elseif ($acao === 'remover_item') {
    // Exclui com a trava de segurança do usuario_id
    $sql = "DELETE FROM carrinho WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $carrinho_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao remover item."]);
    }
}

$conn->close();
?>
