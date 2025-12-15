<x-layouts.app title="Riwayat Berobat Saya">
    <div class="container-fluid px-4 mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Riwayat Berobat Saya</h1>

                <div class="card">
                    <div class="card-body">
                        @if($riwayatPeriksa->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Tanggal Periksa</th>
                                            <th>Poli</th>
                                            <th>Dokter</th>
                                            <th>Biaya Pemeriksaan</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($riwayatPeriksa as $riwayat)
                                            @php
                                                // Biaya pemeriksaan default 150.000
                                                $biayaPeriksa = 150000;
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ \Carbon\Carbon::parse($riwayat->tgl_periksa)->format('d/m/Y') }}</td>
                                                <td>{{ $riwayat->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</td>
                                                <td>dr. {{ $riwayat->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}</td>
                                                <td>
                                                    Rp {{ number_format($biayaPeriksa, 0, ',', '.') }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailModal{{ $riwayat->id }}">
                                                        <i class="fas fa-eye"></i> Lihat
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                                <h4>Belum ada riwayat berobat</h4>
                                <p class="text-muted">Anda belum memiliki riwayat pemeriksaan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals untuk detail -->
    @foreach($riwayatPeriksa as $riwayat)
        <!-- Modal Detail -->
        <div class="modal fade" id="detailModal{{ $riwayat->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pemeriksaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informasi Pemeriksaan</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th style="width: 40%">No. Antrian</th>
                                        <td>{{ $riwayat->daftarPoli->no_antrian ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Periksa</th>
                                        <td>{{ \Carbon\Carbon::parse($riwayat->tgl_periksa)->format('d F Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Poli Tujuan</th>
                                        <td>{{ $riwayat->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dokter</th>
                                        <td>dr. {{ $riwayat->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Biaya Pemeriksaan</th>
                                        <td>Rp {{ number_format(150000, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Catatan Medis</h6>
                                
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Keluhan:</strong></p>
                                    <div class="p-2 bg-light rounded small">
                                        {{ $riwayat->daftarPoli->keluhan ?: 'Tidak ada keluhan' }}
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Catatan Dokter:</strong></p>
                                    <div class="p-2 bg-light rounded small">
                                        {{ $riwayat->catatan ?: 'Tidak ada catatan dari dokter' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($riwayat->detailPeriksas && $riwayat->detailPeriksas->count() > 0)
                            @php
                                $totalObat = 0;
                                foreach($riwayat->detailPeriksas as $detail) {
                                    $totalObat += $detail->obat->harga * ($detail->jumlah ?? 1);
                                }
                            @endphp
                            <hr>
                            <h6>Resep Obat</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Obat</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($riwayat->detailPeriksas as $index => $detail)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $detail->obat->nama_obat }}</td>
                                                <td>Rp {{ number_format($detail->obat->harga, 0, ',', '.') }}</td>
                                                <td>{{ $detail->jumlah ?? 1 }}</td>
                                                <td>Rp {{ number_format(($detail->obat->harga * ($detail->jumlah ?? 1)), 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-active">
                                            <td colspan="4" class="text-end"><strong>Total Biaya Obat:</strong></td>
                                            <td><strong>
                                                Rp {{ number_format($totalObat, 0, ',', '.') }}
                                            </strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        
                        <!-- RINCIAN BIAYA -->
                        <hr>
                        <h6>Rincian Biaya</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td><strong>Biaya Pemeriksaan:</strong></td>
                                        <td class="text-end">Rp {{ number_format(150000, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Biaya Obat:</strong></td>
                                        <td class="text-end">
                                            @php
                                                $totalObat = 0;
                                                if(isset($riwayat->detailPeriksas) && $riwayat->detailPeriksas->count() > 0) {
                                                    foreach($riwayat->detailPeriksas as $detail) {
                                                        $totalObat += $detail->obat->harga * ($detail->jumlah ?? 1);
                                                    }
                                                }
                                                echo 'Rp ' . number_format($totalObat, 0, ',', '.');
                                            @endphp
                                        </td>
                                    </tr>
                                    <tr class="table-active">
                                        <td><strong>Total Keseluruhan:</strong></td>
                                        <td class="text-end"><strong>
                                            @php
                                                $biayaPeriksa = 150000;
                                                $totalObat = 0;
                                                if(isset($riwayat->detailPeriksas) && $riwayat->detailPeriksas->count() > 0) {
                                                    foreach($riwayat->detailPeriksas as $detail) {
                                                        $totalObat += $detail->obat->harga * ($detail->jumlah ?? 1);
                                                    }
                                                }
                                                $totalKeseluruhan = $biayaPeriksa + $totalObat;
                                                echo 'Rp ' . number_format($totalKeseluruhan, 0, ',', '.');
                                            @endphp
                                        </strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- END RINCIAN BIAYA -->
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    
    <!-- Bootstrap 5 JS untuk modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</x-layouts.app>