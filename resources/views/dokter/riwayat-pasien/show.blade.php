<x-layouts.app title="Detail Riwayat Pasien">
    <div class="container-fluid px-4 mt-4">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Detail Riwayat</h2>
                    <a href="{{ route('riwayat-pasien.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card">
                    <h5 class="card-header">Informasi Pasien</h5>
                    <div class="card-body">
                        <p><strong>Nama Pasien:</strong> {{ $periksa->daftarPoli->pasien->nama }}</p>
                        <p><strong>No. Antrian:</strong> {{ $periksa->daftarPoli->no_antrian }}</p>
                        <p><strong>Keluhan:</strong> {{ $periksa->daftarPoli->keluhan }}</p>
                        <p><strong>Poli:</strong> {{ $periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli }}</p>
                        <p><strong>Dokter:</strong> {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama }}</p>
                        <p><strong>Tanggal Periksa:</strong> {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d/m/Y H:i') }}</p>
                        <p><strong>Biaya Pemeriksaan:</strong> Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</p> <!-- TAMBAH INI -->
                    </div>
                </div>

                <div class="card mb-3">
                    <h5 class="card-header">Catatan Dokter</h5>
                    <div class="card-body">
                        <p>{{ $periksa->catatan ?: 'Tidak ada catatan' }}</p>
                    </div>
                </div>

                <div class="card mb-3">
                    <h5 class="card-header">Obat yang Diresepkan</h5>
                    <div class="card-body">
                        @if($periksa->detailPeriksas && $periksa->detailPeriksas->count() > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Obat</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalBiayaObat = 0;
                                    @endphp
                                    @foreach($periksa->detailPeriksas as $index => $detail)
                                        @php
                                            $jumlah = $detail->jumlah ?? 1;
                                            $subtotal = $detail->obat->harga * $jumlah;
                                            $totalBiayaObat += $subtotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $detail->obat->nama_obat }}</td>
                                            <td>{{ $jumlah }}</td>
                                            <td>Rp {{ number_format($detail->obat->harga, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-active">
                                        <td colspan="4" class="text-end"><strong>Total Biaya Obat:</strong></td>
                                        <td><strong>Rp {{ number_format($totalBiayaObat, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">Tidak ada obat yang diresepkan</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Rincian Biaya</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-end">Biaya Pemeriksaan:</td>
                                <td class="text-start">Rp {{ number_format($periksa->biaya_periksa - $totalBiayaObat, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-end">Biaya Obat:</td>
                                <td class="text-start">Rp {{ number_format($totalBiayaObat, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-end"><strong>Total Keseluruhan:</strong></td>
                                <td class="text-start"><strong>Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>