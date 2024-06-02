<?php
include("conecta.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_usuario = $_POST['email_usuario'];

    $stmt = $conn->prepare("SELECT * FROM tb_usuario WHERE email_usuario = ?");
    $stmt->bind_param("s", $email_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "Email já cadastrado.";
    } else {
        echo "Email disponível.";
    }
    $stmt->close();
}
?>