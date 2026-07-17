<div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"></div>

@push('scripts')
<script>
window.toast = function(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const colors = {
        success: 'bg-green-600 text-white',
        error: 'bg-red-600 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-purple-600 text-white',
    };
    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        error: '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008z"/>',
        info: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>',
    };

    const el = document.createElement('div');
    el.className = `${colors[type] || colors.info} pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 opacity-0`;
    el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0">${icons[type] || icons.info}</svg><span>${message}</span>`;
    container.appendChild(el);

    requestAnimationFrame(() => {
        el.classList.remove('opacity-0');
        el.classList.add('opacity-100');
    });

    setTimeout(() => {
        el.classList.remove('opacity-100');
        el.classList.add('opacity-0');
        setTimeout(() => el.remove(), 300);
    }, 3000);
};
</script>
@endpush
