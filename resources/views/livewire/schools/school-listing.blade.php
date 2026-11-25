@verbatim
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
  .rtab,header nav,header .btn,.hic,aside .ad,aside .promo2{display:none}
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
</style>
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><symbol id="i-bm" viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4.5L6 21z"/></symbol><symbol id="i-back" viewBox="0 0 24 24"><path d="M15 5l-7 7 7 7"/></symbol><symbol id="i-home" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10"/></symbol><symbol id="i-chat" viewBox="0 0 24 24"><path d="M4 4h16v12H9l-5 4zM8 9h8M8 12h5"/></symbol><symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol><symbol id="i-swap" viewBox="0 0 24 24"><path d="M4 8h14l-3-3M20 16H6l3 3"/></symbol><symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></symbol><symbol id="i-crown" viewBox="0 0 24 24"><path d="M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5z"/></symbol><symbol id="i-gem" viewBox="0 0 24 24"><path d="M6 4h12l4 6-10 11L2 10zM2 10h20"/></symbol><symbol id="i-check" viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></symbol><symbol id="i-sort" viewBox="0 0 24 24"><path d="M4 7h16M7 12h10M10 17h4"/></symbol><symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol><symbol id="i-grid" viewBox="0 0 24 24"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></symbol><symbol id="i-list" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol><symbol id="i-chev" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></symbol><symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></symbol><symbol id="i-trend" viewBox="0 0 24 24"><path d="M4 20V11M10 20V4M16 20v-7M2 20h20"/></symbol></defs></svg>

<div class="topbar"><div class="wrap">
  <a href="#">Opportunities</a><a href="#">Past papers</a><a href="#">School supplies</a><a href="#">Log in</a><a href="#">Sign up</a>
</div></div>

<header><div class="wrap">
  <a class="logo" href="#">Find<b>UR</b>SCH</a>
  <nav><a href="#">Home</a><a class="on" href="#">Schools</a><a href="#">Compare</a><a href="#">Scholarships</a><a href="#">News</a><a href="#">Jobs</a><a href="#">Events</a></nav>
  <div class="hic"><a class="hb" href="#" aria-label="Saved schools"><svg class="i"><use href="#i-bm"/></svg><em class="svn" hidden>0</em></a><a class="hb" href="#" aria-label="Messages"><svg class="i"><use href="#i-chat"/></svg><em>3</em></a><a class="hb" href="#" aria-label="Account"><svg class="i"><use href="#i-user"/></svg></a></div>
  <a class="btn btn-ghost" href="#">List your school</a>
  <button class="burger" id="burger" type="button" aria-label="Menu" aria-expanded="false">☰</button></div></header>

<a class="rtab" href="#">REGISTER YOUR SCHOOL</a>

<section class="sband"><div class="wrap">
  <form class="sform" onsubmit="return false" role="search">
    <svg class="i ic"><use href="#i-search"/></svg>
    <input class="q" type="search" placeholder="Search school name, town or district" aria-label="Search schools">
    <button class="sb" type="button" aria-label="Save this search" aria-pressed="false"><svg class="i"><use href="#i-bm"/></svg></button>
    <button class="go" type="submit">Search</button>
  </form>
</div></section>

