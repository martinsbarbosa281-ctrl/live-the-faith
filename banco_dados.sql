CREATE DATABASE IF NOT EXISTS live_the_faith;
USE live_the_faith;

-- 👤 USUÁRIOS
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 👑 ADMIN
INSERT INTO usuarios (nome, telefone, email, senha, is_admin)
SELECT * FROM (
    SELECT 
      'Administrador',
      '000000000',
      'admin@livefaith.com',
      '$2y$10$wH6dG9pP6X7QzQK6Qzj7OeGQZ8VxQvX9G1ZJ5YyQj3p9l7sKj1K9G',
      1
) AS tmp
WHERE NOT EXISTS (
    SELECT email FROM usuarios WHERE email = 'admin@livefaith.com'
);

-- 📂 CATEGORIAS
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 🛒 PRODUTOS (CORRIGIDO)
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL,
  imagem VARCHAR(255),
  quantidade_estoque INT DEFAULT 0,
  tipo ENUM('simples','roupa') DEFAULT 'simples',
  tamanhos TEXT,
  categoria_id INT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_produtos_categoria
    FOREIGN KEY (categoria_id)
    REFERENCES categorias(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
