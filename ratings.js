function filterReviews(rating) {
    const cards = document.querySelectorAll('.review-card');
    const buttons = document.querySelectorAll('.filter-btn');

    // Update active button styling
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if(btn.innerText.includes(rating) || (rating === 'all' && btn.innerText === 'All')) {
            btn.classList.add('active');
        }
    });

    // Filter cards
    cards.forEach(card => {
        if(rating === 'all') {
            card.style.display = 'block';
        } else {
            const cardRating = card.getAttribute('data-rating');
            if(cardRating == rating) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        }
    });
}