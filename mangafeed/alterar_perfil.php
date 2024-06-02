<?php
include("conecta.php");
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['usuario_id'];

if (isset($_POST['verificar_senha']) && $_POST['verificar_senha'] === 'true') {
    $senha_usuario = $_POST['senha_usuario'];

    // Verificar a senha atual
    $stmt = $conn->prepare("SELECT senha_usuario FROM tb_usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (password_verify($senha_usuario, $row['senha_usuario'])) {
        echo "Senha correta.";
    } else {
        echo "Senha incorreta.";
    }
    exit();
}

// Recuperando dados atuais
$stmt = $conn->prepare("SELECT user_usuario, email_usuario FROM tb_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['verificar_senha'])) {
    $user_usuario = $_POST['user_usuario'];
    $email_usuario = $_POST['email_usuario'];
    $senha_usuario = $_POST['senha_usuario'];
    $senha_nova = $_POST['senha_nova'];
    $senha_confirm = $_POST['senha_confirm'];

    // Verifica a senha atual
    $stmt = $conn->prepare("SELECT senha_usuario FROM tb_usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($senha_usuario, $row['senha_usuario'])) {
        echo "Senha incorreta.";
        exit();
    }

    // Update de user e email
    $stmt = $conn->prepare("UPDATE tb_usuario SET user_usuario = ?, email_usuario = ? WHERE id_usuario = ?");
    $stmt->bind_param("ssi", $user_usuario, $email_usuario, $current_user_id);
    $stmt->execute();
    $stmt->close();

    // Update de senha, caso seja solicitado
    if (!empty($senha_nova)) {
        $senha_hash = password_hash($senha_nova, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE tb_usuario SET senha_usuario = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $senha_hash, $current_user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Atualiza os dados na sessão
    $_SESSION['user_usuario'] = $user_usuario;
    $_SESSION['email_usuario'] = $email_usuario;
    $_SESSION['success_message'] = "As atualizações foram feitas com sucesso!";

    echo "Atualização bem-sucedida.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Alterar Perfil</title>
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
    <div class="box-editar-perfil">
        <form method="post" action="">
            <h2>Alterar Perfil</h2>
            <label for="user_usuario">Usuário:</label><br>
            <input type="text" id="user_usuario" name="user_usuario" value="<?php echo htmlspecialchars($user_data['user_usuario']); ?>"><br>
            <label for="email_usuario">Email:</label><br>
            <input type="email" id="email_usuario" name="email_usuario" value="<?php echo htmlspecialchars($user_data['email_usuario']); ?>"><br>
            <label for="senha_usuario">Senha Atual:</label><br>
            <input type="password" id="senha_usuario" name="senha_usuario"><br>
            <label for="senha_nova">Nova Senha:</label><br>
            <input type="password" id="senha_nova" name="senha_nova"><br>
            <label for="senha_confirm">Confirmar Nova Senha:</label><br>
            <input type="password" id="senha_confirm" name="senha_confirm"><br><br>
            <input type="submit" value="Atualizar Perfil" class="btn-atualizar">
        </form>
    <div>
    <script src="alterar_perfil.js"></script>
</body>
</html>