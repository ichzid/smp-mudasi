<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | SMP Muhammadiyah Danau Sijabut</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/smp_mudasi.png') }}">
    <!-- DataTables Overrides untuk Tailwind Theme -->
    <style>
        /* Sembunyikan default border dari DataTables */
        table.dataTable.no-footer {
            border-bottom: none !important;
        }
        
        /* Style Search Input */
        .dataTables_filter {
            margin-bottom: 0 !important;
        }
        
        .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #374151 !important; /* text-gray-700 */
        }
        
        .dark .dataTables_filter label {
            color: #9ca3af !important; /* text-gray-400 */
        }
        
        /* Search and Length inputs match TailAdmin forms */
        .dataTables_filter input {
            border-radius: 0.5rem !important; /* rounded-lg */
            border: 1px solid #d1d5db !important; /* border-gray-300 */
            padding: 0.5rem 1rem !important; /* Lebih lega seperti input TailAdmin */
            font-size: 0.875rem !important; /* text-sm */
            line-height: 1.25rem !important;
            outline: none !important;
            background-color: transparent !important;
            margin-left: 0.5rem !important;
            transition: all 0.2s;
            box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05) !important; /* shadow-theme-xs */
        }
        
        .dark .dataTables_filter input {
            border-color: #374151 !important; /* border-gray-700 */
            color: #f3f4f6 !important; /* text-gray-100 */
            background-color: #1a2231 !important; /* bg-gray-dark */
        }
        
        .dataTables_filter input:focus {
            border-color: #465fff !important; /* focus:border-brand-500 */
            box-shadow: 0px 0px 0px 4px rgba(70, 95, 255, 0.12) !important; /* shadow-focus-ring TailAdmin */
        }

        /* Style Length Menu (Show X entries) */
        .dataTables_length {
            margin-bottom: 0 !important;
        }
        
        .dataTables_length label {
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #374151 !important;
        }
        
        .dark .dataTables_length label {
            color: #9ca3af !important;
        }

        .dataTables_length select {
            border-radius: 0.5rem !important;
            border: 1px solid #d1d5db !important;
            padding: 0.375rem 2rem 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
            outline: none !important;
            background-color: transparent !important;
            transition: all 0.2s;
            box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05) !important;
        }
        
        .dark .dataTables_length select {
            border-color: #374151 !important;
            color: #f3f4f6 !important;
            background-color: #1a2231 !important;
        }
        
        .dataTables_length select:focus {
            border-color: #465fff !important;
            box-shadow: 0px 0px 0px 4px rgba(70, 95, 255, 0.12) !important;
        }

        /* Pagination Container Fixes */
        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .dataTables_wrapper .dataTables_paginate span {
            display: flex;
            gap: 0.25rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.25rem;
        }

        /* Pagination Button Base Styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 32px !important;
            height: 32px !important;
            padding: 0 8px !important;
            margin: 0 !important;
            border-radius: 8px !important; /* Diubah menjadi rounded-lg untuk mengikuti border TailAdmin */
            border: 1px solid #e5e7eb !important; /* border-gray-200 */
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #4b5563 !important; /* text-gray-600 */
            background: #ffffff !important;
            transition: all 0.2s;
            cursor: pointer !important;
            text-decoration: none !important;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #9ca3af !important; /* text-gray-400 */
            background: transparent !important;
            border-color: #1f2937 !important; /* border-gray-800 - Disesuaikan dengan TailAdmin */
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #111827 !important; /* text-gray-900 */
            background: #f3f4f6 !important; /* bg-gray-100 - Mengikuti tema abu TailAdmin */
            border-color: #d1d5db !important; /* border-gray-300 */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #f3f4f6 !important; /* text-gray-100 */
            background: rgba(255, 255, 255, 0.05) !important; /* Transparansi ringan */
            border-color: #374151 !important; /* border-gray-700 */
        }

        /* Current/Active Page Styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #ffffff !important;
            background: #465fff !important; /* bg-brand-500 dari theme TailAdmin */
            border-color: #465fff !important;
            box-shadow: 0px 4px 12px -2px rgba(70, 95, 255, 0.25) !important; /* Drop shadow khusus tombol brand TailAdmin */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #ffffff !important;
            background: #465fff !important;
            border-color: #465fff !important;
            box-shadow: 0px 4px 12px -2px rgba(70, 95, 255, 0.25) !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            color: #9ca3af !important;
            background: #f9fafb !important;
            border-color: #e5e7eb !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            color: #4b5563 !important;
            background: transparent !important;
            border-color: #374151 !important;
        }

        /* Previous/Next Button Specific Styles */
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            min-width: unset !important;
            padding: 0 12px !important;
            font-size: 13px !important;
        }

        /* Info Text */
        .dataTables_wrapper .dataTables_info {
            padding-top: 0 !important;
            font-size: 0.875rem !important;
            color: #6b7280 !important; /* text-gray-500 */
        }
        
        .dark .dataTables_wrapper .dataTables_info {
            color: #9ca3af !important; /* text-gray-400 */
        }
        
        /* Table Headers */
        table.dataTable thead th, table.dataTable thead td {
            border-bottom: 1px solid #e5e7eb !important; /* border-gray-200 */
        }
        
        .dark table.dataTable thead th, .dark table.dataTable thead td {
            border-bottom: 1px solid #374151 !important; /* border-gray-800 */
        }
        
        /* Sorting Icons */
        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_desc::after,
        table.dataTable thead .sorting_asc_disabled::after,
        table.dataTable thead .sorting_desc_disabled::after {
            right: 0.5em !important;
            content: "↓" !important;
        }

        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_desc::before,
        table.dataTable thead .sorting_desc::after,
        table.dataTable thead .sorting_asc_disabled::before,
        table.dataTable thead .sorting_asc_disabled::after,
        table.dataTable thead .sorting_desc_disabled::before,
        table.dataTable thead .sorting_desc_disabled::after {
            bottom: 0.9em !important;
            display: block !important;
            opacity: 0.3 !important;
        }
        
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_desc::after {
            opacity: 1 !important;
        }
        /* Tabel Body Text Style */
        table.dataTable tbody td {
            font-size: 0.875rem !important; /* text-sm */
            color: #4b5563 !important; /* text-gray-600 */
        }
        
        .dark table.dataTable tbody td {
            color: #d1d5db !important; /* text-gray-300 */
        }

        /* DataTables 2: teks kontrol, informasi, dan pagination */
        div.dt-container .dt-length,
        div.dt-container .dt-search,
        div.dt-container .dt-info,
        div.dt-container .dt-paging,
        div.dt-container .dt-length label,
        div.dt-container .dt-search label {
            color: #475467 !important;
        }

        .dark div.dt-container .dt-length,
        .dark div.dt-container .dt-search,
        .dark div.dt-container .dt-info,
        .dark div.dt-container .dt-paging,
        .dark div.dt-container .dt-length label,
        .dark div.dt-container .dt-search label {
            color: #f2f4f7 !important;
        }

        .dark div.dt-container .dt-input {
            color: #f2f4f7 !important;
            background-color: #101828 !important;
            border-color: #344054 !important;
        }

        div.dt-container .dt-paging .dt-paging-button {
            color: #475467 !important;
        }

        .dark div.dt-container .dt-paging .dt-paging-button,
        .dark div.dt-container .dt-paging .dt-paging-button.disabled,
        .dark div.dt-container .dt-paging .dt-paging-button.disabled:hover,
        .dark div.dt-container .dt-paging .dt-paging-button.disabled:active {
            color: #f2f4f7 !important;
        }

        .dark div.dt-container .dt-paging .dt-paging-button:hover:not(.disabled):not(.current) {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: #475467 !important;
        }

        .dark div.dt-container .dt-paging .dt-paging-button.current,
        .dark div.dt-container .dt-paging .dt-paging-button.current:hover {
            color: #ffffff !important;
            background: #465fff !important;
            border-color: #465fff !important;
        }
    </style>
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    // Menggunakan light theme sebagai default jika tidak ada cache, mengabaikan systemTheme
                    this.theme = savedTheme || 'light';
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
    
</head>

<body
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

    @stack('scripts')
</body>

</html>
