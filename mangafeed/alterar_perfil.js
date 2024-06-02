document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault();

        let errors = [];
        
        const user_usuario = document.getElementById('user_usuario').value.trim();
        const email_usuario = document.getElementById('email_usuario').value.trim();
        const senha_usuario = document.getElementById('senha_usuario').value.trim();
        const senha_nova = document.getElementById('senha_nova').value.trim();
        const senha_confirm = document.getElementById('senha_confirm').value.trim();
        
        if (user_usuario === '') {
            errors.push("Usuário é obrigatório.");
        }
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (email_usuario === '' || !emailPattern.test(email_usuario)) {
            errors.push("Email válido é obrigatório.");
        }
        if (senha_nova !== senha_confirm) {
            errors.push("As senhas não coincidem.");
        }

        if (senha_usuario === '') {
            errors.push("Senha atual é obrigatória.");
        }

        if (errors.length > 0) {
            displayErrors(errors);
            return;
        }

        // Veficar senha atual
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'alterar_perfil.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            console.log(xhr.responseText);
            if (xhr.status === 200) {
                if (xhr.responseText.trim() === "Senha incorreta.") {
                    errors.push("Senha atual incorreta.");
                    displayErrors(errors);
                } else if (xhr.responseText.trim() === "Senha correta.") {
                    // Se a senha atual estiver correta, pode enviar as atualizações
                    const xhrUpdate = new XMLHttpRequest();
                    xhrUpdate.open('POST', 'alterar_perfil.php', true);
                    xhrUpdate.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhrUpdate.onload = function() {
                        console.log(xhrUpdate.responseText);
                        if (xhrUpdate.status === 200) {
                            if (xhrUpdate.responseText.trim() === "Atualização bem-sucedida.") {
                                alert("As atualizações foram feitas com sucesso!");
                                window.location.href = "perfil.php";
                            } else {
                                displayErrors(["Erro ao atualizar perfil: " + xhrUpdate.responseText]);
                            }
                        } else {
                            displayErrors(["Erro ao atualizar perfil."]);
                        }
                    };
                    xhrUpdate.send('user_usuario=' + encodeURIComponent(user_usuario) +
                                   '&email_usuario=' + encodeURIComponent(email_usuario) +
                                   '&senha_usuario=' + encodeURIComponent(senha_usuario) +
                                   '&senha_nova=' + encodeURIComponent(senha_nova) +
                                   '&senha_confirm=' + encodeURIComponent(senha_confirm));
                } else {
                    displayErrors(["Erro desconhecido: " + xhr.responseText]);
                }
            } else {
                errors.push("Erro na verificação da senha.");
                displayErrors(errors);
            }
        };
        xhr.send('verificar_senha=true&senha_usuario=' + encodeURIComponent(senha_usuario));
    });

    function displayErrors(errors) {
        const errorDiv = document.createElement('div');
        errorDiv.style.color = 'red';
        errorDiv.innerHTML = errors.map(error => `<p>${error}</p>`).join('');
        const form = document.querySelector('form');
        if (form.querySelector('div[style="color: red;"]')) {
            form.querySelector('div[style="color: red;"]').remove();
        }
        form.insertBefore(errorDiv, form.firstChild);
    }
});