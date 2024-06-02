<?php
include("conecta.php");
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['usuario_id'];
$painel_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($painel_id) {
    // Primeiro, exclua as hashtags associadas ao painel
    $stmt = $conn->prepare("DELETE FROM tb_painel_hashtag WHERE id_painel = ?");
    $stmt->bind_param("i", $painel_id);

    if (!$stmt->execute()) {
        echo "Erro ao excluir as hashtags.";
        $stmt->close();
        exit();
    }

    // Em seguida, exclua as curtidas associadas ao painel
    $stmt = $conn->prepare("DELETE FROM tb_curtida WHERE id_painel = ?");
    $stmt->bind_param("i", $painel_id);

    if (!$stmt->execute()) {
        echo "Erro ao excluir as curtidas.";
        $stmt->close();
        exit();
    }

    // Finalmente, exclua o painel
    $stmt = $conn->prepare("DELETE FROM tb_painel WHERE id_painel = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $painel_id, $current_user_id);

    if ($stmt->execute()) {
        header("Location: perfil.php");
        exit();
    } else {
        echo "Erro ao excluir o painel.";
    }
    $stmt->close();
} else {
    echo "Painel não encontrado.";
}
?>
