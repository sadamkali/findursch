<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Find a school in Uganda — FindURSCH</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
/* ---------- TOKENS (shared by every page) ---------- */
:root{
  --brand:#0c5a49; --brand-dk:#083f33; --brand-xdk:#052a22;
  --accent:#f0b030; --accent-dk:#d99a1a;
  --tint:#f1f6f3; --line:#e4e8e6; --ink:#1b2420; --mute:#5d6b65;
  --serif:'Playfair Display',Georgia,serif; --sans:'Inter',system-ui,sans-serif;
  --r:10px; --gap:24px; --wrap:1280px;
}
*{box-sizing:border-box;margin:0;padding:0}
[hidden]{display:none!important}
body{font:15px/1.55 var(--sans);color:var(--ink);background:#fff}
a{color:inherit;text-decoration:none}ul{list-style:none}
.wrap{max-width:var(--wrap);margin:0 auto;padding:0 var(--gap)}
:focus-visible{outline:3px solid var(--accent);outline-offset:2px}

/* Buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:42px;padding:0 20px;border-radius:8px;font:600 14px var(--sans);border:1.5px solid transparent;cursor:pointer;white-space:nowrap}
.btn-gold{background:var(--accent);color:#2a1d00}.btn-gold:hover{background:var(--accent-dk)}
.btn-ghost{border-color:#fff;color:#fff}.btn-ghost:hover{background:rgba(255,255,255,.12)}
.btn-brand{background:var(--brand);color:#fff}.btn-brand:hover{background:var(--brand-dk)}
.btn-block{width:100%}

/* Top bar + header */
.topbar{background:var(--brand-dk);color:#fff;font-size:12.5px}
.topbar .wrap{display:flex;justify-content:flex-end;gap:22px;height:32px;align-items:center}
.topbar a{color:#ffffff}.topbar a:hover{opacity:1}
header{background:var(--brand);color:#fff;position:relative;z-index:40;box-shadow:0 2px 10px rgba(0,0,0,.18)}
header .wrap{display:flex;align-items:center;gap:28px;height:64px}
.logo{font:700 26px var(--serif);letter-spacing:-.3px}.logo b{color:var(--accent)}
.logo-image{display:inline-flex;align-items:center;flex:none;line-height:0}
.logo-image img{display:block;width:168px;height:auto;max-height:42px;object-fit:contain;object-position:left center}
nav{display:flex;gap:4px;margin:0 auto}
nav a{padding:8px 14px;border-radius:8px;font-size:14px;font-weight:500}
nav a:hover,nav a.on{background:rgba(255,255,255,.16)}
.rtab{position:fixed;right:0;top:34%;z-index:50;writing-mode:vertical-rl;background:var(--accent);color:#2a1d00;font:700 12px var(--sans);letter-spacing:.06em;padding:18px 9px;border-radius:10px 0 0 10px;box-shadow:-3px 3px 12px rgba(0,0,0,.2)}

/* Page header + search band */
.phead{background:var(--tint)}
.phead .wrap{display:grid;grid-template-columns:1fr 728px;gap:32px;align-items:center;padding-top:32px;padding-bottom:32px}
.crumbs{font-size:12px;color:var(--mute);margin-bottom:10px}.crumbs a{color:var(--mute)}.crumbs span{margin:0 6px}
.phead h1{font:700 36px/1.15 var(--serif);margin-bottom:8px}
.phead p{font-size:13.5px;color:var(--mute);max-width:430px}.phead p b{color:var(--brand)}
.ad{border-radius:var(--r);display:grid;place-items:center;text-align:center;color:#fff;font:700 22px/1.2 var(--serif);padding:20px;background:linear-gradient(135deg,#a32a2a,#e05a3a)}
.ad small{display:block;font:500 11px var(--sans);color:#fff1ec;margin-top:6px}
.ad.top{height:90px}.ad.side{height:250px}
.sband{background:var(--brand);padding:28px 0}
.sform{display:flex;align-items:center;background:#fff;border-radius:999px;padding:8px 8px 8px 24px;max-width:760px;margin:0 auto;box-shadow:0 10px 30px rgba(0,0,0,.25)}
.sform input{flex:1;border:0;outline:0;font:15px var(--sans);padding:10px 0;min-width:0;background:none}
.sform select{border:0;border-left:1px solid var(--line);padding:10px 14px;font:13.5px var(--sans);background:none;color:var(--ink);max-width:150px}
.sform .go{height:46px;padding:0 28px;border-radius:999px;background:var(--brand-xdk);color:#fff;border:0;font:600 14px var(--sans);cursor:pointer;margin-left:6px}

/* Layout */
.cols{display:grid;grid-template-columns:260px minmax(0,1fr);gap:var(--gap);align-items:start;padding-top:28px}
aside{display:flex;flex-direction:column;gap:var(--gap)}
.card{background:#fff;border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
.box h5{background:var(--brand);color:#fff;font:700 11.5px var(--sans);letter-spacing:.08em;text-transform:uppercase;padding:11px 16px}
.promo2{background:var(--brand);color:#fff;border-radius:14px;padding:24px;position:relative;overflow:hidden}
.promo2::before{content:"";position:absolute;right:-50px;top:-50px;width:170px;height:170px;border-radius:50%;background:rgba(255,255,255,.07)}
.promo2>*{position:relative}
.promo2 small{font:700 11px var(--sans);letter-spacing:.08em;text-transform:uppercase;color:var(--accent)}
.promo2 h3{font:700 26px/1.2 var(--serif);margin:8px 0 10px}.promo2 p{font-size:13px;color:#e8f1ed;margin-bottom:12px}
.promo2 li{font-size:12.5px;display:flex;gap:8px;align-items:center;margin-bottom:6px}
.promo2 li::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--accent)}
.promo2 .btn{margin-top:12px}


/* Inline promo strips + big band */
.promo{display:flex;align-items:center;justify-content:space-between;gap:20px;background:var(--brand-xdk);color:#fff;border-radius:var(--r);padding:20px 28px;max-width:92%;margin:0 auto;width:100%}
.promo small{font:700 10.5px var(--sans);letter-spacing:.08em;text-transform:uppercase;color:var(--accent)}
.promo h3{font:700 21px/1.25 var(--serif);margin:2px 0}.promo p{font-size:12px;color:#e2eee9}
.big{background:var(--brand);color:#fff;border-radius:var(--r);padding:44px 48px;max-width:92%;margin:0 auto;width:100%;display:flex;align-items:center;justify-content:space-between;gap:24px;position:relative;overflow:hidden}
.big::before{content:"";position:absolute;right:-70px;top:-80px;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.07)}
.big>*{position:relative}.big small{font:700 12px var(--sans);letter-spacing:.1em;text-transform:uppercase;color:var(--accent)}
.big h3{font:700 34px/1.15 var(--serif);margin:8px 0 10px}.big p{color:#e8f1ed;max-width:420px}

/* Pagination */
.pg{display:flex;gap:8px;justify-content:center;border-top:1px solid var(--line);margin-top:32px;padding-top:24px}
.pg a{width:36px;height:36px;border:1px solid var(--line);border-radius:8px;display:grid;place-items:center;font:600 13px var(--sans);color:var(--ink)}
.pg a.on{background:var(--brand);border-color:var(--brand);color:#fff}.pg a:hover{border-color:var(--brand)}

/* Footer */
footer{background:var(--brand-xdk);color:#d5dcd8;padding-top:56px;font-size:13.5px;margin-top:64px}
.fgrid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:48px;padding-bottom:48px}
footer h6{color:#fff;font:700 14px var(--sans);margin-bottom:16px}
footer li{margin-bottom:10px;color:#e6eeea}footer li:hover{opacity:1}
footer .logo{display:block;margin-bottom:14px;color:#fff}footer p{color:#d9e5e0;margin-bottom:14px;max-width:380px}
.fbar{border-top:1px solid rgba(255,255,255,.1);padding:24px 0;display:flex;justify-content:space-between;align-items:center;gap:24px;font-size:12.5px}
.fbar .box2{border:1px solid #3d6b5f;border-radius:8px;padding:12px 18px;display:flex;align-items:center;gap:20px}
.fbar .btn{height:36px;border-color:#d9e5e0;color:#fff;font-size:13px}

@media(max-width:1000px){
  .cols,.phead .wrap{grid-template-columns:1fr}
  .phead .wrap{gap:16px;padding-top:20px;padding-bottom:22px}.phead h1{font-size:30px}
  .phead .ad{display:grid;height:70px;font-size:17px}
  .sband{padding:16px 0}
  .sform{padding:6px 6px 6px 18px;max-width:none}.sform select{display:none}
  .sform input{font-size:16px}.sform .go{padding:0 22px;margin-left:6px}
  .cols{padding-top:16px;gap:16px}
  .promo{max-width:100%;padding:14px 16px;gap:12px}.promo p{display:none}.promo h3{font-size:16px}.promo .btn{height:36px;padding:0 14px;font-size:13px}
  .big{max-width:100%;flex-direction:column;align-items:flex-start;padding:28px 24px}.big h3{font-size:26px}
}
/* ---- Mobile shell (shared): hamburger + slide-in drawer, compact top bar, footer grid ---- */
.burger{display:none;margin-left:auto;width:44px;height:44px;border:0;border-radius:8px;background:none;color:#fff;font:400 30px/1 var(--sans);cursor:pointer;place-items:center}
.ov,.drawer{display:none}
body.nolock{overflow:hidden}
@media(max-width:1000px){
  .burger{display:grid}
  header .wrap{gap:12px}
  .topbar{display:block}.topbar .wrap{gap:16px;font-size:12px;overflow:hidden;white-space:nowrap}
  .ov{display:block;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:70;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
  .ov.open{opacity:1;visibility:visible}
  .drawer{display:flex;flex-direction:column;position:fixed;top:0;right:0;bottom:0;width:min(82vw,380px);background:var(--brand-xdk);color:#fff;z-index:80;transform:translateX(100%);visibility:hidden;transition:transform .25s,visibility .25s;box-shadow:-12px 0 32px rgba(0,0,0,.4)}
  .drawer.open{transform:none;visibility:visible}
  .dh{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 20px;border-bottom:1px solid #2a5a4d;flex:none}
  .dh .logo{font-size:24px;color:#fff}
  .dh-actions{display:flex;align-items:center;gap:8px}
  .dh .account-btn{width:40px;height:40px;flex:none}
  .dx{width:40px;height:40px;border:0;background:none;color:#fff;font-size:22px;cursor:pointer;border-radius:8px}.dx:hover{background:#12453a}
  .dl{flex:1;overflow-y:auto;overscroll-behavior:contain}
  .dl a{display:block;padding:16px 20px;border-bottom:1px solid #1d4a3f;font-size:17px;font-weight:500;color:#fff}
  .dl a:hover{background:#0c3d33}.dl a.on{color:var(--accent);font-weight:700}
  .dl hr{border:0;border-top:6px solid var(--brand-dk)}
  .df{flex:none;padding:18px 20px 24px;border-top:1px solid #2a5a4d;display:grid;gap:12px}
  .df a{display:flex;align-items:center;justify-content:center;height:48px;border-radius:8px;font:700 16px var(--sans);border:1.5px solid #fff;color:#fff}
  .df a:hover{background:#12453a}.df a.b2{background:var(--accent);border-color:var(--accent);color:#2a1d00}.df a.b2:hover{background:var(--accent-dk)}
  .fgrid{grid-template-columns:repeat(3,1fr);gap:32px 24px}.fgrid>div:first-child{grid-column:1/-1}
  .fbar{flex-direction:column;align-items:stretch}.fbar .box2{flex-direction:column;align-items:stretch}.fbar .box2 .btn{width:100%}
}
@media(max-width:640px){
  .wrap{padding:0 16px}.topbar a:nth-child(-n+2){display:none}
  .fgrid{grid-template-columns:1fr 1fr}.fgrid>div:last-child{grid-column:1/-1}
  header .logo{font-size:23px}
}
@media(prefers-reduced-motion:reduce){.ov,.drawer{transition:none}}
@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}

/* ===== Search page: Jiji-style layout on FindURSCH tokens ===== */
body{background:var(--tint)}
input{accent-color:var(--brand)}button{font-family:inherit}
.i{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex:none}
/* header extras */
.hic{display:flex;gap:10px}
.hb{position:relative;width:38px;height:38px;border-radius:50%;background:#fff;color:var(--brand);display:grid;place-items:center}
.hb em,.tab em{position:absolute;top:-3px;right:-3px;min-width:17px;height:17px;padding:0 4px;border-radius:9px;background:#d23b2e;color:#fff;font:700 10.5px/17px var(--sans);text-align:center;font-style:normal}
.sband{padding:10px 0;position:sticky;top:0;z-index:45;box-shadow:0 4px 14px rgba(0,0,0,.2)}
.sform .ic{color:var(--mute);margin-right:10px}
.sb{border:0;background:none;color:var(--brand);width:40px;height:40px;display:grid;place-items:center;cursor:pointer}
.sb.on .i{fill:var(--brand)}
.crumb{display:flex;gap:8px;align-items:center;padding:14px 0 12px;font-size:12.5px}
.crumb a,.crumb span{background:#fff;border-radius:99px;padding:3px 12px;color:var(--mute)}.crumb b{color:var(--ink)}
.cols{padding-top:0}
.btn.line{border-color:var(--brand);color:var(--brand);background:#fff}
/* filter sidebar */
.fh{display:flex;justify-content:space-between;align-items:center;width:100%;padding:14px 16px;background:none;border:0;font:700 14px var(--sans);color:var(--ink);cursor:pointer;text-align:left}
.fh span{color:var(--mute);font-weight:400}
.fb{padding:0 16px 14px}.box .fb{padding-top:12px}
.fs.shut .fb{display:none}
.pl{display:block;font-size:14px;margin-bottom:4px}
.cl li{padding:5px 0 5px 12px;font-size:13.5px;cursor:pointer}
.cl li:hover{color:var(--brand)}.cl li.on{font-weight:700;color:var(--brand)}
.cl small{color:var(--mute);font-size:12.5px;font-weight:400;margin-left:4px}.cl small::before{content:"| "}
.cl i{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--c);margin-right:8px}
.cl.short li:nth-child(n+6){display:none}
.more{background:none;border:0;border-bottom:1px dashed var(--brand);color:var(--brand);font:600 13px var(--sans);cursor:pointer;margin:6px 0 0 12px}
.lrow{display:flex;justify-content:space-between;align-items:center;width:100%;padding:12px 16px;border:0;background:none;text-align:left;cursor:pointer;font:700 14px var(--sans);color:var(--ink)}
.lrow small{display:block;font:400 12px var(--sans);color:var(--mute);margin-top:2px}
.chk,.ra{display:flex;align-items:center;gap:10px;padding:6px 0;font-size:13.5px;cursor:pointer}
.chk small,.ra small{color:var(--mute);font-size:12.5px}
#dl{max-height:190px;overflow:auto}
.mm2{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.mm2 input{width:100%;min-width:0;height:44px;border:1px solid #b9c6c0;border-radius:6px;padding:0 10px;font:13.5px var(--sans)}
.rs{display:flex;justify-content:space-between;margin-top:8px}
.rs button{border:0;background:none;font:700 10.5px var(--sans);letter-spacing:.05em;color:var(--mute);cursor:pointer}.rs button+button{color:var(--brand)}
.sh,.sapply,.sov,.mrow{display:none}
/* top of results */
.qchips{display:flex;gap:10px;overflow-x:auto;padding:2px 2px 4px;scrollbar-width:none}.qchips::-webkit-scrollbar{display:none}
.qc{flex:none;display:inline-flex;align-items:center;gap:10px;height:46px;padding:0 16px;border-radius:10px;border:1.5px solid #bcd9cf;background:#e6f2ed;color:var(--ink);font:600 13.5px var(--sans);cursor:pointer}
.qc.gd{border-color:var(--accent);background:#fff6e0}.qc .i{color:var(--brand)}.qc.gd .i{color:var(--accent-dk)}
.qc.on{background:var(--brand);border-color:var(--brand);color:#fff}.qc.on .i{color:var(--accent)}
.pt{font:700 30px/1.2 var(--serif);margin:18px 0 12px;scroll-margin-top:90px}
.tb{display:flex;justify-content:flex-end;gap:10px;margin-bottom:14px}
.dd{position:relative}
.dd>button,.vt{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 14px;border:1px solid var(--line);border-radius:8px;background:#fff;font:600 13.5px var(--sans);color:var(--ink);cursor:pointer}
.dd>button::after{content:"";border:4px solid transparent;border-top-color:currentColor;margin:4px 0 0 2px}
.vt{width:38px;padding:0;justify-content:center}
.dm{display:none;position:absolute;right:0;top:44px;min-width:200px;background:#fff;border:1px solid var(--line);border-radius:10px;box-shadow:0 10px 28px rgba(0,0,0,.16);padding:6px;z-index:30}
.dd.open .dm{display:block}
.dm button{display:flex;justify-content:space-between;width:100%;padding:9px 12px;border:0;background:none;border-radius:6px;font:13.5px var(--sans);text-align:left;cursor:pointer}
.dm button:hover{background:var(--tint)}.dm button.on{font-weight:700;color:var(--brand)}
.act{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}.act:empty{display:none}
.act button{display:inline-flex;gap:6px;align-items:center;height:30px;padding:0 12px;border:1px solid #bcd9cf;border-radius:99px;background:#fff;font:600 12px var(--sans);color:var(--brand);cursor:pointer}
.act .ca{border:0;background:none;color:var(--mute);text-decoration:underline}
/* listing cards */
.res{display:grid;gap:12px}.res.grid{grid-template-columns:repeat(3,1fr);gap:16px}
.lc{display:flex;background:#fff;border:1px solid var(--line);border-radius:var(--r);overflow:hidden;min-height:150px}
.lc.prm{border:2px solid var(--accent);background:#fffaf0}.lc.spt{border:2px solid var(--brand)}
.im{position:relative;display:flex;gap:2px;flex:none;width:260px;background:#fff}
.im i{flex:1;min-width:0;background:linear-gradient(135deg,#9fb8ad,#d9c9a0)}
.im .g1{background:linear-gradient(135deg,#6f8f9a,#b9c8cf)}.im .g2{background:linear-gradient(135deg,#c9a46a,#7e5b34)}.im .g3{background:linear-gradient(135deg,#789a7b,#2f5a43)}
.im.n0{background:var(--tint);display:grid;place-items:center}.im.n0 svg{width:44px}
.tags{position:absolute;left:8px;top:8px;display:flex;flex-direction:column;gap:6px;align-items:flex-start}
.tags span{display:inline-flex;gap:5px;align-items:center;background:#fff;border-radius:99px;padding:3px 10px 3px 7px;font:700 10px var(--sans);letter-spacing:.03em;text-transform:uppercase;box-shadow:0 1px 4px rgba(0,0,0,.15)}
.tags .i{width:12px;height:12px;color:var(--brand)}
.vip{position:absolute;left:0;bottom:0;writing-mode:vertical-rl;transform:rotate(180deg);background:#fff;color:var(--mute);font:600 10px var(--sans);letter-spacing:.05em;padding:6px 3px;text-transform:uppercase}
.bd{flex:1;min-width:0;display:flex;flex-direction:column;gap:7px;padding:14px 16px 12px}
.tp{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
.tp h3.nm{flex:0 1 52%;text-align:right;color:var(--brand);font:700 16px/1.3 var(--sans)}.tp h3.nm a:hover{text-decoration:underline}
.fe{flex:1;min-width:0;font-size:13.5px;color:var(--mute)}.fe b{font-weight:600;color:var(--ink)}
.ds{font-size:13px;color:var(--mute);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.cp{display:flex;flex-wrap:wrap;gap:6px}
.cp span{font-size:11.5px;padding:2px 9px;border-radius:6px;background:var(--tint);border:1px solid var(--line)}
.cp .nw{background:#fdf3d6;border-color:#f0dc9f;color:#8a5f00;font-weight:700}
.ft{display:flex;align-items:center;gap:8px;margin-top:auto;padding-top:4px;font-size:12px;color:var(--mute)}
.loc{display:flex;gap:5px;align-items:center;min-width:0;flex:1;white-space:nowrap;overflow:hidden}.loc .i{width:14px;height:14px;color:var(--brand)}
.sv,.bg{width:28px;height:26px;border-radius:6px;background:#eef1ef;display:grid;place-items:center;flex:none;border:0;color:var(--brand)}
.sv{cursor:pointer}.sv .i{width:16px;height:16px}.sv.on .i{fill:var(--brand)}
.bg .i{width:15px;height:15px;fill:var(--accent);stroke:var(--accent-dk);stroke-width:1.5}.bg.sp .i{fill:#9fd9c5;stroke:var(--brand)}
.grid .lc{flex-direction:column}.grid .im{width:100%;height:170px}
.grid .tp{flex-direction:column;gap:3px}.grid .tp h3.nm{order:-1;text-align:left;flex:none}.grid .ds{display:none}
.span{grid-column:1/-1}
.empty{grid-column:1/-1;background:#fff;border:1px dashed #bcd9cf;border-radius:var(--r);padding:48px 24px;text-align:center}
.empty h3{font:700 22px var(--serif);margin-bottom:6px}.empty p{color:var(--mute);margin-bottom:16px}
.pg{border-top:0;margin-top:24px}.pg a[aria-disabled=true]{opacity:.4;pointer-events:none}
@media(max-width:1100px){.res.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:1000px){
  .rtab,header nav,header .btn,aside .ad,aside .promo2{display:none}
  .hic{display:flex;align-items:center;gap:8px;margin-left:auto}
  .hic .hb[aria-label="Saved schools"]{display:none}
  .hic .hb[aria-label="Account"]{display:grid;width:40px;height:40px}
  .burger{margin-left:0}
  .cols{padding-top:0;grid-template-columns:minmax(0,1fr)}.crumb{padding:12px 0 10px}
  .pt{font-size:22px;margin:12px 0 10px}
  .mrow{display:flex;gap:10px;overflow-x:auto;margin-bottom:12px;scrollbar-width:none}.mrow::-webkit-scrollbar{display:none}
  .mrow button{flex:none;display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 16px;border:1px solid var(--line);border-radius:12px;background:#fff;font:600 14px var(--sans);color:var(--ink)}
  .mrow button::after{content:"";border:4px solid transparent;border-top-color:currentColor;margin-top:4px}
  aside{display:flex;position:fixed;left:0;right:0;bottom:0;max-height:90vh;overflow-y:auto;padding:0 12px;border-radius:16px 16px 0 0;background:var(--tint);z-index:90;transform:translateY(105%);visibility:hidden;transition:transform .25s,visibility .25s;box-shadow:0 -10px 30px rgba(0,0,0,.3)}
  aside>*{flex:none}
  aside.open{transform:none;visibility:visible}
  .sh{display:flex;position:sticky;top:0;z-index:2;justify-content:space-between;align-items:center;padding:16px 4px 10px;background:var(--tint);font:700 20px var(--serif)}
  .sh button{width:36px;height:36px;border:0;border-radius:50%;background:#fff;font-size:16px;cursor:pointer}
  .sapply{display:flex;gap:10px;position:sticky;bottom:0;padding:12px 0;background:var(--tint)}.sapply .btn{flex:1}
  .sov{display:block;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:85;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}.sov.open{opacity:1;visibility:visible}
  .list .im{width:38%;min-width:130px}.list .tp{flex-direction:column;gap:3px}.list .tp h3.nm{order:-1;text-align:left;flex:none}.list .ds,.list .cp{display:none}
}
@media(max-width:640px){
  .res.grid{gap:10px}.grid .im{height:130px}.grid .bd{padding:10px 12px}.tags span:nth-child(n+2){display:none}
  .fe b{font-size:13px}.tp h3.nm{font-size:14.5px}.grid .cp{display:none}
}
@media(prefers-reduced-motion:reduce){aside,.sov{transition:none}}


.im img{display:block;width:100%;height:100%;object-fit:cover}
.im img.logo-mark{width:76px;height:76px;object-fit:contain;margin:auto}
.im.n0{min-height:150px}
.results-empty{grid-column:1/-1;background:#fff;border:1px dashed #bcd9cf;border-radius:var(--r);padding:48px 24px;text-align:center}
.results-empty h3{font:700 22px var(--serif);margin-bottom:6px}
.results-empty p{color:var(--mute);margin-bottom:16px}
.sort-form{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.sort-form select{height:38px;border:1px solid var(--line);border-radius:8px;background:#fff;padding:0 12px;color:var(--ink);font:600 13px var(--sans)}
.result-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:16px 0 12px;color:var(--mute);font-size:13px}
.result-meta b{color:var(--ink)}
.view-links{display:flex;gap:6px}
.view-links a{width:38px;height:38px;display:grid;place-items:center;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--brand)}
.view-links a.on{background:var(--brand);color:#fff;border-color:var(--brand)}
.school-pagination{display:flex;justify-content:center;gap:7px;flex-wrap:wrap;margin-top:24px}
.school-pagination a,.school-pagination span{min-width:36px;height:36px;padding:0 10px;display:grid;place-items:center;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);font:600 13px var(--sans)}
.school-pagination .active{background:var(--brand);border-color:var(--brand);color:#fff}
.school-pagination .disabled{opacity:.4}
.school-card-save{display:inline-flex}
.school-card-save button,.school-card-save a{width:28px;height:26px;border-radius:6px;background:#eef1ef;display:grid;place-items:center;border:0;color:var(--brand);cursor:pointer}
.school-card-save button.on{background:#e6f2ed}
.school-card-save .i{width:16px;height:16px}
.filter-select{width:100%;height:40px;border:1px solid var(--line);border-radius:7px;background:#fff;padding:0 10px;color:var(--ink);font:13px var(--sans)}
@media(max-width:1000px){.sort-form{justify-content:space-between}.sort-form label{flex:1}.sort-form select{width:100%}}


/* Mobile typography and single-column school tiles */
html{-webkit-text-size-adjust:100%;text-size-adjust:100%}
@media(max-width:640px){
  body{font-size:15px;line-height:1.55}
  .wrap{padding-left:16px;padding-right:16px}
  header .wrap{min-height:64px}
  header .logo{font-size:23px}
  header .logo-image img{width:140px;max-height:34px}
  .sband{padding:14px 0}
  .sform{min-height:54px;padding:5px 6px 5px 16px}
  .sform input,.sform input[type="search"]{font-size:16px;line-height:1.35}
  .sform .go{height:44px;padding:0 18px;font-size:14px}
  .crumb{font-size:12.5px;line-height:1.45;flex-wrap:wrap}
  .pt{font-size:23px;line-height:1.2;margin:14px 0 12px}
  .qchips{gap:8px;flex-wrap:wrap;overflow:visible}
  .qc{font-size:12.5px;min-height:40px;height:auto;padding:8px 10px;line-height:1.3}
  .result-meta{font-size:13px;line-height:1.45;gap:8px}
  .sort-form select{font-size:14px;min-height:42px}
  .res.grid{grid-template-columns:minmax(0,1fr);gap:12px}
  .grid .lc{width:100%;min-width:0}
  .grid .im{width:100%;height:170px;min-height:170px}
  .grid .bd{padding:14px 14px 13px;gap:8px}
  .grid .tp{gap:5px}
  .grid .tp h3.nm,.tp h3.nm{font-size:16px;line-height:1.35}
  .grid .fe,.fe{font-size:14px;line-height:1.4}
  .grid .fe b,.fe b{font-size:14px}
  .grid .ds,.ds{font-size:13.5px;line-height:1.45}
  .grid .cp{display:flex;gap:6px}
  .grid .cp span,.cp span{font-size:12px;line-height:1.35;padding:4px 8px}
  .grid .ft,.ft{font-size:13px;line-height:1.4;gap:9px;flex-wrap:wrap}
  .grid .loc,.loc{white-space:normal;overflow-wrap:anywhere}
  .school-pagination a,.school-pagination span{font-size:14px;min-width:38px;height:38px}
  .fh,.lrow{font-size:14px}
  .chk{font-size:14px;line-height:1.4}
  .fb small,.chk small{font-size:12.5px}
  .sapply .btn{min-height:42px;font-size:14px}
}
@media(max-width:380px){
  .pt{font-size:21px}
  .grid .im{height:155px;min-height:155px}
  .grid .bd{padding:12px}
  .grid .tp h3.nm,.tp h3.nm{font-size:15.5px}
  .grid .ft,.ft{font-size:12.5px}
}
</style>
<link rel="stylesheet" href="{{ asset('css/site-footer.css') }}?v=1">
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
<symbol id="i-bm" viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4.5L6 21z"/></symbol>
<symbol id="i-chat" viewBox="0 0 24 24"><path d="M4 4h16v12H9l-5 4zM8 9h8M8 12h5"/></symbol>
<symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol>
<symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></symbol>
<symbol id="i-check" viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></symbol>
<symbol id="i-grid" viewBox="0 0 24 24"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></symbol>
<symbol id="i-list" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
<symbol id="i-chev" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></symbol>
<symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></symbol>
<symbol id="i-cap" viewBox="0 0 24 24"><path d="M12 3 1 9l11 6 9-4.9V17h2V9L12 3zM5 13.2v4L12 21l7-3.8v-4L12 17l-7-3.8z" fill="#b9c8c1" stroke="none"/></symbol>
</defs></svg>

@php
  $directoryIdentity = session('dashboard_identity');
  $directoryIdentityRole = is_array($directoryIdentity) ? ($directoryIdentity['role'] ?? null) : null;
  if (auth()->check()) {
    if (auth()->user()->hasRole('admin')) {
      $directoryAccountUrl = route('auth.dashboard-login', ['returnTo' => '/admin']);
    } elseif (auth()->user()->hasRole('school_owner')) {
      $directoryAccountUrl = route('auth.dashboard-login', ['returnTo' => '/owner']);
    } else {
      $directoryAccountUrl = route('user.home');
    }
  } elseif ($directoryIdentityRole === 'admin') {
    $directoryAccountUrl = route('auth.dashboard-login', ['returnTo' => '/admin']);
  } elseif ($directoryIdentityRole === 'school_owner') {
    $directoryAccountUrl = route('auth.dashboard-login', ['returnTo' => '/owner']);
  } else {
    $directoryAccountUrl = route('login');
  }
@endphp

<div class="topbar"><div class="wrap">
  <a href="{{ route('scholarships.index') }}">Scholarships</a>
  <a href="{{ route('events.index') }}">Events</a>
  <a href="{{ route('articles.index') }}">Articles</a>
  <a href="{{ route('contact') }}">Contact</a>
</div></div>

<header><div class="wrap">
  <a class="logo logo-image" href="{{ route('home') }}"><img src="{{ $headerLogoUrl }}" alt="FindURSCH" width="168" height="37" fetchpriority="high"></a>
  <nav aria-label="Main navigation">
    <a href="{{ route('home') }}">Home</a>
    <a class="on" href="{{ route('schools.index') }}">Schools</a>
    <a href="{{ route('compare') }}">Compare</a>
    <a href="{{ route('scholarships.index') }}">Scholarships</a>
    <a href="{{ route('news.index') }}">News</a>
    <a href="{{ route('jobs.index') }}">Jobs</a>
    <a href="{{ route('events.index') }}">Events</a>
  </nav>
  <div class="hic">
    <a class="hb" href="{{ auth()->check() ? route('user.saved-schools') : route('login') }}" aria-label="Saved schools"><svg class="i"><use href="#i-bm"/></svg>@if(auth()->check() && $savedSchoolCount > 0)<em>{{ $savedSchoolCount }}</em>@endif</a>
    <a class="hb" href="{{ $directoryAccountUrl }}" aria-label="Account"><svg class="i"><use href="#i-user"/></svg></a>
  </div>
  <button class="burger" id="burger" type="button" aria-label="Menu" aria-expanded="false">☰</button>
</div></header>

<a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

<section class="sband"><div class="wrap">
  <form class="sform" method="GET" action="{{ route('schools.index') }}" role="search">
    @foreach(request()->except(['q', 'page']) as $key => $value)
      @if(is_array($value))
        @foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
      @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach
    <svg class="i ic"><use href="#i-search"/></svg>
    <input class="q" type="search" name="q" value="{{ $search }}" placeholder="Search school name, town or district" aria-label="Search schools">
    <button class="go" type="submit">Search</button>
  </form>
</div></section>

<main class="wrap">
  <div class="crumb"><a href="{{ route('home') }}">Home</a><span>{{ number_format($schools->total()) }} verified schools in Uganda</span></div>
  <div class="cols">
    <aside id="filters" aria-label="Filters">
      <div class="sh"><span>Filters</span><button type="button" id="sx" aria-label="Close filters">✕</button></div>
      <form id="filterForm" method="GET" action="{{ route('schools.index') }}">
        <input type="hidden" name="q" value="{{ $search }}">
        <input type="hidden" name="region" value="{{ $regionId }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="view" value="{{ $viewMode }}">
        <input type="hidden" name="type" value="{{ $typeId }}">

        <div class="card box"><h5>Categories</h5><div class="fb"><b class="pl">Schools</b><ul class="cl short" id="cl">
          <li class="{{ !$typeId ? 'on' : '' }}"><a href="{{ route('schools.index', request()->except(['type','page'])) }}">All schools <small>{{ number_format($allSchoolCount) }}</small></a></li>
          @foreach($schoolTypes as $schoolType)
            <li class="{{ (string)$typeId === (string)$schoolType->id ? 'on' : '' }}"><a href="{{ route('schools.index', array_merge(request()->except(['type','page']), ['type' => $schoolType->id])) }}"><i style="--c:{{ ['#2f6fd6','#2f9e5a','#e08a1e','#8a5bd6','#d6479a','#6b7f78','#c9a46a'][$loop->index % 7] }}"></i>{{ $schoolType->name }}<small>{{ number_format($schoolType->public_listing_count) }}</small></a></li>
          @endforeach
        </ul><button class="more" id="more" type="button">Show all categories</button></div></div>

        <section class="card fs" id="fs-loc"><button class="lrow" id="lbtn" type="button" aria-expanded="true"><span>Location<small id="ltx">{{ count($selectedDistrictIds) ? count($selectedDistrictIds).' selected' : 'All Uganda' }}</small></span><svg class="i"><use href="#i-chev"/></svg></button><div class="fb" id="dl">
          @foreach($districts as $districtOption)
            <label class="chk"><input type="checkbox" name="district[]" value="{{ $districtOption->id }}" @checked(in_array((int)$districtOption->id, $selectedDistrictIds, true))>{{ $districtOption->name }} <small>• {{ number_format($districtOption->public_listing_count) }}</small></label>
          @endforeach
        </div></section>

        <section class="card fs" id="fs-fee"><button class="fh" type="button">Fee per term, UGX<span>—</span></button><div class="fb">
          <div class="mm2"><input id="lo" name="fee_min" type="number" min="0" step="10000" value="{{ $feeMin !== null ? (int)$feeMin : '' }}" placeholder="min" aria-label="Minimum fee"><span>–</span><input id="hi" name="fee_max" type="number" min="0" step="10000" value="{{ $feeMax !== null ? (int)$feeMax : '' }}" placeholder="max" aria-label="Maximum fee"></div>
          <div class="rs"><button id="fclr" type="button">CLEAR</button><button type="submit">APPLY</button></div>
          <small>Only active tuition fees published in UGX per term are included.</small>
        </div></section>

        <section class="card fs" id="fs-cu"><button class="fh" type="button">Curriculum<span>—</span></button><div class="fb" id="cu">
          @foreach($curricula as $curriculumOption)
            <label class="chk"><input type="checkbox" name="curriculum[]" value="{{ $curriculumOption->id }}" @checked(in_array((int)$curriculumOption->id, $selectedCurriculumIds, true))>{{ $curriculumOption->name }} <small>• {{ number_format($curriculumOption->public_listing_count) }}</small></label>
          @endforeach
        </div></section>

        <section class="card fs"><button class="fh" type="button">Boarding type<span>—</span></button><div class="fb" id="md">
          @foreach($boardingOptions as $boardingOption)
            <label class="chk"><input type="checkbox" name="boarding[]" value="{{ $boardingOption }}" @checked(in_array($boardingOption, $selectedBoardingTypes, true))>{{ ucwords(str_replace(['_', '-'], ' ', $boardingOption)) }}</label>
          @endforeach
        </div></section>

        <section class="card fs"><button class="fh" type="button">Admissions<span>—</span></button><div class="fb"><label class="chk"><input type="checkbox" name="admissions_open" value="1" @checked($admissionsOpen)>Admissions currently open</label></div></section>

        <div class="ad side">Advertise with FindURSCH<small>Reach parents and students</small></div>
        <div class="promo2"><small>Advertise</small><h3>Put your school in front of parents and students</h3><p>Promote your institution to families searching across Uganda.</p>
          <ul><li>School profile promotion</li><li>Featured school placements</li><li>Reach parents across Uganda</li></ul><a class="btn btn-gold" href="{{ route('contact') }}">Advertise with us</a></div>
        <div class="sapply"><a class="btn line" href="{{ route('schools.index') }}">Clear all</a><button class="btn btn-brand" type="submit">Show schools</button></div>
      </form>
    </aside>

    <section id="top">
      <div class="qchips"><span class="qc on" aria-label="Publicly visible schools"><svg class="i"><use href="#i-check"/></svg>Verified public listings</span><a class="qc gd" href="{{ route('contact') }}">Ask about school fees</a></div>
      <h1 class="pt">Schools in Uganda</h1>
      <div class="mrow"><button type="button" data-o="">All filters</button><button type="button" data-o="fs-loc">Location</button><button type="button" data-o="fs-fee">Fee, UGX</button><button type="button" data-o="fs-cu">Curriculum</button></div>
      <div class="result-meta"><span>Showing <b>{{ number_format($schools->firstItem() ?? 0) }}–{{ number_format($schools->lastItem() ?? 0) }}</b> of <b>{{ number_format($schools->total()) }}</b> schools</span>
        <div class="view-links"><a class="{{ $viewMode === 'grid' ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['view'=>'grid','page'=>null]) }}" aria-label="Grid view"><svg class="i"><use href="#i-grid"/></svg></a><a class="{{ $viewMode === 'list' ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['view'=>'list','page'=>null]) }}" aria-label="List view"><svg class="i"><use href="#i-list"/></svg></a></div>
      </div>
      <form class="sort-form" id="sortForm" method="GET" action="{{ route('schools.index') }}">
        @foreach(request()->except(['region','sort','page']) as $key => $value)
          @if(is_array($value))@foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
          @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <label><select name="region" aria-label="Filter by region" onchange="this.form.submit()"><option value="">All regions</option>@foreach($regions as $regionOption)<option value="{{ $regionOption->id }}" @selected((string)$regionId === (string)$regionOption->id)>{{ $regionOption->name }}</option>@endforeach</select></label>
        <label><select name="sort" aria-label="Sort schools" onchange="this.form.submit()"><option value="newest" @selected($sort==='newest')>Newest first</option><option value="featured" @selected($sort==='featured')>Featured first</option><option value="name" @selected($sort==='name')>Name A–Z</option><option value="fee_low" @selected($sort==='fee_low')>Fees: low to high</option><option value="fee_high" @selected($sort==='fee_high')>Fees: high to low</option><option value="rating" @selected($sort==='rating')>Highest rated</option></select></label>
      </form>

      <div class="act">@if($search !== '')<span class="qc">Search: “{{ $search }}”</span>@endif @if($typeId)<span class="qc">{{ optional($schoolTypes->firstWhere('id', $typeId))->name }}</span>@endif @if(count($selectedDistrictIds))<span class="qc">{{ count($selectedDistrictIds) }} district filter(s)</span>@endif @if($feeMin !== null || $feeMax !== null)<span class="qc">Tuition fee range applied</span>@endif @if($admissionsOpen)<span class="qc">Admissions open</span>@endif @if(request()->hasAny(['q','type','district','region','fee_min','fee_max','curriculum','boarding','admissions_open']))<a class="ca" href="{{ route('schools.index') }}">Clear all filters</a>@endif</div>

      <div class="res {{ $viewMode }}" id="res" aria-live="polite">
        @forelse($schools as $school)
          @php
            $coverUrl = $school->getFirstMediaUrl('cover');
            $logoUrl = $school->getFirstMediaUrl('logo');
            $locationText = collect([$school->town ?: $school->parish?->name, $school->district?->name])->filter()->unique()->implode(', ');
            $isFeatured = (bool) $school->is_featured && (!$school->featured_until || $school->featured_until->isFuture());
          @endphp
          <article class="lc{{ $isFeatured ? ' prm' : '' }}">
            <a class="im{{ $coverUrl ? '' : ' n0' }}" href="{{ route('schools.show', $school) }}" aria-label="View {{ $school->name }}">
              @if($coverUrl)<img src="{{ $coverUrl }}" alt="{{ $school->name }}" loading="lazy">@elseif($logoUrl)<img class="logo-mark" src="{{ $logoUrl }}" alt="{{ $school->name }} logo" loading="lazy">@else<svg class="i" style="width:48px;height:48px;stroke:none"><use href="#i-cap"/></svg>@endif
              @if($isFeatured)<div class="tags"><span>Featured</span></div>@endif
            </a>
            <div class="bd">
              <div class="tp"><div class="fe">
                @if($school->lowest_term_tuition !== null)<b>UGX {{ number_format((float)$school->lowest_term_tuition) }}</b> per term @elseif($school->fees_from !== null && (float)$school->fees_from > 0)<b>From UGX {{ number_format((float)$school->fees_from) }}</b>@if($school->fees_to !== null && (float)$school->fees_to > (float)$school->fees_from)<span> – UGX {{ number_format((float)$school->fees_to) }}</span>@endif @else<b>Fees not published</b>@endif
              </div><h3 class="nm"><a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a></h3></div>
              <p class="ds">{{ $school->tagline ?: \Illuminate\Support\Str::limit(strip_tags((string)$school->description), 160, '…') ?: ($school->type?->name.' in '.$locationText) }}</p>
              <div class="cp"><span>{{ $school->type?->name ?? 'School' }}</span>@foreach($school->curricula as $schoolCurriculum)<span>{{ $schoolCurriculum->abbreviation ?: $schoolCurriculum->name }}</span>@endforeach @if($school->boarding_type)<span>{{ ucwords(str_replace(['_', '-'], ' ', $school->boarding_type)) }}</span>@endif @if($school->admissions_open)<span class="nw">Admissions open</span>@endif</div>
              <div class="ft"><span class="loc"><svg class="i"><use href="#i-pin"/></svg>{{ $locationText ?: 'Uganda' }}</span>
                @if(auth()->check())
                  <form class="school-card-save" method="POST" action="{{ route('school.save.toggle', $school) }}">@csrf<button class="sv {{ in_array((int)$school->id, $savedSchoolIds, true) ? 'on' : '' }}" type="submit" aria-label="Save {{ $school->name }}" aria-pressed="{{ in_array((int)$school->id, $savedSchoolIds, true) ? 'true' : 'false' }}"><svg class="i"><use href="#i-bm"/></svg></button></form>
                @else
                  <span class="school-card-save"><a href="{{ route('login') }}" aria-label="Sign in to save {{ $school->name }}"><svg class="i"><use href="#i-bm"/></svg></a></span>
                @endif
                @if((float)$school->rating_average > 0)<span class="bg" title="Average rating">★ {{ number_format((float)$school->rating_average, 1) }}</span>@endif
              </div>
            </div>
          </article>
        @empty
          <div class="results-empty"><h3>No schools match these filters</h3><p>Try another school name, district, curriculum or fee range.</p><a class="btn btn-brand" href="{{ route('schools.index') }}">Clear all filters</a></div>
        @endforelse
      </div>

      @if($schools->hasPages())
        <nav class="school-pagination" aria-label="School result pages">
          @if($schools->onFirstPage())<span class="disabled" aria-disabled="true">‹</span>@else<a href="{{ $schools->previousPageUrl() }}" rel="prev" aria-label="Previous page">‹</a>@endif
          @foreach($schools->getUrlRange(max(1,$schools->currentPage()-2), min($schools->lastPage(),$schools->currentPage()+2)) as $page => $url)
            @if($page === $schools->currentPage())<span class="active" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
          @endforeach
          @if($schools->hasMorePages())<a href="{{ $schools->nextPageUrl() }}" rel="next" aria-label="Next page">›</a>@else<span class="disabled" aria-disabled="true">›</span>@endif
        </nav>
      @endif
    </section>
  </div>
</main>

<x-site-footer />

<div class="sov" id="sov"></div>
<script>
(function(){
  const burger=document.getElementById('burger');
  const side=document.getElementById('filters');
  const overlay=document.getElementById('sov');
  const close=document.getElementById('sx');
  const more=document.getElementById('more');
  const categories=document.getElementById('cl');
  const locationToggle=document.getElementById('lbtn');
  const locationList=document.getElementById('dl');
  if(burger){
    const nav=document.querySelector('header nav');
    const extras=document.querySelectorAll('.topbar a');
    const drawer=document.createElement('div'); drawer.className='drawer'; drawer.setAttribute('role','dialog'); drawer.setAttribute('aria-modal','true'); drawer.setAttribute('aria-label','Menu');
    const shade=document.createElement('div'); shade.className='ov'; document.body.appendChild(shade);
    const links=nav?Array.from(nav.querySelectorAll('a')).map(a=>'<a href="'+a.href+'">'+a.textContent+'</a>').join(''):'';
    const other=Array.from(extras).map(a=>'<a href="'+a.href+'">'+a.textContent+'</a>').join('');
    drawer.innerHTML='<div class="dh"><a class="logo logo-image" href="{{ route('home') }}"><img src="{{ $mobileLogoUrl }}" alt="FindURSCH" width="156" height="34"></a><div class="dh-actions"><a class="hb account-btn" href="{{ $directoryAccountUrl }}" aria-label="Account"><svg class="i"><use href="#i-user"/></svg></a><button class="dx" type="button" aria-label="Close menu">✕</button></div></div><div class="dl">'+links+'<hr>'+other+'</div>';
    document.body.appendChild(drawer);
    const setOpen=open=>{drawer.classList.toggle('open',open);shade.classList.toggle('open',open);document.body.classList.toggle('nolock',open);burger.setAttribute('aria-expanded',String(open));(open?drawer.querySelector('.dx'):burger).focus()};
    burger.addEventListener('click',()=>setOpen(true)); shade.addEventListener('click',()=>setOpen(false)); drawer.querySelector('.dx').addEventListener('click',()=>setOpen(false)); drawer.addEventListener('click',e=>{if(e.target.closest('a'))setOpen(false)}); document.addEventListener('keydown',e=>{if(e.key==='Escape')setOpen(false)});
  }
  const openFilters=id=>{if(!side||!overlay)return;side.classList.add('open');overlay.classList.add('open');document.body.classList.add('nolock');if(id){const el=document.getElementById(id);if(el){el.classList.remove('shut');el.scrollIntoView({block:'start'})}}};
  const closeFilters=()=>{if(!side||!overlay)return;side.classList.remove('open');overlay.classList.remove('open');document.body.classList.remove('nolock')};
  if(close)close.addEventListener('click',closeFilters); if(overlay)overlay.addEventListener('click',closeFilters);
  if(more&&categories)more.addEventListener('click',()=>{const short=categories.classList.toggle('short');more.textContent=short?'Show all categories':'Show fewer categories'});
  if(locationToggle&&locationList)locationToggle.addEventListener('click',()=>{const hidden=locationList.hidden;locationList.hidden=!hidden;locationToggle.setAttribute('aria-expanded',String(hidden))});
  document.querySelectorAll('.mrow button').forEach(button=>button.addEventListener('click',()=>openFilters(button.dataset.o)));
  document.querySelectorAll('.fh').forEach(button=>button.addEventListener('click',()=>{const panel=button.parentElement;const hidden=panel.classList.toggle('shut');const label=button.querySelector('span');if(label)label.textContent=hidden?'+':'—'}));
  document.getElementById('fclr')?.addEventListener('click',()=>{document.getElementById('lo').value='';document.getElementById('hi').value='';document.getElementById('filterForm').requestSubmit()});
  document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(input=>input.addEventListener('change',()=>document.getElementById('filterForm').requestSubmit()));
  document.addEventListener('keydown',e=>{if(e.key==='Escape')closeFilters()});
})();
</script>
</body>
</html>