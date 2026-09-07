@extends('layouts.kiosk', ['title' => 'Absen Kartu RFID'])

@section('content')
<style>
    :root {
        --ink: #17213b; --muted: #68728a; --canvas: #f5f7fc; --panel: rgba(255, 255, 255, .94);
        --line: #dfe5f0; --accent: #3858a8; --accent-deep: #29417f; --accent-soft: #edf1fb;
        --success: #18805b; --danger: #c63e55; --warning: #b87522;
        --shadow: 0 20px 54px rgba(38, 56, 99, .09);
    }
    :root.dark {
        --ink: #eef2f8; --muted: #a0aabd; --canvas: #101622; --panel: rgba(24, 31, 45, .94);
        --line: #303b4e; --accent: #9bb6f5; --accent-deep: #c8d6ff; --accent-soft: #222e48;
        --success: #67cea3; --danger: #ff91a5; --warning: #efba76;
        --shadow: 0 22px 60px rgba(0, 0, 0, .28);
    }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; overflow-x: hidden; background: var(--canvas); color: var(--ink); }
    body::before { content: ""; position: fixed; inset: 0; pointer-events: none; background: radial-gradient(circle at 8% 8%, rgba(252, 195, 78, .14), transparent 27%), radial-gradient(circle at 92% 12%, rgba(54, 93, 181, .13), transparent 30%); }
    body::after { content: ""; position: fixed; right: 4vw; bottom: 4vh; width: 112px; height: 112px; pointer-events: none; opacity: .24; background-image: radial-gradient(circle, var(--accent) 1.4px, transparent 1.5px); background-size: 14px 14px; mask-image: linear-gradient(135deg, transparent 4%, #000 55%, transparent 100%); }
    [x-cloak] { display: none !important; }
    .sr-scanner { position: fixed; width: 1px; height: 1px; opacity: 0; pointer-events: none; left: -10px; top: -10px; }
    .shell { width: min(1360px, 100%); min-height: 100svh; margin: auto; padding: clamp(20px, 3vw, 40px); display: flex; flex-direction: column; gap: clamp(20px, 3vw, 32px); }
    .topbar { display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .brand { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .brand-mark { width: 58px; height: 58px; flex: none; }
    .brand-mark img { width: 100%; height: 100%; object-fit: contain; }
    .brand-name { font-size: clamp(14px, 1.5vw, 18px); font-weight: 760; line-height: 1.18; letter-spacing: -.015em; }
    .brand-sub { margin-top: 5px; color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .datetime { display: flex; align-items: center; gap: 18px; text-align: right; }
    .clock { font-size: clamp(24px, 3vw, 36px); font-weight: 720; line-height: 1; letter-spacing: -.04em; font-variant-numeric: tabular-nums; }
    .date { margin-top: 5px; color: var(--muted); font-size: 12px; font-weight: 550; }
    .top-actions { display: flex; align-items: center; gap: 8px; }
    .connection, .icon-button { height: 40px; border: 1px solid var(--line); background: var(--panel); }
    .connection { display: inline-flex; align-items: center; gap: 8px; padding: 0 12px; border-radius: 10px; color: var(--success); font-size: 11px; font-weight: 700; }
    .connection-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .connection.offline { color: var(--danger); }
    .icon-button { width: 40px; display: grid; place-items: center; border-radius: 10px; color: var(--ink); cursor: pointer; transition: border-color .15s ease, color .15s ease; }
    .icon-button:hover { color: var(--accent); border-color: var(--accent); }
    .icon-button:focus-visible { outline: 3px solid var(--accent-soft); outline-offset: 2px; }
    .content { flex: 1; min-height: 0; display: grid; grid-template-columns: minmax(0, 2fr) minmax(300px, .78fr); gap: clamp(18px, 2.2vw, 28px); }
    .scanner, .recent { border: 1px solid var(--line); border-radius: 24px; background: var(--panel); box-shadow: var(--shadow); backdrop-filter: blur(16px); }
    .scanner { position: relative; min-height: 540px; overflow: hidden; display: flex; align-items: center; justify-content: center; padding: 86px 42px 74px; text-align: center; }
    .scanner::before { content: ""; position: absolute; width: 96px; height: 96px; top: 82px; right: 42px; border: 18px solid var(--accent-soft); border-radius: 50%; opacity: .72; pointer-events: none; }
    .scanner::after { content: ""; position: absolute; width: 70px; height: 70px; left: 42px; bottom: 76px; border: 1px solid color-mix(in srgb, var(--accent) 24%, var(--line)); border-radius: 20px; transform: rotate(18deg); opacity: .65; pointer-events: none; }
    .scanner-ornament { position: absolute; z-index: 0; pointer-events: none; }
    .scanner-ornament.lines { top: 42%; left: 42px; width: 52px; height: 52px; border: 1px solid color-mix(in srgb, var(--accent) 22%, transparent); border-radius: 50%; opacity: .55; background: radial-gradient(circle at center, transparent 38%, var(--accent-soft) 39% 52%, transparent 53%); }
    .scanner-ornament.arc { right: 48px; bottom: 76px; width: 62px; height: 62px; border: 1px solid color-mix(in srgb, var(--accent) 26%, transparent); border-radius: 50%; opacity: .6; }
    .scanner-ornament.arc::before { content: ""; position: absolute; inset: 12px; border: 1px solid color-mix(in srgb, var(--warning) 38%, transparent); border-radius: 50%; }
    .scanner-status { position: absolute; z-index: 2; top: 24px; left: 24px; display: inline-flex; align-items: center; gap: 9px; padding: 9px 13px; border: 1px solid var(--line); border-radius: 999px; background: var(--panel); color: var(--muted); font-size: 12px; font-weight: 700; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); box-shadow: 0 0 0 4px color-mix(in srgb, var(--success) 13%, transparent); }
    .scanner.processing .status-dot { background: var(--warning); animation: pulse 1s infinite; }
    .scanner.error .status-dot { background: var(--danger); }
    .feature-pills { position: absolute; z-index: 2; top: 20px; right: 20px; display: flex; gap: 6px; }
    .feature-pill { min-height: 32px; display: inline-flex; align-items: center; gap: 6px; padding: 0 10px; border: 1px solid color-mix(in srgb, var(--accent) 18%, var(--line)); border-radius: 999px; background: var(--accent-soft); color: var(--accent-deep); font-size: 10px; font-weight: 700; }
    .feature-pill svg { width: 13px; height: 13px; }
    .scanner-view { position: relative; z-index: 1; width: 100%; display: flex; flex-direction: column; align-items: center; }
    .tap-visual { position: relative; width: clamp(176px, 21vw, 244px); aspect-ratio: 1; display: grid; place-items: center; margin-bottom: 32px; }
    .ring { position: absolute; inset: 0; border: 1px solid color-mix(in srgb, var(--accent) 22%, transparent); border-radius: 50%; }
    .ring:nth-child(2) { inset: 14%; background: var(--accent-soft); border-color: color-mix(in srgb, var(--accent) 16%, transparent); }
    .ring:nth-child(3) { inset: 31%; background: var(--panel); border-color: var(--line); box-shadow: 0 15px 38px rgba(38, 56, 99, .13); }
    .scanner.ready .ring:first-child { animation: breathe 3s ease-in-out infinite; }
    .scanner.processing .ring { animation: scan 1s ease-out infinite; }
    .scanner.success .ring:nth-child(2) { background: color-mix(in srgb, var(--success) 16%, transparent); }
    .scanner.error .ring:nth-child(2) { background: color-mix(in srgb, var(--danger) 12%, transparent); }
    .rfid-icon { position: relative; z-index: 1; width: 58px; height: 58px; color: var(--accent); }
    .scanner.error .rfid-icon { color: var(--danger); }
    h1 { margin: 0; font-size: clamp(28px, 3.2vw, 44px); line-height: 1.08; letter-spacing: -.04em; }
    .instruction { max-width: 510px; margin: 14px auto 0; color: var(--muted); font-size: clamp(14px, 1.35vw, 16px); line-height: 1.65; }
    .scanner-foot { position: absolute; right: 24px; bottom: 20px; left: 24px; display: flex; justify-content: space-between; gap: 16px; padding-top: 15px; border-top: 1px solid var(--line); color: var(--muted); font-size: 10px; letter-spacing: .03em; }
    .scanner-foot strong { color: var(--ink); font-weight: 650; }
    .recent { min-height: 0; padding: 26px; display: flex; flex-direction: column; }
    .recent-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding-bottom: 20px; border-bottom: 1px solid var(--line); }
    .recent h2 { margin: 0; font-size: 17px; letter-spacing: -.02em; }
    .recent-desc { margin: 5px 0 0; color: var(--muted); font-size: 11px; }
    .count { min-width: 28px; height: 28px; padding: 0 8px; display: grid; place-items: center; border-radius: 8px; color: var(--accent); background: var(--accent-soft); font-size: 11px; font-weight: 750; }
    .log-list { list-style: none; margin: 0; padding: 14px 0 0; display: flex; flex-direction: column; gap: 9px; overflow: auto; }
    .empty { flex: 1; min-height: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--muted); text-align: center; font-size: 12px; line-height: 1.6; }
    .empty svg { width: 38px; margin-bottom: 12px; opacity: .42; }
    .log-item { display: grid; grid-template-columns: 44px minmax(0, 1fr); align-items: center; gap: 11px; padding: 10px; border: 1px solid var(--line); border-radius: 12px; background: color-mix(in srgb, var(--panel) 88%, var(--accent-soft)); animation: enter .25s ease-out; }
    .avatar { width: 44px; height: 44px; overflow: hidden; display: grid; place-items: center; border-radius: 10px; object-fit: cover; background: var(--accent-soft); color: var(--accent); }
    .avatar svg { width: 21px; }
    .li-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; font-weight: 720; }
    .li-meta { margin-top: 4px; color: var(--muted); font-size: 10px; }
    .kiosk-result-popup { border-radius: 18px; background: var(--panel); color: var(--ink); }
    .kiosk-result-popup .swal2-title, .kiosk-result-popup .swal2-html-container { color: var(--ink); }
    .kiosk-result-card { display: grid; grid-template-columns: 68px minmax(0, 1fr); gap: 15px; align-items: center; margin-top: 8px; padding: 15px; border: 1px solid var(--line); border-left: 4px solid var(--success); border-radius: 14px; text-align: left; }
    .kiosk-result-photo { width: 68px; height: 68px; overflow: hidden; display: grid; place-items: center; border-radius: 12px; background: var(--accent-soft); color: var(--accent); font-size: 30px; }
    .kiosk-result-photo img { width: 100%; height: 100%; object-fit: cover; }
    .kiosk-result-name { overflow: hidden; text-overflow: ellipsis; font-size: 19px; font-weight: 760; }
    .kiosk-result-meta { margin-top: 4px; color: var(--muted); font-size: 12px; }
    .kiosk-result-status { margin-top: 9px; color: var(--success); font-size: 12px; font-weight: 700; }
    @keyframes breathe { 50% { transform: scale(.96); opacity: .5; } }
    @keyframes scan { from { transform: scale(.65); opacity: 1; } to { transform: scale(1.04); opacity: 0; } }
    @keyframes pulse { 50% { opacity: .35; } }
    @keyframes enter { from { opacity: 0; transform: translateY(-5px); } }
    @media (max-width: 900px) {
        .content { grid-template-columns: 1fr; }
        .scanner { min-height: 500px; }
        .recent { min-height: 290px; }
        .log-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); overflow: visible; }
        .empty { grid-column: 1 / -1; min-height: 170px; }
    }
    @media (max-width: 620px) {
        body { overflow: hidden; }
        .shell { height: 100svh; min-height: 100svh; padding: 14px; gap: 14px; }
        .topbar { flex: none; align-items: flex-start; gap: 10px; }
        .content { flex: 1; min-height: 0; }
        .recent { display: none; }
        .brand { gap: 9px; }
        .brand-mark { width: 46px; height: 46px; }
        .brand-name { max-width: 130px; font-size: 11px; }
        .brand-sub, .date, .connection span:last-child { display: none; }
        .datetime { gap: 7px; }
        .clock { font-size: 19px; }
        .top-actions { gap: 5px; }
        .connection { width: 36px; height: 36px; padding: 0; justify-content: center; }
        .icon-button { width: 36px; height: 36px; }
        .scanner { width: 100%; height: 100%; min-height: 0; padding: 74px 18px 62px; border-radius: 16px; }
        body::after { right: 12px; bottom: 12px; width: 72px; height: 72px; opacity: .12; }
        .scanner::before { width: 58px; height: 58px; top: 76px; right: 18px; border-width: 11px; opacity: .5; }
        .scanner::after { width: 44px; height: 44px; left: 20px; bottom: 64px; border-radius: 13px; opacity: .42; }
        .scanner-ornament.lines { left: 18px; width: 38px; height: 38px; opacity: .38; }
        .scanner-ornament.arc { right: 20px; bottom: 62px; width: 42px; height: 42px; opacity: .42; }
        .scanner-ornament.arc::before { inset: 8px; }
        .scanner-status { top: 20px; left: 18px; }
        .feature-pills { top: 16px; right: 16px; }
        .feature-pill { width: 32px; padding: 0; justify-content: center; }
        .feature-pill span { display: none; }
        .tap-visual { width: min(176px, 50vw); margin-bottom: 24px; }
        .scanner-foot { right: 18px; bottom: 16px; left: 18px; justify-content: center; }
        .scanner-foot span:last-child { display: none; }
        .recent { padding: 20px; border-radius: 16px; }
        .log-list { grid-template-columns: 1fr; }
        .empty { min-height: 145px; }
        .kiosk-result-card { grid-template-columns: 56px minmax(0, 1fr); gap: 11px; padding: 12px; }
        .kiosk-result-photo { width: 56px; height: 56px; }
    }
    @media (max-width: 380px) {
        .brand-name { max-width: 105px; }
        .feature-pills .feature-pill:last-child { display: none; }
    }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; } }
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
            <span class="scanner-ornament lines" aria-hidden="true"></span>
            <span class="scanner-ornament arc" aria-hidden="true"></span>
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
