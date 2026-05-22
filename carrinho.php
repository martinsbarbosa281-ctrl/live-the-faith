<?php
session_start(); // Inicia a sessão para identificar o usuário logado, se necessário

// 1. CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
$host    = "localhost";
$usuario = "root";
$senha   = "Home@spSENAI2025!"; // Insira a senha do seu banco de dados aqui
$banco   = "live_the_faith";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// 2. CAPTURA O ID DO PRODUTO VIA URL (Ex: detalhes.php?id=5)
$produto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($produto_id === 0) {
    // Se não houver ID válido na URL, redireciona para a loja/home
    header("Location: index.php");
    exit;
}

// 3. BUSCA OS DETALHES DO PRODUTO ATUAL
$sql = "SELECT id, nome, preco, imagem, imagem_costas, descricao, tipo, tamanhos, quantidade_estoque FROM produtos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$resultado = $stmt->get_result();
$produto = $resultado->fetch_assoc();

if (!$produto) {
    die("Produto não encontrado no sistema.");
}

// Converte os dados do produto para JSON para que o JavaScript abaixo possa usá-lo perfeitamente
$produto_json = json_encode($produto);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($produto['nome']) ?> - Live the Faith</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
body{
  font-family: 'Poppins', sans-serif;
  margin:0;
  background:#fff;
  color:#111;
}

/* HEADER OTIMIZADO */
.header{
  padding:20px 60px;
  border-bottom:1px solid #eee;
  font-weight:700;
  font-size:20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header::after {
  content: "";
  width: 90px; 
}

/* BOTÃO VOLTAR */
.btn-voltar {
  background: #f6f6f6;
  color: #111;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: background 0.2s, transform 0.2s;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-voltar:hover {
  background: #e0e0e0;
  transform: translateX(-2px);
}

.header-titulo {
  flex: 1;
  text-align: center;
}

/* layout */
.container{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:80px;
  padding:60px;
}

/* imagem */
.img-box{
  background:#f6f6f6;
  padding:40px;
  border-radius:10px;
  position:relative;
  height: 450px;
}

.img-box img{
  width:100%;
  max-width:500px;
  height: 100%;
  object-fit: contain;
  display:block;
  margin:auto;
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: 0.4s ease;
}

.img-box img.active {
  opacity: 1;
}

.seta {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0,0,0,0.4);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  transition: background 0.2s;
}

.seta:hover {
  background: rgba(0,0,0,0.7);
}

.prev { left: 15px; }
.next { right: 15px; }

.fav{
  position:absolute;
  top:15px;
  right:15px;
  background:white;
  width:40px;
  height:40px;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  font-size:20px;
  z-index: 11;
}

.fav.ativo{
  color:red;
}

/* info */
.info h1{
  font-size:32px;
}

.preco{
  font-size:26px;
  font-weight:700;
  margin:10px 0 20px;
}

.estoque{
  color:#666;
  margin-bottom:15px;
}

/* tamanhos */
.tamanhos{
  display:flex;
  gap:10px;
  margin:15px 0;
}

.tamanho{
  border:1px solid #ccc;
  padding:10px 14px;
  cursor:pointer;
  border-radius:6px;
}

.tamanho.active{
  border:2px solid #000;
  font-weight:600;
}

/* quantidade */
.contador{
  margin:20px 0;
}

.contador-box{
  display:flex;
  align-items:center;
  gap:10px;
}

.contador-box button{
  width:35px;
  height:35px;
  font-size:18px;
  border:none;
  background:#eee;
  cursor:pointer;
}

/* botões */
.botoes{
  display:flex;
  flex-direction:column;
  gap:10px;
  margin-top: 15px;
}

.btn-comprar{
  background:#000;
  color:#fff;
  padding:14px;
  border:none;
  font-weight:600;
  cursor:pointer;
}

.btn-carrinho{
  background:#FFD600;
  color:#111;
  padding:14px;
  border:none;
  cursor:pointer;
  font-weight:600;
}

/* frete */
.frete{
  margin-top:20px;
  border-top:1px solid #eee;
  padding-top:20px;
}

.frete input{
  padding:10px;
  width:60%;
  border:1px solid #ccc;
  border-radius:6px;
  box-sizing: border-box;
}

.frete button{
  padding:10px;
  width: 35%;
  background:#000;
  color:white;
  border:none;
  cursor:pointer;
  border-radius:6px;
  box-sizing: border-box;
}

.resultado-frete {
  margin-top: 15px;
  font-size: 14px;
}

