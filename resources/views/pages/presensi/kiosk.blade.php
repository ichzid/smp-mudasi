@extends('layouts.kiosk', ['title' => 'Absen Kartu RFID'])

@section('content')
<style>
    :root {
        --brand: #465fff; --brand-deep: #2a31d8; --brand-soft: #ecf3ff;
        --bg: #f2f7ff; --surface: rgba(255,255,255,.9); --surface-solid: #fff;
        --text: #101828; --muted: #667085; --line: rgba(70,95,255,.14);
        --danger: #dc2626; --warning: #d97706; --shadow: 0 24px 70px rgba(38,46,137,.12);
    }
    :root.dark {
        --brand: #7592ff; --brand-deep: #c2d6ff; --brand-soft: rgba(70,95,255,.16);
        --bg: #0c111d; --surface: rgba(22,25,80,.34); --surface-solid: #161b2e;
        --text: #f2f4f7; --muted: #98a2b3; --line: rgba(156,185,255,.16);
        --danger: #fb7185; --warning: #fbbf24; --shadow: 0 28px 80px rgba(0,0,0,.34);
    }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; overflow-x: hidden; background: var(--bg); color: var(--text); }
    body::before, body::after { content:""; position:fixed; border-radius:50%; filter:blur(1px); pointer-events:none; }
    body::before { width:44rem; height:44rem; top:-26rem; right:-12rem; background:radial-gradient(circle, rgba(70,95,255,.18), transparent 68%); }
    body::after { width:36rem; height:36rem; bottom:-24rem; left:-12rem; background:radial-gradient(circle, rgba(54,65,245,.12), transparent 68%); }
    [x-cloak] { display:none !important; }
    .sr-scanner { position:fixed; width:1px; height:1px; opacity:0; pointer-events:none; left:-10px; top:-10px; }
    .shell { position:relative; z-index:1; min-height:100svh; width:min(1440px,100%); margin:auto; padding:clamp(18px,3vw,42px); display:flex; flex-direction:column; gap:clamp(20px,3vh,34px); }
    .topbar { display:flex; justify-content:space-between; align-items:center; gap:20px; }
    .brand { display:flex; align-items:center; gap:14px; min-width:0; }
    .brand-mark { width:66px; height:66px; flex:none; display:grid; place-items:center; overflow:hidden; }
    .brand-mark img { width:100%; height:100%; object-fit:contain; }
    .brand-name { font-weight:800; font-size:clamp(14px,1.6vw,19px); letter-spacing:-.01em; line-height:1.15; }
    .brand-sub { margin-top:5px; color:var(--muted); font-size:11px; font-weight:700; letter-spacing:.13em; text-transform:uppercase; }
    .datetime { display:flex; align-items:center; gap:15px; text-align:right; }
    .date { color:var(--muted); font-size:clamp(11px,1.2vw,14px); font-weight:600; }
    .clock { font-variant-numeric:tabular-nums; font-size:clamp(22px,3vw,38px); font-weight:800; letter-spacing:-.04em; line-height:1; }
    .top-actions { display:flex; align-items:center; gap:9px; }
    .connection { display:inline-flex; align-items:center; gap:8px; min-height:42px; padding:0 13px; border:1px solid var(--line); border-radius:14px; color:var(--brand-deep); background:var(--surface); font-size:12px; font-weight:750; }
    .connection-dot { width:7px; height:7px; border-radius:50%; background:var(--brand); box-shadow:0 0 0 4px var(--brand-soft); }
    .connection.offline { color:var(--danger); }
    .connection.offline .connection-dot { background:var(--danger); box-shadow:0 0 0 4px rgba(239,68,68,.12); }
    .icon-button { width:42px; height:42px; flex:none; border:1px solid var(--line); border-radius:14px; display:grid; place-items:center; color:var(--text); background:var(--surface); cursor:pointer; transition:transform .18s ease,background .18s ease; }
    .icon-button:hover { transform:translateY(-1px); background:var(--surface-solid); }
    .icon-button:focus-visible { outline:3px solid var(--brand-soft); outline-offset:2px; }
    .content { flex:1; min-height:0; display:grid; grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr); gap:clamp(18px,2.2vw,30px); }
    .scanner, .recent { border:1px solid var(--line); background:var(--surface); backdrop-filter:blur(18px); box-shadow:var(--shadow); border-radius:clamp(24px,3vw,36px); }
    .scanner { min-height:510px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:clamp(74px,7vw,92px) clamp(28px,4vw,58px) clamp(34px,4vw,48px); text-align:center; overflow:hidden; position:relative; }
    .scanner::before { content:""; position:absolute; width:230px; height:230px; top:-145px; right:-80px; border:38px solid var(--brand-soft); border-radius:50%; opacity:.8; pointer-events:none; }
    .scanner::after { content:""; position:absolute; width:150px; height:150px; left:-95px; bottom:-85px; border:1px solid var(--line); border-radius:36px; transform:rotate(32deg); pointer-events:none; }
    .scanner-status { position:absolute; z-index:2; top:24px; left:24px; display:inline-flex; align-items:center; gap:9px; border:1px solid var(--line); background:var(--surface-solid); padding:9px 13px; border-radius:999px; color:var(--muted); font-size:12px; font-weight:750; }
    .feature-pills { position:absolute; z-index:2; top:24px; right:24px; display:flex; align-items:center; gap:7px; }
    .feature-pill { display:inline-flex; align-items:center; gap:6px; min-height:34px; padding:0 10px; border:1px solid var(--line); border-radius:999px; color:var(--brand-deep); background:var(--brand-soft); font-size:10px; font-weight:800; letter-spacing:.04em; }
    .feature-pill svg { width:13px; height:13px; flex:none; }
    .scanner-foot { position:absolute; z-index:2; right:24px; bottom:20px; left:24px; display:flex; align-items:center; justify-content:space-between; gap:16px; padding-top:16px; border-top:1px solid var(--line); color:var(--muted); font-size:10px; font-weight:650; letter-spacing:.04em; }
    .scanner-foot strong { color:var(--text); font-weight:750; }
    .status-dot { width:8px; height:8px; border-radius:50%; background:var(--brand); box-shadow:0 0 0 5px var(--brand-soft); }
    .scanner.processing .status-dot { background:var(--warning); animation:pulse 1s infinite; }
    .scanner.error .status-dot { background:var(--danger); }
    .tap-visual { width:clamp(180px,24vw,270px); aspect-ratio:1; position:relative; display:grid; place-items:center; margin-bottom:28px; }
    .ring { position:absolute; inset:0; border:1px solid rgba(70,95,255,.2); border-radius:50%; }
    .ring:nth-child(2) { inset:15%; background:var(--brand-soft); border-color:rgba(70,95,255,.28); }
    .ring:nth-child(3) { inset:30%; background:var(--surface-solid); border-color:var(--line); box-shadow:0 18px 50px rgba(38,46,137,.14); }
    .scanner.ready .ring:first-child { animation:breathe 2.8s ease-out infinite; }
    .scanner.processing .ring { animation:scan 1s ease-out infinite; }
    .scanner.success .ring:nth-child(2) { background:rgba(34,197,94,.22); }
    .scanner.error .ring:nth-child(2) { background:rgba(239,68,68,.14); border-color:rgba(239,68,68,.28); }
    .rfid-icon { position:relative; z-index:2; width:64px; height:64px; color:var(--brand); }
    .scanner.error .rfid-icon { color:var(--danger); }
    h1 { margin:0; font-size:clamp(27px,3.3vw,46px); line-height:1.08; letter-spacing:-.045em; }
    .instruction { max-width:520px; margin:14px auto 0; color:var(--muted); font-size:clamp(14px,1.4vw,17px); line-height:1.65; }
    .feedback { min-height:24px; margin-top:18px; color:var(--brand-deep); font-size:14px; font-weight:750; }
    .scanner.error .feedback { color:var(--danger); }
    .scanner-view { position:relative; z-index:1; width:100%; display:flex; flex-direction:column; align-items:center; }
    .kiosk-result-popup { border-radius:24px; background:var(--surface-solid); color:var(--text); }
    .kiosk-result-popup .swal2-title, .kiosk-result-popup .swal2-html-container { color:var(--text); }
    .kiosk-result-card { display:grid; grid-template-columns:72px minmax(0,1fr); gap:16px; align-items:center; margin-top:8px; padding:16px; border:1px solid rgba(34,197,94,.35); border-left:5px solid #22c55e; border-radius:18px; background:var(--surface); text-align:left; }
    .kiosk-result-photo { width:72px; height:72px; display:grid; place-items:center; overflow:hidden; border-radius:16px; background:var(--brand-soft); color:var(--brand-deep); font-size:34px; }
    .kiosk-result-photo img { width:100%; height:100%; object-fit:cover; }
    .kiosk-result-name { overflow:hidden; text-overflow:ellipsis; font-size:20px; font-weight:850; }
    .kiosk-result-meta { margin-top:4px; color:var(--muted); font-size:13px; }
    .kiosk-result-status { margin-top:10px; color:#15803d; font-size:13px; font-weight:800; }
    :root.dark .kiosk-result-status { color:#4ade80; }
    .recent { padding:clamp(22px,2.6vw,32px); display:flex; flex-direction:column; min-height:0; }
    .recent-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding-bottom:20px; border-bottom:1px solid var(--line); }
    .recent h2 { margin:0; font-size:18px; letter-spacing:-.02em; }
    .recent-desc { margin:5px 0 0; color:var(--muted); font-size:12px; }
    .count { min-width:30px; height:30px; display:grid; place-items:center; padding:0 9px; border-radius:10px; color:var(--brand-deep); background:var(--brand-soft); font-size:12px; font-weight:800; }
    .log-list { list-style:none; margin:0; padding:15px 0 0; display:flex; flex-direction:column; gap:10px; overflow:auto; }
    .empty { flex:1; min-height:250px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--muted); text-align:center; font-size:13px; line-height:1.6; }
    .empty svg { width:42px; margin-bottom:12px; opacity:.45; }
    .log-item { display:grid; grid-template-columns:48px minmax(0,1fr) auto; align-items:center; gap:12px; padding:11px; border:1px solid var(--line); border-radius:16px; background:var(--surface-solid); animation:enter .3s ease-out; }
    .avatar { width:48px; height:48px; border-radius:14px; object-fit:cover; display:grid; place-items:center; overflow:hidden; background:var(--brand-soft); color:var(--brand); }
    .avatar svg { width:24px; }
    .li-name { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:13px; font-weight:800; }
    .li-meta { margin-top:4px; color:var(--muted); font-size:11px; }
    .li-status { padding:6px 8px; border-radius:8px; font-size:9px; font-weight:850; letter-spacing:.07em; background:var(--brand-soft); color:var(--brand-deep); }
    .li-status.terlambat { background:rgba(245,158,11,.15); color:var(--warning); }
    @keyframes breathe { 0%,100%{transform:scale(.94);opacity:.45} 50%{transform:scale(1);opacity:1} }
    @keyframes scan { from{transform:scale(.65);opacity:1} to{transform:scale(1.05);opacity:0} }
    @keyframes pulse { 50%{opacity:.4} }
    @keyframes enter { from{opacity:0;transform:translateY(-8px)} }
    @media (max-width:900px) { .content{grid-template-columns:1fr}.scanner{min-height:470px}.recent{min-height:300px}.log-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));overflow:visible}.empty{grid-column:1/-1;min-height:180px} }
    @media (max-width:600px) { .shell{padding:14px;gap:14px}.topbar{align-items:flex-start;gap:10px}.brand{gap:9px}.brand-mark{width:50px;height:50px}.brand-name{font-size:12px}.brand-sub,.date,.connection span:last-child{display:none}.datetime{gap:6px}.clock{font-size:19px}.top-actions{gap:6px}.connection{width:38px;min-height:38px;padding:0;justify-content:center}.icon-button{width:38px;height:38px}.scanner{min-height:min(500px,76svh);padding:72px 18px 60px;border-radius:24px}.scanner-status{top:18px;left:18px}.feature-pills{top:18px;right:18px}.feature-pill{width:34px;padding:0;justify-content:center}.feature-pill span{display:none}.scanner-foot{right:18px;bottom:16px;left:18px}.scanner-foot span:last-child{display:none}.tap-visual{width:min(180px,52vw);margin-bottom:20px}.kiosk-result-card{grid-template-columns:58px minmax(0,1fr);gap:12px;padding:13px}.kiosk-result-photo{width:58px;height:58px;border-radius:14px}.recent{border-radius:24px}.log-list{grid-template-columns:1fr}.empty{min-height:150px} }
    @media (max-width:390px) { .brand-name{max-width:105px}.scanner{min-height:390px}.top-actions{position:absolute;right:14px;top:62px}.content{margin-top:34px} }
    @media (prefers-reduced-motion:reduce) { *,*::before,*::after{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important} }