<main class="wrap">
  <div class="crumb"><a href="#">Home</a><span id="rc"></span></div>
  <div class="cols">
    <aside id="filters" aria-label="Filters">
      <div class="sh"><span>Filters</span><button type="button" id="sx" aria-label="Close filters">✕</button></div>
      <div class="card box"><h5>Categories</h5><div class="fb"><b class="pl">Schools</b><ul class="cl short" id="cl"></ul><button class="more" id="more" type="button">Show all 7</button></div></div>
      <section class="card fs" id="fs-loc"><button class="lrow" id="lbtn" type="button" aria-expanded="false"><span>Location<small id="ltx">All Uganda</small></span><svg class="i"><use href="#i-chev"/></svg></button><div class="fb" id="dl" hidden></div></section>
      <section class="card fs" id="fs-fee"><button class="fh" type="button">Fee per term, UGX<span>—</span></button><div class="fb">
        <div class="mm2"><input id="lo" type="number" min="0" step="10000" placeholder="min" aria-label="Minimum fee"><span>–</span><input id="hi" type="number" min="0" step="10000" placeholder="max" aria-label="Maximum fee"></div>
        <div id="rd"></div><div class="rs"><button id="fclr" type="button">CLEAR</button><button id="fsv" type="button">SAVE</button></div></div></section>
      <section class="card fs" id="fs-cu"><button class="fh" type="button">Curriculum<span>—</span></button><div class="fb" id="cu"></div></section>
      <section class="card fs"><button class="fh" type="button">School type<span>—</span></button><div class="fb" id="md"></div></section>
      <section class="card fs"><button class="fh" type="button">Verified schools<span>—</span></button><div class="fb"><label class="chk"><input type="checkbox" id="ver">Verified schools <small id="vn"></small></label></div></section>
      <section class="card fs"><button class="fh" type="button">Scholarships<span>—</span></button><div class="fb"><label class="chk"><input type="checkbox" id="sch">Scholarships offered <small id="sn"></small></label></div></section>
      <div class="ad side">Your advert here<small>300 × 250</small></div>
      <div class="promo2"><small>Advertise</small><h3>Put your brand in front of parents and students</h3><p>Reach families across Uganda every month.</p>
        <ul><li>Banners on every section</li><li>School spotlights</li><li>Featured videos</li></ul><a class="btn btn-gold" href="#">Advertise with us</a></div>
      <div class="sapply"><button class="btn line" id="sc" type="button">Clear all</button><button class="btn btn-brand" id="ap" type="button">Show schools</button></div>
    </aside>

    <section id="top">
      <div class="qchips"><button class="qc" type="button" data-q="ver" aria-pressed="false"><svg class="i"><use href="#i-check"/></svg>Verified schools</button><a class="qc gd" href="#"><svg class="i"><use href="#i-trend"/></svg>Fee trends</a></div>
      <h1 class="pt">Schools in Uganda</h1>
      <div class="mrow"><button type="button" data-o="">All filters</button><button type="button" data-o="fs-loc">Location</button><button type="button" data-o="fs-fee">Fee, UGX</button><button type="button" data-o="fs-cu">Curriculum</button></div>
      <div class="tb">
        <div class="dd" id="ds"><button type="button" aria-haspopup="listbox"><svg class="i"><use href="#i-sort"/></svg><span>All regions</span></button><div class="dm" role="listbox"></div></div>
                <button class="vt" id="vt" type="button"><svg class="i"><use href="#i-grid"/></svg></button>
      </div>
      <div class="act" id="act"></div>
      <div class="res list" id="res" aria-live="polite"></div>
      <nav class="pg" id="pg" aria-label="Pages"></nav>
    </section>
  </div>
</main>

<footer>
  <div class="wrap">
    <div class="fgrid">
      <div><a class="logo" href="#">Find<b>UR</b>SCH</a><p>Uganda's school discovery, comparison and admissions platform, with school profiles, news, events, jobs and scholarships.</p><p>Kampala, Uganda<br>+256 000 000 000<br>hello@findursch.ug</p></div>
      <div><h6>Explore schools</h6><ul><li>Primary schools</li><li>Secondary schools</li><li>Universities</li><li>Vocational schools</li><li>Boarding schools</li><li>International schools</li></ul></div>
      <div><h6>Resources</h6><ul><li>Latest news</li><li>Scholarships</li><li>School jobs</li><li>Events calendar</li><li>Articles</li><li>Past papers</li><li>Compare schools</li></ul></div>
      <div><h6>Support</h6><ul><li>List your school</li><li>Advertise with us</li><li>About</li><li>Contact</li><li>Terms and conditions</li><li>Privacy policy</li></ul></div>
    </div>
    <div class="fbar">
      <span>© 2026 FindURSCH. All rights reserved.</span>
      <div class="box2"><span>Want to reach parents and students every month?</span><a class="btn btn-ghost" href="#">Advertise with us</a></div>
    </div>
  </div>
