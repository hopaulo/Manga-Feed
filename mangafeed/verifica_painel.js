document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(event) {
        let errors = [];
        
        const titulo = document.getElementById('titulo').value.trim();
        const imagem = document.getElementById('imagem') ? document.getElementById('imagem').files[0] : null;
        
        if (titulo === '') {
            errors.push("Título é obrigatório.");
        }
        
        // Se o formulário for de postagem, a imagem também é obrigatória
        if (imagem === undefined && window.location.pathname.endsWith('postar_painel.php')) {
            errors.push("Imagem é obrigatória.");
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