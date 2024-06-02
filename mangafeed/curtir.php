<?php
include("conecta.php");
// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$painel_id = isset($_POST['id_painel']) ? intval($_POST['id_painel']) : 0;

// Verifica se o painel existe
$stmt = $conn->prepare("SELECT id_painel FROM tb_painel WHERE id_painel = ?");
$stmt->bind_param("i", $painel_id);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Painel não encontrado.']);
    exit();
}

// Verifica se o usuário já curtiu o painel
$stmt = $conn->prepare("SELECT * FROM tb_curtida WHERE id_painel = ? AND id_usuario = ?");
$stmt->bind_param("ii", $painel_id, $usuario_id);
$stmt->execute();
$curtida_result = $stmt->get_result();

if ($curtida_result->num_rows > 0) {
    // Se já curtiu, remove a curtida
    $stmt = $conn->prepare("DELETE FROM tb_curtida WHERE id_painel = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $painel_id, $usuario_id);
    $stmt->execute();
    $is_liked = false;
} else {
    // Se não curtiu, adiciona a curtida
    $stmt = $conn->prepare("INSERT INTO tb_curtida (id_painel, id_usuario) VALUES (?, ?)");
    $stmt->bind_param("ii", $painel_id, $usuario_id);
    $stmt->execute();
    $is_liked = true;
}

// Obtém o número atualizado de curtidas
$stmt = $conn->prepare("SELECT COUNT(*) AS num_curtidas FROM tb_curtida WHERE id_painel = ?");
$stmt->bind_param("i", $painel_id);
$stmt->execute();
$count_result = $stmt->get_result();
$count_row = $count_result->fetch_assoc();
$num_curtidas = $count_row['num_curtidas'];

// Retorna apenas JSON
echo json_encode(['success' => true, 'is_liked' => $is_liked, 'num_curtidas' => $num_curtidas]);
?>