<?php
include("conecta.php");
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['usuario_id'];

// Select dos painéis curtidos pelo usuário
$query = "SELECT p.id_painel, p.titulo_painel, p.descricao_painel, p.url_painel, p.data_postagem_painel, u.user_usuario, u.id_usuario,
                 (SELECT COUNT(*) FROM tb_curtida c WHERE c.id_painel = p.id_painel) AS num_curtidas 
          FROM tb_painel p 
          JOIN tb_usuario u ON p.usuario_id = u.id_usuario
          JOIN tb_curtida c ON p.id_painel = c.id_painel
          WHERE c.id_usuario = ?
          ORDER BY p.data_postagem_painel DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Curtidos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<button onclick="topFunction()" id="myBtn" title="Go to top">Topo</button>
<nav class="navbar-perfil">
    <h2><a href="inicial.php">Feed de Mangás</a></h2>
        <?php
        echo '<div class="menu">';
        echo '<a href="perfil.php">' . htmlspecialchars($_SESSION['user_usuario']) . '</a>';
        echo '<a href="logout.php">Logout</a>';
        echo '<a href="postar_painel.php">Postar</a>';
        echo '<a href="alterar_perfil.php">Editar Perfil</a>';
        echo '<a href="curtidos.php">Posts Curtidos</a>';
        echo '</div>';
        ?>
</nav>
<h1>Posts Curtidos</h1>
<?php
// Moostrar os painéis curtidos
while ($row = $result->fetch_assoc()) {
    $is_liked = false;
    $stmt_like = $conn->prepare("SELECT * FROM tb_curtida WHERE id_painel = ? AND id_usuario = ?");
    $stmt_like->bind_param("ii", $row['id_painel'], $current_user_id);
    $stmt_like->execute();
    $curtida_result = $stmt_like->get_result();
    if ($curtida_result->num_rows > 0) {
        $is_liked = true;
    }
    $stmt_like->close();

    echo '<div class="painel">';
    echo '<h3>' . htmlspecialchars($row['titulo_painel']) . '</h3>';
    echo '<p>Postado por: <a href="perfil.php?id=' . htmlspecialchars($row['id_usuario']) . '">' . htmlspecialchars($row['user_usuario']) . '</a> em ' . htmlspecialchars($row['data_postagem_painel']) . '</p>';
    echo '<p>' . htmlspecialchars($row['descricao_painel']) . '</p>';
    echo '<img src="' . BASE_URL . htmlspecialchars($row['url_painel']) . '" alt="Painel de Mangá">';
    echo '<p><span id="like-count-' . htmlspecialchars($row['id_painel']) . '">' . htmlspecialchars($row['num_curtidas']) . '</span> Curtidas</p>';

    // Mostrar as hashtags
    $stmt_hashtags = $conn->prepare("SELECT h.nome_hashtag FROM tb_hashtag h JOIN tb_painel_hashtag ph ON h.id_hashtag = ph.id_hashtag WHERE ph.id_painel = ?");
    $stmt_hashtags->bind_param("i", $row['id_painel']);
    $stmt_hashtags->execute();
    $result_hashtags = $stmt_hashtags->get_result();
    echo '<p>Hashtags: ';
    while ($hashtag = $result_hashtags->fetch_assoc()) {
        echo '<a class="hashtag" href="inicial.php?hashtag=' . urlencode($hashtag['nome_hashtag']) . '">' . htmlspecialchars($hashtag['nome_hashtag']) . '</a>';
    }
    echo '</p>';
    $stmt_hashtags->close();

    $like_button_text = $is_liked ? 'Descurtir' : 'Curtir';
    echo '<input type="button" class="btn-like" id="like-button-' . htmlspecialchars($row['id_painel']) . '" value="' . $like_button_text . '" onclick="toggleLike(' . htmlspecialchars($row['id_painel']) . ')">';
    echo '</div>';
}
$stmt->close();
?>

<script src="like.js"></script>
<script src="botao.js"></script>
<footer>
    <p>Feito por <a href="#" id="author-link">Paulo Oliveira</a></p>
</footer>
</html>