<!-- resources/views/components/presensi-result-modal.blade.php -->
<div x-show="showResult" 
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm transition-opacity"
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
             
            <div class="absolute top-0 left-0 w-full h-2" 
                 :class="resultData.status === 'hadir' ? 'bg-green-500' : 'bg-yellow-500'"></div>
                 
            <div class="p-6 pt-8 text-center">
                <!-- Status Icon -->
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6"
                     :class="resultData.status === 'hadir' ? 'bg-green-100 text-green-500 dark:bg-green-900/30' : 'bg-yellow-100 text-yellow-500 dark:bg-yellow-900/30'">
                    
                    <svg x-show="resultData.status === 'hadir'" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    
                    <svg x-show="resultData.status === 'terlambat'" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <!-- Profile Image -->
                <div class="mx-auto h-24 w-24 rounded-full border-4 border-white dark:border-gray-800 shadow-md overflow-hidden mb-4 bg-gray-100 dark:bg-gray-700">
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
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1" x-text="resultData.nis + ' • ' + resultData.kelas"></p>
                
                <div class="mt-6 py-3 px-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Waktu Scan:</span>
                    <span class="text-lg font-bold font-mono text-gray-900 dark:text-white" x-text="resultData.waktu"></span>
                </div>
                
                <p class="mt-4 text-sm font-medium" 
                   :class="resultData.status === 'hadir' ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'"
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