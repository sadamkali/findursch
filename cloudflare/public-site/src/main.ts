type Curriculum = { id: number; name: string; abbreviation?: string | null };
type SchoolCard = {
  id: number;
  name: string;
  slug: string;
  tagline?: string | null;
  description?: string | null;
  town?: string | null;
  district_name?: string | null;
  region_name?: string | null;
  school_type?: string | null;
  boarding_type?: string | null;
  gender_policy?: string | null;
  fees_from?: number | string | null;
  fees_to?: number | string | null;
  rating_average?: number | string | null;
  reviews_count?: number;
  is_featured?: boolean;
  featured_until?: string | null;
  admissions_open?: boolean;
  image_url?: string | null;
  curricula?: Curriculum[];
};
type SchoolDetail = SchoolCard & {
  address?: string | null;
  meta_title?: string | null;
  meta_description?: string | null;
  maps_url?: string | null;
  phone?: string | null;
  email?: string | null;
  website?: string | null;
  whatsapp?: string | null;
  founded_year?: number | null;
  admission_classes?: string | null;
  admission_deadline?: string | null;
  admission_requirements?: string | null;
  how_to_apply?: string | null;
  programs?: Array<{ id: number; name: string; level?: string; duration?: string; description?: string }>;
  facilities?: Array<{ id: number; name: string; category?: string; notes?: string }>;
  media?: Array<{ id: number; url?: string | null; name?: string; collection_name?: string }>;
  region?: { name: string; slug: string } | null;
  district?: { name: string; slug: string } | null;
  type?: { name: string; slug: string } | null;
};
type ListResponse = { data: SchoolCard[]; meta?: { page: number; per_page: number; total: number; total_pages: number } };
type Category = { id: number; name: string; slug: string; public_listing_count?: number };
type Article = { id: number; title: string; slug: string; excerpt?: string | null; body?: string | null; cover_image?: string | null; read_time_minutes?: number | null; published_at?: string | null; category?: { id: number; name: string; slug: string } | null };
type SchoolPost = { id: number; title: string; description?: string | null; image_url?: string | null; image_alt?: string | null; venue?: string | null; starts_at?: string | null; deadline?: string | null; published_at?: string | null; school?: { id: number; name: string; slug: string } | null };
type Job = { id: number; slug: string; title: string; employer: string; location: string; category: string; employment_type: string; excerpt?: string | null; salary?: string | null; deadline?: string | null; application_url?: string | null };
type Video = { id: number; title: string; youtube_url: string; youtube_video_id: string; school?: { id: number; name: string; slug: string } | null };
type Ad = { id: number; placement_id: string; image_url: string; image_alt?: string | null; destination_url?: string | null; width?: number; height?: number };
type HomepageData = {
  featured_schools: SchoolCard[];
  spotlight_school: SchoolCard | null;
  school_types: Category[];
  news_articles: Article[];
  insight_articles: Article[];
  partner_school_news: SchoolPost | null;
  upcoming_events: SchoolPost[];
  scholarship_count: number;
  latest_jobs: Job[];
  videos: Video[];
  ads: Ad[];
};
type Locations = { regions: Array<{ id: number; name: string; slug: string }>; districts: Array<{ id: number; name: string; slug: string; region_id: number }>; school_types: Category[]; curricula: Curriculum[] };

const app = document.querySelector<HTMLElement>("#app");
const LEGACY_SITE = "https://findurschool-d-dar-k.vercel.app";
const DASHBOARD = "https://account.riotinvestments.space";
const MEDIA_LOGO = "https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/062d6754-5266-46a5-9f83-1a97bc393a19.png";
const DEFAULT_DESCRIPTION = "Discover schools across Uganda. Explore school profiles, compare locations and fee estimates, and find admissions information with FindURSCH.";

