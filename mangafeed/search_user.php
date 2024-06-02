<?php
include("conecta.php");

if (isset($_GET['query'])) {
    $query = "%" . $_GET['query'] . "%";
    $stmt = $conn->prepare("SELECT id_usuario, user_usuario FROM tb_usuario WHERE user_usuario LIKE ? LIMIT 10");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
}
?>