</footer>

<div class="sov" id="sov"></div>

<script>
/* In Laravel: filters become query-string params (cat, district[], fee_min, fee_max, curr[], mode[], verified, schol, sort, time, view, page); cards become one @foreach over the paginated result. */
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
const C=[["Nursery & primary","#2f6fd6"],["Secondary school","#2f9e5a"],["Primary school","#e08a1e"],["Daycare / nursery","#8a5bd6"],["Vocational school","#d6479a"],["Music school","#6b7f78"],["University / college","#c9a46a"]];
const MODE={D:"Day school",DB:"Day & boarding"};
/* name, category, village, district, region, curriculum, mode, fee from (k), fee to (k), flags V verified P premium S spotlight N new Y 5+ years B scholarships, photos, days since listed */
const R=[
["Acacia Grove Nursery & Primary",0,"Nakifuma","Mukono","Central","UNEB","DB",250,700,"VNY",2,1],
["Lakeview Senior Secondary",1,"Bugoya","Buikwe","Central","UNEB","DB",350,900,"N",0,2],
["Cedar Ridge Secondary",1,"Bulenga","Wakiso","Central","UNEB","D",250,300,"NV",3,3],
["Sunrise Day Care and Pre-School",3,"Mpererwe","Kampala","Central","Other","D",250,500,"N",1,1],
["Nilebridge International School",0,"Namugongo","Wakiso","Central","Cambridge","DB",850,3800,"VPYB",3,12],
["Westgate Girls' Secondary",1,"Nsangi","Wakiso","Central","Other","DB",400,800,"",0,20],
["Marigold Infant Schools",0,"Kakunyu","Wakiso","Central","Other","DB",null,null,"",1,9],
["Hillcrest Education Service",0,"Kisalosolo","Kampala","Central","UNEB","D",650,1000,"VY",1,15],
["Rwenzori Nursery & Primary",0,"Bbira","Wakiso","Central","UNEB","D",null,null,"",1,40],
["Greenfield High School",1,"Namugoona","Kampala","Central","UNEB","DB",500,850,"VS",2,5],
["Kihande Senior School",1,"Kihande","Masindi","Western","UNEB","DB",300,800,"B",1,26],
["Little Stars Valley School",0,"Kiiti","Wakiso","Central","UNEB","DB",300,900,"",1,33],
["Masaka Greenfield Academy",1,"Rukungiri town","Rukungiri","Western","UNEB","DB",null,null,"",1,50],
["Eastern Skills Institute",4,"Kireka","Kampala","Central","Other","D",300,500,"V",1,18],
["Northern Star Primary",0,"Bukalanza","Wakiso","Central","UNEB","DB",250,450,"",0,60],
["Jinja Hill Nursery & Primary",0,"Walukuba","Jinja","Eastern","UNEB","DB",300,650,"VY",2,4],
["Gulu Bright Future Primary",0,"Laroo","Gulu","Northern","UNEB","DB",200,450,"B",1,8],
["Entebbe Lakeside Junior School",0,"Kitoro","Wakiso","Central","Cambridge","D",900,2200,"VP",3,2],
["Mbarara Crest Nursery & Primary",0,"Kakoba","Mbarara","Western","UNEB","DB",280,600,"Y",1,11],
["Kira Meadows Early Years",0,"Kira","Wakiso","Central","Other","D",350,750,"N",2,1],
["Mbale Springs Nursery & Primary",0,"Namakwekwe","Mbale","Eastern","UNEB","DB",220,480,"",1,22],
["Ntinda Rainbow School",0,"Ntinda","Kampala","Central","UNEB","D",500,1100,"V",2,6],
["Fort Portal Hilltop Academy",0,"Kijura","Kabarole","Western","UNEB","DB",260,520,"",0,45],
["Mukono Royal Senior School",1,"Seeta","Mukono","Central","UNEB","DB",420,950,"V",2,3],
["Arua Pioneer Secondary",1,"Ewanga","Arua","Northern","UNEB","DB",300,600,"B",1,14],
["Kololo Heritage College",1,"Kololo","Kampala","Central","IB","DB",2200,4500,"VPY",3,7],
["Lira Central High School",1,"Adyel","Lira","Northern","UNEB","DB",250,550,"",1,28],
["Kyambogo Primary School",2,"Kyambogo","Kampala","Central","UNEB","D",300,500,"Y",1,10],
["Busia Border Primary",2,"Busia town","Busia","Eastern","UNEB","D",150,300,"",0,35],
["Masaka Town Primary",2,"Nyendo","Masaka","Central","UNEB","DB",200,420,"",1,19],
["Soroti Unity Primary",2,"Arapai","Soroti","Eastern","UNEB","DB",null,null,"",0,55],
["Bweyogerere Model Primary",2,"Bweyogerere","Wakiso","Central","UNEB","D",350,600,"V",2,2],
["Tiny Steps Day Care",3,"Kyanja","Kampala","Central","Other","D",300,600,"",1,16],
["Kampala Technical Training Institute",4,"Nakawa","Kampala","Central","Other","D",450,1200,"VB",1,9],
["Kampala Conservatoire of Music",5,"Makerere","Kampala","Central","Other","D",600,1500,"S",2,13],
["Bugema Valley University College",6,"Luweero","Luweero","Central","Other","DB",1500,3200,"VB",2,30]
].map((r,i)=>({id:i,n:r[0],c:r[1],v:r[2],d:r[3],g:r[4],cu:r[5],m:r[6],lo:r[7],hi:r[8],f:r[9],ph:r[10],t:r[11]}));
const CAP='<svg viewBox="0 0 24 24" width="44" aria-hidden="true"><path d="M12 3 1 9l11 6 9-4.9V17h2V9L12 3zM5 13.2v4L12 21l7-3.8v-4L12 17l-7-3.8z" fill="#b9c8c1"/></svg>';
const S1='<div class="promo span"><div><small>Premium schools</small><h3>Be the first school parents see</h3><p>Top of search, homepage feature and spotlight adverts.</p></div><a class="btn btn-brand" href="#">See school plans</a></div>';
const S2='<div class="promo span"><div><small>For students</small><h3>UNEB past papers with answers</h3><p>PLE, UCE and UACE papers to download and revise.</p></div><a class="btn btn-gold" href="#">Get past papers</a></div>';
const S3='<div class="big span"><div><small>Opportunities</small><h3>Scholarships and bursaries open now</h3><p>Funding for primary, secondary, university and TVET.</p></div><a class="btn btn-gold" href="#">Find scholarships</a></div>';
const STRIPS=[S1,S2,S3],PS=12;
const B=[["Under 300K",0,299],["300K – 600K",300,599],["600K – 1M",600,999],["1M – 2M",1000,1999],["More than 2M",2000,null]];
const REG=["Central","Eastern","Northern","Western"];
const rank=s=>s.f.includes("P")?0:s.f.includes("S")?1:2;
const byRank=(a,b)=>rank(a)-rank(b)||a.t-b.t;
let saved=new Set();try{saved=new Set(JSON.parse(localStorage.getItem("fu-saved")||"[]"))}catch(e){}
const S={q:"",cat:null,dist:new Set(),lo:null,hi:null,cu:new Set(),m:new Set(),ver:false,sch:false,reg:"",view:matchMedia("(max-width:640px)").matches?"g":"l",page:1};
const IC=n=>`<svg class="i"><use href="#i-${n}"/></svg>`;
const fmt=k=>k>=1000?+(k/1000).toFixed(1)+"M":k+"k";
const feeT=s=>s.lo==null?"Fees on request":"UGX "+fmt(s.lo)+(s.hi>s.lo?" – "+fmt(s.hi):"");
const cnt=f=>R.filter(f).length;
const uniq=k=>[...new Set(R.map(s=>s[k]))];
const ok=s=>(!S.q||(s.n+" "+s.v+" "+s.d+" "+s.g).toLowerCase().includes(S.q))&&(S.cat===null||s.c===S.cat)&&(!S.dist.size||S.dist.has(s.d))
 &&((S.lo===null&&S.hi===null)||(s.lo!==null&&(S.lo===null||s.lo>=S.lo)&&(S.hi===null||s.lo<=S.hi)))
 &&(!S.cu.size||S.cu.has(s.cu))&&(!S.m.size||S.m.has(s.m))&&(!S.ver||s.f.includes("V"))&&(!S.sch||s.f.includes("B"))&&(!S.reg||s.g===S.reg);

