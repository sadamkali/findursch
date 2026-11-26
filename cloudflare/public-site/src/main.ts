type SchoolCard = {
  id: number;
  name: string;
  slug: string;
  tagline?: string | null;
  town?: string | null;
  district_name?: string;
  region_name?: string;
  school_type?: string;
  boarding_type?: string;
  fees_from?: number | string | null;
  fees_to?: number | string | null;
  rating_average?: number | string;
  reviews_count?: number;
  is_featured?: boolean;
  image_url?: string | null;
};

type SchoolDetail = SchoolCard & {
  description?: string | null;
  address?: string | null;
  maps_url?: string | null;
  gender_policy?: string | null;
  phone?: string | null;
  email?: string | null;
  website?: string | null;
  whatsapp?: string | null;
  founded_year?: number | null;
  admissions_open?: boolean;
  admission_classes?: string | null;
  admission_deadline?: string | null;
  admission_requirements?: string | null;
  how_to_apply?: string | null;
  curricula?: Array<{ id: number; name: string; abbreviation: string }>;
  programs?: Array<{ id: number; name: string; level?: string; duration?: string; description?: string }>;
  facilities?: Array<{ id: number; name: string; category?: string; notes?: string }>;
  fees?: Array<{ id: number; category: string; label: string; amount: number | string; currency: string; period: string; notes?: string }>;
  media?: Array<{ id: number; url?: string | null; name?: string }>;
  region?: { name: string; slug: string };
  district?: { name: string; slug: string };
  type?: { name: string; slug: string };
};

type ListResponse = {
  data: SchoolCard[];
  meta?: { page: number; per_page: number; total: number; total_pages: number };
};

const app = document.querySelector<HTMLElement>("#app");
const year = document.querySelector<HTMLElement>("#year");
if (year) year.textContent = String(new Date().getFullYear());

const escapeHtml = (value: unknown): string =>
  String(value ?? "").replace(/[&<>"']/g, (char) => {
    const map: Record<string, string> = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
    };
    return map[char] ?? char;
  });

const safeExternalUrl = (value: string | null | undefined): string | null => {
  if (!value) return null;
  try {
    const parsed = new URL(value);
    return parsed.protocol === "https:" || parsed.protocol === "http:" ? parsed.toString() : null;
  } catch {
    return null;
  }
};

const formatMoney = (value: number | string | null | undefined): string => {
  if (value === null || value === undefined || value === "") return "Ask school";
  const amount = Number(value);
  if (!Number.isFinite(amount) || amount <= 0) return "Ask school";
  return new Intl.NumberFormat("en-UG", {
    style: "currency",
    currency: "UGX",
    maximumFractionDigits: 0,
  }).format(amount);
};

const schoolCard = (school: SchoolCard): string => {
  const place = [school.town, school.district_name, school.region_name]
    .filter(Boolean)
    .join(", ");
  const fees =
    school.fees_from || school.fees_to
      ? `${formatMoney(school.fees_from)} – ${formatMoney(school.fees_to)}`
      : "Fees available on request";
  const media = school.image_url
    ? `<img src="${escapeHtml(school.image_url)}" alt="" loading="lazy" />`
    : `<div class="school-placeholder" aria-hidden="true"><span>${escapeHtml(school.name.slice(0, 1).toUpperCase())}</span></div>`;

  return `
    <article class="school-card">
      <a class="school-image" href="/schools/${encodeURIComponent(school.slug)}">${media}</a>
      <div class="school-card-content">
        <div class="school-card-topline">
          <span class="type-pill">${escapeHtml(school.school_type ?? "School")}</span>
          ${school.is_featured ? '<span class="featured-pill">Featured</span>' : ""}
        </div>
        <h3><a href="/schools/${encodeURIComponent(school.slug)}">${escapeHtml(school.name)}</a></h3>
        <p class="school-place">${escapeHtml(place || "Uganda")}</p>
        <p class="school-tagline">${escapeHtml(school.tagline || "Explore this school's profile, programmes and admissions information.")}</p>
        <div class="school-card-bottom">
          <span class="fee-label">${escapeHtml(fees)}</span>
          <a class="text-link" href="/schools/${encodeURIComponent(school.slug)}">View profile <span aria-hidden="true">→</span></a>
        </div>
      </div>
    </article>`;
};