const escapeHtml = (value: unknown): string =>
  String(value ?? "").replace(/[&<>"']/g, (char) => {
    const map: Record<string, string> = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
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

const legacyUrl = (path: string): string => LEGACY_SITE + (path.startsWith("/") ? path : "/" + path);
const accountUrl = (path: string): string => DASHBOARD + (path.startsWith("/") ? path : "/" + path);

const formatAmount = (value: number | string | null | undefined): string => {
  const amount = Number(value);
  return Number.isFinite(amount) && amount > 0
    ? new Intl.NumberFormat("en-UG", { maximumFractionDigits: 0 }).format(amount)
    : "";
};

const estimatedFee = (school: SchoolCard): string => {
  const from = Number(school.fees_from);
  const to = Number(school.fees_to);
  if (Number.isFinite(from) && Number.isFinite(to) && from > 0 && to > from) {
    return "Estimated UGX " + formatAmount(from) + " – UGX " + formatAmount(to) + " per term";
  }
  return "Fee range not provided";
};

const formatDate = (value: string | null | undefined, options: Intl.DateTimeFormatOptions = { day: "numeric", month: "short", year: "numeric" }): string => {
  if (!value) return "";
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? "" : new Intl.DateTimeFormat("en-UG", options).format(date);
};

async function getJson<T>(path: string): Promise<T> {
  const response = await fetch(path, {
    headers: { Accept: "application/json" },
    credentials: "omit",
  });
  const body = (await response.json().catch(() => ({}))) as T & { error?: string };
  if (!response.ok) throw new Error(body.error || "Request failed (" + response.status + ")");
  return body;
}

function locationLabel(school: SchoolCard): string {
  return [school.town, school.district_name, school.region_name].filter(Boolean).join(", ") || "Uganda";
}

function schoolTile(school: SchoolCard, variant = "tile"): string {
  const url = "/schools/" + encodeURIComponent(school.slug);
  const image = safeExternalUrl(school.image_url);
  const media = image
    ? '<img src="' + escapeHtml(image) + '" alt="' + escapeHtml(school.name) + ' school photo" loading="lazy" decoding="async">'
    : '<span class="findursch-school-tile__placeholder" aria-hidden="true">' + escapeHtml(school.name.slice(0, 2).toUpperCase()) + "</span>";
  const featured = school.is_featured && (!school.featured_until || new Date(school.featured_until).getTime() > Date.now()) ? '<span class="findursch-school-tile__featured">Featured</span>' : "";
  const curricula = (school.curricula || []).slice(0, 3).map((item) => "<span>" + escapeHtml(item.abbreviation || item.name) + "</span>").join("");
  const boarding = school.boarding_type === "both" || school.boarding_type === "mixed"
    ? "Day & boarding"
    : school.boarding_type === "boarding" ? "Boarding" : school.boarding_type === "day" ? "Day" : "";
  const chips = [
    '<span class="is-type">' + escapeHtml(school.school_type || "School") + "</span>",
    curricula,
    boarding ? "<span>" + boarding + "</span>" : "",
    school.admissions_open ? '<span class="is-admissions-open">Admissions open</span>' : "",
  ].join("");
  const rating = Number(school.rating_average) > 0
    ? '<span class="findursch-school-tile__rating"><span aria-hidden="true">★</span> ' + Number(school.rating_average).toFixed(1) + "</span>"
    : '<span class="findursch-school-tile__no-rating">No reviews yet</span>';
  const description = school.tagline || school.description || "Explore this school's profile, programmes and admissions information.";
  return '<article class="findursch-school-tile findursch-school-tile--' + variant + (variant === "tile" ? " school" : "") + '">' +
    '<a class="findursch-school-tile__media" href="' + url + '" aria-label="View ' + escapeHtml(school.name) + '">' + media + featured + "</a>" +
    '<div class="findursch-school-tile__body">' +
    '<h3 class="findursch-school-tile__title"><a href="' + url + '">' + escapeHtml(school.name) + "</a></h3>" +
    '<div class="findursch-school-tile__fee-line">' + escapeHtml(estimatedFee(school)) + "</div>" +
    '<p class="findursch-school-tile__description">' + escapeHtml(description) + "</p>" +
    '<div class="findursch-school-tile__chips">' + chips + "</div>" +
    '<div class="findursch-school-tile__meta"><span class="findursch-school-tile__location"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>' + escapeHtml(locationLabel(school)) + "</span></span>" + rating + '<a class="findursch-school-tile__save" href="' + escapeHtml(accountUrl("/login?returnTo=%2Faccount")) + '" aria-label="Sign in to save ' + escapeHtml(school.name) + '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5h12v16l-6-3.5-6 3.5z"/></svg></a></div>' +
    '<div class="findursch-school-tile__actions"><a href="' + url + '">View school →</a></div>' +
    "</div></article>";
}

function searchPanel(value = "", selectedType = "", schoolTypes: Category[] = []): string {
  const options = schoolTypes.map((type) => '<option value="' + escapeHtml(type.id) + '"' + (String(type.id) === selectedType ? " selected" : "") + ">" + escapeHtml(type.name) + "</option>").join("");
  return '<form class="search" id="school-search" role="search" action="/schools" method="get">' +
    '<label class="search-field"><span>School name or location</span><input name="q" type="search" value="' + escapeHtml(value) + '" placeholder="Search by name, location or district" aria-label="Search schools"></label>' +
    '<label class="search-type"><span>School type</span><select name="type" aria-label="School type"><option value="">All types</option>' + options + "</select></label>" +
    '<button class="btn btn-red" type="submit">Search schools <span aria-hidden="true">→</span></button></form>';
}

function attachSearch(): void {
  const form = document.querySelector<HTMLFormElement>("#school-search");
  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    const input = form.elements.namedItem("q") as HTMLInputElement | null;
    const type = form.elements.namedItem("type") as HTMLSelectElement | null;
    const target = new URL("/schools", window.location.origin);
    const query = input?.value.trim() || "";
    if (query) target.searchParams.set("q", query);
    if (type?.value) target.searchParams.set("type", type.value);
    window.location.assign(target.toString());
  });
}

