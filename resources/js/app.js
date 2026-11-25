import './bootstrap';
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import anchor from '@alpinejs/anchor';

Alpine.plugin(intersect);
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(anchor);

window.Alpine = Alpine;
// Clear selections left by the retired school-comparison feature.
try { window.localStorage.removeItem('compare_schools'); } catch (error) { /* Storage may be unavailable in restricted browsers. */ }
// Global Alpine components


Alpine.data('homepageHero', (configuredSlides) => ({
    slides: Array.isArray(configuredSlides) ? configuredSlides : [],
    active: 0,
    timer: null,
    get currentSlide() {
        return this.slides[this.active] || this.slides[0] || {
            image_url: '',
            title: 'Find the right school for your child',
            description: 'Browse nursery, primary, secondary, vocational and university listings across Uganda, and explore school profiles.',
            button_label: '',
            button_url: '',
        };
    },
    get backgroundStyle() {
        const image = this.currentSlide.image_url;
        return image ? { '--hero-background-image': 'url(' + JSON.stringify(image) + ')' } : {};
    },
    init() {
        this.slides = this.slides.filter(slide => slide && slide.image_url);
        if (!this.slides.length) {
            this.slides = [{
                image_url: '',
                title: 'Find the right school for your child',
                description: 'Browse nursery, primary, secondary, vocational and university listings across Uganda, and explore school profiles.',
                button_label: '',
                button_url: '',
            }];
        }
        if (this.slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.timer = window.setInterval(() => this.next(), 6500);
        }
    },
    destroy() {
        if (this.timer) window.clearInterval(this.timer);
    },
    show(index) {
        this.active = index;
    },
    next() {
        this.active = (this.active + 1) % this.slides.length;
    },
    previous() {
        this.active = (this.active - 1 + this.slides.length) % this.slides.length;
    },
}));


Alpine.data('schoolCarousel', () => ({
    activePage: 0,
    pages: 1,
    timer: null,
    paused: false,
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    resizeHandler: null,
    pauseHandler: null,
    resumeHandler: null,
    init() {
        this.resizeHandler = () => this.measure();
        this.pauseHandler = () => { this.paused = true; };
        this.resumeHandler = () => { this.paused = false; };
        window.addEventListener('resize', this.resizeHandler, { passive: true });
        this.$el.addEventListener('mouseenter', this.pauseHandler);
        this.$el.addEventListener('mouseleave', this.resumeHandler);
        this.$el.addEventListener('focusin', this.pauseHandler);
        this.$el.addEventListener('focusout', this.resumeHandler);
        this.$nextTick(() => this.measure());
    },
    destroy() {
        this.stop();
        if (this.resizeHandler) window.removeEventListener('resize', this.resizeHandler);
        if (this.pauseHandler) {
            this.$el.removeEventListener('mouseenter', this.pauseHandler);
            this.$el.removeEventListener('focusin', this.pauseHandler);
        }
        if (this.resumeHandler) {
            this.$el.removeEventListener('mouseleave', this.resumeHandler);
            this.$el.removeEventListener('focusout', this.resumeHandler);
        }
    },
    start() {
        if (this.reducedMotion || this.pages < 2 || this.timer) return;
        this.timer = window.setInterval(() => { if (!this.paused) this.go((this.activePage + 1) % this.pages); }, 6500);
    },
    stop() {
        if (this.timer) window.clearInterval(this.timer);
        this.timer = null;
    },
    measure() {
        const track = this.$refs.track;
        if (!track || !track.clientWidth) return;
        this.pages = Math.max(1, Math.ceil((track.scrollWidth - 1) / track.clientWidth));
        this.activePage = Math.min(this.pages - 1, Math.round(track.scrollLeft / track.clientWidth));
        if (this.pages > 1) this.start();
        else this.stop();
    },
    sync() { this.measure(); },
    go(page) {
        const track = this.$refs.track;
        if (!track) return;
        this.activePage = ((page % this.pages) + this.pages) % this.pages;
        track.scrollTo({ left: this.activePage * track.clientWidth, behavior: this.reducedMotion ? 'auto' : 'smooth' });
    },
    next() { this.go(this.activePage + 1); },
    previous() { this.go(this.activePage - 1); },
}));

