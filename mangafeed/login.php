<?php
include("conecta.php");
$errors = [];

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
            // Inicia a sessão e redireciona para a página principal
            $_SESSION['usuario_id'] = $row['id_usuario'];
            $_SESSION['user_usuario'] = $row['user_usuario'];
            header("Location: " . BASE_URL . "inicial.php");
            exit();
        } else {
            $errors[] = "Senha incorreta.";
        }
    } else {
        $errors[] = "Email não encontrado.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background-image: url('css/background.jpg');
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
    </style>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="box-login">
        <h2>Login</h2>
        <?php
        if (!empty($errors)) {
            echo '<div style="color: red;">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>
        <form class="login" method="post" action="">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email"><br>
            <label for="senha">Senha:</label><br>
            <input type="password" id="senha" name="senha"><br><br>
            <input type="submit" value="Login" class="btn-login">
        </form>
        <script src="login.js"></script>
    </div>
</body>
</html>