function adBanner(ads: Ad[], placement: string): string {
  const ad = ads.find((item) => item.placement_id === placement);
  if (!ad) return "";
  const image = safeExternalUrl(ad.image_url);
  if (!image) return "";
  const destination = safeExternalUrl(ad.destination_url) || legacyUrl("/contact?subject=Advertising%20enquiry");
  return '<a class="findursch-home-ad" href="' + escapeHtml(destination) + '" target="_blank" rel="noopener noreferrer" aria-label="' + escapeHtml(ad.image_alt || "Sponsored FindURSCH advertisement") + '">' +
    '<img src="' + escapeHtml(image) + '" alt="' + escapeHtml(ad.image_alt || "Advertisement") + '" loading="lazy" decoding="async"><span>Advertisement</span></a>';
}

function schoolCategories(types: Category[]): string {
  if (!types.length) return '<li><a href="/schools">Browse all schools</a></li>';
  return types.map((type) => '<li><a href="/schools?type=' + encodeURIComponent(String(type.id)) + '"><span class="ico"></span>' + escapeHtml(type.name) + '<em>' + Number(type.public_listing_count || 0).toLocaleString("en-UG") + "</em></a></li>").join("");
}

function eventRows(events: SchoolPost[]): string {
  if (!events.length) return '<li class="empty-list-item">No upcoming events have been published yet.</li>';
  return events.map((event) => {
    const date = event.starts_at ? new Date(event.starts_at) : null;
    const day = date && !Number.isNaN(date.getTime()) ? date.toLocaleDateString("en-UG", { day: "2-digit" }) : "";
    const month = date && !Number.isNaN(date.getTime()) ? date.toLocaleDateString("en-UG", { month: "short" }).toUpperCase() : "";
    return '<li><a href="' + escapeHtml(legacyUrl("/events/" + event.id)) + '"><span class="date">' + day + "<small>" + month + "</small></span><div><strong>" + escapeHtml(event.title) + "</strong><span>" + escapeHtml(event.school?.name || event.venue || "School event") + "</span></div></a></li>";
  }).join("");
}

function newsSections(data: HomepageData): string {
  const news = data.news_articles || [];
  const lead = news[0];
  const leadImage = lead ? safeExternalUrl(lead.cover_image) : null;
  const leadStory = lead
    ? '<a class="lead-story" href="' + escapeHtml(legacyUrl("/news/" + lead.slug)) + '"' + (leadImage ? ' style="background-image:url(&quot;' + escapeHtml(leadImage) + '&quot;)"' : "") + '><div class="cap"><small>' + escapeHtml(lead.category?.name || "Education news") + " · " + escapeHtml(formatDate(lead.published_at)) + '</small><h3>' + escapeHtml(lead.title) + "</h3>" + (lead.excerpt ? "<p>" + escapeHtml(lead.excerpt.slice(0, 145)) + "</p>" : "") + '<span class="lead-read-link">Read full story →</span></div></a>'
    : '<div class="cap lead-empty"><small>FindURSCH newsroom</small><h3>Education news and school updates</h3><p>Published education stories will appear here as they become available.</p><a class="lead-read-link" href="' + escapeHtml(legacyUrl("/news")) + '">Browse news →</a></div>';
  const rows = news.slice(1, 6).map((article, index) => {
    const image = safeExternalUrl(article.cover_image);
    const bg = image ? ' style="background-image:url(&quot;' + escapeHtml(image) + '&quot;);background-size:cover;background-position:center"' : "";
    return '<a class="row" href="' + escapeHtml(legacyUrl("/news/" + article.slug)) + '"><div class="ph ' + ["b", "d", "c", ""][index % 4] + '"' + bg + ">" + (image ? "" : "<span>" + escapeHtml(article.title.slice(0, 1).toUpperCase()) + "</span>") + '</div><div><h4>' + escapeHtml(article.title) + "</h4><small>" + escapeHtml(formatDate(article.published_at)) + "</small></div></a>";
  }).join("") || '<p class="empty-list-item">New education headlines will appear here when published.</p>';
  const partner = data.partner_school_news;
  const partnerImage = partner ? safeExternalUrl(partner.image_url) : null;
  const partnerCard = partner
    ? '<a class="card prem" href="' + escapeHtml(legacyUrl("/school-news/" + partner.id)) + '"><div class="ph d"' + (partnerImage ? ' style="background-image:url(&quot;' + escapeHtml(partnerImage) + '&quot;);background-size:cover;background-position:center"' : "") + ">" + (partnerImage ? "" : '<span>FN</span>') + '</div><span class="flag">Partner school news</span><div class="b"><h3>' + escapeHtml(partner.title) + "</h3><p>" + escapeHtml((partner.description || "").slice(0, 140)) + '</p><small><span>' + escapeHtml(formatDate(partner.published_at)) + "</span><span>" + escapeHtml(partner.school?.name || "School community") + "</span></small></div></a>"
    : '<div class="card prem"><div class="ph d"></div><div class="b"><h3>Share a story from your school</h3><p>Tell the FindURSCH newsroom about a school announcement, achievement or community update.</p><a class="more" href="' + escapeHtml(legacyUrl("/contact?subject=School%20news%20submission")) + '">Contact the newsroom →</a></div></div>';
  return '<section class="sec"><div class="head"><h2>Education <span>news headlines</span></h2><a class="more" href="' + escapeHtml(legacyUrl("/news")) + '">All news</a></div><div class="news"><div class="lead ph c" data-home-news-lead>' + leadStory + '</div><div class="card rows">' + rows + "</div>" + partnerCard + "</div></section>";
}

