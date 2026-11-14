(function () {
    const ENDPOINT = 'cart/tambah.php';

    function tampilkanToast(pesan, sukses) {
        if (window.tampilkanToast && window.tampilkanToast !== tampilkanToast) {
            window.tampilkanToast(pesan, sukses);
            return;
        }
        let wrap = document.getElementById('cartToastWrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'cartToastWrap';
            wrap.className = 'cart-toast-wrap';
            wrap.style.cssText = 'position:fixed!important;top:20px!important;right:20px!important;z-index:9999!important;display:flex!important;flex-direction:column!important;gap:8px!important;max-width:350px!important;width:auto!important;pointer-events:none!important;';
            document.body.appendChild(wrap);
        }

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

    if (!window.tampilkanToast) {
        window.tampilkanToast = tampilkanToast;
    }
    window.tampilkanCartToast = window.tampilkanToast;

    function perbaruiBadge(jumlah) {
        const ikon = document.querySelector('.cart-icon-button');
        if (!ikon) return;

        let badge = ikon.querySelector('.cart-count');
        if (jumlah > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'cart-count';
                ikon.appendChild(badge);
            }
            badge.textContent = jumlah;
        } else if (badge) {
            badge.remove();
        }
    }

    window.tambahKeKeranjang = function (id, tombol, qty) {
        if (tombol) tombol.disabled = true;

        const data = new FormData();
        data.append('id', id);
        data.append('qty', qty || 1);

        fetch(ENDPOINT, {
            method: 'POST',
            body: data,
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (hasil) {
                if (hasil.login_required) {
                    tampilkanToast(hasil.message, false);
                    setTimeout(function () {
                        window.location.href = 'auth/login.php';
                    }, 800);
                    return;
                }
                tampilkanToast(hasil.message, hasil.ok);
                if (typeof hasil.count !== 'undefined') {
                    perbaruiBadge(hasil.count);
                }
            })
            .catch(function () {
                tampilkanToast('Gagal menghubungi server. Coba lagi.', false);
            })
            .finally(function () {
                if (tombol) tombol.disabled = false;
            });
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    const cartList = document.getElementById('cartList');
    if (!cartList) return;

    const endpoint = 'cart/aksi_keranjang.php';

    async function kirimData(data) {
        const formData = new FormData();
        Object.keys(data).forEach(function (key) {
            if (Array.isArray(data[key])) {
                data[key].forEach(function (value) {
                    formData.append(key + '[]', value);
                });
            } else {
                formData.append(key, data[key]);
            }
        });

        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        return await response.json();
    }

    const modalElement = document.getElementById('modalHapus');
    const btnKonfirmasi = document.getElementById('btnKonfirmasiHapus');
    const pesanHapus = document.getElementById('pesanHapus');

    let modalHapus = null;
    let dataHapus = null;

    if (modalElement && btnKonfirmasi && pesanHapus && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        modalHapus = new bootstrap.Modal(modalElement);

        cartList.addEventListener('click', function (e) {
            const tombol = e.target.closest('[data-aksi="hapus"]');
            if (!tombol) return;

            const item = tombol.closest('.cart-item');
            if (!item) return;

            const judulElement = item.querySelector('.product-title');
            const judul = judulElement ? judulElement.textContent.trim() : 'buku ini';

            dataHapus = {
                aksi: 'hapus',
                id: item.dataset.id
            };

            pesanHapus.textContent = 'Apakah kamu yakin ingin menghapus "' + judul + '" dari keranjang?';
            modalHapus.show();
        });

        const btnHapusTerpilih = document.getElementById('btnHapusTerpilih');
        if (btnHapusTerpilih) {
            btnHapusTerpilih.addEventListener('click', function () {
                const ids = [];
                document.querySelectorAll('.item-check:checked').forEach(function (checkbox) {
                    const item = checkbox.closest('.cart-item');
                    if (item) {
                        ids.push(item.dataset.id);
                    }
                });

                if (ids.length === 0) {
                    window.tampilkanCartToast('Pilih buku yang ingin dihapus.', false);
                    return;
                }

                dataHapus = {
                    aksi: 'hapus_terpilih',
                    ids: ids
                };

                pesanHapus.textContent = 'Apakah kamu yakin ingin menghapus ' + ids.length + ' buku terpilih?';
                modalHapus.show();
            });
        }

        btnKonfirmasi.addEventListener('click', async function () {
            if (!dataHapus) return;

            btnKonfirmasi.disabled = true;
            btnKonfirmasi.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

            try {
                const hasil = await kirimData(dataHapus);
                if (hasil.ok) {
                    location.reload();
                } else {
                    pesanHapus.textContent = hasil.message || 'Buku gagal dihapus.';
                    btnKonfirmasi.disabled = false;
                    btnKonfirmasi.innerHTML = '<i class="bi bi-trash3 me-1"></i> Ya, Hapus';
                }
            } catch (error) {
                pesanHapus.textContent = 'Terjadi kesalahan. Silakan coba kembali.';
                btnKonfirmasi.disabled = false;
                btnKonfirmasi.innerHTML = '<i class="bi bi-trash3 me-1"></i> Ya, Hapus';
            }
        });

        modalElement.addEventListener('hidden.bs.modal', function () {
            dataHapus = null;
            btnKonfirmasi.disabled = false;
            btnKonfirmasi.innerHTML = '<i class="bi bi-trash3 me-1"></i> Ya, Hapus';
        });
    }

    cartList.addEventListener('click', async function (e) {
        const tombol = e.target.closest('[data-aksi]');
        if (!tombol) return;

        const aksi = tombol.dataset.aksi;
        if (aksi === 'hapus') return;
        if (aksi !== 'tambah' && aksi !== 'kurang') return;

        const item = tombol.closest('.cart-item');
        if (!item) return;

        const id = item.dataset.id;
        const input = item.querySelector('.qty-input');
        if (!input) return;

        let qty = parseInt(input.value, 10) || 1;
        const stok = parseInt(item.dataset.stok, 10) || 0;

        if (aksi === 'tambah') {
            if (qty >= stok) {
                window.tampilkanCartToast('Jumlah sudah mencapai stok tersedia.', false);
                return;
            }
            qty++;
        }

        if (aksi === 'kurang') {
            if (qty <= 1) return;
            qty--;
        }

        tombol.disabled = true;

        try {
            const hasil = await kirimData({
                aksi: 'ubah_jumlah',
                id: id,
                qty: qty
            });

            if (hasil.ok) {
                location.reload();
            } else {
                window.tampilkanCartToast(hasil.message || 'Jumlah gagal diperbarui.', false);
            }
        } catch (error) {
            window.tampilkanCartToast('Terjadi kesalahan saat memperbarui jumlah.', false);
        } finally {
            tombol.disabled = false;
        }
    });

    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.item-check').forEach(function (checkbox) {
                checkbox.checked = checkAll.checked;
            });
        });
    }

    function updateTombolJumlah() {
        document.querySelectorAll('.cart-item').forEach(function (item) {
            const input = item.querySelector('.qty-input');
            const btnKurang = item.querySelector('[data-aksi="kurang"]');
            const btnTambah = item.querySelector('[data-aksi="tambah"]');

            if (!input || !btnKurang || !btnTambah) return;

            const qty = parseInt(input.value, 10) || 1;
            const stok = parseInt(item.dataset.stok, 10) || 0;

            btnKurang.disabled = qty <= 1;
            btnTambah.disabled = qty >= stok;
        });
    }

    updateTombolJumlah();

    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            const checkedBoxes = document.querySelectorAll('.item-check:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                window.tampilkanCartToast('Pilih minimal satu buku di keranjang untuk melanjutkan ke pembayaran.', false);
                return;
            }
            const container = document.getElementById('checkoutIds');
            if (container) {
                container.innerHTML = '';
                checkedBoxes.forEach(function (cb) {
                    const item = cb.closest('.cart-item');
                    if (item && item.dataset.id) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'cart_ids[]';
                        hidden.value = item.dataset.id;
                        container.appendChild(hidden);
                    }
                });
            }
        });
    }
});
