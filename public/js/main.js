document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.querySelector('input[type="file"][name="image"]');
    const previewContainer = document.getElementById('image-preview');

    if (fileInput && previewContainer) {
        fileInput.addEventListener('change', () => {
            const [file] = fileInput.files;
            if (!file) {
                previewContainer.innerHTML = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        });
    }
});

// Toast utility
window.showToast = function(message, type = 'success', timeout = 3000) {
    try {
        const container = document.getElementById('vw-toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'vw-toast vw-toast-' + type;
        toast.setAttribute('role', 'status');
        // structure: message span + close button
        const txt = document.createElement('span');
        txt.innerText = message;
        const close = document.createElement('button');
        close.className = 'vw-toast-close';
        close.innerHTML = '&times;';
        close.style.marginLeft = '0.6rem';
        close.style.background = 'transparent';
        close.style.border = 'none';
        close.style.color = 'inherit';
        close.style.cursor = 'pointer';
        toast.appendChild(txt);
        toast.appendChild(close);
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        container.appendChild(toast);
        // animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
        const t = setTimeout(() => {
            toast.style.transition = 'opacity 200ms ease, transform 200ms ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(6px)';
            setTimeout(() => { toast.remove(); }, 250);
        }, timeout);
        // allow close and message to dismiss
        close.addEventListener('click', () => {
            clearTimeout(t);
            toast.remove();
        });
        txt.addEventListener('click', () => {
            clearTimeout(t);
            toast.remove();
        });
    } catch (e) { console.error('Toast error', e); }
}

// Live badge increment helper
window.incrementLiveBadge = function(btnText) {
    try {
        const el = document.getElementById('liveBadge');
        if (!el) return;
        const val = parseInt(el.innerText || '0') || 0;
        el.innerText = (val + 1).toString();
        el.style.transform = 'scale(1.05)';
        setTimeout(() => { el.style.transform = ''; }, 400);
        // decrement after a short duration
        setTimeout(() => {
            const cur = parseInt(el.innerText || '0') || 0;
            el.innerText = Math.max(0, cur - 1).toString();
        }, 5000);
    } catch (e) { console.error('incrementLiveBadge', e); }
}