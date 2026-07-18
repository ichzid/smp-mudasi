@extends('layouts.kiosk', ['title' => 'Kiosk Presensi RFID'])

@section('content')
<style>
  :root{
    --green:#0f8c47;
    --green-dark:#0a6833;
    --green-light:#e2f2e7;
    --ink:#0f172a;
    --ink-dim:#64748b;
    --bg:#f8fafc;
    --panel:#ffffff;
    --panel-alt:#f1f5f9;
    --border:#e2e8f0;
    --border-strong:#cbd5e1;
    --error:#ef4444;
    --error-bg:#fef2f2;
    --radius:16px;
    --font-ui:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }

  body{
    margin:0; padding:0;
    background:var(--bg) url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f8c47' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    font-family:var(--font-ui);
    color:var(--ink);
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:20px;
    box-sizing:border-box;
  }

  /* Custom overrides for dark mode */
  :root.dark {
    --bg: #0f172a;
    --panel: #1e293b;
    --panel-alt: #334155;
    --border: #334155;
    --ink: #f8fafc;
    --ink-dim: #94a3b8;
  }

  main.kiosk{
    position:relative;
    z-index:1;
    width:100%;
    max-width:1000px;
    display:grid;
    grid-template-columns: 1.35fr 1fr;
    gap:20px;
    align-items:start;
  }

  @media (max-width: 820px){
    main.kiosk{ grid-template-columns: 1fr; }
  }

  .topbar{
    grid-column: 1 / -1;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:2px 4px 6px;
    flex-wrap:wrap;
  }

  .school-id{
    display:flex;
    align-items:center;
    gap:12px;
  }

  .school-name{
    font-size:15px;
    font-weight:700;
    letter-spacing:.01em;
    line-height:1.25;
  }

  .school-sub{
    font-size:11px;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--ink-dim);
    margin-top:2px;
  }

  .time-panel{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
  .date-label{font-size:12px;color:var(--ink-dim);font-weight:600}
  .online-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--green-light);color:var(--green-dark);font-size:11px;font-weight:700}
  .online-dot{width:7px;height:7px;border-radius:999px;background:var(--green);box-shadow:0 0 0 3px rgba(15,140,71,.14)}
  .clock{
    font-family: monospace;
    font-size:16px;
    font-weight:bold;
    color:var(--green-dark);
    background:var(--green-light);
    border:1px solid var(--border-strong);
    padding:6px 12px;
    border-radius:9px;
    letter-spacing:.04em;
  }

  .scanner-card{
    background:var(--panel);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:34px 32px 28px;
    box-shadow: 0 24px 50px -28px rgba(10,50,25,0.22);
  }

  .scan-icon{
    width:54px; height:54px;
    border-radius:14px;
    background:linear-gradient(135deg, var(--green), var(--green-dark));
    display:flex; align-items:center; justify-content:center;
    margin-bottom:20px;
    box-shadow:0 0 0 6px var(--green-light);
  }

  .scanner-card h1{
    font-size:25px;
    font-weight:700;
    margin:0 0 6px;
    letter-spacing:-0.01em;
    color:var(--ink);
  }

  .sub{
    color:var(--ink-dim);
    font-size:14px;
    margin:0 0 24px;
    line-height:1.55;
    max-width:44ch;
  }

  .input-wrap{
    position:relative;
    overflow:hidden;
    border-radius:12px;
  }

  .input-wrap input{
    width:100%;
    background:var(--panel-alt);
    border:2px solid var(--border-strong);
    color:var(--ink);
    padding:16px 20px;
    font-size:20px;
    font-weight:600;
    border-radius:12px;
    outline:none;
    transition: .2s;
    font-family: monospace;
    letter-spacing: .08em;
  }

  .input-wrap input:focus{
    border-color:var(--green);
    background:var(--panel);
    box-shadow:0 0 0 4px var(--green-light);
  }

  .input-wrap input::placeholder{ color:#cbd5e1; }
  .dark .input-wrap input::placeholder { color:#475569; }
  .input-wrap.error input{border-color:var(--error);box-shadow:0 0 0 4px rgba(239,68,68,.1)}
  .scan-form{display:flex;gap:10px;align-items:stretch}
  .scan-form .input-wrap{flex:1}
  .scan-button{border:0;border-radius:12px;background:linear-gradient(135deg,var(--green),var(--green-dark));color:#fff;padding:0 20px;font-size:14px;font-weight:700;cursor:pointer;min-width:112px;transition:.2s;box-shadow:0 10px 24px -12px rgba(15,140,71,.7)}
  .scan-button:hover{transform:translateY(-1px)}
  .scan-button:disabled{opacity:.55;cursor:not-allowed;transform:none}
  .helper-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:10px;color:var(--ink-dim);font-size:11.5px}
  .key-hint{padding:3px 7px;border:1px solid var(--border-strong);border-bottom-width:2px;border-radius:6px;background:var(--panel-alt);font-family:monospace;font-weight:700}

  .scan-line{
    position:absolute;
    top:0; bottom:0; left:-30%;
    width:20px;
    background:linear-gradient(90deg, transparent, rgba(15,140,71,0.2), transparent);
    pointer-events:none;
    display:none;
  }

  .input-wrap.scanning .scan-line{
    display:block;
    animation:sweep .6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
  }

  @keyframes sweep{
    from{ left:-30%; }
    to{ left:100%; }
  }

  .feedback{
    min-height:20px;
    margin-top:14px;
    font-size:13.5px;
    text-align:center;
    font-weight:500;
  }

  .feedback.error{ color:var(--error); }

  .log-panel{
    background:var(--panel);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:24px 22px;
    max-height:430px;
    overflow-y:auto;
    box-shadow: 0 24px 50px -28px rgba(10,50,25,0.22);
  }

  .log-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
  .log-panel h2{
    font-size:14.5px;
    margin:0;
    color:var(--ink-dim);
    font-weight:600;
    letter-spacing:.02em;
  }
  .log-count{display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:26px;padding:0 8px;border-radius:999px;background:var(--panel-alt);color:var(--ink-dim);font-size:11px;font-weight:700}

  .log-list{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; }

  .log-list .empty{
    color:#A9BFB1;
    font-size:13.5px;
    text-align:center;
    padding:30px 0;
  }

  .log-item{
    display:flex;
    align-items:center;
    gap:12px;
    background:var(--panel-alt);
    border:1px solid var(--border);
    border-radius:12px;
    padding:10px 12px;
    animation: fadeIn .3s ease;
  }

  @keyframes fadeIn{ from{ opacity:0; transform:translateY(-6px);} to{opacity:1; transform:translateY(0);} }

  .log-item img{
    width:42px; height:42px;
    border-radius:9px;
    object-fit:cover;
    background:var(--border);
  }

  .log-item > div{ flex:1; min-width:0; }
  
  .log-item .li-name{
    font-size:13.5px;
    font-weight:600;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    color:var(--ink);
  }

  .log-item .li-meta{
    font-size:11.5px;
    color:var(--ink-dim);
    margin-top:2px;
  }

  .log-item .li-status{
    font-size:10px;
    font-weight:700;
    padding:4px 8px;
    border-radius:6px;
    letter-spacing:.05em;
  }

  .li-status.hadir{ background:var(--green-light); color:var(--green-dark); }
  .li-status.terlambat{ background:#fef3c7; color:#b45309; }

  @media (max-width: 640px){
    body{padding:12px;justify-content:flex-start}
    main.kiosk{gap:14px}
    .topbar{padding-top:4px}
    .school-name{font-size:13px}
    .school-sub{font-size:9px}
    .sun-mark{width:34px;height:34px}
    .time-panel{justify-content:flex-start;width:100%;padding-left:46px}
    .date-label{width:100%}
    .scanner-card{padding:26px 20px 22px}
    .scanner-card h1{font-size:21px}
    .scan-form{flex-direction:column}
    .scan-button{height:48px;width:100%}
    .helper-row{align-items:flex-start}
    .log-panel{padding:20px 16px;max-height:none}
  }

  /* Modal Override to hide TailAdmin's default modal when using this */
  [x-cloak] { display: none !important; }
</style>

<main class="kiosk" x-data="kioskData()" x-init="init()">
    <div class="topbar">
      <div class="school-id">
        <svg class="sun-mark" width="40" height="40" viewBox="0 0 40 40">
          <circle cx="20" cy="20" r="18" fill="var(--green)"/>
          <circle cx="20" cy="20" r="7" fill="none" stroke="#fff" stroke-width="2.5"/>
          <g stroke="#fff" stroke-width="2" stroke-linecap="round">
            <g>
              <line x1="20" y1="4" x2="20" y2="8.5"/>
              <line x1="20" y1="36" x2="20" y2="31.5"/>
              <line x1="4" y1="20" x2="8.5" y2="20"/>
              <line x1="36" y1="20" x2="31.5" y2="20"/>
            </g>
            <g>
              <line x1="8.7" y1="8.7" x2="11.9" y2="11.9"/>
              <line x1="31.3" y1="31.3" x2="28.1" y2="28.1"/>
              <line x1="8.7" y1="31.3" x2="11.9" y2="28.1"/>
              <line x1="31.3" y1="8.7" x2="28.1" y2="11.9"/>
            </g>
            <g>
              <line x1="13.5" y1="3.6" x2="15.6" y2="9.1"/>
              <line x1="24.4" y1="30.9" x2="26.5" y2="36.4"/>
              <line x1="3.6" y1="26.5" x2="9.1" y2="24.4"/>
              <line x1="30.9" y1="15.6" x2="36.4" y2="13.5"/>
            </g>
          </g>
        </svg>
        <div>
          <div class="school-name">SMP MUHAMMADIYAH<br/>DANAU SIJABUT</div>
          <div class="school-sub">Sistem Presensi Digital</div>
        </div>
      </div>
      <div class="time-panel">
        <span class="online-badge"><span class="online-dot"></span>Sistem siap</span>
        <span class="date-label" x-text="currentDate">Memuat tanggal...</span>
        <span class="clock" x-text="currentTime">--:--:--</span>
      </div>
    </div>
  
    <section class="scanner-card">
      <div class="scan-icon" aria-hidden="true">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
          <path d="M4 8V6a2 2 0 0 1 2-2h2M18 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M6 20H4a2 2 0 0 1-2-2v-2" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
          <rect x="9" y="9" width="6" height="6" rx="1.4" stroke="white" stroke-width="1.8"/>
        </svg>
      </div>
      <h1>Tempelkan Kartu Pelajar</h1>
      <p class="sub">Untuk simulasi, ketik kode unik kartu lalu tekan <strong>Enter</strong>. Nantinya kolom ini akan otomatis terisi saat kartu RFID ditempelkan ke alat pembaca.</p>
  
      <form @submit.prevent="processScan()" class="scan-form">
        <div class="input-wrap" :class="isProcessing ? 'scanning' : (isError ? 'error' : '')">
          <input type="text" x-ref="rfidInput" x-model="rfidCode" placeholder="Masukkan kode UID kartu" autocomplete="off" :disabled="isProcessing" aria-label="Kode UID kartu RFID" />
          <div class="scan-line"></div>
        </div>
        <button type="submit" class="scan-button" :disabled="isProcessing || !rfidCode.trim()" x-text="isProcessing ? 'Memproses...' : 'Catat hadir'">Catat hadir</button>
      </form>
      <div class="helper-row">
        <span>Input otomatis kembali aktif setelah presensi selesai.</span>
        <span>Tekan <span class="key-hint">Enter</span></span>
      </div>
      
      <div class="feedback" :class="isError ? 'error' : ''" role="status" aria-live="polite" x-text="feedbackMsg"></div>
    </section>
  
    <aside class="log-panel">
      <div class="log-heading">
        <div>
          <h2>Presensi Terakhir</h2>
          <p class="mt-1 text-xs text-gray-400">Riwayat selama halaman ini terbuka</p>
        </div>
        <span class="log-count" x-text="recentScans.length">0</span>
      </div>
      <ul class="log-list">
        <template x-if="recentScans.length === 0">
            <li class="empty">Belum ada presensi hari ini</li>
        </template>
        
        <template x-for="scan in recentScans" :key="scan.id">
            <li class="log-item">
                <template x-if="scan.foto_url">
                    <img :src="scan.foto_url" :alt="scan.nama" />
                </template>
                <template x-if="!scan.foto_url">
                    <div class="flex items-center justify-center w-[42px] h-[42px] rounded-lg bg-gray-200 dark:bg-gray-600">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                </template>
                <div>
                    <div class="li-name" x-text="scan.nama"></div>
                    <div class="li-meta" x-text="scan.kelas + ' · ' + scan.waktu"></div>
                </div>
                <span class="li-status" :class="scan.status === 'hadir' ? 'hadir' : 'terlambat'" x-text="scan.status === 'hadir' ? 'HADIR' : 'TERLAMBAT'"></span>
            </li>
        </template>
      </ul>
    </aside>

    <!-- Modal Success Override -->
    <x-presensi-result-modal />
</main>

<script>
    function kioskData() {
        return {
            currentTime: '--:--:--',
            currentDate: '',
            rfidCode: '',
            isProcessing: false,
            isError: false,
            feedbackMsg: '',
            
            showResult: false,
            resultData: null,
            resultType: 'success',
            resultMessage: '',
            timer: null,
            
            recentScans: [],
            
            init() {
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
                
                // Keep focus on input
                document.addEventListener('click', () => {
                    if (!this.showResult && !this.isProcessing) this.focusInput();
                });
                
                this.focusInput();
            },
            
            updateClock() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
            },
            
            focusInput() {
                setTimeout(() => {
                    if (this.$refs.rfidInput) {
                        this.$refs.rfidInput.focus();
                    }
                }, 100);
            },
            
            async processScan() {
                if (this.rfidCode.trim() === '' || this.isProcessing) return;
                
                const codeToProcess = this.rfidCode.trim().toUpperCase();
                this.rfidCode = ''; // Clear for next scan immediately
                this.isProcessing = true;
                this.isError = false;
                this.feedbackMsg = '';
                
                try {
                    const response = await fetch('{{ route("presensi.scan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ kode_uid: codeToProcess })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        this.showSuccess(data.data);
                        this.addToRecentLog(data.data);
                    } else {
                        this.showError(data.message || 'Kartu tidak dikenali.');
                    }
                } catch (error) {
                    this.showError('Gagal terhubung ke server.');
                } finally {
                    this.isProcessing = false;
                }
            },
            
            showSuccess(data) {
                this.resultData = data;
                this.resultType = 'success';
                this.resultMessage = data.pesan;
                this.feedbackMsg = data.pesan;
                this.displayModal();
            },
            
            showError(message) {
                this.isError = true;
                this.feedbackMsg = message;
                setTimeout(() => {
                    this.isError = false;
                }, 400);
                this.focusInput();
            },
            
            addToRecentLog(data) {
                const newScan = {
                    id: data.nis,
                    nama: data.nama,
                    kelas: data.kelas,
                    waktu: data.waktu,
                    status: data.status,
                    foto_url: data.foto_url
                };

                this.recentScans = this.recentScans.filter(scan => scan.id !== newScan.id);
                this.recentScans.unshift(newScan);
                if(this.recentScans.length > 6) {
                    this.recentScans.pop();
                }
            },
            
            displayModal() {
                this.showResult = true;
                
                // Auto close after 3 seconds
                if (this.timer) clearTimeout(this.timer);
                
                this.timer = setTimeout(() => {
                    this.closeModal();
                }, 3500);
            },
            
            closeModal() {
                this.showResult = false;
                this.focusInput();
            }
        }
    }
</script>
@endsection