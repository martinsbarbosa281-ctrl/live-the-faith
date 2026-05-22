<?php
session_start(); // 🌟 IMPORTANTE: Lê a memória da sessão do servidor

// Se não houver sessão ativa, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html"); 
    exit;
}

// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "live_the_faith");

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$user_id = $_SESSION['usuario_id'];

// 🛒 SQL estruturado exatamente com as colunas da sua tabela 'carrinho'
$sql = "SELECT c.id AS carrinho_id, p.id AS produto_id, p.nome, p.preco, p.imagem, c.quantidade, c.tamanho,
               IFNULL(c.valor_frete, 0) AS valor_frete, IFNULL(c.tipo_frete, 'Padrão') AS tipo_frete
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id 
        WHERE c.usuario_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();

$itens_carrinho = [];
while ($item = $resultado->fetch_assoc()) {
    $itens_carrinho[] = $item;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seu Carrinho - Live the Faith</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #f4f5f7;
  color: #1e293b;
  margin: 0;
  padding-bottom: 60px;
}

/* HEADER OTIMIZADO */
header {
  position: fixed;
  top: 0;
  width: 100%;
  background-color: #ffffff;
  padding: 16px 40px;
  color: #0f172a;
  font-weight: 700;
  font-size: 18px;
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 20px;
  box-sizing: border-box;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

header::after {
  content: "";
  width: 90px;
}

.header-titulo {
  flex: 1;
  text-align: center;
}

/* BOTÃO VOLTAR */
.voltar {
  background: #f1f5f9;
  color: #0f172a;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
  text-decoration: none;
}

.voltar:hover {
  background: #e2e8f0;
  transform: translateX(-2px);
}

/* LAYOUT ESTRUTURADO */
.container {
  max-width: 1200px;
  margin: 100px auto 0;
  padding: 0 20px;
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 30px;
  align-items: start;
}

.container.vazio {
  grid-template-columns: 1fr;
  text-align: center;
}

/* LISTA DE PRODUTOS */
.lista-produtos {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* CARD DO PRODUTO */
.card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
  border: 1px solid #edf2f7;
  display: flex;
  align-items: center;
  gap: 20px;
}

.card-selecionador {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-right: 5px;
}

.checkbox-produto {
  width: 22px;
  height: 22px;
  cursor: pointer;
  accent-color: #000000;
}

.card-img-box {
  width: 120px;
  height: 140px;
  background: #f8fafc;
  border-radius: 8px;
  padding: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.card-conteudo {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-self: stretch;
}

.nome {
  font-weight: 600;
  font-size: 16px;
  color: #0f172a;
  margin: 0 0 6px 0;
  line-height: 1.4;
}

.meta-info {
  font-size: 13px;
  color: #64748b;
  margin: 2px 0;
}

.meta-info strong {
  color: #334155;
}

/* CONTROLES DE QUANTIDADE */
.quantidade {
  display: inline-flex;
  align-items: center;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
  gap: 12px;
  margin-top: 10px;
  width: fit-content;
}

.quantidade button {
  width: 28px;
  height: 28px;
  border: none;
  cursor: pointer;
  background: white;
  color: #0f172a;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  transition: background 0.2s;
}

.quantidade button:hover {
  background: #f8fafc;
}

.qtd-num {
  font-weight: 600;
  font-size: 14px;
  min-width: 16px;
  text-align: center;
}

/* BLOCO DE PREÇOS */
.card-valores {
  text-align: right;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-end;
  min-width: 140px;
  border-left: 1px solid #f1f5f9;
  padding-left: 20px;
  align-self: stretch;
}

.detalhe-preco {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 4px;
}

.total-item {
  font-weight: 700;
  font-size: 16px;
  color: #0f172a;
  margin-top: auto;
}

.card-acoes {
  display: flex;
  gap: 8px;
  width: 100%;
  margin-top: 10px;
}

.btn-mini {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: 0.2s;
}

.btn-mini.remover {
  background: #fee2e2;
  color: #ef4444;
}

.btn-mini.remover:hover {
  background: #fca5a5;
}

/* RESUMO DA COMPRA */
.painel-checkout {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
  border: 1px solid #edf2f7;
  position: sticky;
  top: 90px;
}

.checkout-titulo {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 12px;
}

.checkout-linha {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #64748b;
  margin-bottom: 12px;
}

.checkout-linha.total {
  border-top: 1px dashed #e2e8f0;
  padding-top: 14px;
  margin-top: 14px;
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
}

.btn-finalizar-tudo {
  width: 100%;
  background: #000;
  color: white;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  margin-top: 20px;
  transition: background 0.2s;
}

.btn-finalizar-tudo:hover {
  background: #1e293b;
}

.aviso-vazio {
  text-align: center;
  font-size: 18px;
  color: #64748b;
  padding: 60px 20px;
}

/* RESPONSIVIDADE */
@media(max-width: 992px) {
  .container { grid-template-columns: 1fr; margin-top: 90px; }
  .painel-checkout { position: static; }
}
@media(max-width: 600px) {
  header { padding: 12px 20px; }
  .card { flex-direction: column; gap: 15px; align-items: flex-start; }
  .card-selecionador { width: 100%; justify-content: flex-start; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px; }
  .card-img-box { width: 100%; height: 160px; }
  .card-valores { text-align: left; align-items: flex-start; border-left: none; border-top: 1px solid #f1f5f9; padding-left: 0; padding-top: 15px; min-width: unset; }
}
</style>
</head>

<body>

<header>
  <button class="voltar" onclick="voltarPagina()">← Voltar</button>
  <div class="header-titulo">🛒 Seu Carrinho</div>
</header>

<div class="container <?= empty($itens_carrinho) ? 'vazio' : '' ?>" id="containerCarrinho">
  
  <?php if (empty($itens_carrinho)): ?>
      <div id="lista" style="width: 100%;">
          <p class='aviso-vazio'>Seu carrinho está vazio no momento. 🐾</p>
      </div>
  <?php else: ?>
      
      <div class="lista-produtos" id="lista">
          <?php foreach ($itens_carrinho as $item): 
              $qtd = intval($item['quantidade']);
              $precoUnitario = floatval($item['preco']);
              $subtotalPecas = $precoUnitario * $qtd;
              $valorFreteItem = floatval($item['valor_frete']);
              $totalItemComFrete = $subtotalPecas + $valorFreteItem;
          ?>
              <div class="card" id="card-<?= $item['carrinho_id'] ?>">
                <div class="card-selecionador">
                  <input type="checkbox" class="checkbox-produto" checked 
                         value="<?= $item['carrinho_id'] ?>"
                         data-subtotal="<?= $subtotalPecas ?>" 
                         data-frete="<?= $valorFreteItem ?>"
                         onchange="atualizarResumoValores()">
                </div>

                <div class="card-img-box">
                  <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="Imagem do produto">
                </div>
                
                <div class="card-conteudo">
                  <div>
                    <h3 class="nome"><?= htmlspecialchars($item['nome']) ?></h3>
                    <div class="meta-info">Tamanho: <strong><?= htmlspecialchars($item['tamanho']) ?></strong></div>
                    <div class="meta-info">Entrega: <strong><?= htmlspecialchars($item['tipo_frete']) ?></strong></div>
                    
                    <div class="quantidade">
                      <button onclick="alterarQuantidade(<?= $item['carrinho_id'] ?>, -1)">-</button>
                      <span class="qtd-num" id="qtd-<?= $item['carrinho_id'] ?>"><?= $qtd ?></span>
                      <button onclick="alterarQuantidade(<?= $item['carrinho_id'] ?>, 1)">+</button>
                    </div>
                  </div>

                  <div class="card-acoes">
                    <button class="btn-mini remover" onclick="removerItem(<?= $item['carrinho_id'] ?>)">Remover</button>
                  </div>
                </div>

                <div class="card-valores">
                  <div style="width: 100%;">
                    <div class="detalhe-preco">Peças: R$ <span class="val-pecas"><?= number_format($subtotalPecas, 2, ',', '.') ?></span></div>
                    <div class="detalhe-preco">Frete: R$ <span class="val-frete"><?= number_format($valorFreteItem, 2, ',', '.') ?></span></div>
                  </div>
                  <div class="total-item">R$ <span class="val-total-item"><?= number_format($totalItemComFrete, 2, ',', '.') ?></span></div>
                </div>
              </div>
          <?php endforeach; ?>
      </div>

      <div class="painel-checkout" id="painelTotal">
        <div class="checkout-titulo">Resumo do Pedido</div>
        <div class="checkout-linha">
          <span>Subtotal dos itens:</span>
          <span id="resumoSubtotal">R$ 0,00</span>
        </div>
        <div class="checkout-linha">
          <span>Total em fretes:</span>
          <span id="resumoFrete">R$ 0,00</span>
        </div>
        <div class="checkout-linha total">
          <span>Total Geral:</span>
          <span id="total">R$ 0,00</span>
        </div>
        <button class="btn-finalizar-tudo" onclick="comprarTodos()">Finalizar Compra</button>
      </div>
  <?php endif; ?>
</div>

<script>
function voltarPagina(){
  window.history.back();
}

function atualizarResumoValores() {
  const checkboxes = document.querySelectorAll('.checkbox-produto');
  let acumulaSubtotal = 0;
  let acumulaFrete = 0;

  checkboxes.forEach(checkbox => {
    if (checkbox.checked) {
      acumulaSubtotal += parseFloat(checkbox.getAttribute('data-subtotal')) || 0;
      acumulaFrete += parseFloat(checkbox.getAttribute('data-frete')) || 0;
    }
  });

  let totalGeral = acumulaSubtotal + acumulaFrete;

  document.getElementById("resumoSubtotal").innerText = "R$ " + acumulaSubtotal.toFixed(2).replace('.', ',');
  document.getElementById("resumoFrete").innerText = "R$ " + acumulaFrete.toFixed(2).replace('.', ',');
  document.getElementById("total").innerText = "R$ " + totalGeral.toFixed(2).replace('.', ',');
}

function alterarQuantidade(carrinhoId, mudanca) {
  const spanQtd = document.getElementById(`qtd-${carrinhoId}`);
  let qtdAtual = parseInt(spanQtd.innerText);
  let novaQtd = qtdAtual + mudanca;

  if (novaQtd < 1) return; 

  let formData = new FormData();
  formData.append('carrinho_id', carrinhoId);
  formData.append('nova_quantidade', novaQtd);
  formData.append('acao', 'atualizar_quantidade');

  fetch('atualizar_carrinho.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(dados => {
    if(dados.sucesso) {
      spanQtd.innerText = novaQtd;
      
      const card = document.getElementById(`card-${carrinhoId}`);
      card.querySelector('.val-pecas').innerText = dados.novo_subtotal_formatado;
      card.querySelector('.val-total-item').innerText = dados.novo_total_item_formatado;
      
      const checkbox = card.querySelector('.checkbox-produto');
      checkbox.setAttribute('data-subtotal', dados.novo_subtotal);
      
      atualizarResumoValores();
    } else {
      alert(dados.mensagem || "Erro ao atualizar quantidade.");
    }
  })
  .catch(error => console.error('Erro:', error));
}

function removerItem(carrinhoId) {
  if(!confirm("Deseja realmente remover este item do carrinho?")) return;

  let formData = new FormData();
  formData.append('carrinho_id', carrinhoId);
  formData.append('acao', 'remover_item');

  fetch('atualizar_carrinho.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(dados => {
    if(dados.sucesso) {
      window.location.reload();
    } else {
      alert("Erro ao excluir o item.");
    }
  });
}

function comprarTodos(){
  const checkboxesMarcados = document.querySelectorAll('.checkbox-produto:checked');
  
  if(checkboxesMarcados.length === 0){
    alert("Por favor, selecione ao menos um produto para poder prosseguir para o pagamento.");
    return;
  }

  let idsSelecionados = [];
  checkboxesMarcados.forEach(cb => {
    idsSelecionados.push(cb.value);
  });

  let form = document.createElement('form');
  form.method = 'POST';
  form.action = 'pagina_pagamento.php'; 

  let input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'itens_comprar';
  input.value = JSON.stringify(idsSelecionados);
  form.appendChild(input);

  document.body.appendChild(form);
  form.submit();
}

if(document.getElementById("painelTotal")) {
  atualizarResumoValores();
}
</script>

</body>
</html>
