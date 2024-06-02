<?php
include("conecta.php");
// Verifica se há um filtro de hashtag
$hashtag_filter = isset($_GET['hashtag']) ? $_GET['hashtag'] : '';
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'data_postagem_painel';
$order_direction = isset($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC';

// Recupera os painéis do banco de dados mais recentes
$query = "SELECT p.id_painel, p.titulo_painel, p.descricao_painel, p.url_painel, p.data_postagem_painel, u.id_usuario, u.user_usuario,
                 (SELECT COUNT(*) FROM tb_curtida c WHERE c.id_painel = p.id_painel) AS num_curtidas 
          FROM tb_painel p 
          JOIN tb_usuario u ON p.usuario_id = u.id_usuario";
if (!empty($hashtag_filter)) {
    $query .= " JOIN tb_painel_hashtag ph ON p.id_painel = ph.id_painel 
                JOIN tb_hashtag h ON ph.id_hashtag = h.id_hashtag 
                WHERE h.nome_hashtag = ?";
}
$query .= " ORDER BY " . ($order_by == 'num_curtidas' ? 'num_curtidas' : 'p.data_postagem_painel') . " " . ($order_direction == 'ASC' ? 'ASC' : 'DESC');
$stmt = $conn->prepare($query);

if (!empty($hashtag_filter)) {
    $stmt->bind_param("s", $hashtag_filter);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<p class="sem_hashtag">Nenhuma hashtag encontrada :( </p>';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed de Mangás</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<header>
<nav class="navbar">
    <div class="nav-content">
        <div class="nav-left">
            <h1><a id="logo" href="inicial.php">Feed de Mangás</a></h1>
        </div>
        <div class="nav-right">
            <?php
            // Verifica se o usuário está logado
            if (isset($_SESSION['usuario_id'])) {
                $usuario_nome = htmlspecialchars($_SESSION['user_usuario']);
                 echo '<a class"menu" href="perfil.php">' . $usuario_nome . '</a>';
                 echo '<a class"menu" href="logout.php">Logout</a>';
            } else {
                echo '<a class"menu" href="login.php">Fazer login</a>';
                echo '<a class"menu" href="cadastro.php">Cadastrar</a>';
            }
            ?>
        </div>
    </div>
</nav>
<div class="box-search">
    <div class="user-search">
        <label for="search-user">Buscar usuário:</label>
        <input type="text" id="search-user" name="search-user" onkeyup="searchUser(this.value)">
        <div id="user-suggestions"></div>
    </div>
</div>
<div class="filters">
    <form method="get" action="">
        <label for="hashtag">Filtrar por hashtag:</label>
        <input type="text" id="hashtag" name="hashtag" value="<?php echo htmlspecialchars($hashtag_filter); ?>"><br>
        <label for="order_by">Ordenar por:</label>
        <select id="order_by" name="order_by">
            <option value="data_postagem_painel" <?php if ($order_by == 'data_postagem_painel') echo 'selected'; ?>>Data de Postagem</option>
            <option value="num_curtidas" <?php if ($order_by == 'num_curtidas') echo 'selected'; ?>>Quantidade de Curtidas</option>
        </select><br>
        <label for="order_direction">Ordem:</label>
        <select id="order_direction" name="order_direction">
            <option value="DESC" <?php if ($order_direction == 'DESC') echo 'selected'; ?>>Decrescente</option>
            <option value="ASC" <?php if ($order_direction == 'ASC') echo 'selected'; ?>>Crescente</option>
        </select>
        <button type="submit">Aplicar Filtros</button>
    </form>
</div>
</header>
<body>
<button onclick="topFunction()" id="myBtn" title="Go to top">Topo</button>
<?php
// Exibe os painéis
while ($row = $result->fetch_assoc()) {
    $is_liked = false;
    if (isset($_SESSION['usuario_id'])) {
        $stmt_like = $conn->prepare("SELECT * FROM tb_curtida WHERE id_painel = ? AND id_usuario = ?");
        $stmt_like->bind_param("ii", $row['id_painel'], $_SESSION['usuario_id']);
        $stmt_like->execute();
        $curtida_result = $stmt_like->get_result();
        if ($curtida_result->num_rows > 0) {
            $is_liked = true;
        }
        $stmt_like->close();
    }
    
    // Recupera as hashtags do painel
    $stmt_hashtags = $conn->prepare("SELECT h.nome_hashtag 
                                     FROM tb_hashtag h 
                                     JOIN tb_painel_hashtag ph ON h.id_hashtag = ph.id_hashtag 
                                     WHERE ph.id_painel = ?");
    $stmt_hashtags->bind_param("i", $row['id_painel']);
    $stmt_hashtags->execute();
    $hashtags_result = $stmt_hashtags->get_result();
    $hashtags = [];
    while ($hashtag_row = $hashtags_result->fetch_assoc()) {
        $hashtags[] = '<a href="?hashtag=' . urlencode($hashtag_row['nome_hashtag']) . '" class="hashtag-link">#' . htmlspecialchars($hashtag_row['nome_hashtag']) . '</a>';
    }
    $stmt_hashtags->close();

    echo '<div class="painel">';
    echo '<h3>' . htmlspecialchars($row['titulo_painel']) . '</h3>';
    echo '<p>Postado por: <a href="perfil.php?id=' . htmlspecialchars($row['id_usuario']) . '">' . htmlspecialchars($row['user_usuario']) . '</a> em ' . htmlspecialchars($row['data_postagem_painel']) . '</p>';
    echo '<p>' . htmlspecialchars($row['descricao_painel']) . '</p>';
    echo '<img src="' . BASE_URL . htmlspecialchars($row['url_painel']) . '" alt="Painel de Mangá">';
    echo '<p><span id="like-count-' . htmlspecialchars($row['id_painel']) . '">' . htmlspecialchars($row['num_curtidas']) . '</span> Curtidas</p>';
    if (!empty($hashtags)) {
        echo '<p>Hashtags: ' . implode(', ', $hashtags) . '</p>';
    }
    if (isset($_SESSION['usuario_id'])) {
        $like_button_text = $is_liked ? 'Descurtir' : 'Curtir';
        echo '<input type="button" class="btn-like" id="like-button-' . htmlspecialchars($row['id_painel']) . '" value="' . $like_button_text . '" onclick="toggleLike(' . htmlspecialchars($row['id_painel']) . ')">';
    }
    echo '</div>';
}
$stmt->close();
?>

<script src="like.js"></script>
<script src="search_user.js"></script>
<script src="botao.js"></script>
</body>
<footer>
    <p>Feito por <a href="#" id="author-link">Paulo Oliveira</a></p>
</footer>
</html>