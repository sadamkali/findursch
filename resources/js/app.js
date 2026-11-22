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
// Global Alpine components
Alpine.data('compareBar', () => ({
    schools: JSON.parse(localStorage.getItem('compare_schools') || '[]'),
    init() {
        const sync = () => {
            this.schools = JSON.parse(localStorage.getItem('compare_schools') || '[]');
        };

        window.addEventListener('storage', sync);
        window.addEventListener('findursch:compare-add', event => this.add(event.detail));
    },
    add(slug) {
        if (this.schools.length < 3 && !this.schools.includes(slug)) {
            this.schools.push(slug);
            localStorage.setItem('compare_schools', JSON.stringify(this.schools));
        }
    },
    remove(slug) {
        this.schools = this.schools.filter(s => s !== slug);
        localStorage.setItem('compare_schools', JSON.stringify(this.schools));
    },
    clear() {
        this.schools = [];
        localStorage.removeItem('compare_schools');
    },
    has(slug) {
        return this.schools.includes(slug);
    }
}));

Alpine.data('homepageHero', (configuredSlides) => ({
    slides: Array.isArray(configuredSlides) ? configuredSlides : [],
    active: 0,
    timer: null,
    get currentSlide() {
        return this.slides[this.active] || this.slides[0] || {
            image_url: '',
            title: 'Find the right school for your child',
            description: 'Browse nursery, primary, secondary, vocational and university listings across Uganda, and compare them side by side.',
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
                description: 'Browse nursery, primary, secondary, vocational and university listings across Uganda, and compare them side by side.',
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
    init() {
        this.$nextTick(() => this.measure());
        window.addEventListener('resize', () => this.measure());
    },
    measure() {
        const track = this.$refs.track;
        if (!track || !track.clientWidth) return;
        this.pages = Math.max(1, Math.ceil(track.scrollWidth / track.clientWidth));
        this.activePage = Math.min(this.pages - 1, Math.round(track.scrollLeft / track.clientWidth));
    },
    sync() { this.measure(); },
    go(page) {
        const track = this.$refs.track;
        if (!track) return;
        this.activePage = Math.max(0, Math.min(this.pages - 1, page));
        track.scrollTo({ left: this.activePage * track.clientWidth, behavior: 'smooth' });
    },
    next() { this.go(this.activePage + 1); },
    previous() { this.go(this.activePage - 1); },
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

Alpine.start();
