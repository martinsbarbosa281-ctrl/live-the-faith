<?php
$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

$id = $_POST['id'];
$preco = $_POST['preco'];

$conn->query("UPDATE produtos SET preco='$preco' WHERE id=$id");
?>
