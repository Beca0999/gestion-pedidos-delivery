<div x-data="{ 
    count: {{ \App\Models\Order::count() }},
    audio: new Audio('https://cdn.pixabay.com/audio/2021/08/04/audio_3386000780.mp3')
}" 
x-init="
    setInterval(async () => {
        const response = await fetch('/api/orders/count');
        const data = await response.json();
        if (data.count > count) {
            audio.play();
            count = data.count;
        }
    }, 10000)
">
</div>