Alpine.data('homepageHorizontalCarousel', () => ({
    canScroll: false,
    timer: null,
    paused: false,
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    resizeHandler: null,
    pauseHandler: null,
    resumeHandler: null,
    init() {
        this.resizeHandler = () => this.measure();
        this.pauseHandler = () => { this.paused = true; };
        this.resumeHandler = () => { this.paused = false; };
        window.addEventListener('resize', this.resizeHandler, { passive: true });
        this.$el.addEventListener('mouseenter', this.pauseHandler);
        this.$el.addEventListener('mouseleave', this.resumeHandler);
        this.$el.addEventListener('focusin', this.pauseHandler);
        this.$el.addEventListener('focusout', this.resumeHandler);
        this.$nextTick(() => this.measure());
    },
    destroy() {
        if (this.timer) window.clearInterval(this.timer);
        if (this.resizeHandler) window.removeEventListener('resize', this.resizeHandler);
        if (this.pauseHandler) {
            this.$el.removeEventListener('mouseenter', this.pauseHandler);
            this.$el.removeEventListener('focusin', this.pauseHandler);
        }
        if (this.resumeHandler) {
            this.$el.removeEventListener('mouseleave', this.resumeHandler);
            this.$el.removeEventListener('focusout', this.resumeHandler);
        }
    },
    measure() {
        const track = this.$refs.track;
        if (!track) return;
        this.canScroll = track.scrollWidth > track.clientWidth + 8;
        if (this.canScroll && !this.reducedMotion && !this.timer) {
            this.timer = window.setInterval(() => { if (!this.paused) this.next(); }, 7000);
        } else if (!this.canScroll && this.timer) {
            window.clearInterval(this.timer);
            this.timer = null;
        }
    },
    move(direction) {
        const track = this.$refs.track;
        const card = track?.firstElementChild;
        if (!track || !card) return;
        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap) || 24;
        const step = card.getBoundingClientRect().width + gap;
        const maximum = Math.max(0, track.scrollWidth - track.clientWidth);
        const target = direction > 0
            ? (track.scrollLeft + step >= maximum - 4 ? 0 : track.scrollLeft + step)
            : (track.scrollLeft - step <= 4 ? maximum : track.scrollLeft - step);
        track.scrollTo({ left: target, behavior: this.reducedMotion ? 'auto' : 'smooth' });
    },
    next() { this.move(1); },
    previous() { this.move(-1); },
}));

Alpine.data('homepageLeadCarousel', (count) => ({
    active: 0,
    count: Number(count) || 0,
    timer: null,
    paused: false,
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    init() {
        this.pauseHandler = () => { this.paused = true; };
        this.resumeHandler = () => { this.paused = false; };
        this.$el.addEventListener('mouseenter', this.pauseHandler);
        this.$el.addEventListener('mouseleave', this.resumeHandler);
        this.$el.addEventListener('focusin', this.pauseHandler);
        this.$el.addEventListener('focusout', this.resumeHandler);
        if (this.count > 1 && !this.reducedMotion) {
            this.timer = window.setInterval(() => { if (!this.paused) this.next(); }, 6500);
        }
    },
    destroy() {
        if (this.timer) window.clearInterval(this.timer);
        if (this.pauseHandler) {
            this.$el.removeEventListener('mouseenter', this.pauseHandler);
            this.$el.removeEventListener('focusin', this.pauseHandler);
        }
        if (this.resumeHandler) {
            this.$el.removeEventListener('mouseleave', this.resumeHandler);
            this.$el.removeEventListener('focusout', this.resumeHandler);
        }
    },
    show(index) { this.active = Math.max(0, Math.min(this.count - 1, Number(index) || 0)); },
    next() { if (this.count > 1) this.active = (this.active + 1) % this.count; },
    previous() { if (this.count > 1) this.active = (this.active - 1 + this.count) % this.count; },
}));

