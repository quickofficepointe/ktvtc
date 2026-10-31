{{-- Any image that fails to load (missing upload, dead link) shows a neutral placeholder instead of its file name --}}
<script>
    document.addEventListener('error', function (e) {
        var img = e.target;
        if (img.tagName === 'IMG' && !img.dataset.fallback) {
            img.dataset.fallback = '1';
            img.alt = '';
            img.src = '{{ asset('Assets/images/placeholder.svg') }}';
            img.style.objectFit = 'cover';
        }
    }, true);
</script>