const card=s=>{
 const V=s.f.includes("V"),Y=s.f.includes("Y"),P=s.f.includes("P"),Sp=s.f.includes("S"),N=s.f.includes("N");
 const tg=(V?`<span>${IC("check")}Verified school</span>`:"")+(Y?`<span>${IC("user")}5+ years on FindURSCH</span>`:"");
 const sv=saved.has(s.id);
 return `<article class="lc${P?" prm":Sp?" spt":""}"><a class="im${s.ph?"":" n0"}" href="#" aria-label="${s.n}">${s.ph?`<i class="g${s.id%4}"></i>`:CAP}${tg?`<div class="tags">${tg}</div>`:""}${P?'<b class="vip">Premium</b>':""}</a><div class="bd"><div class="tp"><div class="fe"><b>${feeT(s)}</b>${s.lo!==null?" per term":""}</div><h3 class="nm"><a href="#">${s.n}</a></h3></div><p class="ds">${C[s.c][0]} offering the ${s.cu} curriculum as a ${MODE[s.m].toLowerCase()} in ${s.v}, ${s.d}.</p><div class="cp"><span>${s.cu}</span><span>${MODE[s.m]}</span>${N?'<span class="nw">New</span>':""}</div><div class="ft"><span class="loc">${IC("pin")}${s.v}, ${s.d}</span><button class="sv${sv?" on":""}" data-id="${s.id}" aria-pressed="${sv}" aria-label="Save ${s.n}">${IC("bm")}</button>${P?`<span class="bg" title="Premium">${IC("crown")}</span>`:Sp?`<span class="bg sp" title="Spotlight">${IC("gem")}</span>`:""}</div></div></article>`};