</style>

<main class="shell" x-data="kioskData()" x-init="init()">
    <input class="sr-scanner" type="text" x-ref="rfidInput" x-model="rfidCode" @keydown.enter.prevent="processScan()" @blur="restoreFocus()" autocomplete="off" autocapitalize="characters" spellcheck="false" aria-label="Pembaca kartu RFID">

    <header class="topbar">
        <div class="brand">
            <div class="brand-mark">
                <img src="{{ asset('images/logo/smp_mudasi.png') }}" alt="Logo SMP Muhammadiyah Danau Sijabut">
            </div>
            <div><div class="brand-name">SMP Muhammadiyah<br>Danau Sijabut</div><div class="brand-sub">Sistem Presensi Digital</div></div>
        </div>
        <div class="datetime">
            <div><div class="clock" x-text="currentTime">--:--:--</div><div class="date" x-text="currentDate"></div></div>
            <div class="top-actions">
                <div class="connection" :class="{ offline: !isOnline }" role="status" aria-live="polite"><span class="connection-dot"></span><span x-text="isOnline ? 'Terhubung' : 'Offline'"></span></div>
                <button x-show="fullscreenSupported" class="icon-button" type="button" @click="toggleFullscreen()" :aria-label="isFullscreen ? 'Keluar dari layar penuh' : 'Masuk layar penuh'" :title="isFullscreen ? 'Keluar layar penuh' : 'Layar penuh'">
                    <svg x-show="!isFullscreen" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg>
                    <svg x-show="isFullscreen" x-cloak width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 8h5V3M21 8h-5V3M3 16h5v5M21 16h-5v5"/></svg>
                </button>
                <button class="icon-button" type="button" @click="toggleTheme()" aria-label="Ganti tema warna">
                    <svg x-show="!isDark" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" d="M21 12.8A8.5 8.5 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                    <svg x-show="isDark" x-cloak width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="4" stroke-width="1.8"/><path stroke-width="1.8" stroke-linecap="round" d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
            </div>
        </div>
    </header>

    <div class="content">
        <section class="scanner" :class="scannerState" @click="restoreFocus()" aria-labelledby="scanner-title">
            <div class="scanner-status" role="status" aria-live="polite"><span class="status-dot"></span><span x-text="statusLabel"></span></div>
            <div class="feature-pills" aria-label="Fitur perangkat">
                <span class="feature-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4-8 4-8-4 8-4zM6 9.5V15c0 1.7 2.7 3 6 3s6-1.3 6-3V9.5"/></svg>
                    <span>Presensi Siswa</span>
                </span>
                <span class="feature-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M12 3a4 4 0 014 4v2h1a2 2 0 012 2v8H5v-8a2 2 0 012-2h1V7a4 4 0 014-4zM9 9h6"/></svg>
                    <span>Aman &amp; Terintegrasi</span>
                </span>
            </div>
            <div class="scanner-view">
                <div class="tap-visual" aria-hidden="true">
                    <span class="ring"></span><span class="ring"></span><span class="ring"></span>
                    <svg class="rfid-icon" viewBox="0 0 64 64" fill="none"><rect x="21" y="14" width="22" height="36" rx="5" stroke="currentColor" stroke-width="3"/><path d="M14 22c-6 6-6 14 0 20M8 16C-1 25-1 39 8 48M50 22c6 6 6 14 0 20M56 16c9 9 9 23 0 32" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="32" cy="32" r="4" fill="currentColor"/></svg>
                </div>
                <h1 id="scanner-title" x-text="headline">Tempelkan Kartu Pelajar</h1>
                <p class="instruction" x-text="instruction">Dekatkan kartu RFID ke alat pembaca. Presensi akan tercatat secara otomatis.</p>
            </div>

            <div class="scanner-foot" aria-hidden="true">
                <span><strong>SMP Muhammadiyah Danau Sijabut</strong> · Sistem Presensi Digital</span>
                <span>RFID Attendance Terminal</span>
            </div>
        </section>

        <aside class="recent" aria-labelledby="recent-title">
            <div class="recent-head"><div><h2 id="recent-title">Presensi Terbaru</h2><p class="recent-desc">Aktivitas pada perangkat ini</p></div><span class="count" x-text="recentScans.length">0</span></div>
            <ul class="log-list">
                <template x-if="recentScans.length === 0"><li class="empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.5" d="M4 6.5h16v13H4zM8 4v5M16 4v5M4 10h16"/></svg>Belum ada presensi.<br>Hasil pemindaian akan tampil di sini.</li></template>
                <template x-for="scan in recentScans" :key="scan.key"><li class="log-item">
                    <template x-if="scan.foto_url"><img class="avatar" :src="scan.foto_url" :alt="'Foto ' + scan.nama"></template>
                    <template x-if="!scan.foto_url"><span class="avatar" aria-hidden="true"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zm-8 9a8 8 0 0116 0H4z"/></svg></span></template>
                    <div><div class="li-name" x-text="scan.nama"></div><div class="li-meta" x-text="scan.kelas + ' · ' + scan.waktu"></div></div>

                </li></template>
            </ul>
        </aside>
    </div>

