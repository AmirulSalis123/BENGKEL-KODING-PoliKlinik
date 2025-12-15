<x-layouts.app title="Data Obat">
    <div class="container-fluid px-4 mt-4">
        {{-- Alert flash message --}}
        @if (session('message'))
        <div class="alert alert-{{ session('type', 'success') }} alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- Notifikasi stok menipis/habis --}}
        @if(session('stock_warning'))
            @php $warning = session('stock_warning'); @endphp
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Peringatan Stok!</strong> Obat <strong>{{ $warning['obat'] }}</strong> {{ $warning['status'] }} (Stok: {{ $warning['stok'] }})
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h1 class="mb-4">Data Obat</h1>

        {{-- Debug info --}}
        @if(isset($debug) && $debug)
        <div class="alert alert-info">
            <strong>Debug Info:</strong> 
            Total Obat: {{ count($obats ?? []) }} | 
            Obats Type: {{ gettype($obats) }} |
            Is Collection: {{ $obats instanceof Illuminate\Support\Collection ? 'Yes' : 'No' }}
        </div>
        @endif

        {{-- Dashboard statistik stok --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">Total Obat</h6>
                                <h3>{{ $totalObat ?? count($obats ?? []) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-pills fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">Stok Aman</h6>
                                <h3>{{ $stokAman ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>Stok Menipis</h6>
                                <h3>{{ $stokMenipis ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">Stok Habis</h6>
                                <h3>{{ $stokHabis ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol aksi --}}
        <div class="d-flex justify-content-between mb-3">
            <div>
                <a href="{{ route('obat.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Obat
                </a>
            </div>
            <div>
                <a href="{{ route('obat.index', ['status' => 'habis']) }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-times-circle"></i> Stok Habis
                </a>
                <a href="{{ route('obat.index', ['status' => 'menipis']) }}" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-exclamation-triangle"></i> Stok Menipis
                </a>
                <a href="{{ route('obat.index', ['status' => 'aman']) }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-check-circle"></i> Stok Aman
                </a>
                <a href="{{ route('obat.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list"></i> Semua
                </a>
            </div>
        </div>

        @if(!isset($obats) || $obats->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Tidak ada data obat yang ditemukan. 
            @if(auth()->check() && auth()->user()->can('create', App\Models\Obat::class))
            <a href="{{ route('obat.create') }}" class="alert-link">Klik di sini untuk menambah obat pertama</a>
            @endif
        </div>
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Obat</th>
                        <th>Kemasan</th>
                        <th>Stok</th>
                        <th>Status Stok</th>
                        <th>Harga</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Fungsi untuk menghitung persentase berdasarkan skala yang diberikan
                        function calculatePercentage($stok) {
                            // Skala persentase ke stok
                            $scale = [
                                0 => 0,
                                1 => 10000,
                                2 => 20000,
                                3 => 30000,
                                4 => 40000,
                                5 => 50000,
                                6 => 60000,
                                7 => 70000,
                                8 => 80000,
                                9 => 90000,
                                10 => 100000,
                                11 => 110000,
                                12 => 120000,
                                13 => 130000,
                                14 => 140000,
                                15 => 150000,
                                16 => 160000,
                                17 => 170000,
                                18 => 180000,
                                19 => 190000,
                                20 => 200000,
                                21 => 210000,
                                22 => 220000,
                                23 => 230000,
                                24 => 240000,
                                25 => 250000,
                                26 => 260000,
                                27 => 270000,
                                28 => 280000,
                                29 => 290000,
                                30 => 300000,
                                31 => 310000,
                                32 => 320000,
                                33 => 330000,
                                34 => 340000,
                                35 => 350000,
                                36 => 360000,
                                37 => 370000,
                                38 => 380000,
                                39 => 390000,
                                40 => 400000,
                                41 => 410000,
                                42 => 420000,
                                43 => 430000,
                                44 => 440000,
                                45 => 450000,
                                46 => 460000,
                                47 => 470000,
                                48 => 480000,
                                49 => 490000,
                                50 => 500000,
                                51 => 510000,
                                52 => 520000,
                                53 => 530000,
                                54 => 540000,
                                55 => 550000,
                                56 => 560000,
                                57 => 570000,
                                58 => 580000,
                                59 => 590000,
                                60 => 600000,
                                61 => 610000,
                                62 => 620000,
                                63 => 630000,
                                64 => 640000,
                                65 => 650000,
                                66 => 660000,
                                67 => 670000,
                                68 => 680000,
                                69 => 690000,
                                70 => 700000,
                                71 => 710000,
                                72 => 720000,
                                73 => 730000,
                                74 => 740000,
                                75 => 750000,
                                76 => 760000,
                                77 => 770000,
                                78 => 780000,
                                79 => 790000,
                                80 => 800000,
                                81 => 810000,
                                82 => 820000,
                                83 => 830000,
                                84 => 840000,
                                85 => 849999,
                                86 => 859999,
                                87 => 869999,
                                88 => 879999,
                                89 => 889999,
                                90 => 899999,
                                91 => 909999,
                                92 => 919999,
                                93 => 929999,
                                94 => 939999,
                                95 => 949999,
                                96 => 959999,
                                97 => 969999,
                                98 => 979999,
                                99 => 989999,
                                100 => 999999
                            ];
                            
                            // Jika stok 0, return 0%
                            if ($stok <= 0) {
                                return 0;
                            }
                            
                            // Jika stok >= 999.999, return 100%
                            if ($stok >= 999999) {
                                return 100;
                            }
                            
                            // Cari persentase berdasarkan stok
                            $foundPercentage = 0;
                            foreach ($scale as $percentage => $scaleStok) {
                                if ($stok >= $scaleStok) {
                                    $foundPercentage = $percentage;
                                } else {
                                    break;
                                }
                            }
                            
                            // Jika ditemukan persentase eksak, return itu
                            if (isset($scale[$foundPercentage]) && $stok == $scale[$foundPercentage]) {
                                return $foundPercentage;
                            }
                            
                            // Interpolasi linear untuk nilai di antara skala
                            if ($foundPercentage < 100) {
                                $currentStok = $scale[$foundPercentage];
                                $nextStok = $scale[$foundPercentage + 1];
                                $stokRange = $nextStok - $currentStok;
                                $stokDiff = $stok - $currentStok;
                                
                                // Interpolasi untuk mendapatkan persentase desimal
                                $interpolatedPercentage = $foundPercentage + ($stokDiff / $stokRange);
                                return min(100, max(0, $interpolatedPercentage));
                            }
                            
                            return $foundPercentage;
                        }
                    @endphp
                    
                    @foreach ($obats as $index => $obat)
                    @php 
                        // Fallback jika method getStockStatus tidak ada
                        if (method_exists($obat, 'getStockStatus')) {
                            $status = $obat->getStockStatus();
                        } else {
                            // Manual status calculation
                            $stok = $obat->stok ?? 0;
                            if ($stok == 0) {
                                $status = [
                                    'status' => 'habis',
                                    'message' => 'Stok Habis',
                                    'class' => 'danger',
                                    'icon' => 'fas fa-times-circle'
                                ];
                            } elseif ($stok <= 10) {
                                $status = [
                                    'status' => 'menipis',
                                    'message' => 'Stok Menipis',
                                    'class' => 'warning',
                                    'icon' => 'fas fa-exclamation-triangle'
                                ];
                            } else {
                                $status = [
                                    'status' => 'aman',
                                    'message' => 'Stok Aman',
                                    'class' => 'success',
                                    'icon' => 'fas fa-check-circle'
                                ];
                            }
                        }
                        
                        // Hitung persentase berdasarkan skala yang ditentukan
                        $percentage = calculatePercentage($obat->stok ?? 0);
                        $displayPercentage = number_format($percentage, 1);
                        
                        // Warna progress bar
                        $barColor = $status['class'];
                        
                        // Tentukan posisi teks
                        if ($percentage >= 25) {
                            $textClass = 'text-white';
                            $textShadow = 'text-shadow';
                        } elseif ($percentage >= 5) {
                            $textClass = 'text-dark';
                            $textShadow = '';
                        } else {
                            $textClass = 'text-dark outside-text';
                            $textShadow = '';
                        }
                        
                        // Tentukan posisi left untuk teks
                        if ($percentage <= 5) {
                            $textPosition = 5;
                        } elseif ($percentage >= 95) {
                            $textPosition = 95;
                        } else {
                            $textPosition = $percentage;
                        }
                        
                        // Tentukan kelas untuk tooltip info skala
                        $tooltipInfo = "Skala: ";
                        if ($percentage == 0) {
                            $tooltipInfo .= "0% = 0 stok";
                        } elseif ($percentage == 100) {
                            $tooltipInfo .= "100% = 999.999+ stok";
                        } else {
                            $tooltipInfo .= number_format($displayPercentage, 1) . "% = ~" . number_format($obat->stok ?? 0, 0, ',', '.') . " stok";
                        }
                    @endphp
                    <tr class="@if($status['status'] == 'habis') table-danger @elseif($status['status'] == 'menipis') table-warning @endif">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $obat->nama_obat ?? 'N/A' }}</td>
                        <td>{{ $obat->kemasan ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong class="fs-6">{{ number_format($obat->stok ?? 0, 0, ',', '.') }}</strong>
                                    <small class="text-muted ms-1">{{ $obat->satuan ?? '' }}</small>
                                </div>
                                <small class="text-muted">
                                    {{ $displayPercentage }}%
                                </small>
                            </div>
                            
                            {{-- Progress bar dengan skala khusus --}}
                            <div class="progress-container position-relative" 
                                 data-bs-toggle="tooltip" 
                                 data-bs-html="true"
                                 title="<div class='text-start'><strong>Info Skala Stok</strong><br>{{ $tooltipInfo }}<br>Skala lengkap: 0% (0) - 100% (999.999)</div>">
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-{{ $barColor }}" 
                                         role="progressbar" 
                                         style="width: {{ $percentage }}%"
                                         aria-valuenow="{{ $obat->stok ?? 0 }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="999999">
                                    </div>
                                </div>
                                
                                {{-- Teks persentase --}}
                                @if(!str_contains($textClass, 'outside-text') && $percentage > 0)
                                <div class="progress-percentage {{ $textClass }} {{ $textShadow }}"
                                     style="left: {{ $textPosition }}%;">
                                    {{ $displayPercentage }}%
                                </div>
                                @endif
                                
                                {{-- Marker untuk nilai-nilai penting --}}
                                @if($percentage >= 100)
                                <div class="scale-marker" style="left: 100%;" data-bs-toggle="tooltip" title="100% = 999.999 stok">
                                    <div class="marker-line"></div>
                                    <div class="marker-label">Max</div>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $status['class'] }}">
                                <i class="{{ $status['icon'] }} me-1"></i>
                                {{ $status['message'] }}
                            </span>
                            <br>
                            <small class="text-muted">
                                @if(isset($obat->stok_minimum) && $obat->stok_minimum > 0)
                                    Min. {{ $obat->stok_minimum }}
                                @endif
                            </small>
                        </td>
                        <td>
                            @if(isset($obat->harga))
                                Rp {{ number_format($obat->harga, 0, ',', '.') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @if(isset($obat->id))
                                <a href="{{ route('obat.edit', $obat->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <form action="{{ route('obat.destroy', $obat->id) }}" method="POST" 
                                      style="display: inline-block;"
                                      onsubmit="return confirm('Yakin ingin menghapus Data Obat \"{{ $obat->nama_obat ?? 'ini' }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto hide alert setelah 5 detik
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.remove('show');
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Animasi progress bar
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const finalWidth = bar.style.width;
            bar.style.width = '0%';
            bar.style.transition = 'width 1s ease-in-out';
            
            setTimeout(() => {
                bar.style.width = finalWidth;
            }, 100);
        });
    });
</script>

<style>
    .progress-container {
        position: relative;
    }
    
    .progress {
        background-color: #e9ecef;
        border-radius: 6px;
        overflow: visible !important;
        position: relative;
    }
    
    .progress-bar {
        transition: width 1s ease-in-out;
        position: relative;
        border-radius: 6px;
    }
    
    .progress-percentage {
        position: absolute;
        top: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.65rem;
        font-weight: bold;
        white-space: nowrap;
        z-index: 5;
        padding: 1px 3px;
        border-radius: 2px;
        background-color: rgba(255, 255, 255, 0.3);
    }
    
    .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .fs-6 {
        font-size: 1.1rem !important;
    }
    
    /* Marker untuk skala */
    .scale-marker {
        position: absolute;
        top: -10px;
        transform: translateX(-50%);
        z-index: 10;
    }
    
    .marker-line {
        width: 2px;
        height: 5px;
        background-color: #333;
        margin: 0 auto;
    }
    
    .marker-label {
        font-size: 0.6rem;
        color: #666;
        text-align: center;
        white-space: nowrap;
    }
    
    /* Warna khusus untuk level stok */
    .bg-success { background-color: #28a745 !important; }
    .bg-warning { background-color: #ffc107 !important; }
    .bg-danger { background-color: #dc3545 !important; }
</style>
</x-layouts.app>