let AC=[];
const res=$("#res"),side=$("#filters"),sov=$("#sov");
function render(){
 const L=R.filter(ok).sort(byRank),pages=Math.max(1,Math.ceil(L.length/PS));S.page=Math.min(S.page,pages);
 const sl=L.slice((S.page-1)*PS,S.page*PS);
 res.className="res "+(S.view=="g"?"grid":"list");
 res.innerHTML=sl.length?sl.map((s,i)=>card(s)+(i==5&&sl.length>6?STRIPS[S.page-1]||"":"")).join(""):'<div class="empty"><h3>No schools match these filters</h3><p>Try a different district, fee range or curriculum.</p><button class="btn btn-brand" data-clear>Clear all filters</button></div>';
 $("#rc").innerHTML=`${L.length} results for <b>Schools</b> in Uganda`;
 $("#ap").textContent=`Show ${L.length} school${L.length==1?"":"s"}`;
 const p=n=>`<a href="#" data-p="${n}"${n<1||n>pages?' aria-disabled="true"':""}>${n<S.page?"‹":"›"}</a>`;
 $("#pg").innerHTML=pages>1?p(S.page-1)+[...Array(pages)].map((_,i)=>`<a href="#" data-p="${i+1}"${i+1==S.page?' class="on" aria-current="page"':""}>${i+1}</a>`).join("")+p(S.page+1):"";
 AC=[];
 if(S.q)AC.push([`“${S.q}”`,()=>{S.q="";$$(".q").forEach(x=>x.value="")}]);
 if(S.reg)AC.push([S.reg+" region",()=>S.reg=""]);
 if(S.cat!==null)AC.push([C[S.cat][0],()=>S.cat=null]);
 S.dist.forEach(d=>AC.push([d,()=>S.dist.delete(d)]));
 if(S.lo!==null||S.hi!==null)AC.push([`UGX ${S.lo?fmt(S.lo):"0"}${S.hi!==null?" – "+fmt(S.hi):"+"}`,()=>S.lo=S.hi=null]);
 S.cu.forEach(c=>AC.push([c,()=>S.cu.delete(c)]));
 S.m.forEach(m=>AC.push([MODE[m],()=>S.m.delete(m)]));
 if(S.ver)AC.push(["Verified",()=>S.ver=false]);
 if(S.sch)AC.push(["Scholarships",()=>S.sch=false]);
 $("#act").innerHTML=AC.length?AC.map((a,i)=>`<button type="button" data-a="${i}">${a[0]} ✕</button>`).join("")+'<button type="button" class="ca" data-clear>Clear all</button>':"";
 $$(".svn").forEach(e=>{e.textContent=saved.size;e.hidden=!saved.size});
}
function sync(){
 $$(".cl li").forEach(l=>l.classList.toggle("on",(l.dataset.k===""?null:+l.dataset.k)===S.cat));
 $$("[data-d]").forEach(x=>x.checked=S.dist.has(x.dataset.d));$$("[data-cu]").forEach(x=>x.checked=S.cu.has(x.dataset.cu));$$("[data-m]").forEach(x=>x.checked=S.m.has(x.dataset.m));
 $("#ver").checked=S.ver;$("#sch").checked=S.sch;$('[data-q=ver]').classList.toggle("on",S.ver);$('[data-q=ver]').setAttribute("aria-pressed",S.ver);
 $("#lo").value=S.lo===null?"":S.lo*1000;$("#hi").value=S.hi===null?"":S.hi*1000;
 $$("[name=fee]").forEach(r=>{const b=B[r.value];r.checked=S.lo===b[1]&&S.hi===b[2]});
 $("#ltx").textContent=S.dist.size?[...S.dist].join(", "):"All Uganda";
 $("#ds button span").textContent=S.reg?S.reg+" region":"All regions";
 $$("#ds .dm button").forEach(b=>b.classList.toggle("on",b.dataset.v==S.reg));
 const g=S.view=="g";$("#vt use").setAttribute("href",g?"#i-list":"#i-grid");$("#vt").setAttribute("aria-label",g?"Switch to list view":"Switch to grid view");
}
const go=()=>{S.page=1;sync();render()};
const reset=()=>{Object.assign(S,{q:"",reg:"",cat:null,lo:null,hi:null,ver:false,sch:false});S.dist.clear();S.cu.clear();S.m.clear();$$(".q").forEach(x=>x.value="")};

