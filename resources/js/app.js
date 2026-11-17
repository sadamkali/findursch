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

Alpine.start();

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