function videoSection(videos: Video[]): string {
  if (!videos.length) {
    return '<div class="card empty-card"><h3>School videos will appear here</h3><p>Approved videos shared by schools will be listed here.</p><a class="more" href="' + escapeHtml(legacyUrl("/news?view=videos")) + '">Explore school videos</a></div>';
  }
  return '<div class="vids">' + videos.slice(0, 2).map((video, index) => {
    const url = safeExternalUrl(video.youtube_url);
    const thumb = video.youtube_video_id ? "https://img.youtube.com/vi/" + encodeURIComponent(video.youtube_video_id) + "/hqdefault.jpg" : "";
    return '<a class="card vid" href="' + escapeHtml(url || "#") + '" target="_blank" rel="noopener noreferrer"><div class="ph ' + ["c", "b"][index % 2] + '"' + (thumb ? ' style="background-image:url(&quot;' + thumb + '&quot;);background-size:cover;background-position:center"' : "") + '><i class="play" aria-hidden="true"></i></div><div class="b"><small>' + escapeHtml(video.school?.name || "School community") + '</small><h4>' + escapeHtml(video.title) + "</h4><span>Watch on YouTube ↗</span></div></a>";
  }).join("") + "</div>";
}

function insightSection(data: HomepageData): string {
  const insights = data.insight_articles || [];
  const featured = insights[0];
  const spotlight = data.spotlight_school;
  if (!featured && !spotlight) return "";
  const image = featured ? safeExternalUrl(featured.cover_image) : null;
  const featuredCard = featured
    ? '<a class="card big" href="' + escapeHtml(legacyUrl("/articles/" + featured.slug)) + '"><div class="ph d"' + (image ? ' style="background-image:url(&quot;' + escapeHtml(image) + '&quot;);background-size:cover;background-position:center"' : "") + ">" + (image ? "" : "<span>" + escapeHtml(featured.title.slice(0, 2).toUpperCase()) + "</span>") + '</div><div class="b"><h3>' + escapeHtml(featured.title) + "</h3><p>" + escapeHtml((featured.excerpt || featured.body || "").replace(/<[^>]*>/g, "").slice(0, 145)) + '</p><small><span>' + escapeHtml(formatDate(featured.published_at, { month: "short", year: "numeric" })) + "</span><span>" + Number(featured.read_time_minutes || 0) + " min read</span></small></div></a>"
    : "";
  const rows = insights.slice(1, 5).map((article, index) => {
    const cover = safeExternalUrl(article.cover_image);
    return '<a class="row" href="' + escapeHtml(legacyUrl("/articles/" + article.slug)) + '"><div class="ph ' + ["b", "", "c", "d"][index % 4] + '"' + (cover ? ' style="background-image:url(&quot;' + escapeHtml(cover) + '&quot;);background-size:cover;background-position:center"' : "") + ">" + (cover ? "" : "<span>" + escapeHtml(article.title.slice(0, 1).toUpperCase()) + "</span>") + '</div><div><h4>' + escapeHtml(article.title) + "</h4><small>" + escapeHtml(formatDate(article.published_at, { month: "short", year: "numeric" })) + "</small></div></a>";
  }).join("");
  const spotlightImage = spotlight ? safeExternalUrl(spotlight.image_url) : null;
  const spotlightCard = spotlight
    ? '<div class="spl"' + (spotlightImage ? ' style="background-image:linear-gradient(135deg,rgba(5,30,24,.5),rgba(5,30,24,.85)),url(&quot;' + escapeHtml(spotlightImage) + '&quot;);background-size:cover;background-position:center"' : "") + '><div><small>School spotlight</small><h3>' + escapeHtml(spotlight.name) + '</h3><p>' + escapeHtml((spotlight.tagline || spotlight.description || "Explore this school profile and the information shared with families.").slice(0, 100)) + '</p><a class="btn btn-gold btn-block" href="/schools/' + encodeURIComponent(spotlight.slug) + '">Learn more about this school</a></div></div>'
    : '<div class="spl"><div><small>School spotlight</small><h3>Discover schools across Uganda</h3><p>Browse verified school profiles and explore your options.</p><a class="btn btn-gold btn-block" href="/schools">Explore schools</a></div></div>';
  return '<section class="tinted"><div class="wrap"><div class="head"><h2>Educational <span>insights</span></h2><a class="more" href="' + escapeHtml(legacyUrl("/articles")) + '">All articles</a></div><div class="ins">' + featuredCard + (rows ? '<div class="card rows">' + rows + "</div>" : "") + spotlightCard + "</div></div></section>";
}

