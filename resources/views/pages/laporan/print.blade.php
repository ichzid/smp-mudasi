<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>{{ $title }}</title><style>body{font-family:Arial,sans-serif;color:#111;margin:32px}h1{margin-bottom:4px}.toolbar{margin-bottom:24px}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #999;padding:7px;text-align:left}.number{text-align:center}th{background:#eee}@media print{.toolbar{display:none}body{margin:0}}</style></head>
<body>
<div class="toolbar"><button onclick="window.print()">Print / Save as PDF</button></div>
<h1>Laporan Presensi {{ ucfirst($tab) }}</h1>
<p>Tahun Ajaran: {{ $tahunAjaran->nama }} - Semester {{ $tahunAjaran->semester }}<br>Rombel: {{ $rombel?->tingkat }} - {{ $rombel?->nama }}<br>Periode: {{ $tab === 'harian' ? \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') : \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</p>
<table><thead><tr>@if($tab === 'bulanan')<th>NIS</th><th>Nama Siswa</th>@else<th>Rombel</th>@endif @foreach($statuses as $status)<th>{{ ucfirst($status) }}</th>@endforeach<th>Total</th></tr></thead><tbody>
@forelse($rows as $row)<tr>@if($tab === 'bulanan')<td>{{ $row->nis }}</td><td>{{ $row->nama_lengkap }}</td>@else<td>{{ $row->tingkat }} - {{ $row->nama }}</td>@endif @foreach($statuses as $status)<td class="number">{{ $row->{$status} }}</td>@endforeach<td class="number">{{ $row->total }}</td></tr>@empty<tr><td colspan="9">Belum ada data presensi.</td></tr>@endforelse
</tbody></table></body></html>
