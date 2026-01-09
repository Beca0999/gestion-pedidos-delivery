<script>
    document.addEventListener('livewire:load', function () {
        let lastCount = null;
        setInterval(() => {
            let badge = document.querySelector('.fi-sidebar-group-item-badge');
            if (badge) {
                let currentCount = parseInt(badge.innerText);
                if (lastCount !== null && currentCount > lastCount) {
                    let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    audio.play();
                }
                lastCount = currentCount;
            }
        }, 10000);
    });
</script>
