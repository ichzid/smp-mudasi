<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $title }}</title><style>body{font-family:Arial;margin:24px}table{border-collapse:collapse;width:100%;font-size:11px}th,td{border:1px solid #777;padding:6px}@media print{button{display:none}}</style></head>
<body>
<button onclick="print()">Cetak</button>
<h2>Laporan Guru {{ ucfirst($tab) }}</h2>
<p>{{ $tab === 'harian' ? $tanggal : $bulan }} · Guru aktif saat ini (termasuk untuk tanggal historis)</p>
<table>
    <thead><tr><th>ID Guru</th><th>Nomor Identitas</th><th>Nama</th>
        @if($tab === 'harian')
            <th>Status Masuk</th><th>Waktu Masuk</th><th>Waktu Pulang</th><th>Status Pulang</th>
        @else
            <th>Hadir</th><th>Terlambat</th><th>Belum Pulang</th>
        @endif
    </tr></thead>
    <tbody>
        @foreach($rows as $row)
            <tr><td>{{ $row->id }}</td><td>{{ $row->nomor_indentitas ?: '-' }}</td><td>{{ $row->nama_lengkap }}</td>
                @if($tab === 'harian')
                    <td>{{ $row->status }}</td><td>{{ $row->waktu_masuk ?? '-' }}</td><td>{{ $row->waktu_pulang ?? '-' }}</td><td>{{ $row->status_pulang ?? '-' }}</td>
                @else
                    <td>{{ $row->hadir }}</td><td>{{ $row->terlambat }}</td><td>{{ $row->belum_pulang }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
