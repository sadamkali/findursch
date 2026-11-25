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


// Uploaded homepage reference interactions, scoped away from the shared header.
function initializeFindURSCHReferenceHome() {
 const home = document.querySelector('.findursch-home-page');
 if (!home || home.dataset.referenceInitialized === 'true') return;
 home.dataset.referenceInitialized = 'true';
const $=id=>home.querySelector('#'+id),P=["","b","c","d"];
const dots=(el,n,i)=>el.innerHTML=Array.from({length:n},(_,k)=>`<i class="${k==i?"on":""}" data-i="${k}"></i>`).join("");

/* Hero dots swap the background */
$("hdots").onclick=e=>{const i=e.target.dataset.i;if(i==null)return;$("hero").classList.toggle("two",i==1);dots($("hdots"),2,+i)};

/* Scroll carousels (featured schools, videos). In Blade: loop over schools / videos. */
function carousel(track,prev,next,dotEl){
  const t=$(track),step=()=>t.firstElementChild.getBoundingClientRect().width+24;
  $(prev).onclick=()=>t.scrollBy({left:-step(),behavior:"smooth"});$(next).onclick=()=>t.scrollBy({left:step(),behavior:"smooth"});
  if(dotEl){const n=3;dots($(dotEl),n,0);t.addEventListener("scroll",()=>{const r=t.scrollLeft/(t.scrollWidth-t.clientWidth||1);dots($(dotEl),n,Math.min(n-1,Math.round(r*(n-1))))},{passive:true})}
}
const SCH=[["Nursery & primary","Acacia Grove Nursery & Primary","Wakiso district","UNEB"],["Secondary","Lakeview Senior Secondary","Mukono district","UNEB"],["Nursery & primary","Sunrise Day Care School","Kampala district","Other"],["Primary","Hillcrest Education Service","Kampala district","UNEB"],["Secondary","Cedar Ridge Secondary","Wakiso district","UNEB"],["Nursery & primary","Nilebridge International","Wakiso district","Cambridge"]];
$("ft").innerHTML=SCH.map((s,i)=>`<a class="card fc" href="#"><div class="ph ${P[i%4]}"></div><div class="b"><span class="kick">${s[0]}</span><h3>${s[1]}</h3><small>${s[2]}</small><span class="tag">${s[3]}</span> <span class="tag new">New</span></div></a>`).join("");
const VID=[["School name","Children's music classes","2026 · 0 views"],["School name","A visit to our school","2026 · 0 views"],["School name","Open day highlights","2026 · 0 views"]];
$("vt").innerHTML=VID.map((v,i)=>`<a class="card vid" href="#"><div class="ph ${P[(i+2)%4]}"><i class="play"></i></div><div class="b"><small>${v[0]}</small><h4>${v[1]}</h4><span>${v[2]}</span></div></a>`).join("");
carousel("ft","fp","fn","fd");carousel("vt","vp","vn");

/* News lead slider */
const L=[["Makerere University · Yesterday","Lead story headline sits over a dark gradient for contrast","c"],["Education desk · 2 days ago","Second lead story from the news desk","d"],["Policy · 3 days ago","Third lead story on education policy","b"],["Schools · 4 days ago","Fourth lead story from a partner school",""]];
let li=0;
function lead(i){li=(i+L.length)%L.length;const l=L[li];$("ls").textContent=l[0];$("lt").textContent=l[1];$("lead").className="lead ph "+l[2];dots($("ld"),L.length,li)}
$("lp").onclick=()=>lead(li-1);$("ln").onclick=()=>lead(li+1);$("ld").onclick=e=>{if(e.target.dataset.i!=null)lead(+e.target.dataset.i)};lead(0);

/* News list: arrows rotate the list */
let N=["Headline for a short news item","Second headline in the list","Third headline in the list","Fourth headline in the list","Fifth headline in the list"],ni=0;
function rows(){$("rows").innerHTML=[0,1,2].map(k=>{const j=(ni+k)%N.length;return `<a class="row" href="#"><div class="ph ${P[j%4]}"></div><div><h4>${N[j]}</h4><small>18th November 2026</small></div></a>`}).join("")+'<div class="mini"><button id="ru" aria-label="Previous">↑</button><button id="rd" aria-label="Next">↓</button></div>';
  $("ru").onclick=()=>{ni=(ni+N.length-1)%N.length;rows()};$("rd").onclick=()=>{ni=(ni+1)%N.length;rows()}}
rows();

/* Testimonials */
const Q=[["“Joining FindURSCH made our digital presence reach families we could not reach before. Enquiries have grown steadily.”","KM","Person name","Director, School name"],["“Parents now find our fees, facilities and contacts in one place, and the enquiries arrive ready to act on.”","AB","Person name","Head teacher, School name"],["“The listing took five minutes and the verified badge gave families more confidence in us.”","CD","Person name","Bursar, School name"]];
function quote(i){const q=Q[i];$("qt").textContent=q[0];$("qi").textContent=q[1];$("qn").textContent=q[2];$("qr").textContent=q[3];dots($("qd"),Q.length,i)}
$("qd").onclick=e=>{if(e.target.dataset.i!=null)quote(+e.target.dataset.i)};quote(0);
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeFindURSCHReferenceHome, { once: true });
else initializeFindURSCHReferenceHome();
document.addEventListener('livewire:navigated', initializeFindURSCHReferenceHome);

Alpine.start();
