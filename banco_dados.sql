CREATE DATABASE IF NOT EXISTS live_the_faith;
USE live_the_faith;

-- =========================================================================
-- 👤 TABELA USUÁRIOS
-- =========================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(100) NOT NULL,
    telefone       VARCHAR(20)  NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    senha          VARCHAR(255) NOT NULL,
    is_admin       TINYINT(1)   DEFAULT 0,
    data_cadastro  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- 👑 ADMIN FIXO (Garante a inserção apenas se não existir)
INSERT INTO usuarios (nome, telefone, email, senha, is_admin)
SELECT 
    'Administrador',
    '000000000',
    'admin@livefaith.com',
    '$2y$10$wH6dG9pP6X7QzQK6Qzj7OeGQZ8VxQvX9G1ZJ5YyQj3p9l7sKj1K9G',
    1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'admin@livefaith.com'
);

-- =========================================================================
-- 📂 TABELA CATEGORIAS
-- =========================================================================
CREATE TABLE IF NOT EXISTS categorias (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- =========================================================================
-- 📦 TABELA PRODUTOS
-- =========================================================================
CREATE TABLE IF NOT EXISTS produtos (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nome               VARCHAR(150) NOT NULL,
    descricao          TEXT,
    preco              DECIMAL(10,2) NOT NULL,
    imagem             TEXT NOT NULL, 
    imagem_costas      TEXT, -- 🌟 Corrigido: Removido o "AFTER imagem" que causava o erro
    quantidade_estoque INT DEFAULT 0,
    tipo               ENUM('simples','roupa') DEFAULT 'simples',
    tamanhos           TEXT,
    categoria_id       INT,
    data_criacao       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON DELETE SET NULL
);

-- =========================================================================
-- ❤️ TABELA FAVORITOS
-- =========================================================================
CREATE TABLE IF NOT EXISTS favoritos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NOT NULL,
    produto_id      INT NOT NULL,
    data_adicionado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Garante que o mesmo usuário não favorite o mesmo produto duas vezes
    CONSTRAINT unique_usuario_produto UNIQUE (usuario_id, produto_id),

    -- Relacionamentos (Chaves Estrangeiras)
    CONSTRAINT fk_favoritos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE, -- Se o usuário for deletado, apaga os favoritos dele

    CONSTRAINT fk_favoritos_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON DELETE CASCADE  -- Se o produto for deletado, sai da lista de favoritos
);

-- =========================================================================
-- 🛒 TABELA CARRINHO
-- =========================================================================
CREATE TABLE IF NOT EXISTS carrinho (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NULL,          -- NULL permite que usuários não logados adicionem ao carrinho
    sessao_id       VARCHAR(255) NULL, -- Identificador temporário para usuários anônimos
    produto_id      INT NOT NULL,
    quantidade      INT NOT NULL DEFAULT 1,
    tamanho         VARCHAR(10) NULL,  -- Crucial para seu site de roupas (guarda P, M, G, etc.)
    data_adicionado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Relacionamentos (Chaves Estrangeiras)
    CONSTRAINT fk_carrinho_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_carrinho_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON DELETE CASCADE
);

-- =========================================================================
-- 🔍 CONSULTAS DE TESTE (Opcional)
-- =========================================================================
-- Exemplo: Buscar itens do carrinho do usuário ID 5
-- SELECT c.*, p.nome, p.preco, p.imagem 
-- FROM carrinho c
-- JOIN produtos p ON c.produto_id = p.id
-- WHERE c.usuario_id = 5;
