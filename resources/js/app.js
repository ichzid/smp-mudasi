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
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';



window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.flatpickrIndonesian = Indonesian;
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

    const errorClass = 'js-inline-error';
    const fieldSelector = 'input[name]:enabled, select[name]:enabled, textarea[name]:enabled';
    const ignoredTypes = new Set(['hidden', 'submit', 'button', 'reset']);

    const targetForms = [...document.forms].filter(form => {
        if ((form.method || 'get').toLowerCase() === 'get' || form.hasAttribute('data-no-inline-validation')) return false;
        if (form.action.includes('/logout')) return false;
        const method = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
        if (method === 'DELETE') return false;
        return [...form.querySelectorAll(fieldSelector)].some(field => !ignoredTypes.has(field.type) && field.name !== '_token' && field.name !== '_method');
    });

    const fieldsByName = (form, name) => [...form.querySelectorAll(fieldSelector)].filter(field => field.name === name);
    const visibleField = field => field._flatpickr?.altInput || field;
    const errorId = field => `js-error-${(field.id || field.name).replace(/[^a-zA-Z0-9_-]/g, '-')}`;

    const errorAnchor = field => {
        const custom = field.closest('[data-validation-group]')?.querySelector('[data-validation-error]');
        if (custom) return { custom, parent: null };
        const datePicker = field.closest('.custom-datepicker');
        if (datePicker) return { custom: null, parent: datePicker.parentElement };
        const relative = field.closest('.relative');
        return { custom: null, parent: relative?.parentElement || field.parentElement };
    };

    const clearError = (form, name) => {
        const group = fieldsByName(form, name);
        group.forEach(field => {
            field.removeAttribute('aria-invalid');
            field.removeAttribute('aria-describedby');
            const shown = visibleField(field);
            shown.removeAttribute('aria-invalid');
            shown.removeAttribute('aria-describedby');
        });
        form.querySelectorAll(`.${errorClass}[data-field-name="${CSS.escape(name)}"]`).forEach(error => error.remove());
    };

    const showError = (form, field, message) => {
        clearError(form, field.name);
        const id = errorId(field);
        fieldsByName(form, field.name).forEach(item => {
            item.setAttribute('aria-invalid', 'true');
            item.setAttribute('aria-describedby', id);
            const shown = visibleField(item);
            shown.setAttribute('aria-invalid', 'true');
            shown.setAttribute('aria-describedby', id);
        });
        const error = document.createElement('p');
        error.id = id;
        error.dataset.fieldName = field.name;
        error.className = `${errorClass} mt-1.5 text-xs text-error-500`;
        error.textContent = message;
        const { custom, parent } = errorAnchor(field);
        if (custom) custom.append(error); else parent.append(error);
    };

    const validDate = value => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
        const [year, month, day] = value.split('-').map(Number);
        const date = new Date(Date.UTC(year, month - 1, day));
        return date.getUTCFullYear() === year && date.getUTCMonth() === month - 1 && date.getUTCDate() === day;
    };

    const fieldError = (form, field) => {
        const group = fieldsByName(form, field.name);
        const value = field.value.trim();
        const required = field.required || field.dataset.requiredGroup !== undefined;
        if ((field.type === 'radio' || field.type === 'checkbox') && required && !group.some(item => item.checked)) return 'Kolom ini wajib diisi.';
        if (field.type !== 'radio' && field.type !== 'checkbox' && required && !value) return 'Kolom ini wajib diisi.';
        if (!value && field.type !== 'file') return null;
        if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Format email tidak valid.';
        if (field.minLength > -1 && value.length < field.minLength) return `Minimal ${field.minLength} karakter.`;
        if (field.maxLength > -1 && value.length > field.maxLength) return `Maksimal ${field.maxLength} karakter.`;
        if (field.pattern && !(new RegExp(`^(?:${field.pattern})$`)).test(value)) return 'Format kolom tidak valid.';
        if (field.type === 'number' && field.min !== '' && Number(value) < Number(field.min)) return `Nilai minimal ${field.min}.`;
        if (field.type === 'number' && field.max !== '' && Number(value) > Number(field.max)) return `Nilai maksimal ${field.max}.`;
        if ((field.type === 'date' || field.dataset.dateFormat === 'Y-m-d' || field._flatpickr) && value && !validDate(value)) return 'Tanggal tidak valid.';
        if (field.type === 'file' && field.files.length) {
            const file = field.files[0];
            const accepted = field.accept.split(',').map(type => type.trim()).filter(Boolean);
            if (accepted.length && !accepted.some(type => type.endsWith('/*') ? file.type.startsWith(type.slice(0, -1)) : type === file.type)) return 'Tipe file tidak valid.';
            const maxSize = Number(field.dataset.maxFileSize || 0);
            if (maxSize && file.size > maxSize) return 'Ukuran file tidak boleh lebih dari 2 MB.';
        }
        if (field.dataset.sameAs) {
            const comparison = form.elements[field.dataset.sameAs];
            if (comparison?.value && value !== comparison.value) return 'Konfirmasi kata sandi tidak sama.';
        }
        return null;
    };

    targetForms.forEach(form => {
        form.noValidate = true;
        form.addEventListener('submit', event => {
            const fields = [...form.querySelectorAll(fieldSelector)].filter(field => !ignoredTypes.has(field.type) && field.name !== '_token' && field.name !== '_method');
            const checkedNames = new Set();
            let firstInvalid = null;
            fields.forEach(field => {
                if ((field.type === 'radio' || field.type === 'checkbox') && checkedNames.has(field.name)) return;
                checkedNames.add(field.name);
                const message = fieldError(form, field);
                if (message) {
                    showError(form, field, message);
                    firstInvalid ||= visibleField(field);
                } else clearError(form, field.name);
            });
            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(() => firstInvalid.focus({ preventScroll: true }), 250);
            }
        });
        form.addEventListener('input', event => {
            if (event.target.matches(fieldSelector)) clearError(form, event.target.name);
        });
        form.addEventListener('change', event => {
            if (!event.target.matches(fieldSelector)) return;
            clearError(form, event.target.name);
            const message = fieldError(form, event.target);
            if (message) showError(form, event.target, message);
        });
    });
});
