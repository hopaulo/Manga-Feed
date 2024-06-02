<?php
include("conecta.php");
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['usuario_id'];
$painel_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $hashtags = $_POST['hashtags'];

    // Atualiza o painel
    $stmt = $conn->prepare("UPDATE tb_painel SET titulo_painel = ?, descricao_painel = ? WHERE id_painel = ? AND usuario_id = ?");
    $stmt->bind_param("ssii", $titulo, $descricao, $painel_id, $current_user_id);

    if ($stmt->execute()) {
        // Atualiza as hashtags
        $conn->query("DELETE FROM tb_painel_hashtag WHERE id_painel = $painel_id");
        $hashtags_array = explode(',', $hashtags);
        foreach ($hashtags_array as $hashtag) {
            $hashtag = trim($hashtag);
            if (!empty($hashtag)) {
                // Adiciona a hashtag na tabela de hashtags se não existir
                $stmt_hashtag = $conn->prepare("INSERT INTO tb_hashtag (nome_hashtag) VALUES (?) ON DUPLICATE KEY UPDATE nome_hashtag=nome_hashtag");
                $stmt_hashtag->bind_param("s", $hashtag);
                $stmt_hashtag->execute();

                // Adiciona a relação entre o painel e a hashtag
                $stmt_hashtag_id = $conn->prepare("SELECT id_hashtag FROM tb_hashtag WHERE nome_hashtag = ?");
                $stmt_hashtag_id->bind_param("s", $hashtag);
                $stmt_hashtag_id->execute();
                $result_hashtag_id = $stmt_hashtag_id->get_result();
                $row_hashtag_id = $result_hashtag_id->fetch_assoc();
                $hashtag_id = $row_hashtag_id['id_hashtag'];

                $stmt_painel_hashtag = $conn->prepare("INSERT INTO tb_painel_hashtag (id_painel, id_hashtag) VALUES (?, ?)");
                $stmt_painel_hashtag->bind_param("ii", $painel_id, $hashtag_id);
                $stmt_painel_hashtag->execute();
            }
        }
        header("Location: perfil.php");
        exit();
    } else {
        echo "Erro ao atualizar o painel.";
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT * FROM tb_painel WHERE id_painel = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $painel_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Painel não encontrado.";
        exit();
    }

    $painel = $result->fetch_assoc();
    $stmt->close();

    // Recupera as hashtags associadas ao painel
    $stmt_hashtags = $conn->prepare("SELECT h.nome_hashtag FROM tb_hashtag h JOIN tb_painel_hashtag ph ON h.id_hashtag = ph.id_hashtag WHERE ph.id_painel = ?");
    $stmt_hashtags->bind_param("i", $painel_id);
    $stmt_hashtags->execute();
    $result_hashtags = $stmt_hashtags->get_result();

    $hashtags = [];
    while ($row_hashtag = $result_hashtags->fetch_assoc()) {
        $hashtags[] = $row_hashtag['nome_hashtag'];
    }
    $hashtags_str = implode(', ', $hashtags);
    $stmt_hashtags->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Painel</title>
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
<div class="box-editar-painel">
    <h1>Editar Painel</h1>
    <form action="editar_painel.php?id=<?php echo $painel_id; ?>" method="post">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" id="titulo" value="<?php echo htmlspecialchars($painel['titulo_painel']); ?>"><br>
        <label for="descricao">Descrição:</label><br>
        <textarea name="descricao" class="descricao" id="descricao"><?php echo htmlspecialchars($painel['descricao_painel']); ?></textarea><br>
        <label for="hashtags">Hashtags (separadas por vírgula):</label><br>
        <input type="text" name="hashtags" id="hashtags" value="<?php echo htmlspecialchars($hashtags_str); ?>"><br><br>
        <input type="submit" value="Atualizar" class="btn-atualizar">
        <script src="verifica_painel.js"></script>
    </form>
</div>
</body>
</html>