const searchPanel = (value = ""): string => `
  <form class="school-search" id="school-search">
    <label class="search-field">
      <span>School name or location</span>
      <input name="q" type="search" value="${escapeHtml(value)}" placeholder="Try a school, town or district" />
    </label>
    <button class="button button-primary" type="submit">Search schools <span aria-hidden="true">→</span></button>
  </form>`;

async function getJson<T>(path: string): Promise<T> {
  const response = await fetch(path, {
    headers: { Accept: "application/json" },
    credentials: "omit",
  });
  const body = (await response.json()) as T & { error?: string };
  if (!response.ok) {
    throw new Error(body.error || `Request failed (${response.status})`);
  }
  return body;
}

function attachSearch(): void {
  const form = document.querySelector<HTMLFormElement>("#school-search");
  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    const input = form.elements.namedItem("q") as HTMLInputElement | null;
    const query = input?.value.trim() ?? "";
    const target = new URL("/schools", window.location.origin);
    if (query) target.searchParams.set("q", query);
    window.location.assign(target.toString());
  });
}

async function renderHome(): Promise<void> {
  if (!app) return;
  app.innerHTML = `
    <section class="hero">
      <div class="hero-orbit hero-orbit-one"></div>
      <div class="hero-orbit hero-orbit-two"></div>
      <div class="hero-inner">
        <p class="eyebrow eyebrow-light">SCHOOL DISCOVERY, MADE CLEARER</p>
        <h1>Find a school that fits <em>your learner.</em></h1>
        <p class="hero-copy">Explore schools across Uganda, compare the details that matter and make a more confident choice for the next step.</p>
        ${searchPanel()}
        <div class="hero-trust"><span>Explore school profiles</span><span>Compare locations and fees</span><span>Review admissions details</span></div>
      </div>
      <div class="hero-caption"><span class="caption-mark"></span> Discover schools across Uganda</div>
    </section>

    <section class="section section-featured">
      <div class="section-heading">
        <div><p class="eyebrow">START EXPLORING</p><h2>Schools worth <em>discovering.</em></h2></div>
        <a class="text-link" href="/schools">Browse all schools <span aria-hidden="true">→</span></a>
      </div>
      <div id="home-schools" class="school-grid"><div class="loading-card">Loading school directory…</div></div>
    </section>

    <section class="section how-section" id="how-it-works">
      <div class="how-copy"><p class="eyebrow">A BETTER WAY TO CHOOSE</p><h2>Make the search feel <em>simple.</em></h2><p>FindURSCH brings school information together so families can explore options by location, school type and the details that matter to them.</p></div>
      <div class="how-steps">
        <div><span>01</span><h3>Explore</h3><p>Search schools by name, district or location.</p></div>
        <div><span>02</span><h3>Compare</h3><p>Review programmes, facilities and fee information.</p></div>
        <div><span>03</span><h3>Connect</h3><p>Use each school profile to find admissions information.</p></div>
      </div>
    </section>

    <section class="section about-section" id="about">
      <p class="eyebrow">ABOUT FINDURSCH</p>
      <h2>More clarity for every <em>school journey.</em></h2>
      <p>Our goal is to make school discovery more accessible by bringing useful school information into one place for families and communities across Uganda.</p>
      <a class="button button-outline" href="/schools">Explore the directory</a>
    </section>`;

  attachSearch();

  try {
    const result = await getJson<ListResponse>("/api/v1/schools?sort=featured&limit=6");
    const target = document.querySelector<HTMLElement>("#home-schools");
    if (!target) return;
    target.innerHTML = result.data.length
      ? result.data.map(schoolCard).join("")
      : '<p class="empty-state">No published school profiles are available yet.</p>';
  } catch {
    const target = document.querySelector<HTMLElement>("#home-schools");
    if (target) target.innerHTML = '<p class="empty-state">The school directory is being connected. Please check again shortly.</p>';
  }
}

