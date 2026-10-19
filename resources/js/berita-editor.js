import Quill from 'quill';
import 'quill/dist/quill.snow.css';

function initBeritaEditor() {
    const wrapper = document.getElementById('berita-editor');
    const field = document.getElementById('berita-isi');
    const form = document.getElementById('berita-form');
    if (!wrapper || !field || !form) return;

    const quill = new Quill('#berita-quill', {
        theme: 'snow',
        placeholder: 'Tulis isi berita di sini...',
        formats: ['header', 'bold', 'italic', 'underline', 'strike', 'list', 'blockquote', 'link'],
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean'],
            ],
        },
    });

    if (field.value) {
        quill.setContents(quill.clipboard.convert({ html: field.value }));
    }

    const syncContent = () => {
        field.value = quill.getText().trim() ? quill.getSemanticHTML() : '';
    };
    syncContent();
    quill.on('text-change', syncContent);
    wrapper.classList.remove('hidden');
    field.classList.add('hidden');
    field.required = false;

    form.addEventListener('submit', event => {
        syncContent();
        if (field.value) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        quill.focus();
        window.Swal.fire({
            icon: 'warning',
            title: 'Isi konten belum diisi',
            text: 'Isi konten berita wajib diisi.',
            confirmButtonColor: '#465fff',
        });
    }, true);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBeritaEditor);
} else {
    initBeritaEditor();
}
