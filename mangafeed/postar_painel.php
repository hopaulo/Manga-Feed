<?php
include("conecta.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se o usuário está logado
    if (!isset($_SESSION['usuario_id'])) {
        $errors[] = "Você precisa estar logado para postar um painel.";
    } else {
        $titulo = $_POST['titulo'];
        $descricao = $_POST['descricao'];
        $imagem = $_FILES['imagem'];
        $hashtags = $_POST['hashtags'];

        // Move o arquivo para o diretório de uploads
        $upload_dir = 'uploads/';
        $upload_file = $upload_dir . basename($imagem['name']);
        if (move_uploaded_file($imagem['tmp_name'], $upload_file)) {
            // Insere os dados no banco de dados
            $usuario_id = $_SESSION['usuario_id'];
            $stmt = $conn->prepare("INSERT INTO tb_painel (usuario_id, titulo_painel, descricao_painel, url_painel) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $usuario_id, $titulo, $descricao, $upload_file);
            if ($stmt->execute()) {
                $painel_id = $stmt->insert_id;

                // Processa as hashtags
                if (!empty($hashtags)) {
                    $hashtag_list = explode(',', $hashtags);
                    foreach ($hashtag_list as $hashtag) {
                        $hashtag = trim($hashtag);
                        if (!empty($hashtag)) {
                            $hashtag = ltrim($hashtag, '#');

                            // Verifica se a hashtag já existe
                            $stmt_hashtag = $conn->prepare("SELECT id_hashtag FROM tb_hashtag WHERE nome_hashtag = ?");
                            $stmt_hashtag->bind_param("s", $hashtag);
                            $stmt_hashtag->execute();
                            $result = $stmt_hashtag->get_result();

                            if ($result->num_rows > 0) {
                                // Hashtag já existe
                                $row = $result->fetch_assoc();
                                $hashtag_id = $row['id_hashtag'];
                            } else {
                                // Insere nova hashtag
                                $stmt_insert_hashtag = $conn->prepare("INSERT INTO tb_hashtag (nome_hashtag) VALUES (?)");
                                $stmt_insert_hashtag->bind_param("s", $hashtag);
                                $stmt_insert_hashtag->execute();
                                $hashtag_id = $stmt_insert_hashtag->insert_id;
                                $stmt_insert_hashtag->close();
                            }
                            $stmt_hashtag->close();

                            // Associa a hashtag ao painel
                            $stmt_painel_hashtag = $conn->prepare("INSERT INTO tb_painel_hashtag (id_painel, id_hashtag) VALUES (?, ?)");
                            $stmt_painel_hashtag->bind_param("ii", $painel_id, $hashtag_id);
                            $stmt_painel_hashtag->execute();
                            $stmt_painel_hashtag->close();
                        }
                    }
                }

                header("Location: " . BASE_URL . "inicial.php");
            } else {
                $errors[] = "Erro ao postar o painel: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Erro ao fazer upload da imagem.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postar Painel</title>
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
    <?php
    if (!empty($errors)) {
        echo '<div style="color: red;">';
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
    }
    if (!empty($success)) {
        echo '<div style="color: green;">' . htmlspecialchars($success) . '</div>';
    }
    ?>
    <div class="box-postar">
        <h2>Postar Painel</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="titulo">Título:</label><br>
            <input type="text" id="titulo" name="titulo"><br>
            <label for="descricao">Descrição:</label><br>
            <textarea id="descricao" name="descricao" class="descricao"></textarea><br>
            <label for="imagem">Imagem:</label><br>
            <input type="file" id="imagem" name="imagem" accept="image/jpeg, image/png"><br>
            <label for="hashtags">Hashtags (separadas por vírgula):</label><br>
            <input type="text" id="hashtags" name="hashtags" placeholder="#hashtag1, #hashtag2" class="input-hashtag"><br><br>
            <input type="submit" value="Postar" class="btn-postar">
        </form>
    </div>
    <script src="verifica_painel.js"></script>
</body>
</html>