async function renderDirectory(): Promise<void> {
  if (!app) return;
  const params = new URLSearchParams(window.location.search);
  const search = params.get("q") ?? "";
  app.innerHTML = `
    <section class="page-intro">
      <div class="page-intro-inner">
        <p class="eyebrow">FIND YOUR SCHOOL</p>
        <h1>Explore schools <em>across Uganda.</em></h1>
        <p>Search published school profiles and compare the information available for each school.</p>
        ${searchPanel(search)}
      </div>
    </section>
    <section class="section directory-section">
      <div class="directory-heading"><div><p class="eyebrow">SCHOOL DIRECTORY</p><h2 id="result-title">Published schools</h2></div><span id="result-count" class="result-count"></span></div>
      <div id="directory-schools" class="school-grid"><div class="loading-card">Searching schools…</div></div>
      <nav class="pagination" id="pagination" aria-label="School directory pages"></nav>
    </section>`;
  attachSearch();

  const page = Math.max(1, Number(params.get("page") || "1"));
  const query = new URLSearchParams({ page: String(page), limit: "12", sort: "newest" });
  if (search) query.set("q", search);

  try {
    const result = await getJson<ListResponse>(`/api/v1/schools?${query.toString()}`);
    const target = document.querySelector<HTMLElement>("#directory-schools");
    const count = document.querySelector<HTMLElement>("#result-count");
    if (target) {
      target.innerHTML = result.data.length
        ? result.data.map(schoolCard).join("")
        : '<p class="empty-state">No schools matched this search. Try another school name or location.</p>';
    }
    if (count) count.textContent = `${result.meta?.total ?? result.data.length} schools found`;
    const pagination = document.querySelector<HTMLElement>("#pagination");
    const totalPages = result.meta?.total_pages ?? 1;
    if (pagination && totalPages > 1) {
      const previous = page > 1 ? page - 1 : null;
      const next = page < totalPages ? page + 1 : null;
      const link = (targetPage: number, label: string, disabled: boolean): string => {
        const nextParams = new URLSearchParams(window.location.search);
        nextParams.set("page", String(targetPage));
        return `<a class="page-button ${disabled ? "is-disabled" : ""}" href="/schools?${nextParams.toString()}" ${disabled ? 'aria-disabled="true"' : ""}>${label}</a>`;
      };
      pagination.innerHTML = `${link(previous ?? 1, "← Previous", !previous)}<span>Page ${page} of ${totalPages}</span>${link(next ?? totalPages, "Next →", !next)}`;
    }
  } catch {
    const target = document.querySelector<HTMLElement>("#directory-schools");
    if (target) target.innerHTML = '<p class="empty-state">The school directory is temporarily unavailable. Please try again shortly.</p>';
  }
}