/* build sidebar */
$("#cl").innerHTML=`<li class="on" data-k="">All schools<small>${R.length}</small></li>`+C.map((c,i)=>`<li data-k="${i}"><i style="--c:${c[1]}"></i>${c[0]}<small>${cnt(s=>s.c==i)}</small></li>`).join("");
$("#dl").innerHTML=uniq("d").sort((a,b)=>cnt(s=>s.d==b)-cnt(s=>s.d==a)||a.localeCompare(b)).map(d=>`<label class="chk"><input type="checkbox" data-d="${d}">${d} <small>• ${cnt(s=>s.d==d)}</small></label>`).join("");
$("#cu").innerHTML=uniq("cu").map(c=>`<label class="chk"><input type="checkbox" data-cu="${c}">${c} <small>• ${cnt(s=>s.cu==c)}</small></label>`).join("");
$("#md").innerHTML=uniq("m").map(m=>`<label class="chk"><input type="checkbox" data-m="${m}">${MODE[m]} <small>• ${cnt(s=>s.m==m)}</small></label>`).join("");
$("#rd").innerHTML=B.map((b,i)=>`<label class="ra"><input type="radio" name="fee" value="${i}">${b[0]} <small>• ${cnt(s=>s.lo!==null&&s.lo>=b[1]&&(b[2]===null||s.lo<=b[2]))}</small></label>`).join("");
$("#vn").textContent="• "+cnt(s=>s.f.includes("V"));$("#sn").textContent="• "+cnt(s=>s.f.includes("B"));
$("#ds .dm").innerHTML=[["","All regions"],...REG.map(r=>[r,r+" <small>• "+cnt(s=>s.g==r)+"</small>"])].map(x=>`<button type="button" role="option" data-v="${x[0]}">${x[1]}</button>`).join("");

