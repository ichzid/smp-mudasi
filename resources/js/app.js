import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

// jQuery & DataTables
import jQuery from 'jquery';
window.$ = jQuery;
window.jQuery = jQuery;
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
window.DataTable = DataTable;

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';



window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }

    // Initialize DataTables globally
    const initDataTables = () => {
        const tables = document.querySelectorAll('.data-table');
        const presensiTables = document.querySelectorAll('.data-table-presensi');
        
        const dtOptions = {
            responsive: true,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang ditemukan",
                emptyTable: "Tidak ada data yang tersedia di tabel",
            },
            // Styling for Tailwind CSS integration
            dom: '<"flex flex-col sm:flex-row justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-800 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-800 gap-4"ip>',
            pagingType: "simple_numbers",
            initComplete: function() {
                // Apply Tailwind CSS classes to DataTables elements
                $('.dataTables_filter input').addClass('dark:bg-dark-900 focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90');
                $('.dataTables_length select').addClass('dark:bg-dark-900 focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent px-3 py-1 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90');
            }
        };

        if (tables.length > 0) {
            tables.forEach(table => {
                if (!$.fn.DataTable.isDataTable(table)) {
                    new DataTable(table, dtOptions);
                }
            });
        }
        
        if (presensiTables.length > 0) {
            presensiTables.forEach(table => {
                if (!$.fn.DataTable.isDataTable(table)) {
                    new DataTable(table, {
                        ...dtOptions,
                        paging: false, // Disable pagination for presensi forms so all data is submitted
                        info: false,
                        dom: '<"flex flex-col sm:flex-row justify-end items-center pb-4 border-b border-gray-200 dark:border-gray-800 gap-4"f>rt'
                    });
                }
            });
        }
    };

    initDataTables();
});
