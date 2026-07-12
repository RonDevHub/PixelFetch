document.addEventListener('DOMContentLoaded', () => {
    const galleryItems = document.querySelectorAll('.gallery-item');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxDesc = document.getElementById('lightbox-desc');
    const lightboxLikes = document.getElementById('lightbox-likes');
    const lightboxComments = document.getElementById('lightbox-comments');
    const lightboxPermalink = document.getElementById('lightbox-permalink');
    const closeBtn = document.getElementById('close-btn');

    if (!lightbox || !lightboxImg || !lightboxDesc || !lightboxLikes || !lightboxComments || !lightboxPermalink) {
        console.error('PixelFetch: Lightbox-Elemente im DOM nicht gefunden.');
        return;
    }

    galleryItems.forEach(item => {
        item.addEventListener('click', () => {
            const imgSrc = item.getAttribute('data-img');
            const desc = item.getAttribute('data-desc') || '';
            const likes = item.getAttribute('data-likes') || '0';
            const comments = item.getAttribute('data-comments') || '0';
            const permalink = item.getAttribute('data-permalink') || '#';

            lightboxImg.src = imgSrc;
            lightboxDesc.textContent = desc;
            lightboxLikes.textContent = likes;
            lightboxComments.textContent = comments;
            lightboxPermalink.href = permalink;

            lightbox.style.display = 'flex';
        });
    });

    const closeLightbox = () => {
        lightbox.style.display = 'none';
        lightboxImg.src = '';
        lightboxDesc.textContent = '';
        lightboxPermalink.href = '#';
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', closeLightbox);
    }

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.style.display === 'flex') {
            closeLightbox();
        }
    });

    window.addEventListener('message', (event) => {
        if (event.data && typeof event.data === 'object' && event.data.type === 'SET_PIXELFETCH_THEME') {
            const targetTheme = event.data.theme;
            if (['light', 'dark', 'auto'].includes(targetTheme)) {
                document.documentElement.setAttribute('data-theme', targetTheme);
            }
        }
    });
});