async function renderSchool(slug: string): Promise<void> {
  if (!app) return;
  app.innerHTML = '<section class="section"><div class="loading-card">Loading school profile…</div></section>';
  try {
    const response = await getJson<{ data: SchoolDetail }>(`/api/v1/schools/${encodeURIComponent(slug)}`);
    const school = response.data;
    const place = [school.town, school.district?.name, school.region?.name].filter(Boolean).join(", ");
    const gallery = (school.media ?? []).filter((item) => item.url);
    const curricula = (school.curricula ?? []).map((item) => `<span class="detail-chip">${escapeHtml(item.name)}</span>`).join("");
    const programmes = (school.programs ?? []).map((item) => `<article class="detail-list-item"><strong>${escapeHtml(item.name)}</strong><span>${escapeHtml([item.level, item.duration].filter(Boolean).join(" · "))}</span></article>`).join("");
    const facilities = (school.facilities ?? []).map((item) => `<span class="detail-chip">${escapeHtml(item.name)}</span>`).join("");
    const fees = (school.fees ?? []).map((item) => `<article class="fee-row"><span><strong>${escapeHtml(item.label)}</strong><small>${escapeHtml(item.period)}${item.academic_year ? ` · ${escapeHtml(item.academic_year)}` : ""}</small></span><strong>${formatMoney(item.amount)}</strong></article>`).join("");
    const heroImage = gallery.find((item) => item.url)?.url ?? "";
    const websiteUrl = safeExternalUrl(school.website);
    const mapsUrl = safeExternalUrl(school.maps_url);

    app.innerHTML = `
      <section class="detail-hero">
        <div class="detail-hero-inner">
          <a class="back-link" href="/schools">← Back to schools</a>
          <p class="eyebrow eyebrow-light">${escapeHtml(school.type?.name ?? school.school_type ?? "School")} · ${escapeHtml(place || "Uganda")}</p>
          <h1>${escapeHtml(school.name)}</h1>
          <p class="detail-tagline">${escapeHtml(school.tagline ?? "")}</p>
          <div class="detail-tags"><span>${escapeHtml(school.boarding_type ?? "School")}</span><span>${escapeHtml(school.gender_policy ?? "")}</span>${school.admissions_open ? "<span>Admissions open</span>" : ""}</div>
        </div>
      </section>
      <section class="section detail-section">
        <div class="detail-main">
          <div class="detail-block"><p class="eyebrow">ABOUT THE SCHOOL</p><h2>School <em>overview.</em></h2><p class="detail-description">${escapeHtml(school.description ?? "More information about this school will be added to its profile.")}</p></div>
          ${heroImage ? `<img class="detail-cover" src="${escapeHtml(heroImage)}" alt="${escapeHtml(school.name)}" />` : ""}
          ${curricula ? `<div class="detail-block"><p class="eyebrow">CURRICULUM</p><h2>Learning <em>pathways.</em></h2><div class="detail-chip-list">${curricula}</div></div>` : ""}
          ${programmes ? `<div class="detail-block"><p class="eyebrow">PROGRAMMES</p><h2>What students <em>can study.</em></h2><div class="detail-list">${programmes}</div></div>` : ""}
          ${facilities ? `<div class="detail-block"><p class="eyebrow">FACILITIES</p><h2>School <em>facilities.</em></h2><div class="detail-chip-list">${facilities}</div></div>` : ""}
          ${fees ? `<div class="detail-block"><p class="eyebrow">SCHOOL FEES</p><h2>Fee <em>information.</em></h2><div class="fee-list">${fees}</div></div>` : ""}
          ${school.admission_requirements || school.how_to_apply ? `<div class="detail-block"><p class="eyebrow">ADMISSIONS</p><h2>Joining the <em>school.</em></h2>${school.admission_classes ? `<p><strong>Classes:</strong> ${escapeHtml(school.admission_classes)}</p>` : ""}${school.admission_requirements ? `<p>${escapeHtml(school.admission_requirements)}</p>` : ""}${school.how_to_apply ? `<p>${escapeHtml(school.how_to_apply)}</p>` : ""}</div>` : ""}
        </div>
        <aside class="detail-aside">
          <div class="contact-card"><p class="eyebrow">SCHOOL INFORMATION</p><h2>Contact & <em>location.</em></h2>
            <p><span>Location</span><strong>${escapeHtml(place || school.address || "Uganda")}</strong></p>
            ${school.phone ? `<p><span>Phone</span><strong>${escapeHtml(school.phone)}</strong></p>` : ""}
            ${school.email ? `<p><span>Email</span><strong>${escapeHtml(school.email)}</strong></p>` : ""}
            ${websiteUrl ? `<p><span>Website</span><a href="${escapeHtml(websiteUrl)}" rel="noopener noreferrer" target="_blank">Visit school website ↗</a></p>` : ""}
            ${mapsUrl ? `<a class="button button-primary button-full" href="${escapeHtml(mapsUrl)}" rel="noopener noreferrer" target="_blank">View on map ↗</a>` : ""}
          </div>
        </aside>
      </section>`;
    document.title = `${school.name} | FindURSCH`;
  } catch {
    app.innerHTML = '<section class="section"><div class="empty-state"><h2>School profile not found</h2><p>This school may not have a published profile yet.</p><a class="button button-primary" href="/schools">Back to schools</a></div></section>';
  }
}

async function start(): Promise<void> {
  if (!app) return;
  const path = window.location.pathname.replace(/\/+$/, "") || "/";
  const schoolDetail = path.match(/^\/schools\/([^/]+)$/);
  if (schoolDetail) {
    await renderSchool(decodeURIComponent(schoolDetail[1]));
  } else if (path === "/schools") {
    await renderDirectory();
  } else {
    await renderHome();
  }
}

void start();
