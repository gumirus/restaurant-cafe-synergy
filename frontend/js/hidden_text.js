// ========== HIDDEN TEXT (ограничение символов) ==========
document.addEventListener('DOMContentLoaded', function() {
    const maxLength = 100;
    const elements = document.querySelectorAll('.text-limit');

    elements.forEach(el => {
        const text = el.textContent.trim();
        if (text.length > maxLength) {
            el.textContent = text.substring(0, maxLength) + '...';
        }
    });
});
