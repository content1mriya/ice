document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const mainNav = document.querySelector('[data-main-nav]');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            mainNav.classList.toggle('is-open');
        });
    }

    const modal = document.getElementById('orderModal');
    const productInput = document.getElementById('orderProduct');
    const pageInput = document.getElementById('orderPageUrl');
    const messageBox = document.getElementById('orderMessage');
    const orderForm = document.getElementById('orderForm');

    function openModal(productName) {
        if (!modal) return;
        if (productInput) productInput.value = productName || 'Консультація з підбору кондиціонера';
        if (pageInput) pageInput.value = window.location.href;
        if (messageBox) {
            messageBox.textContent = '';
            messageBox.className = 'form-message';
        }
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-order-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openModal(btn.getAttribute('data-product'));
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
            closeLightbox();
        }
    });

    if (orderForm) {
        orderForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (messageBox) {
                messageBox.textContent = 'Відправляємо...';
                messageBox.className = 'form-message';
            }

            fetch(orderForm.action, {
                method: 'POST',
                body: new FormData(orderForm),
                headers: { 'Accept': 'application/json' }
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (messageBox) {
                        messageBox.textContent = data.message || 'Готово.';
                        messageBox.className = 'form-message ' + (data.success ? 'is-success' : 'is-error');
                    }
                    if (data.success) {
                        orderForm.reset();
                        if (productInput) productInput.value = productInput.defaultValue;
                    }
                })
                .catch(function () {
                    if (messageBox) {
                        messageBox.textContent = 'Помилка відправки. Спробуйте ще раз.';
                        messageBox.className = 'form-message is-error';
                    }
                });
        });
    }

    const galleryImages = Array.isArray(window.productGalleryImages) ? window.productGalleryImages : [];
    const mainImage = document.getElementById('productMainImage');
    const lightbox = document.getElementById('productLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    let currentIndex = 0;

    function setMainImage(index) {
        if (!galleryImages[index]) return;
        currentIndex = index;
        if (mainImage) mainImage.src = galleryImages[index];
        document.querySelectorAll('.js-product-thumb').forEach(function (thumb) {
            thumb.classList.toggle('is-active', Number(thumb.getAttribute('data-index')) === index);
        });
    }

    function openLightbox(index) {
        if (!lightbox || !lightboxImage || !galleryImages.length) return;
        currentIndex = index || 0;
        lightboxImage.src = galleryImages[currentIndex];
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        if (!modal || !modal.classList.contains('is-open')) {
            document.body.style.overflow = '';
        }
    }

    function moveLightbox(direction) {
        if (!galleryImages.length) return;
        currentIndex = (currentIndex + direction + galleryImages.length) % galleryImages.length;
        if (lightboxImage) lightboxImage.src = galleryImages[currentIndex];
        setMainImage(currentIndex);
    }

    document.querySelectorAll('.js-product-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            setMainImage(Number(thumb.getAttribute('data-index')) || 0);
        });
        thumb.addEventListener('dblclick', function () {
            openLightbox(Number(thumb.getAttribute('data-index')) || 0);
        });
    });

    document.querySelectorAll('.js-lightbox-open').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openLightbox(Number(btn.getAttribute('data-index')) || currentIndex);
        });
    });

    document.querySelectorAll('[data-lightbox-close]').forEach(function (btn) {
        btn.addEventListener('click', closeLightbox);
    });
    document.querySelectorAll('[data-lightbox-prev]').forEach(function (btn) {
        btn.addEventListener('click', function () { moveLightbox(-1); });
    });
    document.querySelectorAll('[data-lightbox-next]').forEach(function (btn) {
        btn.addEventListener('click', function () { moveLightbox(1); });
    });
    if (lightbox) {
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) closeLightbox();
        });
    }

    // Product tabs: event delegation so it works even if buttons are replaced/cached.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-tab-target]');
        if (!button) return;

        const tabs = button.closest('[data-tabs]');
        if (!tabs) return;

        const target = button.getAttribute('data-tab-target');
        const buttons = tabs.querySelectorAll('[data-tab-target]');
        const panels = tabs.querySelectorAll('[data-tab-panel]');

        buttons.forEach(function (btn) {
            const isActive = btn === button;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target);
        });
    });

});

// Smooth carousel for homepage featured products.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-product-carousel]').forEach(function (carousel) {
        const viewport = carousel.querySelector('[data-carousel-viewport]');
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        if (!viewport || !prev || !next) return;

        function getStep() {
            const item = carousel.querySelector('.featured-carousel-item');
            if (!item) return viewport.clientWidth;
            const styles = window.getComputedStyle(carousel.querySelector('.featured-carousel-track'));
            const gap = parseFloat(styles.columnGap || styles.gap || 0) || 0;
            return item.getBoundingClientRect().width + gap;
        }

        function updateArrows() {
            const maxScroll = viewport.scrollWidth - viewport.clientWidth - 2;
            prev.classList.toggle('is-disabled', viewport.scrollLeft <= 2);
            next.classList.toggle('is-disabled', viewport.scrollLeft >= maxScroll);
        }

        prev.addEventListener('click', function () {
            viewport.scrollBy({ left: -getStep(), behavior: 'smooth' });
        });

        next.addEventListener('click', function () {
            viewport.scrollBy({ left: getStep(), behavior: 'smooth' });
        });

        viewport.addEventListener('scroll', function () {
            window.requestAnimationFrame(updateArrows);
        });
        window.addEventListener('resize', updateArrows);
        updateArrows();
    });
});
