document.addEventListener('DOMContentLoaded', function () {
    const carousels = document.querySelectorAll('.carousel');

    carousels.forEach((carousel, index) => {
        const inner = carousel.querySelector('.carousel-inner');
        const items = carousel.querySelectorAll('.carousel-item');
        const itemWidth = 21;
        const itemMargin = 1.2;
        let currentIndex = 0;

        items.forEach(item => {
            const clone = item.cloneNode(true);
            inner.appendChild(clone);
        });

        function slide() {
            currentIndex++;
            let translateX = -currentIndex * (itemWidth + itemMargin);
            if (index === 1) {
                translateX -= 10;
            }
            inner.style.transition = 'transform 0.5s ease';
            inner.style.transform = `translateX(${translateX}%)`;

            if (currentIndex >= items.length) {
                setTimeout(() => {
                    inner.style.transition = 'none';
                    currentIndex = 0;
                    translateX = index === 1 ? -10 : 0;
                    inner.style.transform = `translateX(${translateX}%)`;
                    setTimeout(() => {
                        inner.style.transition = 'transform 0.5s ease';
                    }, 10);
                }, 500);
            }
        }

        setInterval(slide, 3000);
    });
});