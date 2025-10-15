document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const passwordField = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        // Cambiar tipo
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        
        // Cambiar icono
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
});