function renderFooter(): void {
  const year = document.querySelector<HTMLElement>("#year");
  if (year) year.textContent = String(new Date().getFullYear());
}

async function renderHome(): Promise<void> {
  if (!app) return;
  let data: HomepageData = {
    featured_schools: [], spotlight_school: null, school_types: [], news_articles: [],
    insight_articles: [], partner_school_news: null, upcoming_events: [], scholarship_count: 0,
    latest_jobs: [], videos: [], ads: [],
  };
  try {
    const result = await getJson<{ data: HomepageData }>("/api/v1/homepage");
    data = result.data;
  } catch (error) {
    console.error("FindURSCH homepage could not load dynamic sections", error);
  }

  const schools = (data.featured_schools || []).slice(0, 9);
  const heroTitle = "Find the right school for your child";
  const heroDescription = "Browse nursery, primary, secondary, vocational and university listings across Uganda, and explore school profiles.";
  const schoolCards = schools.length
    ? schools.map((school) => schoolTile(school)).join("")
    : '<div class="card empty-card"><h3>School listings are being prepared</h3><p>Verified school profiles will appear here as they are published.</p><a class="more" href="/schools">Browse all schools</a></div>';

  const jobs = (data.latest_jobs || []).map((job) => '<li><a href="' + escapeHtml(legacyUrl("/jobs/" + job.slug)) + '">' + escapeHtml(job.title) + '<span>' + escapeHtml([job.employer, job.location].filter(Boolean).join(" · ")) + "</span></a></li>").join("") || '<li class="empty-list-item">No open education vacancies are published yet.</li>';
  const schoolTypes = schoolCategories(data.school_types || []);
  const scholarshipCount = Number(data.scholarship_count || 0).toLocaleString("en-UG");

  app.innerHTML = '<div class="findursch-home-page">' +
    '<section class="hero" data-home-hero role="region" aria-label="FindURSCH homepage highlights">' +
      '<div class="hero-content"><p class="eyebrow eyebrow-light">SCHOOL DISCOVERY, MADE CLEARER</p><h1>' + heroTitle + '</h1><p>' + heroDescription + "</p>" +
      searchPanel("", "", data.school_types || []) +
      '<div class="hero-trust"><span>Explore school profiles</span><span>Compare locations and fee estimates</span><span>Review admissions details</span></div></div>' +
      '<div class="hero-caption"><span class="caption-mark"></span> Discover schools across Uganda</div></section>' +

    '<div class="wrap"><div class="cols sec"><div><div class="head"><h2>Featured <span>schools</span></h2><a class="more" href="/schools?sort=featured">View all schools →</a></div>' +
      '<div class="slider"><button class="arrow l" type="button" data-school-prev aria-label="Previous featured schools">‹</button><div class="cards" data-home-school-track>' + schoolCards + '</div><button class="arrow r" type="button" data-school-next aria-label="Next featured schools">›</button></div>' +
      (data.spotlight_school ? '<div class="spotlight-slot">' + schoolTile(data.spotlight_school, "spotlight") + "</div>" : "") +
      '<div class="strip"><div><h4>Is your school on FindURSCH?</h4><p>Parents across Uganda search here for schools every day.</p></div><a class="btn btn-gold" href="' + escapeHtml(accountUrl("/register?type=school_owner&returnTo=%2Faccount")) + '">List your school</a></div></div>' +
      '<aside>' + adBanner(data.ads || [], "side") +
        '<div class="card box"><h5>Browse by category</h5><ul>' + schoolTypes + '</ul><a class="foot" href="/schools">All school categories</a></div>' +
        '<div class="card box"><h5>Upcoming events</h5><ul>' + eventRows(data.upcoming_events || []) + '</ul><a class="foot" href="' + escapeHtml(legacyUrl("/events")) + '">View all events</a></div>' +
      "</aside></div>" +

      newsSections(data) +

      '<div class="cols sec"><div class="stack">' +
        '<div class="band"><div><small>Advertise with us</small><h3>Reach parents and students every month</h3><p>Promote your school, business or service to families across Uganda.</p></div><div class="acts"><a class="btn btn-gold" href="' + escapeHtml(legacyUrl("/contact?subject=Advertising%20enquiry")) + '">Advertise</a><a class="btn btn-ghost" href="' + escapeHtml(legacyUrl("/contact?subject=Advertising%20enquiry")) + '">Send inquiry</a></div></div>' +
        '<div><div class="head"><h2>Featured <span>school videos</span></h2><a class="more" href="' + escapeHtml(legacyUrl("/news?view=videos")) + '">All videos</a></div>' + videoSection(data.videos || []) + "</div>" +
        '<div class="band edge"><div><h3>Scholarships &amp; bursaries</h3><p>Browse funding opportunities for Ugandan students at every level. <strong>' + scholarshipCount + '</strong> current opportunities.</p><div class="chips"><a href="' + escapeHtml(legacyUrl("/scholarships?category=government")) + '">Government</a><a href="' + escapeHtml(legacyUrl("/scholarships?category=foundations")) + '">Private bursaries</a><a href="' + escapeHtml(legacyUrl("/scholarships?category=university")) + '">University grants</a><a href="' + escapeHtml(legacyUrl("/scholarships?category=international")) + '">International</a></div></div><a class="btn btn-ghost" href="' + escapeHtml(legacyUrl("/scholarships")) + '">Browse</a></div>' +
      '</div><aside><div class="card box jobs"><h5>Latest jobs</h5><ul>' + jobs + '</ul><a class="foot" href="' + escapeHtml(legacyUrl("/jobs")) + '">View all jobs</a></div>' +
      '<div class="promo"><small>Advertise</small><h3>Put your brand in front of parents</h3><p>Reach families across Uganda every month.</p><a class="btn btn-gold" href="' + escapeHtml(legacyUrl("/contact?subject=Advertising%20enquiry")) + '">Advertise with us</a></div>' +
      "</aside></div></div>" +

      insightSection(data) +
      '<div class="banner" aria-label="Sponsored advertising banners">' + adBanner(data.ads || [], "foot1") + adBanner(data.ads || [], "foot2") + "</div>" +
      '<section class="cta"><h2>Is your school listed?</h2><p>Join schools across Uganda reaching parents, students and educators every day.</p><div class="acts"><a class="btn btn-white" href="' + escapeHtml(accountUrl("/register?type=school_owner&returnTo=%2Faccount")) + '">Register your school</a><a class="btn btn-ghost" href="' + escapeHtml(legacyUrl("/about")) + '">View benefits</a></div></section>' +
    "</div>";

  attachSearch();
  attachSchoolCarousel();
  renderFooter();
}

