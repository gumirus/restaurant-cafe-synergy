// ========== FAQ ACCORDION ==========
document.addEventListener('DOMContentLoaded', function() {
    const faqList = document.getElementById('faq-list');
    if (!faqList) return;

    const faqData = [
        { q: 'Как сделать заказ?', a: 'Выберите блюда из меню, добавьте в корзину и оформите заказ.' },
        { q: 'Какое время доставки?', a: 'Среднее время доставки — 60 минут.' },
        { q: 'Есть ли у вас вегетарианские блюда?', a: 'Да, в меню представлен отдельный раздел вегетарианских блюд.' },
        { q: 'Как оплатить заказ?', a: 'Наличными курьеру, картой онлайн или при получении.' },
    ];

    faqList.innerHTML = faqData.map((item, index) => `
        <div class="faq-item">
            <div class="faq-question" data-index="${index}">${item.q}</div>
            <div class="faq-answer">${item.a}</div>
        </div>
    `).join('');

    // Обработчик клика
    faqList.addEventListener('click', function(e) {
        const question = e.target.closest('.faq-question');
        if (!question) return;

        const item = question.parentElement;
        item.classList.toggle('active');
    });
});
