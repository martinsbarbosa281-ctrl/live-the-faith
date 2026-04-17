<?php
$conn = new mysqli("localhost","root","Home@spSENAI2025!","live_the_faith");

$id = $_GET['id'];

$conn->query("DELETE FROM produtos WHERE id=$id");
?>
