function toggleLike(painelId) {
    const likeButton = document.getElementById('like-button-' + painelId);
    const likeCount = document.getElementById('like-count-' + painelId);

    // Envia a requisição AJAX para curtir/descurtir o painel
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'curtir.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Atualiza a interface do usuário com os novos dados
                likeCount.textContent = response.num_curtidas;
                likeButton.value = response.is_liked ? 'Descurtir' : 'Curtir';
            } else {
                alert('Erro ao curtir/descurtir o painel: ' + response.message);
            }
        } else {
            alert('Erro ao fazer a solicitação.');
        }
    };
    xhr.send('id_painel=' + painelId);
}