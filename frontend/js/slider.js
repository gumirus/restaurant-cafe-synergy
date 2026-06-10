// ========== NEWS SLIDER ==========
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.news-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.slide');
    const prevBtn = slider.querySelector('.slider-prev');
    const nextBtn = slider.querySelector('.slider-next');
    const dotsContainer = slider.querySelector('.slider-dots');
    
    let currentSlide = 0;
    let slideInterval;

    // Создаём точки
    slides.forEach((_, i) => {
        const dot = document.createElement('span');
        dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
        dot.addEventListener('click', () => goToSlide(i));
        if (dotsContainer) dotsContainer.appendChild(dot);
    });

    function goToSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.slider-dot').forEach(d => d.classList.remove('active'));
        
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        const dots = document.querySelectorAll('.slider-dot');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }

    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });

    function startInterval() {
        slideInterval = setInterval(nextSlide, 5000);
    }
    function resetInterval() {
        clearInterval(slideInterval);
        startInterval();
    }

    // Показываем первый слайд
    goToSlide(0);
    startInterval();
});
