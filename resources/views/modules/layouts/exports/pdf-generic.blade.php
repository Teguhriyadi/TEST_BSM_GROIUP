<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $judulLaporan ?? 'Laporan' }}</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; }
  h1 { font-size: 18px; color: #0284c7; text-align: center; margin-bottom: 4px; }
  h2 { font-size: 13px; color: #f97316; text-align: center; margin-top: 0; margin-bottom: 14px; font-weight: normal; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background-color: #0284c7; color: white; padding: 6px 8px; text-align: left; font-weight: bold; border: 1px solid #ddd; font-size: 11px; }
  td { padding: 5px 8px; border: 1px solid #ddd; font-size: 11px; vertical-align: top; }
  tr:nth-child(even) td { background-color: #f9fafb; }
  .header-info { margin-bottom: 18px; }
  .header-info td { border: none; padding: 2px; }
  .footer { margin-top: 30px; text-align: right; font-size: 11px; color: #666; }
  .text-end { text-align: right; }
  .text-center { text-align: center; }
  .text-start { text-align: left; }
  tr.summary-row td { background-color: #fef3c7; font-weight: 700; border-top: 2px solid #f59e0b; }
</style>
</head>
<body>
<h1>SISTEM KOPERASI SIMPAN PINJAM</h1>
<h2>{{ strtoupper($judulLaporan ?? 'LAPORAN') }}</h2>

<table class="header-info" style="width: 55%;">
  <tr><td style="width: 130px;">Periode</td><td>: {{ $periode ?? 'Seluruh Periode' }}</td></tr>
  <tr><td>Cabang</td><td>: {{ $infoCabang ?? 'Seluruh Cabang' }}</td></tr>
  <tr><td>Dicetak Tanggal</td><td>: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</td></tr>
</table>
<hr>

@php
  $headerCount = count($headerKolom);
@endphp
<table>
  <thead>
    <tr>
    @foreach($headerKolom as $col)
      @php
        $align = 'text-start';
        $label = $col;
        if (is_array($col)) {
          $label = $col['label'] ?? '';
          $align = $col['align'] ?? 'text-start';
        }
      @endphp
      <th class="{{ $align }}">{{ $label }}</th>
    @endforeach
    </tr>
  </thead>
  <tbody>
    @if($dataRows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
      @php $dataRows = $dataRows->getCollection(); @endphp
    @endif
    @forelse($dataRows as $idx => $row)
      @php
        $cells = $rowCallback($row, $idx + 1);
        if (!is_array($cells)) $cells = [];
        $isSummary = $cells['__is_summary'] ?? false;
        unset($cells['__is_summary']);
        $trClass = $isSummary ? 'summary-row' : '';
      @endphp
      <tr class="{{ $trClass }}">
        @foreach($headerKolom as $colIdx => $col)
          @php
            $align = 'text-start';
            $key = $colIdx;
            if (is_array($col)) {
              $align = $col['align'] ?? 'text-start';
              $key = $col['key'] ?? $colIdx;
            }
            $value = $cells[$key] ?? ($cells[$colIdx] ?? '');
          @endphp
          <td class="{{ $align }}">{!! $value !!}</td>
        @endforeach
      </tr>
    @empty
      <tr><td colspan="{{ $headerCount }}" class="text-center text-muted py-4">— Tidak ada data untuk ditampilkan —</td></tr>
    @endforelse
    @foreach($summaryFooter as $sfIdx => $sfRow)
      @php
        $cells = $sfRow['cells'] ?? [];
        $isSummary = true;
      @endphp
      <tr class="summary-row">
        @foreach($headerKolom as $colIdx => $col)
          @php
            $align = 'text-start';
            $key = $colIdx;
            if (is_array($col)) {
              $align = $col['align'] ?? 'text-start';
              $key = $col['key'] ?? $colIdx;
            }
            $value = $cells[$key] ?? ($cells[$colIdx] ?? '');
            if (is_string($value) && strlen($value) > 0 && $colIdx === 0 && count($cells) === 1) {
              $value = '<strong>' . e($value) . '</strong>';
            }
          @endphp
          <td class="{{ $align }}">{!! $value !!}</td>
        @endforeach
      </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">
Dicetak oleh Sistem Koperasi Simpan Pinjam pada {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm:ss') }}
</div>
</body>
</html>