Alpine.data('homepageRows', (items) => ({
    items: Array.isArray(items) ? items : [],
    active: 0,
    timer: null,
    paused: false,
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    get visibleItems() {
        if (!this.items.length) return [];
        const visible = Math.min(3, this.items.length);
        return Array.from({ length: visible }, (_, offset) => this.items[(this.active + offset) % this.items.length]);
    },
    init() {
        this.pauseHandler = () => { this.paused = true; };
        this.resumeHandler = () => { this.paused = false; };
        this.$el.addEventListener('mouseenter', this.pauseHandler);
        this.$el.addEventListener('mouseleave', this.resumeHandler);
        this.$el.addEventListener('focusin', this.pauseHandler);
        this.$el.addEventListener('focusout', this.resumeHandler);
        if (this.items.length > 3 && !this.reducedMotion) {
            this.timer = window.setInterval(() => { if (!this.paused) this.next(); }, 7500);
        }
    },
    destroy() {
        if (this.timer) window.clearInterval(this.timer);
        if (this.pauseHandler) {
            this.$el.removeEventListener('mouseenter', this.pauseHandler);
            this.$el.removeEventListener('focusin', this.pauseHandler);
        }
        if (this.resumeHandler) {
            this.$el.removeEventListener('mouseleave', this.resumeHandler);
            this.$el.removeEventListener('focusout', this.resumeHandler);
        }
    },
    next() { if (this.items.length > 1) this.active = (this.active + 1) % this.items.length; },
    previous() { if (this.items.length > 1) this.active = (this.active - 1 + this.items.length) % this.items.length; },
}));

Alpine.data('homepageQuotes', (items) => ({
    quotes: Array.isArray(items) ? items : [],
    active: 0,
    timer: null,
    paused: false,
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    get quote() {
        return this.quotes[this.active] || { rating: 5, body: '', initial: 'F', name: 'FindURSCH', school: '' };
    },
    init() {
        this.pauseHandler = () => { this.paused = true; };
        this.resumeHandler = () => { this.paused = false; };
        this.$el.addEventListener('mouseenter', this.pauseHandler);
        this.$el.addEventListener('mouseleave', this.resumeHandler);
        this.$el.addEventListener('focusin', this.pauseHandler);
        this.$el.addEventListener('focusout', this.resumeHandler);
        if (this.quotes.length > 1 && !this.reducedMotion) {
            this.timer = window.setInterval(() => { if (!this.paused) this.next(); }, 8000);
        }
    },
    destroy() {
        if (this.timer) window.clearInterval(this.timer);
        if (this.pauseHandler) {
            this.$el.removeEventListener('mouseenter', this.pauseHandler);
            this.$el.removeEventListener('focusin', this.pauseHandler);
        }
        if (this.resumeHandler) {
            this.$el.removeEventListener('mouseleave', this.resumeHandler);
            this.$el.removeEventListener('focusout', this.resumeHandler);
        }
    },
    show(index) { this.active = Math.max(0, Math.min(this.quotes.length - 1, Number(index) || 0)); },
    next() { if (this.quotes.length > 1) this.active = (this.active + 1) % this.quotes.length; },
}));

Alpine.data('schoolTabs', () => ({
    active: 'overview',
    setTab(tab) { this.active = tab }
}));

Alpine.data('searchSuggestions', () => ({
    q: '',
    results: [],
    open: false,
    loading: false,
    async search() {
        if (this.q.length < 2) {
            this.results = [];
            this.open = false;
            return;
        }
        this.loading = true;
        try {
            const r = await fetch(`/api/search/suggest?q=${encodeURIComponent(this.q)}`);
            const d = await r.json();
            this.results = d.data || [];
            this.open = true;
        } catch (e) {
            this.results = [];
        }
        this.loading = false;
    }
}));

Alpine.data('lightbox', () => ({
    open: false,
    activeIndex: 0,
    images: [],
    show(images, index) {
        this.images = images;
        this.activeIndex = index;
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.open = false;
        document.body.style.overflow = '';
    },
    next() {
        this.activeIndex = (this.activeIndex + 1) % this.images.length;
    },
    prev() {
        this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;
    }
}));
 

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!prefersReducedMotion && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('motion-enabled');

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -48px 0px',
    });

    const observeRevealTargets = (root = document) => {
        const elements = [];

        if (root instanceof Element && root.matches('[data-reveal]')) elements.push(root);
        if (root.querySelectorAll) elements.push(...root.querySelectorAll('[data-reveal]'));

        elements.forEach(element => {
            if (element.dataset.revealObserved) return;
            element.dataset.revealObserved = 'true';
            revealObserver.observe(element);
        });
    };

    observeRevealTargets();

    const revealMutationObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node instanceof Element) observeRevealTargets(node);
            });
        });
    });

    if (document.body) {
        revealMutationObserver.observe(document.body, { childList: true, subtree: true });
    }
} else {
    document.querySelectorAll('[data-reveal]').forEach(element => element.classList.add('is-visible'));
}


