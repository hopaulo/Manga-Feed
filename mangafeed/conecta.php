<?php
define('BASE_URL', '/');
$hostname = 'localhost';
$database = 'id22260101_mangafeed';
$username = 'id22260101_usuario';
$password = '*Usuario24';

// Estabelece a conexão com o banco de dados
$conn = new mysqli($hostname, $username, $password, $database);

// Verifica se ocorreu algum erro na conexão
if ($conn->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conn->connect_error);
}

// Iniciar a sessão
// Optei por iniciar a sessão nesse arquivo pois ele será incluido em todas as páginas do site.
session_start();
?>