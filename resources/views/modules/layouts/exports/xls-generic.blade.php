<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<title>{{ $judulLaporan ?? 'Laporan' }}</title>
<!--[if gte mso 9]>
<xml>
<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{{ substr($judulLaporan ?? 'Laporan',0,25) }}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; }
  table { border-collapse: collapse; }
  th { background-color: #0284c7; color: #ffffff; padding: 6px 8px; text-align: left; font-weight: bold; border: 1px solid #000000; }
  td { padding: 5px 8px; border: 1px solid #000000; mso-number-format: "\@"; }
  tr.summary-row td { background-color: #fef3c7; font-weight: 700; }
  .text-end { text-align: right; mso-number-format: '#,##0.00'; }
  .text-center { text-align: center; }
  .text-start { text-align: left; }
  .title { font-size: 18px; color: #0284c7; font-weight: bold; }
  .subtitle { font-size: 13px; color: #f97316; }
  .info-label { font-weight: bold; }
</style>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr><td class="title" colspan="99">SISTEM KOPERASI SIMPAN PINJAM</td></tr>
  <tr><td class="subtitle" colspan="99"><strong>{{ strtoupper($judulLaporan ?? 'LAPORAN') }}</strong></td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td class="info-label" width="130">Periode</td><td colspan="98">: {{ $periode ?? 'Seluruh Periode' }}</td></tr>
  <tr><td class="info-label">Cabang</td><td colspan="98">: {{ $infoCabang ?? 'Seluruh Cabang' }}</td></tr>
  <tr><td class="info-label">Dicetak Tanggal</td><td colspan="98">: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</td></tr>
</table>
<br>

@php
  $headerCount = count($headerKolom);
@endphp
<table border="1" cellpadding="3" cellspacing="0" width="100%">
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
      <tr><td colspan="{{ $headerCount }}" class="text-center">— Tidak ada data untuk ditampilkan —</td></tr>
    @endforelse
    @foreach($summaryFooter as $sfIdx => $sfRow)
      @php
        $cells = $sfRow['cells'] ?? [];
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
          @endphp
          <td class="{{ $align }}">{!! $value !!}</td>
        @endforeach
      </tr>
    @endforeach
  </tbody>
</table>
</body>
</html>
