function searchUser(query) {
    if (query.length === 0) {
        document.getElementById('user-suggestions').innerHTML = "";
        return;
    }
    //Consulta dinâmica por usuários
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            const users = JSON.parse(xhr.responseText);
            let suggestions = "";
            if (users.length > 0) {
                suggestions = "<ul>";
                users.forEach(user => {
                    suggestions += `<li><a href="perfil.php?id=${user.id_usuario}">${user.user_usuario}</a></li>`;
                });
                suggestions += "</ul>";
            } else {
                suggestions = "<p>No users found</p>";
            }
            document.getElementById('user-suggestions').innerHTML = suggestions;
        }
    };
    xhr.open("GET", "search_user.php?query=" + encodeURIComponent(query), true);
    xhr.send();
}
