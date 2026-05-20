CREATE DATABASE IF NOT EXISTS live_the_faith;
USE live_the_faith;

-- =========================================================================
-- 👤 TABELA USUÁRIOS
-- =========================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL,
    telefone      VARCHAR(20)  NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    senha         VARCHAR(255) NOT NULL,
    is_admin      TINYINT(1)   DEFAULT 0,
    data_cadastro TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
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
