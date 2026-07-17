@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb 
            pageTitle="Tambah Anggota Rombel" 
            pageSubtitle="Pilih siswa untuk ditambahkan ke rombongan belajar ini." 
            :breadcrumbs="[
                ['label' => 'Manajemen Rombel Siswa', 'url' => route('rombel-siswa.index', ['tahun_ajaran_id' => $rombel->tahun_ajaran_id, 'rombel_id' => $rombel->id])]
            ]"
        />

        <div class="mb-6 bg-brand-50 dark:bg-brand-900/20 p-5 rounded-2xl border border-brand-100 dark:border-brand-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-brand-600 dark:text-brand-400 mb-1">Menambahkan siswa ke:</p>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $rombel->nama_rombel }} (Kelas {{ $rombel->tingkat }})</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tahun Ajaran: {{ $rombel->tahunAjaran->nama }} - Semester {{ $rombel->tahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }}</p>
            </div>
            <a href="{{ route('rombel-siswa.index', ['tahun_ajaran_id' => $rombel->tahun_ajaran_id, 'rombel_id' => $rombel->id]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
                Batal & Kembali
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('rombel-siswa.store') }}" method="POST" id="tambahAnggotaForm">
                @csrf
                <input type="hidden" name="rombel_id" value="{{ $rombel->id }}">
                
                <div class="mb-6 w-full sm:w-1/3">
                    <label for="tanggal_masuk" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Masuk (Efektif) <span class="text-red-500">*</span></label>
                    <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('tanggal_masuk')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Pilih Siswa</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Daftar siswa di bawah adalah siswa yang belum terdaftar di rombel manapun pada tahun ajaran ini.</p>
                    </div>
                    
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchSiswa" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 pl-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="Cari nama siswa...">
                    </div>
                </div>
                
                @error('siswa_id')
                    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm dark:bg-red-900/30 dark:text-red-400">
                        Anda harus memilih minimal satu siswa.
                    </div>
                @enderror

                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-8">
                    <div class="max-h-[500px] overflow-y-auto">
                        <table class="w-full text-left border-collapse" id="siswaTable">
                            <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 shadow-sm">
                                <tr>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-12">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="selectAll" class="w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 dark:focus:ring-brand-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </div>
                                    </th>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-32">NISN</th>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Nama Siswa</th>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-24">L/P</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900/30">
                                @forelse($siswas as $siswa)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors siswa-row cursor-pointer" onclick="toggleCheckbox('checkbox-{{ $siswa->id }}')">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center">
                                                <input type="checkbox" id="checkbox-{{ $siswa->id }}" name="siswa_id[]" value="{{ $siswa->id }}" class="siswa-checkbox w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 dark:focus:ring-brand-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" onclick="event.stopPropagation()">
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-900 dark:text-gray-300 font-medium">
                                            {{ $siswa->nisn }}
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-900 dark:text-white siswa-name">
                                            {{ $siswa->nama_lengkap }}
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $siswa->jenis_kelamin }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Semua siswa sudah terdaftar di rombel pada tahun ajaran ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-800">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span id="selectedCount" class="font-bold text-brand-600 dark:text-brand-400">0</span> siswa dipilih
                    </div>
                    <button type="submit" id="btnSubmit" disabled class="px-6 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan Anggota Rombel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Checkbox & Search -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.siswa-checkbox');
            const selectedCountLabel = document.getElementById('selectedCount');
            const btnSubmit = document.getElementById('btnSubmit');
            const searchInput = document.getElementById('searchSiswa');
            
            function updateCount() {
                const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
                selectedCountLabel.textContent = checked;
                
                if (checked > 0) {
                    btnSubmit.removeAttribute('disabled');
                } else {
                    btnSubmit.setAttribute('disabled', 'disabled');
                }
                
                // Update selectAll status
                const visibleCheckboxes = document.querySelectorAll('tr:not(.hidden) .siswa-checkbox').length;
                const visibleChecked = document.querySelectorAll('tr:not(.hidden) .siswa-checkbox:checked').length;
                
                if(visibleCheckboxes > 0 && visibleCheckboxes === visibleChecked) {
                    selectAll.checked = true;
                } else {
                    selectAll.checked = false;
                }
            }

            // Select All logic
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('tr:not(.hidden) .siswa-checkbox').forEach(cb => {
                        cb.checked = isChecked;
                    });
                    updateCount();
                });
            }

            // Individual checkbox logic
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function(e) {
                    e.stopPropagation(); // prevent row click from firing twice
                    updateCount();
                });
            });

            // Search logic
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.siswa-row');
                    
                    rows.forEach(row => {
                        const name = row.querySelector('.siswa-name').textContent.toLowerCase();
                        if (name.includes(searchTerm)) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                    updateCount();
                });
            }
        });

        // Function for row click
        function toggleCheckbox(id) {
            const cb = document.getElementById(id);
            cb.checked = !cb.checked;
            // trigger change event for the counter update
            cb.dispatchEvent(new Event('change'));
        }
    </script>
@endsection