/* events */
side.addEventListener("click",e=>{
 const li=e.target.closest(".cl li");if(li){S.cat=li.dataset.k===""?null:+li.dataset.k;go();return}
 const h=e.target.closest(".fh");if(h){const f=h.parentElement.classList.toggle("shut");h.querySelector("span").textContent=f?"+":"—"}
});
side.addEventListener("change",e=>{const t=e.target;
 if(t.dataset.d)t.checked?S.dist.add(t.dataset.d):S.dist.delete(t.dataset.d);
 else if(t.dataset.cu)t.checked?S.cu.add(t.dataset.cu):S.cu.delete(t.dataset.cu);
 else if(t.dataset.m)t.checked?S.m.add(t.dataset.m):S.m.delete(t.dataset.m);
 else if(t.id=="ver")S.ver=t.checked;else if(t.id=="sch")S.sch=t.checked;
 else if(t.name=="fee"){S.lo=B[t.value][1];S.hi=B[t.value][2]}
 else if(t.id=="lo"||t.id=="hi"){S.lo=$("#lo").value===""?null:$("#lo").value/1000;S.hi=$("#hi").value===""?null:$("#hi").value/1000}
 go()});
$("#fsv").onclick=()=>{S.lo=$("#lo").value===""?null:$("#lo").value/1000;S.hi=$("#hi").value===""?null:$("#hi").value/1000;go()};
$("#fclr").onclick=()=>{S.lo=S.hi=null;go()};
$("#more").onclick=()=>{const s=$("#cl").classList.toggle("short");$("#more").textContent=s?"Show all 7":"Show less"};
$("#lbtn").onclick=()=>{const d=$("#dl"),o=d.hidden;d.hidden=!o;$("#lbtn").setAttribute("aria-expanded",o)};
$$(".q").forEach(q=>q.oninput=()=>{S.q=q.value.trim().toLowerCase();$$(".q").forEach(x=>x!==q&&(x.value=q.value));go()});
$$(".sb").forEach(b=>b.onclick=()=>{const o=!b.classList.contains("on");$$(".sb").forEach(x=>{x.classList.toggle("on",o);x.setAttribute("aria-pressed",o)})});
$('[data-q=ver]').onclick=()=>{S.ver=!S.ver;go()};
$$(".dd").forEach(d=>d.querySelector("button").onclick=e=>{e.stopPropagation();$$(".dd").forEach(x=>x!==d&&x.classList.remove("open"));d.classList.toggle("open")});
document.addEventListener("click",()=>$$(".dd").forEach(x=>x.classList.remove("open")));
$("#ds .dm").onclick=e=>{const b=e.target.closest("button");if(b){S.reg=b.dataset.v;go()}};
$("#vt").onclick=()=>{S.view=S.view=="g"?"l":"g";sync();render()};
res.addEventListener("click",e=>{
 const b=e.target.closest(".sv");
 if(b){const id=+b.dataset.id;saved.has(id)?saved.delete(id):saved.add(id);try{localStorage.setItem("fu-saved",JSON.stringify([...saved]))}catch(x){}
  b.classList.toggle("on",saved.has(id));b.setAttribute("aria-pressed",saved.has(id));$$(".svn").forEach(n=>{n.textContent=saved.size;n.hidden=!saved.size});return}
 if(e.target.closest("[data-clear]")){reset();go()}
 if(e.target.closest(".lc a"))e.preventDefault()});
