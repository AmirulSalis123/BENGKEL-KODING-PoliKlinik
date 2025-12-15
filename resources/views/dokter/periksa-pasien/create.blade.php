<x-layouts.app title="Pemeriksaan Pasien">
    <div class="container-fluid px-4 mt-4">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="mb-4">Pemeriksaan Pasien</h1>

                <!-- Info Pasien -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Pasien</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Nama Pasien:</strong> {{ $daftarPoli->pasien->nama }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>No. Antrian:</strong> {{ $daftarPoli->no_antrian }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Keluhan:</strong> {{ $daftarPoli->keluhan }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('periksa-pasien.store') }}" method="POST" id="formPeriksa">
                            @csrf
                            <input type="hidden" name="id_daftar_poli" value="{{ $id }}">

                            <!-- Catatan dan Biaya -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="catatan" class="form-label">Catatan Pemeriksaan</label>
                                        <textarea name="catatan" id="catatan" class="form-control" rows="4" placeholder="Masukkan diagnosa dan catatan pemeriksaan..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="biaya_periksa" class="form-label">Biaya Pemeriksaan (Rp)</label>
                                        <input type="number" name="biaya_periksa" id="biaya_periksa" 
                                               class="form-control" value="150000" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Resep Obat -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Resep Obat</h5>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary" id="tambahObat">
                                            <i class="fas fa-plus"></i> Tambah Obat
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered" id="tableObat">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%;">Nama Obat</th>
                                                    <th>Kemasan</th>
                                                    <th>Stok</th>
                                                    <th>Harga</th>
                                                    <th>Jumlah</th>
                                                    <th>Subtotal</th>
                                                    <th style="width: 80px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="obatBody">
                                                <!-- Baris akan ditambahkan via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Input hidden untuk menyimpan data obat -->
                                    <input type="hidden" name="obat_json" id="obatJson" value="[]">
                                    <!-- Input untuk jumlah obat (akan diisi dinamis) -->
                                    <div id="hiddenInputsContainer"></div>
                                </div>
                            </div>

                            <!-- Ringkasan Biaya -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Ringkasan Biaya</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td class="text-end"><strong>Biaya Obat:</strong></td>
                                                    <td style="width: 200px;" id="totalObatCell">Rp 0</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end"><strong>Biaya Periksa:</strong></td>
                                                    <td id="totalPeriksaCell">Rp 150.000</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td class="text-end"><strong>Biaya Keseluruhan:</strong></td>
                                                    <td><strong id="totalBiayaCell">Rp 150.000</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-save"></i> Simpan Pemeriksaan
                                </button>
                                <a href="{{ route('periksa-pasien.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template untuk baris obat -->
    <template id="obatRowTemplate">
        <tr>
            <td>
                <select class="form-select select-obat" required>
                    <option value="">Pilih Obat</option>
                    @foreach($obats as $obat)
                        <option value="{{ $obat->id }}" 
                                data-stok="{{ $obat->stok }}" 
                                data-harga="{{ $obat->harga }}"
                                data-nama="{{ $obat->nama_obat }}"
                                data-kemasan="{{ $obat->kemasan }}">
                            {{ $obat->nama_obat }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="kemasan">-</td>
            <td class="stok-tersedia">-</td>
            <td class="harga">-</td>
            <td>
                <input type="number" class="form-control jumlah-obat" min="1" value="1" required>
            </td>
            <td class="subtotal">Rp 0</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger hapus-obat">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const obatBody = document.getElementById('obatBody');
            const tambahObatBtn = document.getElementById('tambahObat');
            const obatJsonInput = document.getElementById('obatJson');
            const biayaPeriksaInput = document.getElementById('biaya_periksa');
            const submitBtn = document.getElementById('submitBtn');
            const template = document.getElementById('obatRowTemplate');
            const hiddenInputsContainer = document.getElementById('hiddenInputsContainer');
            let rowCounter = 0;

            // Fungsi untuk menghitung total obat dan total biaya
            function hitungTotal() {
                let totalObat = 0;
                
                document.querySelectorAll('.subtotal').forEach(function(cell) {
                    const subtotal = parseInt(cell.textContent.replace(/\D/g, '')) || 0;
                    totalObat += subtotal;
                });
                
                const biayaPeriksa = parseInt(biayaPeriksaInput.value) || 0;
                const totalKeseluruhan = biayaPeriksa + totalObat;
                
                document.getElementById('totalObatCell').textContent = 
                    'Rp ' + totalObat.toLocaleString('id-ID');
                document.getElementById('totalPeriksaCell').textContent = 
                    'Rp ' + biayaPeriksa.toLocaleString('id-ID');
                document.getElementById('totalBiayaCell').innerHTML = 
                    '<strong>Rp ' + totalKeseluruhan.toLocaleString('id-ID') + '</strong>';
                
                return totalObat;
            }

            // Fungsi untuk update informasi obat di baris
            function updateObatInfo(select) {
                const row = select.closest('tr');
                const selectedOption = select.options[select.selectedIndex];
                
                if (selectedOption.value) {
                    const stok = parseInt(selectedOption.dataset.stok) || 0;
                    const harga = parseInt(selectedOption.dataset.harga) || 0;
                    const jumlahInput = row.querySelector('.jumlah-obat');
                    
                    row.querySelector('.kemasan').textContent = selectedOption.dataset.kemasan || '-';
                    row.querySelector('.stok-tersedia').textContent = stok.toLocaleString('id-ID');
                    row.querySelector('.harga').textContent = 
                        'Rp ' + harga.toLocaleString('id-ID');
                    
                    jumlahInput.max = stok;
                    
                    validateStock(selectedOption.value, jumlahInput.value, select);
                    hitungSubtotal(row);
                } else {
                    row.querySelector('.kemasan').textContent = '-';
                    row.querySelector('.stok-tersedia').textContent = '-';
                    row.querySelector('.harga').textContent = '-';
                    row.querySelector('.subtotal').textContent = 'Rp 0';
                    row.classList.remove('table-danger');
                }
                
                updateObatData();
            }

            // Fungsi hitung subtotal per baris
            function hitungSubtotal(row) {
                const select = row.querySelector('.select-obat');
                const jumlahInput = row.querySelector('.jumlah-obat');
                const selectedOption = select.options[select.selectedIndex];
                
                if (selectedOption.value) {
                    const harga = parseInt(selectedOption.dataset.harga) || 0;
                    const jumlah = parseInt(jumlahInput.value) || 0;
                    const subtotal = harga * jumlah;
                    
                    row.querySelector('.subtotal').textContent = 
                        'Rp ' + subtotal.toLocaleString('id-ID');
                    
                    hitungTotal();
                    updateObatData();
                }
            }

            // Fungsi untuk update data obat ke JSON dan input hidden
            function updateObatData() {
                const rows = document.querySelectorAll('#obatBody tr');
                const obatArray = [];
                
                hiddenInputsContainer.innerHTML = '';
                
                rows.forEach((row, index) => {
                    const select = row.querySelector('.select-obat');
                    const jumlahInput = row.querySelector('.jumlah-obat');
                    
                    if (select.value) {
                        obatArray.push({
                            id: select.value,
                            jumlah: parseInt(jumlahInput.value) || 1
                        });
                        
                        const inputId = document.createElement('input');
                        inputId.type = 'hidden';
                        inputId.name = `obat_json[]`;
                        inputId.value = select.value;
                        hiddenInputsContainer.appendChild(inputId);
                        
                        const inputJumlah = document.createElement('input');
                        inputJumlah.type = 'hidden';
                        inputJumlah.name = `obat_jumlah[]`;
                        inputJumlah.value = jumlahInput.value;
                        hiddenInputsContainer.appendChild(inputJumlah);
                    }
                });
                
                obatJsonInput.value = JSON.stringify(obatArray);
            }

            // ========== VALIDASI STOK REAL-TIME ==========
            // Event listener untuk perubahan obat
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('select-obat')) {
                    const jumlahInput = e.target.closest('tr').querySelector('.jumlah-obat');
                    validateStock(e.target.value, jumlahInput.value, e.target);
                }
            });

            // Event listener untuk perubahan jumlah
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('jumlah-obat')) {
                    const select = e.target.closest('tr').querySelector('.select-obat');
                    if (select.value) {
                        validateStock(select.value, e.target.value, select);
                    }
                }
            });

            // Fungsi validasi stok via API
            async function validateStock(obatId, jumlah, selectElement) {
                if (!obatId) return;
                
                try {
                    const response = await fetch('{{ route("periksa-pasien.check-stock") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            obat_id: obatId,
                            jumlah: parseInt(jumlah) || 1
                        })
                    });

                    const result = await response.json();
                    const row = selectElement.closest('tr');
                    const stokCell = row.querySelector('.stok-tersedia');
                    const jumlahInput = row.querySelector('.jumlah-obat');
                    
                    if (result.sufficient) {
                        stokCell.innerHTML = `
                            <div>
                                <span class="text-dark">
                                    ${result.stok_tersedia.toLocaleString('id-ID')}
                                </span>
                            </div>
                        `;
                    } else {
                        stokCell.innerHTML = `
                            <div>
                                <span class="text-dark">
                                    ${result.stok_tersedia.toLocaleString('id-ID')}
                                </span>
                                <small class="d-block text-danger mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> ${result.message}
                                </small>
                            </div>
                        `;
                    }
                    
                    if (!result.sufficient) {
                        row.classList.add('table-danger');
                        jumlahInput.classList.add('is-invalid');
                    } else {
                        row.classList.remove('table-danger');
                        jumlahInput.classList.remove('is-invalid');
                    }
                    
                    jumlahInput.max = result.stok_tersedia;
                    
                    if (parseInt(jumlahInput.value) > result.stok_tersedia) {
                        jumlahInput.value = result.stok_tersedia;
                        hitungSubtotal(row);
                    }
                    
                } catch (error) {
                    console.error('Error checking stock:', error);
                    const stokCell = selectElement.closest('tr').querySelector('.stok-tersedia');
                    stokCell.innerHTML = `<span class="text-dark">Error cek stok</span>`;
                }
            }

            // Fungsi tambah baris obat
            function tambahBarisObat() {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('tr');
                
                obatBody.appendChild(clone);
                
                const select = row.querySelector('.select-obat');
                const jumlahInput = row.querySelector('.jumlah-obat');
                const hapusBtn = row.querySelector('.hapus-obat');
                
                select.addEventListener('change', function() {
                    updateObatInfo(this);
                });
                
                jumlahInput.addEventListener('input', function() {
                    const select = this.closest('tr').querySelector('.select-obat');
                    const selectedOption = select.options[select.selectedIndex];
                    
                    if (selectedOption.value) {
                        const stok = parseInt(selectedOption.dataset.stok) || 0;
                        
                        if (parseInt(this.value) > stok) {
                            this.value = stok;
                        }
                        
                        validateStock(selectedOption.value, this.value, select);
                        hitungSubtotal(this.closest('tr'));
                    }
                });
                
                hapusBtn.addEventListener('click', function() {
                    this.closest('tr').remove();
                    hitungTotal();
                    updateObatData();
                });
            }

            // Event listener untuk tombol tambah obat
            tambahObatBtn.addEventListener('click', tambahBarisObat);

            // Event listener untuk perubahan biaya periksa
            biayaPeriksaInput.addEventListener('input', function() {
                hitungTotal();
            });

            // Validasi form sebelum submit
            document.getElementById('formPeriksa').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const catatan = document.getElementById('catatan').value.trim();
                if (!catatan) {
                    alert('Harap isi catatan pemeriksaan!');
                    document.getElementById('catatan').focus();
                    return;
                }
                
                const biayaPeriksa = parseInt(biayaPeriksaInput.value) || 0;
                if (biayaPeriksa <= 0) {
                    alert('Biaya pemeriksaan harus lebih dari 0!');
                    biayaPeriksaInput.focus();
                    return;
                }
                
                // PERBAIKAN DI SINI - Gunakan filter untuk mendapatkan select yang punya value
                const allSelects = document.querySelectorAll('.select-obat');
                const selectedObats = Array.from(allSelects).filter(select => select.value !== "");
                
                let hasInvalidStock = false;
                const errors = [];
                
                if (selectedObats.length > 0) {
                    for (const select of selectedObats) {
                        const row = select.closest('tr');
                        const jumlahInput = row.querySelector('.jumlah-obat');
                        const obatId = select.value;
                        const jumlah = parseInt(jumlahInput.value) || 1;
                        const obatNama = select.options[select.selectedIndex].text;
                        
                        try {
                            const response = await fetch('{{ route("periksa-pasien.check-stock") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    obat_id: obatId,
                                    jumlah: jumlah
                                })
                            });
                            
                            const result = await response.json();
                            
                            if (!result.sufficient) {
                                hasInvalidStock = true;
                                errors.push(result.message);
                                row.classList.add('table-danger');
                                
                                if (errors.length === 1) {
                                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            } else {
                                row.classList.remove('table-danger');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            hasInvalidStock = true;
                            errors.push('Gagal validasi stok obat: ' + obatNama);
                        }
                    }
                    
                    if (hasInvalidStock) {
                        const modal = new bootstrap.Modal(document.getElementById('errorModal'));
                        document.getElementById('errorList').innerHTML = 
                            errors.map(error => `<li>${error}</li>`).join('');
                        modal.show();
                        return false;
                    }
                }
                
                this.submit();
            });

            // Tambah baris pertama secara otomatis
            tambahBarisObat();
        });
    </script>
    @endpush

    <!-- Modal untuk error validasi -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Validasi Gagal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-times-circle"></i> Tidak dapat menyimpan pemeriksaan karena:</h6>
                        <ul class="mb-0" id="errorList">
                            <!-- Error list akan diisi oleh JavaScript -->
                        </ul>
                    </div>
                    <p class="mb-0">Silahkan perbaiki resep obat sebelum menyimpan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>