</main>

<script>
function kioskData() {
    return {
        currentTime: '--:--:--', currentDate: '', rfidCode: '', scannerState: 'ready',
        feedbackMsg: '', resultData: null, modalActive: false,
        recentScans: [], focusTimer: null, isDark: document.documentElement.classList.contains('dark'),
        isOnline: navigator.onLine, isFullscreen: Boolean(document.fullscreenElement), fullscreenSupported: Boolean(document.fullscreenEnabled),
        get isProcessing() { return this.scannerState === 'processing'; },
        get isLocked() { return this.isProcessing || this.modalActive; },
        get statusLabel() { return { ready:'Siap tempel', processing:'Membaca kartu', success:'Presensi berhasil', error:'Pemindaian gagal' }[this.scannerState]; },
        get headline() { return { ready:'Tempelkan Kartu Pelajar', processing:'Kartu Sedang Dibaca', success:'Presensi Tercatat', error:'Kartu Belum Terbaca' }[this.scannerState]; },
        get instruction() { if (!this.isOnline) return 'Perangkat sedang offline. Periksa koneksi sebelum memindai kembali.'; return this.scannerState === 'processing' ? 'Mohon tunggu dan jangan tempelkan kartu lain.' : 'Dekatkan kartu RFID ke alat pembaca, lalu tunggu hingga hasil tampil.'; },
        init() {
            this.updateClock(); setInterval(() => this.updateClock(), 1000);
            document.addEventListener('click', () => this.restoreFocus());
            window.addEventListener('focus', () => this.restoreFocus());
            window.addEventListener('online', () => { this.isOnline=true; this.restoreFocus(); });
            window.addEventListener('offline', () => { this.isOnline=false; this.restoreFocus(); });
            document.addEventListener('fullscreenchange', () => { this.isFullscreen=Boolean(document.fullscreenElement); this.restoreFocus(); });
            document.addEventListener('visibilitychange', () => { if (!document.hidden) this.restoreFocus(); });
            this.restoreFocus();
        },
        updateClock() { const now=new Date(); const timezone='Asia/Jakarta'; this.currentTime=now.toLocaleTimeString('id-ID',{timeZone:timezone,hour12:false,hour:'2-digit',minute:'2-digit',second:'2-digit'}); this.currentDate=now.toLocaleDateString('id-ID',{timeZone:timezone,weekday:'long',day:'2-digit',month:'long',year:'numeric'}); },
        toggleTheme() { this.isDark=!this.isDark; document.documentElement.classList.toggle('dark',this.isDark); localStorage.setItem('theme',this.isDark?'dark':'light'); this.restoreFocus(); },
        async toggleFullscreen() { try { if (document.fullscreenElement) await document.exitFullscreen(); else await document.documentElement.requestFullscreen(); } catch (_) { this.feedbackMsg='Layar penuh tidak dapat diaktifkan pada browser ini.'; } finally { this.restoreFocus(); } },
        restoreFocus() { if (this.isLocked) return; clearTimeout(this.focusTimer); this.focusTimer=setTimeout(() => { if (!this.isLocked && this.$refs.rfidInput) this.$refs.rfidInput.focus({preventScroll:true}); }, 40); },
        async processScan() {
            if (this.isLocked) { this.rfidCode=''; return; }
            const code=this.rfidCode.trim().toUpperCase(); this.rfidCode='';
            if (!code) { this.restoreFocus(); return; }
            this.scannerState='processing'; this.feedbackMsg='';
            try {
                const response=await fetch('{{ route("presensi.scan") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({kode_uid:code})});
                const data=await response.json().catch(() => ({}));
                if (data.action && data.action !== 'error') {
                    await this.showResult(data);
                } else if (data.action === 'error' && response.status < 500) {
                    await this.showError(data.message || 'Kartu tidak dikenali. Silakan coba kembali.');
                } else {
                    console.error('Kesalahan layanan presensi', {status:response.status, data});
                    await this.showError('Presensi belum dapat diproses. Silakan coba kembali atau hubungi petugas.');
                }
            } catch (error) {
                console.error('Layanan presensi tidak dapat dihubungi', error);
                await this.showError('Server tidak dapat dihubungi. Periksa koneksi jaringan.');
            }
            finally { this.resetScanner(); }
        },
        buildResultContent(data) {
            const card=document.createElement('div'); card.className='kiosk-result-card';
            const photo=document.createElement('div'); photo.className='kiosk-result-photo';
            if (data.foto_url) { const image=document.createElement('img'); image.src=data.foto_url; image.alt=`Foto ${data.nama || 'siswa'}`; photo.append(image); } else { photo.textContent='👤'; photo.setAttribute('aria-hidden','true'); }
            const details=document.createElement('div');
            const name=document.createElement('div'); name.className='kiosk-result-name'; name.textContent=data.nama || 'Siswa';
            const meta=document.createElement('div'); meta.className='kiosk-result-meta'; meta.textContent=`${data.kelas || '-'} · ${data.waktu || '-'}`;
            const status=document.createElement('div'); status.className='kiosk-result-status'; status.textContent=data.pesan || (data.status === 'terlambat' ? 'Presensi terlambat tercatat' : 'Presensi berhasil tercatat');
            details.append(name,meta,status); card.append(photo,details); return card;
        },
        async showResult(response) {
            const data=response.data; const action=response.action;
            this.scannerState=action==='terlalu_awal'?'error':'success'; this.feedbackMsg=response.message; this.resultData=data; this.modalActive=true;
            if (['masuk','pulang'].includes(action)) this.addToRecentLog({...data,waktu:action==='masuk'?data.waktu_masuk:data.waktu_pulang,status:action});
            const options={
                masuk:{icon:'success',title:'Presensi Masuk Berhasil'}, pulang:{icon:'success',title:'Presensi Pulang Berhasil'},
                terlalu_awal:{icon:'warning',title:'Belum Waktunya Pulang'}, lengkap:{icon:'info',title:'Presensi Hari Ini Sudah Lengkap'}
            }[action];
            await Swal.fire({icon:options.icon,title:options.title,html:this.buildResultContent({...data,waktu:data.waktu_pulang||data.waktu_masuk,pesan:response.message}),confirmButtonText:'Tutup',timer:2000,timerProgressBar:true,allowOutsideClick:false,customClass:{popup:'kiosk-result-popup'}});
        },
        async showError(message) {
            this.scannerState='error'; this.feedbackMsg=message; this.resultData=null; this.modalActive=true;
            await Swal.fire({icon:'error',title:'Presensi Gagal',text:message,confirmButtonText:'Tutup',timer:2000,timerProgressBar:true,allowOutsideClick:false,allowEscapeKey:true,customClass:{popup:'kiosk-result-popup'}});
        },
        addToRecentLog(data) { const scan={key:data.nis+'-'+Date.now(),id:data.nis,nama:data.nama,kelas:data.kelas,waktu:data.waktu,status:data.status,action:data.action,foto_url:data.foto_url}; this.recentScans.unshift(scan); this.recentScans=this.recentScans.slice(0,6); },
        resetScanner() { this.modalActive=false; this.scannerState='ready'; this.feedbackMsg=''; this.resultData=null; this.rfidCode=''; this.restoreFocus(); }
    }
}
</script>
@endsection
