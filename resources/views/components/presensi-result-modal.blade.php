<!-- resources/views/components/presensi-result-modal.blade.php -->
<style>
    .result-modal-card { will-change: transform, opacity; }
    .swal-success-icon { position:relative; width:80px; height:80px; border:4px solid #a5dc86; border-radius:50%; animation:swalIconPop .5s cubic-bezier(.175,.885,.32,1.275) both; }
    .swal-success-line { position:absolute; display:block; height:5px; border-radius:3px; background:#a5dc86; }
    .swal-success-line-tip { width:25px; left:14px; top:46px; transform:rotate(45deg); animation:swalTip .75s .2s ease both; }
    .swal-success-line-long { width:47px; right:8px; top:38px; transform:rotate(-45deg); animation:swalLong .75s .2s ease both; }
    .result-content-rise { animation: resultContentRise .45s .18s ease-out both; }
    @keyframes swalIconPop { 0%{opacity:0;transform:rotate(-45deg) scale(.35)} 60%{opacity:1;transform:rotate(0) scale(1.08)} 100%{transform:scale(1)} }
    @keyframes swalTip { 0%{width:0;left:1px;top:19px} 54%{width:0;left:1px;top:19px} 70%{width:50px;left:-8px;top:37px} 84%{width:17px;left:21px;top:48px} 100%{width:25px;left:14px;top:46px} }
    @keyframes swalLong { 0%{width:0;right:46px;top:54px} 65%{width:0;right:46px;top:54px} 84%{width:55px;right:0;top:35px} 100%{width:47px;right:8px;top:38px} }
    @keyframes resultContentRise { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    @media (prefers-reduced-motion: reduce) { .swal-success-icon,.swal-success-line,.result-content-rise { animation-duration:.01ms!important; animation-delay:0ms!important; } }
</style>
<div x-show="showResult"
     x-cloak
     role="dialog"
     aria-modal="true"
     :aria-label="resultType === 'success' ? 'Hasil presensi berhasil' : 'Hasil presensi gagal'"
     @click.self="closeModal()"
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/65 p-4 backdrop-blur-md transition-opacity"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
     
    <!-- Success Card -->
    <template x-if="resultType === 'success' && resultData">
        <div class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden transform transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
             
            <div class="absolute top-0 left-0 h-2 w-full bg-green-500"></div>
                 
            <div class="p-6 pt-8 text-center">
                <!-- Status Icon -->
                <div class="swal-success-icon mx-auto mb-6" aria-hidden="true">
                    <span class="swal-success-line swal-success-line-tip"></span>
                    <span class="swal-success-line swal-success-line-long"></span>
                </div>
                
                <!-- Profile Image -->
                <div class="result-content-rise mx-auto h-24 w-24 rounded-full border-4 border-white dark:border-gray-800 shadow-md overflow-hidden mb-4 bg-gray-100 dark:bg-gray-700">
                    <template x-if="resultData.foto_url">
                        <img :src="resultData.foto_url" alt="Foto Siswa" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!resultData.foto_url">
                        <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </template>
                </div>
                
                <!-- Info -->
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="resultData.nama"></h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1" x-text="resultData.kelas"></p>
                
                <div class="mt-6 py-3 px-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Waktu Scan:</span>
                    <span class="text-lg font-bold font-mono text-gray-900 dark:text-white" x-text="resultData.waktu"></span>
                </div>
                
                <p class="mt-4 text-sm font-medium text-green-600 dark:text-green-400"
                   x-text="resultMessage">
                </p>
            </div>
        </div>
    </template>
    
    <!-- Error Card -->
    <template x-if="resultType === 'error'">
        <div class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden transform transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
             
            <div class="absolute top-0 left-0 w-full h-2 bg-red-500"></div>
            
            <div class="p-6 pt-8 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 text-red-500 dark:bg-red-900/30 mb-6">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Presensi Gagal</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6" x-text="resultMessage"></p>
                
                <button @click="closeModal()" class="w-full inline-flex justify-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Tutup
                </button>
            </div>
        </div>
    </template>
</div>