function attachSchoolCarousel(): void {
  const track = document.querySelector<HTMLElement>("[data-home-school-track]");
  const previous = document.querySelector<HTMLButtonElement>("[data-school-prev]");
  const next = document.querySelector<HTMLButtonElement>("[data-school-next]");
  if (!track) return;
  const step = (): number => {
    const first = track.querySelector<HTMLElement>(".findursch-school-tile");
    return first ? first.getBoundingClientRect().width + 24 : track.clientWidth;
  };
  previous?.addEventListener("click", () => track.scrollBy({ left: -step(), behavior: "smooth" }));
  next?.addEventListener("click", () => track.scrollBy({ left: step(), behavior: "smooth" }));
  const update = (): void => {
    if (previous) previous.disabled = track.scrollLeft <= 4;
    if (next) next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
  };
  track.addEventListener("scroll", update, { passive: true });
  window.addEventListener("resize", update);
  update();
}

async function renderDirectory(): Promise<void> {
  if (!app) return;
  const params = new URLSearchParams(window.location.search);
  const search = params.get("q") ?? params.get("search") ?? "";
  const type = params.get("type") ?? "";
  let locations: Locations = { regions: [], districts: [], school_types: [], curricula: [] };
  try {
    const response = await getJson<{ data: Locations }>("/api/v1/locations");
    locations = response.data;
  } catch (error) {
    console.error("Directory filter options are unavailable", error);
  }

  app.innerHTML = '<div class="findursch-home-page"><section class="page-intro"><div class="page-intro-inner"><p class="eyebrow">FIND YOUR SCHOOL</p><h1>Explore schools <em>across Uganda.</em></h1><p>Search published school profiles and compare the information available for each school.</p>' +
    searchPanel(search, type, locations.school_types) + '</div></section><section class="section directory-section"><div class="directory-heading"><div><p class="eyebrow">SCHOOL DIRECTORY</p><h2 id="result-title">Published schools</h2></div><span id="result-count" class="result-count"></span></div><div id="directory-schools" class="school-grid"><div class="loading-card">Searching schools…</div></div><nav class="pagination" id="pagination" aria-label="School directory pages"></nav></section></div>';
  attachSearch();

  const page = Math.max(1, Number(params.get("page") || "1"));
  const query = new URLSearchParams({ page: String(page), limit: "12", sort: params.get("sort") || "newest" });
  if (search) query.set("q", search);
  if (type) query.set("type", type);
  const district = params.get("district");
  const region = params.get("region");
  if (district) query.set("district", district);
  if (region) query.set("region", region);

  try {
    const result = await getJson<ListResponse>("/api/v1/schools?" + query.toString());
    const target = document.querySelector<HTMLElement>("#directory-schools");
    const count = document.querySelector<HTMLElement>("#result-count");
    if (target) {
      target.innerHTML = result.data.length
        ? result.data.map((school) => schoolTile(school)).join("")
        : '<p class="empty-state">No schools matched this search. Try another school name or location.</p>';
    }
    if (count) count.textContent = (result.meta?.total ?? result.data.length) + " schools found";
    const pagination = document.querySelector<HTMLElement>("#pagination");
    const totalPages = result.meta?.total_pages ?? 1;
    if (pagination && totalPages > 1) {
      const link = (targetPage: number, label: string, disabled: boolean): string => {
        const nextParams = new URLSearchParams(window.location.search);
        nextParams.set("page", String(targetPage));
        return '<a class="page-button ' + (disabled ? "is-disabled" : "") + '" href="/schools?' + nextParams.toString() + '"' + (disabled ? ' aria-disabled="true"' : "") + ">" + label + "</a>";
      };
      pagination.innerHTML = link(Math.max(1, page - 1), "← Previous", page <= 1) +
        "<span>Page " + page + " of " + totalPages + "</span>" +
        link(Math.min(totalPages, page + 1), "Next →", page >= totalPages);
    }
  } catch (error) {
    console.error("School directory could not load", error);
    const target = document.querySelector<HTMLElement>("#directory-schools");
    if (target) target.innerHTML = '<p class="empty-state">The school directory is temporarily unavailable. Please try again shortly.</p>';
  }
}

