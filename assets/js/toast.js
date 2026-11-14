(function () {
    function tampilkanToast(pesan, sukses) {
        if (!pesan) return;
        let wrap = document.getElementById('cartToastWrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'cartToastWrap';
            wrap.className = 'cart-toast-wrap';
            document.body.appendChild(wrap);
        }
        wrap.style.cssText = 'position:fixed!important;top:20px!important;right:20px!important;z-index:9999!important;display:flex!important;flex-direction:column!important;gap:8px!important;max-width:350px!important;width:auto!important;pointer-events:none!important;';

        const toast = document.createElement('div');
        toast.className = 'shadow rounded-3 px-3 py-2 small fw-semibold text-white d-flex align-items-start gap-2 ' + (sukses ? 'bg-success' : 'bg-danger');
        toast.style.cssText = 'pointer-events:auto;max-width:350px;width:fit-content;';
        toast.innerHTML = '<i class="bi ' + (sukses ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') + ' mt-1"></i><span></span>';
        toast.lastChild.textContent = pesan;
        wrap.appendChild(toast);

        setTimeout(function () {
            toast.style.transition = 'opacity .3s';
            toast.style.opacity = '0';
            setTimeout(function () {
                toast.remove();
            }, 300);
        }, 2600);
    }

    window.tampilkanToast = tampilkanToast;
    window.tampilkanCartToast = tampilkanToast;
})();

