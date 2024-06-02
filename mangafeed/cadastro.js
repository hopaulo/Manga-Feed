document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(event) {
        let errors = [];
        
        const nomeUsuario = document.getElementById('nome_usuario').value.trim();
        const userUsuario = document.getElementById('user_usuario').value.trim();
        const emailUsuario = document.getElementById('email_usuario').value.trim();
        const senhaUsuario = document.getElementById('senha_usuario').value.trim();
        const senhaConfirm = document.getElementById('senha_confirm').value.trim();
        
        if (nomeUsuario === '') {
            errors.push("Nome é obrigatório.");
        }
        if (userUsuario === '') {
            errors.push("Usuário é obrigatório.");
        }
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (emailUsuario === '' || !emailPattern.test(emailUsuario)) {
            errors.push("Email válido é obrigatório.");
        }
        if (senhaUsuario === '') {
            errors.push("Senha é obrigatória.");
        }
        if (senhaUsuario !== senhaConfirm) {
            errors.push("As senhas não coincidem.");
        }

        // Verifica se email já está cadastrado
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'verifica_email.php', false);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send('email_usuario=' + encodeURIComponent(emailUsuario));
        if (xhr.responseText.trim() === "Email já cadastrado.") {
            errors.push("Email já cadastrado.");
        }
        
        if (errors.length > 0) {
            event.preventDefault();
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
});
