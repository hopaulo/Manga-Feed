<?php
include("conecta.php");
// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial
header("Location: " . BASE_URL . "inicial.php");
exit();
?>