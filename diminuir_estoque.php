<?php

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
  echo json_encode(["status" => "erro", "mensagem" => "Erro conexão"]);
  exit;
}

$id = $_POST['id'] ?? 0;
$quantidade = intval($_POST['quantidade'] ?? 1);
$tamanhoEscolhido = $_POST['tamanho'] ?? ''; // 🔥 ADICIONADO: Recebe o tamanho (Ex: P, M, G)

/* 🔥 VERIFICA TIPO E ESTOQUE PRIMEIRO */
$check = $conn->prepare("SELECT quantidade_estoque, tipo, tamanhos FROM produtos WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if($result->num_rows === 0){
  echo json_encode(["status" => "erro", "mensagem" => "Produto não encontrado"]);
  exit;
}

$row = $result->fetch_assoc();
$tipo = $row['tipo'];
$estoqueGeralAtual = intval($row['quantidade_estoque']);

// --- LÓGICA PARA ROUPAS (COM TAMANHOS) ---
if($tipo === "roupa") {
    if(empty($tamanhoEscolhido)) {
        echo json_encode(["status" => "erro", "mensagem" => "Tamanho não especificado para esta roupa."]);
        exit;
    }

    // Decodifica o JSON de tamanhos do banco
    $tamanhosArray = json_decode($row['tamanhos'] ?: '{}', true);

    // Garante que o tamanho existe no JSON
    if(!isset($tamanhosArray[$tamanhoEscolhido])) {
        echo json_encode(["status" => "erro", "mensagem" => "Tamanho '$tamanhoEscolhido' inválido para este produto."]);
        exit;
    }

    // Verifica se há estoque para o tamanho específico
    if(intval($tamanhosArray[$tamanhoEscolhido]) < $quantidade) {
        echo json_encode(["status" => "erro", "mensagem" => "Sem estoque suficiente para o tamanho $tamanhoEscolhido"]);
        exit;
    }

    // Deduz a quantidade do tamanho escolhido
    $tamanhosArray[$tamanhoEscolhido] -= $quantidade;

    // Recalcula o estoque geral baseado na nova soma da grade
    $novoEstoqueGeral = array_sum($tamanhosArray);
    $novoJsonTamanhos = json_encode($tamanhosArray);

    /* 🔥 ATUALIZA O ESTOQUE GERAL E O JSON DE TAMANHOS */
    $stmt = $conn->prepare("UPDATE produtos SET quantidade_estoque = ?, tamanhos = ? WHERE id = ?");
    $stmt->bind_param("isi", $novoEstoqueGeral, $novoJsonTamanhos, $id);

} else {
    // --- LÓGICA PARA PRODUTO COMUM ---
    if($estoqueGeralAtual < $quantidade){
      echo json_encode(["status" => "erro", "mensagem" => "Sem estoque suficiente"]);
      exit;
    }

    $stmt = $conn->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantidade, $id);
}

// Executa a query final gerada pelas condições acima
if($stmt->execute()){
  echo json_encode(["status" => "ok", "mensagem" => "Estoque atualizado com sucesso"]);
}else{
  echo json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar estoque"]);
}

$stmt->close();
$conn->close();

?>
