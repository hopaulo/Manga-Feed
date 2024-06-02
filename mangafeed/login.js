document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(event) {
        let errors = [];
        
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value.trim();
        
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (email === '' || !emailPattern.test(email)) {
            errors.push("Email válido é obrigatório.");
        }
        if (senha === '') {
            errors.push("Senha é obrigatória.");
        }

        // Verificação das credenciais
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'verifica_login.php', false);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send('email=' + encodeURIComponent(email) + '&senha=' + encodeURIComponent(senha));
        if (xhr.responseText.trim() === "Credenciais inválidas.") {
            errors.push("Credenciais inválidas.");
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