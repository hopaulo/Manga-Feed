<?php
include("conecta.php");

$current_user_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$view_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;
$is_own_profile = ($current_user_id === $view_user_id);

// Recupera as informações do usuário
$stmt_user = $conn->prepare("SELECT * FROM tb_usuario WHERE id_usuario = ?");
$stmt_user->bind_param("i", $view_user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows === 0) {
    echo "Usuário não encontrado.";
    exit();
}

$user_info = $user_result->fetch_assoc();
$stmt_user->close();

// Recupera os painéis do usuário
$stmt_paineis = $conn->prepare("SELECT p.id_painel, p.titulo_painel, p.descricao_painel, p.url_painel, p.data_postagem_painel,
                               (SELECT COUNT(*) FROM tb_curtida c WHERE c.id_painel = p.id_painel) AS num_curtidas
                                FROM tb_painel p 
                                WHERE p.usuario_id = ?
                                ORDER BY p.data_postagem_painel DESC");
$stmt_paineis->bind_param("i", $view_user_id);
$stmt_paineis->execute();
$paineis_result = $stmt_paineis->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user_info['user_usuario']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<button onclick="topFunction()" id="myBtn" title="Go to top">Topo</button>
<nav class="navbar-perfil">
    <h2><a href="inicial.php">Feed de Mangás</a></h2>
    <?php
    if ($is_own_profile) {
        echo '<div class="menu">';
        echo '<a href="perfil.php">' . htmlspecialchars($user_info['user_usuario']) . '</a>';
        echo '<a href="logout.php">Logout</a>';
        echo '<a href="postar_painel.php">Postar</a>';
        echo '<a href="alterar_perfil.php">Editar Perfil</a>';
        echo '<a href="curtidos.php">Posts Curtidos</a>';
        echo '</div>';
    }
    ?>
</nav>
<h1><?php echo htmlspecialchars($user_info['user_usuario']); ?></h1>
<?php
// Exibe os painéis
while ($row = $paineis_result->fetch_assoc()) {
    $is_liked = false;
    if ($current_user_id) {
        $stmt_like = $conn->prepare("SELECT * FROM tb_curtida WHERE id_painel = ? AND id_usuario = ?");
        $stmt_like->bind_param("ii", $row['id_painel'], $current_user_id);
        $stmt_like->execute();
        $curtida_result = $stmt_like->get_result();
        if ($curtida_result->num_rows > 0) {
            $is_liked = true;
        }
        $stmt_like->close();
    }

    echo '<div class="painel">';
    echo '<h3>' . htmlspecialchars($row['titulo_painel']) . '</h3>';
    echo '<p>Postado em ' . htmlspecialchars($row['data_postagem_painel']) . '</p>';
    echo '<p>' . htmlspecialchars($row['descricao_painel']) . '</p>';
    echo '<img src="' . BASE_URL . htmlspecialchars($row['url_painel']) . '" alt="Painel de Mangá">';
    echo '<p>Curtidas: <span id="like-count-' . htmlspecialchars($row['id_painel']) . '">' . htmlspecialchars($row['num_curtidas']) . '</span></p>';

    // Exibe as hashtags
    $stmt_hashtags = $conn->prepare("SELECT h.nome_hashtag FROM tb_hashtag h JOIN tb_painel_hashtag ph ON h.id_hashtag = ph.id_hashtag WHERE ph.id_painel = ?");
    $stmt_hashtags->bind_param("i", $row['id_painel']);
    $stmt_hashtags->execute();
    $result_hashtags = $stmt_hashtags->get_result();
    echo '<p>Hashtags: ';
    while ($hashtag = $result_hashtags->fetch_assoc()) {
        echo '<a class="hashtag" href="inicial.php?hashtag=' . urlencode($hashtag['nome_hashtag']) . '">' . htmlspecialchars($hashtag['nome_hashtag']) . ' </a>';
    }
    echo '</p>';
    $stmt_hashtags->close();

    if ($current_user_id) {
        $like_button_text = $is_liked ? 'Descurtir' : 'Curtir';
        echo '<input type="button" class="btn-like" id="like-button-' . htmlspecialchars($row['id_painel']) . '" value="' . $like_button_text . '" onclick="toggleLike(' . htmlspecialchars($row['id_painel']) . ')">';
    }
    if ($is_own_profile) {
        echo '<a href="editar_painel.php?id=' . htmlspecialchars($row['id_painel']) . '">Editar</a> | ';
        echo '<a href="excluir_painel.php?id=' . htmlspecialchars($row['id_painel']) . '" onclick="return confirm(\'Tem certeza que deseja excluir este painel?\')">Excluir</a>';
    }
    echo '</div>';
}
$stmt_paineis->close();
?>

<script src="like.js"></script>
<script src="botao.js"></script>
<footer>
    <p>Feito por <a href="#" id="author-link">Paulo Oliveira</a></p>
</footer>
</html>