async function renderSchool(slug: string): Promise<void> {
  if (!app) return;
  app.innerHTML = '<section class="section"><div class="loading-card">Loading school profile…</div></section>';
  try {
    const response = await getJson<{ data: SchoolDetail }>("/api/v1/schools/" + encodeURIComponent(slug));
    const school = response.data;
    const place = [school.town, school.district?.name, school.region?.name].filter(Boolean).join(", ");
    const gallery = (school.media || []).filter((item) => item.url);
    const curricula = (school.curricula || []).map((item) => '<span class="detail-chip">' + escapeHtml(item.name) + "</span>").join("");
    const programmes = (school.programs || []).map((item) => '<article class="detail-list-item"><strong>' + escapeHtml(item.name) + "</strong><span>" + escapeHtml([item.level, item.duration].filter(Boolean).join(" · ")) + "</span></article>").join("");
    const facilities = (school.facilities || []).map((item) => '<span class="detail-chip">' + escapeHtml(item.name) + "</span>").join("");
    const heroImage = safeExternalUrl(gallery.find((item) => item.collection_name === "cover")?.url || gallery[0]?.url || school.image_url || null);
    const websiteUrl = safeExternalUrl(school.website);
    const mapsUrl = safeExternalUrl(school.maps_url);
    const feeRange = estimatedFee(school);

    app.innerHTML = '<div class="findursch-home-page"><section class="detail-hero"><div class="detail-hero-inner"><a class="back-link" href="/schools">← Back to schools</a><p class="eyebrow eyebrow-light">' + escapeHtml(school.type?.name || school.school_type || "School") + " · " + escapeHtml(place || "Uganda") + '</p><h1>' + escapeHtml(school.name) + "</h1><p class=\"detail-tagline\">" + escapeHtml(school.tagline || "") + '</p><div class="detail-tags"><span>' + escapeHtml(school.boarding_type || "School") + "</span>" + (school.gender_policy ? "<span>" + escapeHtml(school.gender_policy) + "</span>" : "") + (school.admissions_open ? "<span>Admissions open</span>" : "") + "</div></div></section>" +
      '<section class="section detail-section"><div class="detail-main">' +
      '<div class="detail-block"><p class="eyebrow">ABOUT THE SCHOOL</p><h2>School <em>overview.</em></h2><p class="detail-description">' + escapeHtml(school.description || "More information about this school will be added to its profile.") + "</p></div>" +
      (heroImage ? '<img class="detail-cover" src="' + escapeHtml(heroImage) + '" alt="' + escapeHtml(school.name) + '" loading="lazy" decoding="async">' : "") +
      (curricula ? '<div class="detail-block"><p class="eyebrow">CURRICULUM</p><h2>Learning <em>pathways.</em></h2><div class="detail-chip-list">' + curricula + "</div></div>" : "") +
      (programmes ? '<div class="detail-block"><p class="eyebrow">PROGRAMMES</p><h2>What students <em>can study.</em></h2><div class="detail-list">' + programmes + "</div></div>" : "") +
      (facilities ? '<div class="detail-block"><p class="eyebrow">FACILITIES</p><h2>School <em>facilities.</em></h2><div class="detail-chip-list">' + facilities + "</div></div>" : "") +
      '<div class="detail-block"><p class="eyebrow">SCHOOL FEES</p><h2>Estimated fee <em>range.</em></h2><p class="fee-estimate">' + escapeHtml(feeRange) + '</p><p class="fee-note">Exact fees, where available, are provided directly by the school through its official fees circular.</p></div>' +
      ((school.admission_requirements || school.how_to_apply) ? '<div class="detail-block"><p class="eyebrow">ADMISSIONS</p><h2>Joining the <em>school.</em></h2>' + (school.admission_classes ? "<p><strong>Classes:</strong> " + escapeHtml(school.admission_classes) + "</p>" : "") + (school.admission_requirements ? "<p>" + escapeHtml(school.admission_requirements) + "</p>" : "") + (school.how_to_apply ? "<p>" + escapeHtml(school.how_to_apply) + "</p>" : "") + "</div>" : "") +
      '</div><aside class="detail-aside"><div class="contact-card"><p class="eyebrow">SCHOOL INFORMATION</p><h2>Contact &amp; <em>location.</em></h2><p><span>Location</span><strong>' + escapeHtml(place || school.address || "Uganda") + "</strong></p>" +
      (school.phone ? "<p><span>Phone</span><strong>" + escapeHtml(school.phone) + "</strong></p>" : "") +
      (school.email ? "<p><span>Email</span><strong>" + escapeHtml(school.email) + "</strong></p>" : "") +
      (websiteUrl ? '<p><span>Website</span><a href="' + escapeHtml(websiteUrl) + '" rel="noopener noreferrer" target="_blank">Visit school website ↗</a></p>' : "") +
      (mapsUrl ? '<a class="button button-primary button-full" href="' + escapeHtml(mapsUrl) + '" rel="noopener noreferrer" target="_blank">View on map ↗</a>' : "") +
      "</div></aside></section>" +
      (gallery.length > 1 ? '<section class="section detail-gallery"><p class="eyebrow">SCHOOL PHOTOS</p><h2>Gallery</h2><div class="gallery-grid">' + gallery.map((item) => '<img src="' + escapeHtml(safeExternalUrl(item.url) || "") + '" alt="' + escapeHtml(item.name || school.name) + '" loading="lazy">').join("") + "</div></section>" : "") +
      "</div>";
    document.title = (school.meta_title || school.name) + " | FindURSCH";
  } catch (error) {
    console.error("School profile could not load", error);
    app.innerHTML = '<div class="findursch-home-page"><section class="section"><div class="empty-state"><h2>School profile not found</h2><p>This school may not have a published profile yet.</p><a class="button button-primary" href="/schools">Back to schools</a></div></section></div>';
  }
}

