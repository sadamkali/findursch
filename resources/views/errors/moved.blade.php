<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>This page has moved — FindURSCH</title>
<meta name="robots" content="noindex">
<meta name="description" content="This page has a new address. We will take you there, or you can search for what you need.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,700&display=swap" rel="stylesheet">
<style>
/* ---------- TOKENS (shared by every page) ---------- */
:root{
  --brand:#0c5a49; --brand-dk:#083f33; --brand-xdk:#052a22;
  --accent:#f0b030; --accent-dk:#d99a1a; --wa:#1fa855;
  --tint:#f1f6f3; --line:#e4e8e6; --ink:#1b2420; --mute:#5d6b65;
  --on-dark:#e2eee9; --danger:#b3261e; --danger-bg:#fbe9e7;
  --serif:'Playfair Display',Georgia,serif; --sans:'Inter',system-ui,sans-serif;
  --r:10px; --gap:24px; --wrap:1280px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font:15px/1.55 var(--sans);color:var(--ink);background:#fff}
a{color:inherit;text-decoration:none}ul{list-style:none}
.wrap{max-width:var(--wrap);margin:0 auto;padding:0 var(--gap)}
:focus-visible{outline:3px solid var(--accent);outline-offset:2px}

/* Buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:42px;padding:0 20px;border-radius:8px;font:600 14px var(--sans);border:1.5px solid transparent;cursor:pointer;white-space:nowrap}
.btn-ghost{border-color:#fff;color:#fff}.btn-ghost:hover{background:rgba(255,255,255,.16)}

/* Top bar + header */
.topbar{background:var(--brand-dk);color:#fff;font-size:12.5px}
.topbar .wrap{display:flex;justify-content:flex-end;gap:22px;height:32px;align-items:center}
.topbar a:hover{text-decoration:underline}
header{background:var(--brand);color:#fff;position:sticky;top:0;z-index:40;box-shadow:0 2px 10px rgba(0,0,0,.18)}
header .wrap{display:flex;align-items:center;gap:28px;height:64px}
.logo{font:700 26px var(--serif);letter-spacing:-.3px}.logo b{color:var(--accent)}
nav{display:flex;gap:4px;margin:0 auto}
nav a{padding:8px 14px;border-radius:8px;font-size:14px;font-weight:500}
nav a:hover{background:rgba(255,255,255,.18)}
.rtab{position:fixed;right:0;top:34%;z-index:50;writing-mode:vertical-rl;background:var(--accent);color:#2a1d00;font:700 12px var(--sans);letter-spacing:.06em;padding:18px 9px;border-radius:10px 0 0 10px;box-shadow:-3px 3px 12px rgba(0,0,0,.2)}

/* Footer */
footer{background:var(--brand-xdk);color:#fff;padding-top:56px;font-size:13.5px}
.fgrid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:48px;padding-bottom:48px}
footer h6{color:#fff;font:700 14px var(--sans);margin-bottom:16px}
footer li{margin-bottom:10px;color:var(--on-dark)}footer li:hover{color:#fff;text-decoration:underline}
footer .logo{display:block;margin-bottom:14px}footer p{color:var(--on-dark);margin-bottom:14px;max-width:380px}
.fbar{border-top:1px solid #2a5a4d;padding:24px 0;display:flex;justify-content:space-between;align-items:center;gap:24px;font-size:12.5px;color:var(--on-dark)}
.fbar .box2{border:1px solid #3d6b5f;border-radius:8px;padding:12px 18px;display:flex;align-items:center;gap:20px}
.fbar .btn{height:36px;font-size:13px}

@media(max-width:1000px){
  .fgrid{grid-template-columns:1fr}nav,.topbar{display:none}header .btn-ghost{display:none}
  .fbar{flex-direction:column;align-items:flex-start}.fbar .box2{flex-direction:column;align-items:flex-start}
}

/* ---- Mobile shell (shared): hamburger + slide-in drawer, compact top bar, footer grid ---- */
.burger{display:none;margin-left:auto;width:44px;height:44px;border:1.5px solid #fff;border-radius:8px;background:none;color:#fff;font:700 20px var(--sans);cursor:pointer;place-items:center}
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
  .dh{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #2a5a4d;flex:none}
  .dh .logo{font-size:24px;color:#fff}
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


:root{--ok-bg:#e3f4e8;--ok-ink:#17703a;--warn-bg:#fdf3d6;--warn-line:#f0b030;--warn-ink:#6b4a00;--bad:#b3261e;--bad-bg:#fbe9e7}
[hidden]{display:none!important}
.btn-gold{background:var(--accent);color:#2a1d00}.btn-gold:hover{background:var(--accent-dk)}
.btn-brand{background:var(--brand);color:#fff}.btn-brand:hover{background:var(--brand-dk)}
.btn-line{border-color:var(--brand);color:var(--brand);background:#fff}.btn-line:hover{background:var(--tint)}
.btn-white{background:#fff;color:var(--brand)}.btn.sm{height:36px;padding:0 14px;font-size:13px}.btn-block{width:100%}
.btn:disabled{background:#e4e8e6;color:#6d7b74;border-color:#e4e8e6;cursor:not-allowed}
.card{background:#fff;border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
.ph{background:linear-gradient(135deg,#9fb8ad,#d9c9a0)}.ph.b{background:linear-gradient(135deg,#6f8f9a,#b9c8cf)}.ph.c{background:linear-gradient(135deg,#c9a46a,#7e5b34)}.ph.d{background:linear-gradient(135deg,#789a7b,#2f5a43)}
.crumbs{font-size:12.5px;color:var(--mute);padding:16px 0 12px}.crumbs a:hover{color:var(--brand);text-decoration:underline}.crumbs b{color:var(--ink);font-weight:600}
.ptint{background:var(--tint);border-bottom:1px solid var(--line);padding-bottom:28px}.pdark{background:#000;color:#fff;text-align:center;padding:44px 24px 40px}.pdark h1{font:700 40px/1.2 var(--serif)}.pdark h1 em{color:var(--accent)}.pdark p{max-width:620px;margin:12px auto 0;color:var(--on-dark)}
h1.t{font:700 38px/1.15 var(--serif)}.lede{max-width:760px;color:var(--mute);margin-top:10px}
.sec{padding-top:48px}.head{display:flex;justify-content:space-between;align-items:baseline;gap:12px;margin-bottom:16px}.head h2{font:700 24px var(--serif)}.more{font:600 13px var(--sans);color:var(--brand)}
.cols{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:var(--gap);align-items:start}.stackc{display:flex;flex-direction:column;gap:var(--gap)}
.box h5{background:var(--brand);color:#fff;font:700 11.5px var(--sans);letter-spacing:.08em;text-transform:uppercase;padding:11px 16px}
.box li a{display:flex;justify-content:space-between;gap:10px;padding:11px 16px;border-bottom:1px solid var(--line);font-size:13.5px;font-weight:500}.box li a:hover{background:var(--tint);color:var(--brand)}.box li em{font-style:normal;color:var(--mute);font-size:12px}
.ad{border-radius:var(--r);display:grid;place-items:center;text-align:center;color:#fff;font:700 22px/1.2 var(--serif);padding:20px;background:linear-gradient(135deg,#a32a2a,#e05a3a);height:250px}.ad small{display:block;font:500 11px var(--sans);color:#fff1ec;margin-top:6px}.ad.slim{height:90px;max-width:728px;margin:24px auto 0}
.pill{display:inline-block;font:700 10.5px var(--sans);letter-spacing:.04em;text-transform:uppercase;padding:2px 9px;border-radius:99px;background:var(--ok-bg);color:var(--ok-ink)}.pill.w{background:var(--warn-bg);color:var(--warn-ink)}.pill.g{background:#eef1ef;color:#44514b}.pill.b{background:var(--tint);color:var(--brand);border:1px solid #bcd9cf}.pill.s{background:var(--bad-bg);color:var(--bad)}
.stars{color:var(--accent-dk);letter-spacing:1px}.kick{font:700 10.5px var(--sans);letter-spacing:.06em;text-transform:uppercase;color:var(--brand)}
.in{height:44px;border:1px solid #cdd6d2;border-radius:8px;padding:0 12px;font:14px var(--sans);background:#fff;color:var(--ink)}
.fld{display:block;font:600 13px var(--sans);margin-bottom:16px}.fld input,.fld select,.fld textarea{display:block;width:100%;margin-top:6px;height:46px;padding:0 14px;border:1px solid #cdd6d2;border-radius:8px;font:15px var(--sans);background:#fff;color:var(--ink)}.fld textarea{height:auto;padding:12px 14px;resize:vertical}.fld input:focus,.fld select:focus,.fld textarea:focus{outline:2px solid var(--brand);border-color:var(--brand)}
.err{color:var(--bad);font:600 12.5px var(--sans);margin:-6px 0 12px}
.pg{display:flex;gap:6px;justify-content:center;margin-top:28px}.pg button{min-width:36px;height:36px;padding:0 12px;border:1px solid var(--line);border-radius:6px;background:#fff;font:600 13px var(--sans);cursor:pointer;color:var(--ink)}.pg button.on{background:var(--brand);border-color:var(--brand);color:#fff}.pg button:disabled{color:#9aa6a0;cursor:default}
.chips{display:flex;gap:8px;flex-wrap:wrap}.chips button,.chips a{height:34px;padding:0 14px;border-radius:99px;border:1px solid var(--line);background:#fff;font:500 13px var(--sans);cursor:pointer;color:var(--ink);display:inline-flex;align-items:center;gap:6px}.chips button:hover,.chips a:hover{border-color:var(--brand)}.chips .on{background:var(--brand);border-color:var(--brand);color:#fff}.chips em{font-style:normal;font-size:11.5px;opacity:1;color:inherit;font-weight:700}
.sg{display:grid;grid-template-columns:repeat(2,1fr);gap:var(--gap)}.sc{display:flex;flex-direction:column}.sc .ph{height:130px;position:relative}.sc .ph .pill{position:absolute;left:10px;top:10px}.sc .b{padding:14px 16px;flex:1}.sc h3{font:600 15px/1.3 var(--sans);margin:2px 0 4px}.sc .m{font-size:12.5px;color:var(--mute)}.sc .f{display:flex;gap:8px;align-items:center;padding:12px 16px;border-top:1px solid var(--line)}.sc .f label{margin-left:auto;display:flex;gap:6px;font-size:12.5px;align-items:center;cursor:pointer}.sc .f input{accent-color:var(--brand)}
.tray{position:fixed;left:50%;bottom:20px;transform:translateX(-50%);z-index:60;background:var(--brand-xdk);color:#fff;border-radius:12px;padding:10px 12px 10px 20px;display:flex;gap:16px;align-items:center;box-shadow:0 10px 30px rgba(0,0,0,.35);max-width:calc(100% - 24px)}.tray button{border:0;background:none;color:var(--on-dark);cursor:pointer;font:600 13px var(--sans);text-decoration:underline}
.toast{position:fixed;right:20px;bottom:20px;background:var(--ink);color:#fff;padding:12px 18px;border-radius:10px;font:600 13.5px var(--sans);z-index:100;box-shadow:0 8px 24px rgba(0,0,0,.3)}
.note{display:flex;gap:12px;align-items:flex-start;background:var(--warn-bg);border:1px solid var(--warn-line);border-left:5px solid var(--warn-line);border-radius:var(--r);padding:14px 18px;font-size:13.5px;color:#4a3a14}.note b{color:var(--ink)}
.band{background:var(--brand);color:#fff;border-radius:var(--r);padding:28px 32px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}.band h3{font:700 24px/1.25 var(--serif)}.band p{color:var(--on-dark);font-size:14px;margin-top:4px}
details.faq{border:1px solid var(--line);border-radius:var(--r);background:#fff;margin-bottom:10px}details.faq summary{cursor:pointer;padding:14px 18px;font:600 15px var(--sans);list-style:none;display:flex;justify-content:space-between;gap:12px}details.faq summary::after{content:"+";font-size:20px;line-height:1;color:var(--brand)}details.faq[open] summary::after{content:"\2212"}details.faq p{padding:0 18px 16px;color:var(--mute);font-size:14.5px}
@media(max-width:1000px){.cols{grid-template-columns:1fr}.sg{grid-template-columns:1fr 1fr}.pdark h1{font-size:30px}h1.t{font-size:30px}}
@media(max-width:640px){.sg{grid-template-columns:1fr}}

.er{padding:28px 0 64px;background:linear-gradient(var(--tint),#fff 75%)}
.er-in{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);gap:48px;align-items:center;padding-top:8px}
.er-art{display:grid;place-items:center}.art{width:100%;max-width:540px;height:auto;overflow:visible}.art *{transform-box:fill-box;transform-origin:center}
.er-code{font:700 clamp(64px,11vw,128px)/1 var(--serif);color:var(--brand);display:flex;gap:6px;margin-bottom:8px}.er-code span{display:inline-block;animation:bob 2.6s ease-in-out infinite}.er-code span:nth-child(2){animation-delay:.25s;color:var(--accent-dk)}.er-code span:nth-child(3){animation-delay:.5s}
.er-txt h1{font:700 clamp(26px,4vw,38px)/1.2 var(--serif);margin-bottom:10px}.er-txt .lede{margin:0 0 4px}
.er-act{display:flex;gap:12px;flex-wrap:wrap;margin:24px 0 6px}.er-act .btn{height:46px;padding:0 22px}
.er-search{display:flex;gap:10px;max-width:520px;margin-top:22px}.er-search .in{flex:1;min-width:0;height:48px;font-size:15px}.er-search .btn{height:48px}
.sg2{margin-top:14px;font-size:14.5px;color:var(--mute)}.sg2 a{color:var(--brand);font-weight:700;text-decoration:underline}
.er-tech{margin-top:26px;border-top:1px solid var(--line);padding-top:14px;font-size:13px;color:var(--mute)}.er-tech summary{cursor:pointer;font-weight:600;color:var(--ink)}.er-tech dl{display:grid;grid-template-columns:130px 1fr;gap:6px 12px;margin:12px 0}.er-tech dt{color:var(--mute)}.er-tech dd{word-break:break-all;color:var(--ink)}.er-tech code{background:var(--tint);padding:1px 6px;border-radius:4px}
.er-pl{margin-top:44px}.er-pl h2{font:700 22px var(--serif);margin-bottom:14px}.pl{display:grid;grid-template-columns:repeat(3,1fr);gap:var(--gap)}.pl a{display:block;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:16px 18px;transition:transform .2s,border-color .2s}.pl a:hover{border-color:var(--brand);transform:translateY(-3px)}.pl b{display:block;font:600 15px var(--sans);margin-bottom:2px;color:var(--brand)}.pl span{font-size:13px;color:var(--mute)}
.who{display:inline-flex;gap:8px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:99px;padding:5px 14px 5px 6px;font-size:13px;margin-bottom:14px}.who i{width:24px;height:24px;border-radius:50%;background:var(--brand);color:#fff;display:grid;place-items:center;font:700 11px var(--sans);font-style:normal}
.demo{margin-top:34px;font-size:12.5px;color:var(--mute);border:1px dashed #b9c8c1;border-radius:10px;padding:10px 14px}.demo a{color:var(--brand);text-decoration:underline;margin-right:12px;display:inline-block}
.cd{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:14px 18px;max-width:520px;margin-top:20px}.cd svg{width:64px;height:64px;flex:none;transform:rotate(-90deg)}.cd circle{fill:none;stroke-width:6}.cd .bg{stroke:var(--line)}.cd .fg{stroke:var(--brand);stroke-linecap:round;stroke-dasharray:163.4;stroke-dashoffset:0;animation:ringdown var(--t,6s) linear forwards}.cd.stop .fg{animation-play-state:paused}.cd b{display:block;font:700 18px var(--serif)}.cd span{font-size:13.5px;color:var(--mute)}
.urls{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:16px;font-size:13px}.urls code{background:#fff;border:1px solid var(--line);border-radius:8px;padding:4px 10px;word-break:break-all}.urls .old{text-decoration:line-through;color:var(--mute)}.urls .new{border-color:var(--brand);color:var(--brand);font-weight:600}
/* ---- animation ---- */
@keyframes bob{50%{transform:translateY(-10px)}}@keyframes float{50%{transform:translateY(-12px)}}@keyframes drift{from{transform:translateX(-14px)}to{transform:translateX(14px)}}
@keyframes wave{0%,100%{transform:skewY(0) scaleX(1)}50%{transform:skewY(-9deg) scaleX(.88)}}@keyframes flick{0%,100%{fill:#f0b030}46%{fill:#ffd774}52%{fill:#f0b030}}
@keyframes search{0%,100%{transform:translateX(0) rotate(0)}50%{transform:translateX(150px) rotate(10deg)}}@keyframes blink{0%,100%{transform:scale(1)}50%{transform:scale(1.25)}}
@keyframes spin{to{transform:rotate(360deg)}}@keyframes swing{0%,100%{transform:rotate(8deg)}50%{transform:rotate(-8deg)}}
@keyframes shake{0%,100%{transform:translateX(0)}15%{transform:translateX(-5px)}30%{transform:translateX(5px)}45%{transform:translateX(-4px)}60%{transform:translateX(4px)}75%{transform:translateX(-2px)}}
@keyframes drop{0%{transform:translateY(-16px)}45%{transform:translateY(0)}}@keyframes ring{0%{transform:scale(.7);opacity:.9}100%{transform:scale(1.9);opacity:0}}
@keyframes march{to{stroke-dashoffset:-26}}@keyframes ringdown{to{stroke-dashoffset:163.4}}@keyframes pop{0%{transform:scale(.4);opacity:0}70%{transform:scale(1.08);opacity:1}100%{transform:scale(1)}}
.cloud{animation:drift 9s ease-in-out infinite alternate}.cloud.d2{animation-duration:12s;animation-delay:-4s}.fl{animation:float 4.2s ease-in-out infinite}.fl.f2{animation-delay:-1.4s}.fl.f3{animation-delay:-2.6s}
.flag{transform-origin:left center;animation:wave 1.7s ease-in-out infinite}.win{animation:flick 3.4s infinite}.win.w2{animation-delay:-1.7s}.mag{animation:search 5.5s ease-in-out infinite}.qm{animation:blink 1.8s ease-in-out infinite}
.hand{transform-box:view-box}.h1{transform-origin:260px 150px;animation:spin 12s linear infinite}.h2{transform-origin:260px 150px;animation:spin 60s linear infinite}
.swing{transform-origin:top center;animation:swing 3.4s ease-in-out infinite}.swing.s2{animation-delay:-1.7s}.shackle{animation:drop 2.4s ease-out both}.lockg{animation:shake .7s 2.2s both}.ringc{transform-origin:center;animation:ring 2.6s ease-out infinite}.ringc.r2{animation-delay:1.3s}
.march{animation:march 1.2s linear infinite}.popin{animation:pop .8s cubic-bezier(.2,.9,.3,1.2) both}
@media (prefers-reduced-motion:reduce){.art *,.er-code span,.pl a{animation:none!important;transition:none!important}.cd .fg{animation:none!important}}
@media(max-width:900px){.er-in{grid-template-columns:minmax(0,1fr);gap:4px}.er-art{order:-1}.art{max-width:380px}.pl{grid-template-columns:1fr 1fr}.er-tech dl{grid-template-columns:100px 1fr}}
@media(max-width:560px){.pl{grid-template-columns:1fr}.er-act .btn{flex:1 1 100%}.er-search{flex-direction:column}.er-search .btn{width:100%}.cd{flex-direction:column;text-align:center}}</style>
</head>
<body>
@include('errors.partials.header')

<main class="er" id="main"><div class="wrap"><div class="er-in"><div class="er-art"><svg class="art popin" viewBox="0 0 520 340" role="img" aria-labelledby="a2"><title id="a2">A box of books moving from an old building to a new one</title>
<circle cx="260" cy="176" r="158" fill="#e6f1ec"/>
<g class="cloud"><ellipse cx="410" cy="78" rx="42" ry="13" fill="#fff"/><ellipse cx="432" cy="68" rx="24" ry="12" fill="#fff"/></g>
<ellipse cx="260" cy="298" rx="214" ry="14" fill="#c7dbd2"/>
<g><rect x="44" y="196" width="108" height="96" fill="none" stroke="#8fa399" stroke-width="4" stroke-dasharray="8 6" rx="4"/><polygon points="36,198 98,156 160,198" fill="none" stroke="#8fa399" stroke-width="4" stroke-dasharray="8 6"/><text x="98" y="252" text-anchor="middle" font-size="13" font-weight="700" fill="#6d7b74" font-family="Inter,sans-serif">OLD ADDRESS</text></g>
<g class="popin" style="animation-delay:.3s"><rect x="368" y="176" width="116" height="116" rx="5" fill="#0c5a49"/><polygon points="358,180 426,134 494,180" fill="#083f33"/><rect class="win" x="382" y="198" width="28" height="26" rx="3" fill="#f0b030"/><rect class="win w2" x="442" y="198" width="28" height="26" rx="3" fill="#f0b030"/><rect x="412" y="238" width="28" height="54" rx="3" fill="#052a22"/><line x1="426" y1="134" x2="426" y2="104" stroke="#5d6b65" stroke-width="3"/><polygon class="flag" points="426,104 454,112 426,122" fill="#f0b030"/></g>
<path class="march" d="M154 262 C 224 190, 296 190, 366 262" fill="none" stroke="#9fb8ad" stroke-width="4" stroke-dasharray="3 10" stroke-linecap="round"/>
<g transform="translate(154 262)"><g><animateMotion dur="3.8s" repeatCount="indefinite" path="M0 0 C 70 -72, 142 -72, 212 0" calcMode="spline" keyTimes="0;1" keySplines=".45 0 .25 1"/><rect x="-20" y="-30" width="40" height="30" rx="3" fill="#c9a46a"/><rect x="-20" y="-20" width="40" height="5" fill="#a9844c"/><rect x="-12" y="-38" width="24" height="8" rx="2" fill="#6f8f9a"/><rect x="-8" y="-45" width="18" height="7" rx="2" fill="#789a7b"/></g></g>
<rect x="253" y="228" width="8" height="70" rx="2" fill="#7e5b34"/>
<g class="swing"><polygon points="256,232 196,232 180,246 196,260 256,260" fill="#fff" stroke="#9fb8ad" stroke-width="2.5"/><text x="222" y="251" text-anchor="middle" font-size="12" font-weight="700" fill="#6d7b74" font-family="Inter,sans-serif">OLD</text></g>
<g class="swing s2"><polygon points="258,196 318,196 334,210 318,224 258,224" fill="#f0b030" stroke="#d99a1a" stroke-width="2.5"/><text x="292" y="215" text-anchor="middle" font-size="12" font-weight="700" fill="#2a1d00" font-family="Inter,sans-serif">NEW</text></g></svg></div>
<div class="er-txt"><p class="er-code" aria-hidden="true" id="code"><span>3</span><span>0</span><span>1</span></p>
<h1 id="h">This page has a new address</h1><p class="lede" id="l">We’ve moved it. We’ll take you to the new page in a moment.</p>
<div class="urls" id="urls"><code class="old" id="from"></code><span aria-hidden="true">→</span><code class="new" id="to"></code></div>
<div class="cd" id="cd" role="status" aria-live="polite"><svg viewBox="0 0 64 64" aria-hidden="true"><circle class="bg" cx="32" cy="32" r="26"/><circle class="fg" cx="32" cy="32" r="26"/></svg><div><b id="cb">Redirecting in 6 seconds</b><span>Use the buttons below if you would rather stay here.</span></div></div>
<form class="er-search" id="sf" role="search" hidden><input class="in" id="q" type="search" placeholder="Search for a school, article or event" aria-label="Search FindURSCH"><button class="btn btn-brand">Search</button></form>
<div class="er-act"><a class="btn btn-gold" id="go" href="findursch-schools.html">Go to the new page</a><button class="btn btn-line" id="stay" type="button">Stay here</button><a class="btn btn-line" href="findursch-home.html" id="home" hidden>Back to the homepage</a></div>
<details class="er-tech"><summary>Technical details</summary><dl><dt>Address</dt><dd><code id="req"></code></dd><dt>Time</dt><dd id="time"></dd><dt>Reference</dt><dd id="ref"></dd></dl><button class="btn btn-line sm" id="copy" type="button">Copy details for support</button></details><p class="demo"><b>Preview links (for designers, remove in production):</b><br><a href="findursch-404.html">Page not found</a><a href="findursch-moved.html">Page moved</a><a href="findursch-moved.html?state=gone">Page removed</a><a href="findursch-403.html?reason=login">Access denied: signed out</a><a href="findursch-403.html?reason=role">Access denied: wrong account</a><a href="findursch-403.html?reason=plan">Access denied: plan</a></p></div></div></div></main>

@include('errors.partials.footer')
<script>(function(){
var b=document.getElementById("burger");if(!b)return;
var navA=[].slice.call(document.querySelectorAll("header nav a")),
    extra=[].slice.call(document.querySelectorAll(".topbar a")).filter(function(a){return !/log in|sign.?up/i.test(a.textContent)});
var link=function(a,on){return '<a href="'+(a.getAttribute("href")||"#")+'"'+(on?' class="on"':'')+'>'+a.textContent+'</a>'};
var h='<div class="dh"><a class="logo" href="{{ route('home') }}">Find<b>UR</b>SCH</a><button class="dx" type="button" aria-label="Close menu">\u2715</button></div><div class="dl">';
navA.forEach(function(a){h+=link(a,a.classList.contains("on"))});
h+='<hr>';extra.forEach(function(a){h+=link(a,false)});
h+='<a href="{{ route('register') }}">List your school</a></div><div class="df"><a class="b1" href="{{ route('login') }}">Sign in</a><a class="b2" href="{{ route('register') }}">Register</a></div>';
var d=document.createElement("div");d.className="drawer";d.setAttribute("role","dialog");d.setAttribute("aria-modal","true");d.setAttribute("aria-label","Menu");d.innerHTML=h;
var o=document.createElement("div");o.className="ov";document.body.appendChild(o);document.body.appendChild(d);
function set(v){d.classList.toggle("open",v);o.classList.toggle("open",v);document.body.classList.toggle("nolock",v);b.setAttribute("aria-expanded",v);(v?d.querySelector(".dx"):b).focus()}
b.onclick=function(){set(true)};o.onclick=function(){set(false)};
d.querySelector(".dx").onclick=function(){set(false)};
d.addEventListener("click",function(e){if(e.target.closest(".dl a"))set(false)});
document.addEventListener("keydown",function(e){if(e.key==="Escape"&&d.classList.contains("open"))set(false)});
})();</script>
<script>

const $=id=>document.getElementById(id),qs=new URLSearchParams(location.search),RM=matchMedia("(prefers-reduced-motion: reduce)").matches;
const stamp=pfx=>{$("time").textContent=new Date().toLocaleString("en-GB",{timeZone:"Africa/Kampala",dateStyle:"medium",timeStyle:"short"})+" EAT";$("ref").textContent=pfx+"-"+Math.random().toString(36).slice(2,8).toUpperCase()};
let _t;const toast=m=>{let t=document.querySelector(".toast");if(!t){t=document.createElement("div");t.className="toast";t.setAttribute("role","status");document.body.appendChild(t)}t.textContent=m;t.hidden=false;clearTimeout(_t);_t=setTimeout(()=>t.hidden=true,2200)};
const copy=()=>{const x=`Address: ${$("req").textContent}\nTime: ${$("time").textContent}\nReference: ${$("ref").textContent}`;(navigator.clipboard?navigator.clipboard.writeText(x):Promise.reject()).then(()=>toast("Details copied"),()=>toast("Copy is not available here"))};
if($("copy"))$("copy").onclick=copy;
if(RM)document.querySelectorAll("animateMotion").forEach(a=>a.remove());/* Laravel: keep a redirects table (old_path, new_path, status). A match returns a 301 redirect. A removed page returns a 410 "Gone" view like the second state. */
const state=qs.get("state")==="gone"?"gone":"moved",to=qs.get("to")||"findursch-schools.html",from=qs.get("from")||"/schools/acacia-grove-primary";
$("req").textContent=from;stamp("MOV");$("from").textContent=from;$("to").textContent=to.replace(/^\//,"");$("go").href=to;
const SEC=6;let left=SEC,timer;
if(state==="gone"){
  document.title="This page is no longer available — FindURSCH";$("code").innerHTML="<span>4</span><span>1</span><span>0</span>";
  $("h").textContent="This page is no longer available";$("l").textContent="The school, article or event you were looking for has been removed or merged with another page, and there is no new address for it.";
  $("urls").innerHTML=`<code class="old">${from}</code>`;$("cd").hidden=true;$("stay").hidden=true;$("go").hidden=true;$("home").hidden=false;$("sf").hidden=false;
}else{
  $("cb").textContent=`Redirecting in ${SEC} seconds`;
  if(RM){$("cd").hidden=true;$("l").textContent="We’ve moved it. Use the button to open the new page.";$("stay").hidden=true}   /* no automatic redirect when the visitor prefers reduced motion */
  else{$("cd").style.setProperty("--t",SEC+"s");timer=setInterval(()=>{left--;if(left<=0){clearInterval(timer);$("cb").textContent="Taking you there…";location.replace(to);return}$("cb").textContent=`Redirecting in ${left} second${left===1?"":"s"}`},1000)}
}
$("stay").onclick=()=>{clearInterval(timer);$("cd").classList.add("stop");$("cb").textContent="Redirect cancelled";$("cd").querySelector("span").textContent="You can open the new page whenever you are ready.";$("stay").hidden=true};
$("sf").onsubmit=e=>{e.preventDefault();location.href="findursch-search.html?q="+encodeURIComponent($("q").value.trim())};
</script>
</body>
</html>