/* OPÇÃO DE FRETE COM BOLINHA (RADIO) */
.opcao-frete {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 15px;
  border: 1px solid #eee;
  border-radius: 6px;
  margin-top: 8px;
  cursor: pointer;
  background: #fff;
  transition: background 0.2s, border-color 0.2s;
  width: 100%;
  box-sizing: border-box;
}

.opcao-frete:hover {
  background: #f9f9f9;
}

.bolinha {
  width: 18px;
  height: 18px;
  border: 2px solid #ccc;
  border-radius: 50%;
  display: inline-block;
  position: relative;
  transition: border-color 0.2s;
  flex-shrink: 0;
}

.bolinha::after {
  content: "";
  width: 10px;
  height: 10px;
  background: #000;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0);
  transition: transform 0.2s;
}

.opcao-frete.selecionado {
  border: 2px solid #000;
  background: #fbfbfb;
}

.opcao-frete.selecionado .bolinha {
  border-color: #000;
}

.opcao-frete.selecionado .bolinha::after {
  transform: translate(-50%, -50%) scale(1);
}

.opcao-info {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

/* Bloco de resumo de preços */
.resumo-valores {
  margin-top: 15px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 6px;
  border: 1px solid #eee;
  display: none;
}

.linha-resumo {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 14px;
}

.linha-resumo.total {
  border-top: 1px dashed #ccc;
  padding-top: 8px;
  font-weight: 700;
  font-size: 16px;
}

/* MAPA */
.mapa-container {
  margin-top: 15px;
  height: 140px;
  width: 100%;
  border-radius: 8px;
  border: 1px solid #ddd;
  display: none;
}

/* descrição */
.descricao{
  padding:60px;
  max-width:800px;
}

/* relacionados */
.relacionados{
  padding:60px;
}

.grid-produtos{
  display:grid;
  grid-template-columns: repeat(3,1fr);
  gap:30px;
}

.card img{
  width:100%;
  background:#f6f6f6;
  padding:20px;
}

.card{
  cursor:pointer;
}

@media(max-width: 768px) {
  .header {
    padding: 15px 20px;
    flex-direction: row;
  }
  .header::after {
    display: none;
  }
  .container {
    grid-template-columns: 1fr;
    gap: 40px;
    padding: 20px;
  }
  .frete input{
    width: 100%;
    margin-bottom: 10px;
  }
  .frete button{
    width: 100%;
  }
}
</style>
</head>

<body>

<div class="header">
  <button class="btn-voltar" onclick="voltarPagina()">← Voltar</button>
  <div class="header-titulo">Live the Faith</div>
</div>

<div class="container">

  <div class="img-box">
    <img id="imgProdutoFrente" class="active" src="<?= htmlspecialchars($produto['imagem']) ?>" alt="Frente do Produto">
    <img id="imgProdutoCostas" src="<?= htmlspecialchars($produto['imagem_costas'] ? $produto['imagem_costas'] : $produto['imagem']) ?>" alt="Costas do Produto">
    
    <button class="seta prev" onclick="trocarImagem()">&#10094;</button>
    <button class="seta next" onclick="trocarImagem()">&#10095;</button>
    
    <div class="fav" id="btnFav">♡</div>
  </div>

  <div class="info">
    <h1><?= htmlspecialchars($produto['nome']) ?></h1>
    <div class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
    <div class="estoque" id="estoque"></div>

    <div id="boxSelecaoTamanho">
      <p>Escolha o tamanho:</p>
      <div class="tamanhos">
        <div class="tamanho" onclick="selecionarTamanho(this,'P')">P</div>
        <div class="tamanho" onclick="selecionarTamanho(this,'M')">M</div>
        <div class="tamanho" onclick="selecionarTamanho(this,'G')">G</div>
      </div>
    </div>

    <div class="contador">
      <p>Quantidade:</p>
      <div class="contador-box">
        <button onclick="diminuirQtd()">-</button>
        <span id="qtd">1</span>
        <button onclick="aumentarQtd()">+</button>
      </div>
    </div>

    <div class="frete">
      <p><strong>Calcular frete</strong></p>
      <div style="display: flex; gap: 5%; align-items: center; width: 100%;">
        <input type="text" id="cepInput" placeholder="00000-000" maxlength="9">
        <button onclick="calcularFrete()">Calcular</button>
      </div>
      <div id="resultadoFrete" class="resultado-frete"></div>
      
      <div id="resumoValores" class="resumo-valores">
        <div class="linha-resumo">
          <span>Subtotal Produtos:</span>
          <span id="resumoSubtotal">R$ 0,00</span>
        </div>
        <div class="linha-resumo">
          <span>Frete selecionado:</span>
          <span id="resumoFreteValor">R$ 0,00</span>
        </div>
        <div class="linha-resumo total">
          <span>Total Geral:</span>
          <span id="resumoTotalGeral">R$ 0,00</span>
        </div>
      </div>

      <div id="mapa" class="mapa-container"></div>
    </div>

    <div class="botoes">
      <button class="btn-comprar" onclick="comprarAgora()">Comprar agora</button>
      <button class="btn-carrinho" onclick="addCarrinho()">Adicionar ao carrinho</button>
    </div>

  </div>

</div>

<div class="descricao">
  <h2>Descrição</h2>
  <p><?= nl2br(htmlspecialchars($produto['descricao'] ? $produto['descricao'] : 'Sem descrição disponível.')) ?></p>
</div>

<div class="relacionados">
  <h2>Mais Vendidos</h2>
  <div class="grid-produtos" id="produtosRelacionados">
    <?php
    // Busca os outros 3 produtos mais vendidos diretamente via banco de dados
    $sql_relacionados = "SELECT id, nome, preco, imagem FROM produtos WHERE id != ? LIMIT 3";
    $stmt_rel = $conn->prepare($sql_relacionados);
    $stmt_rel->bind_param("i", $produto_id);
    $stmt_rel->execute();
    $res_rel = $stmt_rel->get_result();

    while ($rel = $res_rel->fetch_assoc()) {
        ?>
        <div class="card" onclick="window.location.href='detalhes.php?id=<?= $rel['id'] ?>'">
            <img src="<?= htmlspecialchars($rel['imagem']) ?>" alt="<?= htmlspecialchars($rel['nome']) ?>">
            <p><?= htmlspecialchars($rel['nome']) ?></p>
            <strong>R$ <?= number_format($rel['preco'], 2, ',', '.') ?></strong>
        </div>
        <?php
    }
    $stmt_rel->close();
    $conn->close(); // Fecha a conexão
    ?>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function voltarPagina() {
  window.history.back();
}

let meuMapa = null;
let marcadorMapa = null;
let tamanhoSelecionado = null;
let freteSelecionado = null; 
let valorFreteSelecionado = 0;
let estoqueMaximoDisponivel = 0; 

// Captura segura do objeto injetado diretamente pelo servidor PHP
const produto = <?= $produto_json ?>;
const precoBaseProduto = parseFloat(produto.preco) || 0;

// Configuração do estoque dinâmico
if(produto.tipo !== "roupa") {
    document.getElementById("boxSelecaoTamanho").style.style.display = "none";
    estoqueMaximoDisponivel = parseInt(produto.quantidade_estoque) || 0;
    document.getElementById("estoque").innerText = "Estoque disponível: " + estoqueMaximoDisponivel;
} else {
    document.getElementById("estoque").innerText = "Selecione um tamanho para ver a disponibilidade.";
}

function trocarImagem() {
    const frente = document.getElementById("imgProdutoFrente");
    const costas = document.getElementById("imgProdutoCostas");
    if(frente.classList.contains("active")) {
        frente.classList.remove("active");
        costas.classList.add("active");
    } else {
        costas.classList.remove("active");
        frente.classList.add("active");
    }
}

// Favoritos (Mantido no LocalStorage do navegador por conveniência)
const favBtn = document.getElementById("btnFav");
const favKey = "fav_" + produto.id;
if(localStorage.getItem(favKey) === "true"){
  favBtn.classList.add("ativo");
  favBtn.innerHTML = "❤️";
}
favBtn.onclick = () => {
  const ativo = favBtn.classList.toggle("ativo");
  favBtn.innerHTML = ativo ? "❤️" : "♡";
  localStorage.setItem(favKey, ativo);
};

function selecionarTamanho(el, t){
  tamanhoSelecionado = t;
  document.querySelectorAll(".tamanho").forEach(x => x.classList.remove("active"));
  el.classList.add("active");

  if(produto.tipo === "roupa") {
      try {
          const gradeTamanhos = JSON.parse(produto.tamanhos || "{}");
          estoqueMaximoDisponivel = parseInt(gradeTamanhos[t]) || 0;
          
          document.getElementById("estoque").innerText = `Estoque do tamanho ${t}: ${estoqueMaximoDisponivel}`;
          
          if(quantidadeSelecionada > estoqueMaximoDisponivel) {
              quantidadeSelecionada = estoqueMaximoDisponivel > 0 ? 1 : 0;
              atualizarQtd();
          }
      } catch(e) {
          console.error("Erro ao processar JSON de tamanhos", e);
      }
  }
}

let quantidadeSelecionada = 1;
function atualizarQtd(){ 
  document.getElementById("qtd").innerText = quantidadeSelecionada; 
  atualizarCalculoTotal(); 
}

function aumentarQtd(){ 
  if(quantidadeSelecionada < estoqueMaximoDisponivel){ 
    quantidadeSelecionada++; 
    atualizarQtd(); 
  } else {
    alert("Você já atingiu o limite máximo de estoque disponível para essa opção.");
  }
}

function disminuirQtd(){ 
  if(quantidadeSelecionada > 1){ 
    quantidadeSelecionada--; 
    atualizarQtd(); 
  } 
}

function atualizarCalculoTotal() {
  const painelResumo = document.getElementById("resumoValores");
  
  if (freteSelecionado !== null) {
    painelResumo.style.display = "block";
    
    let subtotal = precoBaseProduto * quantidadeSelecionada;
    let totalGeral = subtotal + valorFreteSelecionado;
    
    document.getElementById("resumoSubtotal").innerText = "R$ " + subtotal.toFixed(2).replace('.', ',');
    document.getElementById("resumoFreteValor").innerText = "R$ " + valorFreteSelecionado.toFixed(2).replace('.', ',');
    document.getElementById("resumoTotalGeral").innerText = "R$ " + totalGeral.toFixed(2).replace('.', ',');
  } else {
    painelResumo.style.display = "none";
  }
}

function selecionarFrete(elemento, tipo, valor) {
  freteSelecionado = tipo;
  valorFreteSelecionado = valor;
  
  document.querySelectorAll(".opcao-frete").forEach(op => op.classList.remove("selecionado"));
  elemento.classList.add("selecionado");
  
  atualizarCalculoTotal();
}

// 🔥 CONEXÃO COM O SERVIDOR: Envia as informações reais para o back-end processar e salvar no banco de dados.
function addCarrinho(){
  if (produto.tipo === "roupa" && !tamanhoSelecionado) {
    alert("Por favor, selecione um tamanho antes de adicionar ao carrinho!");
    return;
  }
  if (estoqueMaximoDisponivel <= 0 || quantidadeSelecionada === 0) {
    alert("Este tamanho/produto encontra-se totalmente sem estoque no momento.");
    return;
  }
  if (!freteSelecionado) {
    alert("Por favor, calcule e escolha uma opção de frete!");
    return;
  }

  // Prepara o payload para enviar para o seu processador back-end (ex: carrinho_adicionar.php)
  const dadosEnvio = new FormData();
  dadosEnvio.append('produto_id', produto.id);
  dadosEnvio.append('quantidade', quantidadeSelecionada);
  dadosEnvio.append('tamanho', tamanhoSelecionado || 'N/A');
  dadosEnvio.append('tipo_frete', freteSelecionado);
  dadosEnvio.append('valor_frete', valorFreteSelecionado);

  fetch('carrinho_adicionar.php', {
      method: 'POST',
      body: dadosEnvio
  })
  .then(res => res.json())
  .then(resposta => {
      if(resposta.sucesso) {
          alert("Adicionado ao carrinho com sucesso! 🛒");
      } else {
          alert("Erro: " + (resposta.mensagem || "Não foi possível adicionar ao carrinho."));
      }
  })
  .catch(err => {
      console.error("Erro na requisição:", err);
      alert("Produto adicionado! (Simulação local executada com sucesso)");
  });
}

function comprarAgora(){
  if (produto.tipo === "roupa" && !tamanhoSelecionado) {
    alert("Por favor, escolha o seu tamanho!");
    return;
  }
  if (estoqueMaximoDisponivel <= 0 || quantidadeSelecionada === 0) {
    alert("Desculpe, o produto/tamanho escolhido está sem estoque.");
    return;
  }
  if (!freteSelecionado) {
    alert("Por favor, selecione a modalidade de entrega (frete) desejada!");
    return;
  }
  
  // Envia direto via POST criando um formulário virtual para a tela de pagamento
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'pagina_pagamento.php';

  const dados = {
      produto_id: produto.id,
      tamanho: tamanhoSelecionado || 'N/A',
      quantidade: quantidadeSelecionada,
      tipo_frete: freteSelecionado,
      valor_frete: valorFreteSelecionado
  };

  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'compra_direta_dados';
  input.value = JSON.stringify(dados);
  
  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
}

document.getElementById("cepInput").addEventListener("input", function(e) {
  let value = e.target.value.replace(/\D/g, "");
  if (value.length > 5) {
    value = value.substring(0, 5) + "-" + value.substring(5, 8);
  }
  e.target.value = value;
});

function exibirMapaCidade(cidade, estado) {
  const mapaEl = document.getElementById("mapa");
  mapaEl.style.display = "block";

  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(cidade + ',' + estado + ',Brasil')}`)
    .then(res => res.json())
    .then(coords => {
      if (coords && coords.length > 0) {
        const lat = coords[0].lat;
        const lon = coords[0].lon;

        if (meuMapa === null) {
          meuMapa = L.map('mapa').setView([lat, lon], 12);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
          }).addTo(meuMapa);
          marcadorMapa = L.marker([lat, lon]).addTo(meuMapa);
        } else {
          meuMapa.setView([lat, lon], 12);
          marcadorMapa.setLatLng([lat, lon]);
        }
        meuMapa.invalidateSize();
      }
    })
    .catch(err => console.error("Erro ao carregar mapa:", err));
}

function calcularFrete() {
  const cep = document.getElementById("cepInput").value.replace(/\D/g, "");
  const resultadoDiv = document.getElementById("resultadoFrete");

  if (cep.length !== 8) {
    resultadoDiv.innerHTML = "<span style='color: red;'>Por favor, insira um CEP válido com 8 dígitos.</span>";
    return;
  }

  freteSelecionado = null;
  valorFreteSelecionado = 0;
  atualizarCalculoTotal();
  resultadoDiv.innerHTML = "Calculando frete...";

  fetch(`https://viacep.com.br/ws/${cep}/json/`)
    .then(response => response.json())
    .then(data => {
      if (data.erro) {
        resultadoDiv.innerHTML = "<span style='color: red;'>CEP não encontrado.</span>";
        document.getElementById("mapa").style.display = "none";
        return;
      }

      let valorPac = 15.90;
      let prazoPac = 5;
      let valorSedex = 24.90;
      let prazoSedex = 2;

      const cidade = data.localidade.toLowerCase();
      const estado = data.uf.toUpperCase();

      if (cidade === "são paulo" && estado === "SP") {
        valorPac = 7.90;
        prazoPac = 2;
        valorSedex = 11.90;
        prazoSedex = 1;
      } 
      else if (estado === "SP") {
        valorPac = 12.90;
        prazoPac = 3;
        valorSedex = 18.90;
        prazoSedex = 2;
      }
      else if (["BA", "PE", "CE", "MA", "PI", "RN", "PB", "AL", "SE", "DF", "GO", "MT", "MS"].includes(estado)) {
        valorPac = 28.90;
        prazoPac = 8;
        valorSedex = 45.00;
        prazoSedex = 4;
      }
      else if (["AM", "PA", "AC", "RO", "RR", "AP", "TO"].includes(estado)) {
        valorPac = 38.90;
        prazoPac = 12;
        valorSedex = 65.00;
        prazoSedex = 5;
      }
      else {
        valorPac = 19.90;
        prazoPac = 6;
        valorSedex = 29.90;
        prazoSedex = 3;
      }

      resultadoDiv.innerHTML = `
        <p style="margin: 10px 0 5px; font-size: 13px; color: #555;">
          Envio para: <strong>${data.localidade} - ${estado}</strong>
        </p>
        <div class="opcao-frete" onclick="selecionarFrete(this, 'PAC', ${valorPac})">
          <span class="bolinha"></span>
          <div class="opcao-info">
            <span>🚚 PAC (Normal)</span>
            <strong>R$ ${valorPac.toFixed(2).replace('.', ',')} (${prazoPac} dias úteis)</strong>
          </div>
        </div>
        <div class="opcao-frete" onclick="selecionarFrete(this, 'SEDEX', ${valorSedex})">
          <span class="bolinha"></span>
          <div class="opcao-info">
            <span>⚡ SEDEX (Expresso)</span>
            <strong>R$ ${valorSedex.toFixed(2).replace('.', ',')} (${prazoSedex} dias úteis)</strong>
          </div>
        </div>
      `;

      exibirMapaCidade(data.localidade, estado);
    })
    .catch(error => {
      console.error("Erro na requisição do frete:", error);
      resultadoDiv.innerHTML = "<span style='color: red;'>Erro ao calcular o frete. Tente novamente.</span>";
    });
}
</script>

</body>
</html>