$("#act").onclick=e=>{const b=e.target.closest("button");if(!b)return;if(b.dataset.clear!==undefined)reset();else AC[b.dataset.a][1]();go()};
$("#pg").onclick=e=>{const a=e.target.closest("a");if(!a)return;e.preventDefault();S.page=+a.dataset.p;sync();render();$("#top").scrollIntoView({behavior:"smooth",block:"start"})};

/* mobile: filters open as a bottom sheet */
function sheet(o,id){side.classList.toggle("open",o);sov.classList.toggle("open",o);document.body.classList.toggle("nolock",o);
 if(o&&id){const el=$("#"+id);el.classList.remove("shut");if(id=="fs-loc"){$("#dl").hidden=false;$("#lbtn").setAttribute("aria-expanded","true")}setTimeout(()=>el.scrollIntoView({block:"start"}),60)}}
$$(".mrow button").forEach(b=>b.onclick=()=>sheet(true,b.dataset.o));
$("#sx").onclick=$("#ap").onclick=sov.onclick=()=>sheet(false);
$("#sc").onclick=()=>{reset();go()};
document.addEventListener("keydown",e=>{if(e.key==="Escape"){sheet(false);$$(".dd").forEach(x=>x.classList.remove("open"))}});
sync();render();
</script>
<script>(function(){
var b=document.getElementById("burger");if(!b)return;
var navA=[].slice.call(document.querySelectorAll("header nav a")),
    extra=[].slice.call(document.querySelectorAll(".topbar a")).filter(function(a){return !/log in|sign.?up/i.test(a.textContent)});
var link=function(a,on){return '<a href="'+(a.getAttribute("href")||"#")+'"'+(on?' class="on"':'')+'>'+a.textContent+'</a>'};
var h='<div class="dh"><a class="logo" href="#">Find<b>UR</b>SCH</a><button class="dx" type="button" aria-label="Close menu">\u2715</button></div><div class="dl">';
navA.forEach(function(a){h+=link(a,a.classList.contains("on"))});
h+='<hr>';extra.forEach(function(a){h+=link(a,false)});
h+='<a href="#">List your school</a></div><div class="df"><a class="b1" href="#">Sign in</a><a class="b2" href="#">Register</a></div>';
var d=document.createElement("div");d.className="drawer";d.setAttribute("role","dialog");d.setAttribute("aria-modal","true");d.setAttribute("aria-label","Menu");d.innerHTML=h;
var o=document.createElement("div");o.className="ov";document.body.appendChild(o);document.body.appendChild(d);
function set(v){d.classList.toggle("open",v);o.classList.toggle("open",v);document.body.classList.toggle("nolock",v);b.setAttribute("aria-expanded",v);(v?d.querySelector(".dx"):b).focus()}
b.onclick=function(){set(true)};o.onclick=function(){set(false)};
d.querySelector(".dx").onclick=function(){set(false)};
d.addEventListener("click",function(e){if(e.target.closest(".dl a"))set(false)});
document.addEventListener("keydown",function(e){if(e.key==="Escape"&&d.classList.contains("open"))set(false)});
})();</script>
</body>
</html>
@endverbatim
