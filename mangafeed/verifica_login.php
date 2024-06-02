<?php
include("conecta.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verifica se o email e a senha estão corretos
    $stmt = $conn->prepare("SELECT id_usuario, user_usuario, senha_usuario FROM tb_usuario WHERE email_usuario = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($senha, $row['senha_usuario'])) {
            echo "Login bem-sucedido.";
        } else {
            echo "Credenciais inválidas.";
        }
    } else {
        echo "Credenciais inválidas.";
    }
    $stmt->close();
}
?>