function attachGlobalNavigation(): void {
  const openButton = document.querySelector<HTMLButtonElement>("[data-menu-open]");
  const closeButton = document.querySelector<HTMLButtonElement>("[data-menu-close]");
  const drawer = document.querySelector<HTMLElement>("#mobile-primary-navigation");
  const overlay = document.querySelector<HTMLButtonElement>(".site-mobile-overlay");
  const close = (): void => {
    drawer?.setAttribute("hidden", "");
    overlay?.setAttribute("hidden", "");
    document.body.classList.remove("site-menu-open");
    openButton?.setAttribute("aria-expanded", "false");
    openButton?.focus();
  };
  const open = (): void => {
    drawer?.removeAttribute("hidden");
    overlay?.removeAttribute("hidden");
    document.body.classList.add("site-menu-open");
    openButton?.setAttribute("aria-expanded", "true");
    drawer?.querySelector<HTMLElement>("a[href]")?.focus();
  };
  openButton?.addEventListener("click", open);
  closeButton?.addEventListener("click", close);
  overlay?.addEventListener("click", close);
  drawer?.querySelectorAll<HTMLAnchorElement>("a[href]").forEach((link) => link.addEventListener("click", close));
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && drawer && !drawer.hasAttribute("hidden")) close();
  });
}

function updateRegisterTab(): void {
  const path = window.location.pathname.replace(/\/+$/, "") || "/";
  const tab = document.querySelector<HTMLElement>(".site-register-tab");
  if (tab) tab.hidden = path === "/" || path === "/schools";
}

async function start(): Promise<void> {
  attachGlobalNavigation();
  renderFooter();
  updateRegisterTab();
  if (!app) return;
  const path = window.location.pathname.replace(/\/+$/, "") || "/";
  const schoolDetail = path.match(/^\/schools\/([^/]+)$/);
  if (schoolDetail) {
    await renderSchool(decodeURIComponent(schoolDetail[1]));
  } else if (path === "/schools") {
    await renderDirectory();
  } else if (path === "/") {
    await renderHome();
  } else {
    window.location.replace(legacyUrl(window.location.pathname + window.location.search));
  }
}

void start();
