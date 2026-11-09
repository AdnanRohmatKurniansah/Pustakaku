


(function () {
    const ENDPOINT = 'cart/tambah.php';

    function tampilkanToast(pesan, sukses) {
        let wrap = document.getElementById('cartToastWrap');

        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'cartToastWrap';
            wrap.style.cssText =
                'position:fixed;right:16px;bottom:16px;z-index:2000;display:flex;flex-direction:column;gap:8px;max-width:340px;';
            document.body.appendChild(wrap);
        }

        const toast = document.createElement('div');

        toast.className =
            'shadow rounded-3 px-3 py-2 small fw-semibold text-white d-flex align-items-start gap-2 ' +
            (sukses ? 'bg-success' : 'bg-danger');

        toast.innerHTML =
            '<i class="bi ' +
            (sukses ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') +
            ' mt-1"></i><span></span>';

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


    window.tampilkanCartToast = tampilkanToast;

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

    window.tambahKeKeranjang = function (id, tombol) {
        if (tombol) tombol.disabled = true;

        const data = new FormData();
        data.append('id', id);
        data.append('qty', 1);

        fetch(ENDPOINT, {
            method: 'POST',
            body: data,
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (hasil) {
                tampilkanToast(hasil.message, hasil.ok);

                if (typeof hasil.count !== 'undefined') {
                    perbaruiBadge(hasil.count);
                }
            })
            .catch(function () {
                tampilkanToast(
                    'Gagal menghubungi server. Coba lagi.',
                    false
                );
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

    if (modalElement && btnKonfirmasi && pesanHapus) {
        modalHapus = new bootstrap.Modal(modalElement);

        // Klik tombol hapus satu buku
        cartList.addEventListener('click', function (e) {
            const tombol = e.target.closest('[data-aksi="hapus"]');

            if (!tombol) return;

            const item = tombol.closest('.cart-item');
            if (!item) return;

            const judulElement = item.querySelector('.product-title');
            const judul = judulElement
                ? judulElement.textContent.trim()
                : 'buku ini';

            dataHapus = {
                aksi: 'hapus',
                id: item.dataset.id
            };

            pesanHapus.textContent =
                'Apakah kamu yakin ingin menghapus "' +
                judul +
                '" dari keranjang?';

            modalHapus.show();
        });

        const btnHapusTerpilih =
            document.getElementById('btnHapusTerpilih');

        if (btnHapusTerpilih) {
            btnHapusTerpilih.addEventListener('click', function () {
                const ids = [];

                document.querySelectorAll('.item-check:checked')
                    .forEach(function (checkbox) {
                        const item = checkbox.closest('.cart-item');

                        if (item) {
                            ids.push(item.dataset.id);
                        }
                    });

                if (ids.length === 0) {
                    window.tampilkanCartToast(
                        'Pilih buku yang ingin dihapus.',
                        false
                    );
                    return;
                }

                dataHapus = {
                    aksi: 'hapus_terpilih',
                    ids: ids
                };

                pesanHapus.textContent =
                    'Apakah kamu yakin ingin menghapus ' +
                    ids.length +
                    ' buku terpilih?';

                modalHapus.show();
            });
        }

        btnKonfirmasi.addEventListener('click', async function () {
            if (!dataHapus) return;

            btnKonfirmasi.disabled = true;
            btnKonfirmasi.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

            try {
                const hasil = await kirimData(dataHapus);

                if (hasil.ok) {
                    location.reload();
                } else {
                    pesanHapus.textContent =
                        hasil.message || 'Buku gagal dihapus.';

                    btnKonfirmasi.disabled = false;
                    btnKonfirmasi.innerHTML =
                        '<i class="bi bi-trash3 me-1"></i> Ya, Hapus';
                }

            } catch (error) {
                pesanHapus.textContent =
                    'Terjadi kesalahan. Silakan coba kembali.';

                btnKonfirmasi.disabled = false;
                btnKonfirmasi.innerHTML =
                    '<i class="bi bi-trash3 me-1"></i> Ya, Hapus';
            }
        });

        modalElement.addEventListener('hidden.bs.modal', function () {
            dataHapus = null;

            btnKonfirmasi.disabled = false;
            btnKonfirmasi.innerHTML =
                '<i class="bi bi-trash3 me-1"></i> Ya, Hapus';
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
                window.tampilkanCartToast(
                    'Jumlah sudah mencapai stok tersedia.',
                    false
                );
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
                window.tampilkanCartToast(
                    hasil.message || 'Jumlah gagal diperbarui.',
                    false
                );
            }

        } catch (error) {
            window.tampilkanCartToast(
                'Terjadi kesalahan saat memperbarui jumlah.',
                false
            );
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
});