// The reference homepage is connected to live FindURSCH data and accessible controls.
function initializeFindURSCHHomepage() {
    const home = document.querySelector('.findursch-home-page');
    if (!home || home.dataset.homeInteractionsReady === 'true') return;
    home.dataset.homeInteractionsReady = 'true';

    const schoolTrack = home.querySelector('[data-home-school-track]');
    const schoolStep = () => {
        const card = schoolTrack?.querySelector('.school');
        return card ? card.getBoundingClientRect().width + 24 : 320;
    };
    home.querySelector('[data-home-school-prev]')?.addEventListener('click', () => {
        schoolTrack?.scrollBy({ left: -schoolStep(), behavior: 'smooth' });
    });
    home.querySelector('[data-home-school-next]')?.addEventListener('click', () => {
        schoolTrack?.scrollBy({ left: schoolStep(), behavior: 'smooth' });
    });

    const schoolDots = [...home.querySelectorAll('[data-home-school-dot]')];
    schoolDots.forEach(dot => dot.addEventListener('click', () => {
        schoolTrack?.scrollTo({
            left: Number(dot.dataset.homeSchoolDot || 0) * schoolStep(),
            behavior: 'smooth'
        });
    }));
    schoolTrack?.addEventListener('scroll', () => {
        if (!schoolDots.length) return;
        const active = Math.max(0, Math.min(
            schoolDots.length - 1,
            Math.round(schoolTrack.scrollLeft / schoolStep())
        ));
        schoolDots.forEach((dot, index) => dot.classList.toggle('on', index === active));
    }, { passive: true });

    const hero = home.querySelector('[data-home-hero]');
    if (hero) {
        const heroSlides = [...hero.querySelectorAll('[data-home-hero-slide]')];
        const heroDots = [...hero.querySelectorAll('[data-home-hero-dot]')];
        const heroTitle = hero.querySelector('[data-home-hero-title]');
        const heroDescription = hero.querySelector('[data-home-hero-description]');
        const heroCta = hero.querySelector('[data-home-hero-cta]');
        const previousButton = hero.querySelector('[data-home-hero-prev]');
        const nextButton = hero.querySelector('[data-home-hero-next]');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        let activeHeroSlide = Math.max(0, heroSlides.findIndex(slide => slide.classList.contains('is-active')));
        let heroPaused = reducedMotion;
        let pointerStart = null;

        const showHeroSlide = index => {
            if (!heroSlides.length) return;
            activeHeroSlide = (index + heroSlides.length) % heroSlides.length;

            heroSlides.forEach((slide, slideIndex) => {
                const isActive = slideIndex === activeHeroSlide;
                slide.classList.toggle('is-active', isActive);
                slide.setAttribute('aria-hidden', String(!isActive));
            });

            heroDots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === activeHeroSlide;
                dot.classList.toggle('on', isActive);
                dot.setAttribute('aria-pressed', String(isActive));
            });

            const slide = heroSlides[activeHeroSlide];
            if (heroTitle) heroTitle.textContent = slide.dataset.slideTitle || '';
            if (heroDescription) {
                heroDescription.textContent = slide.dataset.slideDescription || '';
                heroDescription.hidden = !slide.dataset.slideDescription;
            }

            if (heroCta) {
                const label = (slide.dataset.slideButtonLabel || '').trim();
                const url = (slide.dataset.slideButtonUrl || '').trim();
                const internalUrl = url.startsWith('/') && !url.startsWith('//');
                let safeExternalUrl = false;
                try {
                    safeExternalUrl = new URL(url).protocol === 'https:';
                } catch {
                    safeExternalUrl = false;
                }

                if (label && (internalUrl || safeExternalUrl)) {
                    heroCta.textContent = label;
                    heroCta.href = url;
                    heroCta.hidden = false;
                } else {
                    heroCta.hidden = true;
                    heroCta.removeAttribute('href');
                    heroCta.textContent = '';
                }
            }
        };

        if (heroSlides.length > 0) {
            showHeroSlide(activeHeroSlide);
        }

        previousButton?.addEventListener('click', () => showHeroSlide(activeHeroSlide - 1));
        nextButton?.addEventListener('click', () => showHeroSlide(activeHeroSlide + 1));
        heroDots.forEach(dot => dot.addEventListener('click', () => {
            showHeroSlide(Number(dot.dataset.homeHeroDot || 0));
        }));

        if (heroSlides.length > 1) {
            const autoplay = window.setInterval(() => {
                if (!hero.isConnected) {
                    window.clearInterval(autoplay);
                    return;
                }
                if (!heroPaused && !document.hidden) {
                    showHeroSlide(activeHeroSlide + 1);
                }
            }, 6500);

            home.__homepageHeroTimer = autoplay;
            hero.addEventListener('mouseenter', () => { heroPaused = true; });
            hero.addEventListener('mouseleave', () => { heroPaused = reducedMotion; });
            hero.addEventListener('focusin', () => { heroPaused = true; });
            hero.addEventListener('focusout', event => {
                if (!hero.contains(event.relatedTarget)) heroPaused = reducedMotion;
            });

            hero.addEventListener('pointerdown', event => {
                if (event.pointerType !== 'touch') return;
                pointerStart = { x: event.clientX, y: event.clientY };
                heroPaused = true;
            }, { passive: true });

            hero.addEventListener('pointerup', event => {
                if (!pointerStart) return;
                const deltaX = event.clientX - pointerStart.x;
                const deltaY = event.clientY - pointerStart.y;
                pointerStart = null;
                if (Math.abs(deltaX) > 48 && Math.abs(deltaX) > Math.abs(deltaY) * 1.25) {
                    showHeroSlide(activeHeroSlide + (deltaX < 0 ? 1 : -1));
                }
                heroPaused = reducedMotion;
            }, { passive: true });

            hero.addEventListener('pointercancel', () => {
                pointerStart = null;
                heroPaused = reducedMotion;
            }, { passive: true });
        }
    }

    const lead = home.querySelector('[data-home-news-lead]');
    if (lead) {
        const slides = [...lead.querySelectorAll('[data-home-news-slide]')];
        const dots = [...lead.querySelectorAll('[data-home-news-dot]')];
        let active = Math.max(0, slides.findIndex(slide => !slide.hidden));
        const show = index => {
            if (!slides.length) return;
            active = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => { slide.hidden = i !== active; });
            dots.forEach((dot, i) => dot.classList.toggle('on', i === active));
        };
        lead.querySelector('[data-home-news-prev]')?.addEventListener('click', () => show(active - 1));
        lead.querySelector('[data-home-news-next]')?.addEventListener('click', () => show(active + 1));
        dots.forEach(dot => dot.addEventListener('click', () => show(Number(dot.dataset.homeNewsDot || 0))));
    }

    home.querySelectorAll('[data-home-row-rotator]').forEach(list => {
        const rows = [...list.querySelectorAll('.row')];
        if (rows.length <= 3) return;
        let start = 0;
        const render = () => rows.forEach((row, index) => {
            row.hidden = ((index - start + rows.length) % rows.length) >= 3;
        });
        list.querySelector('[data-home-row-prev]')?.addEventListener('click', () => {
            start = (start - 1 + rows.length) % rows.length;
            render();
        });
        list.querySelector('[data-home-row-next]')?.addEventListener('click', () => {
            start = (start + 1) % rows.length;
            render();
        });
        render();
    });

    home.querySelectorAll('[data-home-reviews]').forEach(reviews => {
        const slides = [...reviews.querySelectorAll('[data-home-review-slide]')];
        const dots = [...reviews.querySelectorAll('[data-home-review-dot]')];
        const show = index => {
            slides.forEach((slide, i) => { slide.hidden = i !== index; });
            dots.forEach((dot, i) => dot.classList.toggle('on', i === index));
        };
        dots.forEach(dot => dot.addEventListener('click', () => {
            show(Number(dot.dataset.homeReviewDot || 0));
        }));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFindURSCHHomepage, { once: true });
} else {
    initializeFindURSCHHomepage();
}
document.addEventListener('livewire:navigated', initializeFindURSCHHomepage);

Alpine.start();
