<script>
    window.addEventListener('play-notification-sound', () => {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play().catch(error => console.log('Esperando interacción del usuario para sonar...'));
    });
</script>
