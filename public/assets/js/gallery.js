document.addEventListener('DOMContentLoaded', () => {
    const galleryItems = document.querySelectorAll('.gallery-item');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxDesc = document.getElementById('lightbox-desc');
    const lightboxLikes = document.getElementById('lightbox-likes');
    const lightboxComments = document.getElementById('lightbox-comments');
    const closeBtn = document.getElementById('close-btn');

    galleryItems.forEach(item => {
        item.addEventListener('click', () => {
            const imgSrc = item.getAttribute('data-img');
            const desc = item.getAttribute('data-desc') || 'Keine Beschreibung vorhanden.';
            const likes = item.getAttribute('data-likes');
            const comments = item.getAttribute('data-comments');

            lightboxImg.src = imgSrc;
            lightboxDesc.textContent = desc; // TextContent verhindert HTML-Injection
            lightboxLikes.textContent = likes;
            lightboxComments.textContent = comments;

            lightbox.classList.add('active');
        });
    });

    const closeLightbox = () => {
        lightbox.classList.remove('active');
        lightboxImg.src = '';
    };

    closeBtn.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    // ESC-Taste zum Schließen
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });
});