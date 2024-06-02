<?php
include("conecta.php");
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_usuario = $_POST['nome_usuario'];
    $user_usuario = $_POST['user_usuario'];
    $email_usuario = $_POST['email_usuario'];
    $senha_usuario = $_POST['senha_usuario'];
    $senha_confirm = $_POST['senha_confirm'];

    // Hash da senha (criptografia)
    $senha_hash = password_hash($senha_usuario, PASSWORD_BCRYPT);

    // Inserir no banco de dados
    $stmt = $conn->prepare("INSERT INTO tb_usuario (nome_usuario, user_usuario, email_usuario, senha_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome_usuario, $user_usuario, $email_usuario, $senha_hash);
    if ($stmt->execute()) {
        // Inicia a sessão e redireciona para a página principal
        session_start();
        $_SESSION['usuario_id'] = $stmt->insert_id; // ID do usuário recém-cadastrado
        $_SESSION['user_usuario'] = $user_usuario;
        header("Location: " . BASE_URL . "inicial.php");
        exit();
    } else {
        if ($stmt->errno == 1062) {
            $errors[] = "Usuário ou email já cadastrado.";
        } else {
            $errors[] = "Erro ao cadastrar usuário: " . $stmt->error;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Usuário</title>
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
    <div class="box-cadastro">
        <h2>Cadastro de Usuário</h2>
        <form method="post" action="">
            <label for="nome_usuario">Nome:</label><br>
            <input type="text" id="nome_usuario" name="nome_usuario"><br>
            <label for="user_usuario">Usuário:</label><br>
            <input type="text" id="user_usuario" name="user_usuario"><br>
            <label for="email_usuario">Email:</label><br>
            <input type="text" id="email_usuario" name="email_usuario"><br>
            <label for="senha_usuario">Senha:</label><br>
            <input type="password" id="senha_usuario" name="senha_usuario"><br>
            <label for="senha_confirm">Confirmar Senha:</label><br>
            <input type="password" id="senha_confirm" name="senha_confirm"><br><br>
            <input type="submit" value="Cadastrar" class="btn-cadastro">
        </form>
    </div>
    <script src="cadastro.js"></script>    
</body>
</html>