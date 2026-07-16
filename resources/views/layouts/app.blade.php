<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | TailAdmin - Laravel Tailwind CSS Admin Dashboard Template</title>
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
        
        .dataTables_filter input {
            border-radius: 0.5rem !important; /* rounded-lg */
            border: 1px solid #d1d5db !important; /* border-gray-300 */
            padding: 0.5rem 0.75rem !important; /* py-2 px-3 */
            font-size: 0.875rem !important; /* text-sm */
            line-height: 1.25rem !important;
            outline: none !important;
            background-color: transparent !important;
            margin-left: 0.5rem !important;
        }
        
        .dark .dataTables_filter input {
            border-color: #374151 !important; /* border-gray-700 */
            color: #f3f4f6 !important; /* text-gray-100 */
        }
        
        .dataTables_filter input:focus {
            border-color: #3b82f6 !important; /* focus:border-blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
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
        }
        
        .dark .dataTables_length select {
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }
        
        .dataTables_length select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        /* Pagination Styles */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem !important;
            margin-left: 0.25rem !important;
            border-radius: 0.375rem !important;
            border: 1px solid transparent !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #374151 !important;
            background: transparent !important;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #9ca3af !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #111827 !important;
            background: #f3f4f6 !important; /* bg-gray-100 */
            border-color: #e5e7eb !important;
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #f3f4f6 !important;
            background: #374151 !important; /* bg-gray-700 */
            border-color: #4b5563 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #2563eb !important; /* text-blue-600 */
            background: #eff6ff !important; /* bg-blue-50 */
            border-color: #bfdbfe !important; /* border-blue-200 */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #60a5fa !important; /* text-blue-400 */
            background: rgba(37, 99, 235, 0.1) !important;
            border-color: rgba(59, 130, 246, 0.2) !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            color: #9ca3af !important;
            background: transparent !important;
            border-color: transparent !important;
            cursor: not-allowed !important;
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
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
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
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
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
