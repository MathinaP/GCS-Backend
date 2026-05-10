<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 16mm 15mm; size: A4 portrait; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 7.2pt; color: #000; padding: 2mm; }
  table { width: 100%; border-collapse: collapse; }
  td, th { vertical-align: top; }
  .title { text-align: center; font-size: 10pt; font-weight: bold; text-decoration: underline; margin-bottom: 2.5pt; }
  .box { border: 0.5pt solid #000; border-top: none; }
  .first-box { border-top: 0.5pt solid #000; }
  .b { border: 0.5pt solid #000; }
  .bb { border-bottom: 0.5pt solid #000; }
  .br { border-right: 0.5pt solid #000; }
  .p { padding: 2.2pt 3.5pt; }
  .label { font-size: 6.5pt; color: #333; }
  .value { font-size: 7.2pt; font-weight: bold; min-height: 8pt; }
  .party-title { font-size: 7pt; font-weight: bold; margin-bottom: 2pt; }
  .party-name { font-size: 8.5pt; font-weight: bold; }
  .small { font-size: 6.7pt; line-height: 1.35; }
  .right { text-align: right; }
  .center { text-align: center; }
  .bold { font-weight: bold; }
  .italic { font-style: italic; }
  .nowrap { white-space: nowrap; }
  .inv-page { border: 0.8pt solid #000; margin: 0 auto; width: 100%; }
  .inv-title { color: #000; text-align: center; font-size: 11pt; font-weight: bold; letter-spacing: .4pt; padding: 4pt 0; text-decoration: underline; border-bottom: 0.6pt solid #000; }
  .inv-accent { color: #000; }
  .inv-box { border: 0.6pt solid #1d1d1d; }
  .inv-bb { border-bottom: 0.6pt solid #1d1d1d; }
  .inv-br { border-right: 0.6pt solid #1d1d1d; }
  .inv-p { padding: 3.5pt 4.5pt; }
  .inv-label { font-size: 6.6pt; color: #3d3d3d; }
  .inv-value { font-size: 7.5pt; font-weight: bold; min-height: 9pt; }
  .inv-company { font-size: 11pt; font-weight: bold; color: #000; letter-spacing: .2pt; }
  .inv-section { color: #000; font-weight: bold; text-transform: uppercase; font-size: 6.7pt; padding: 2.3pt 4pt; border-bottom: 0.6pt solid #1d1d1d; }
  .inv-party-block { border-top: 0.6pt solid #1d1d1d; margin: 3pt -4.5pt -3.5pt; padding: 3pt 4.5pt; }
  .inv-party-block + .inv-party-block { margin-top: 0; }
  .inv-head th { color: #000; border: 0.6pt solid #1d1d1d; padding: 4pt 3pt; font-size: 6.7pt; }
  .inv-row td { border-left: 0.6pt solid #1d1d1d; border-right: 0.6pt solid #1d1d1d; padding: 4pt 3pt; height: 20pt; }
  .inv-muted { color: #555; }
  .inv-footer-note { text-align: center; font-size: 6.8pt; color: #000; font-weight: bold; margin-top: 3pt; }
</style>
</head>
<body>

@php
  use App\Helpers\NumberToWords;

  $titles = [
    'invoice' => 'TAX INVOICE',
    'proforma_invoice' => 'PROFORMA INVOICE',
    'purchase_order' => 'PURCHASE ORDER',
    'quotation' => 'QUOTATION',
  ];

  $docLabels = [
    'invoice' => 'Invoice No.',
    'proforma_invoice' => 'Proforma Invoice No.',
    'purchase_order' => 'Purchase Order No.',
    'quotation' => 'Quotation No.',
  ];

  $docTitle = $titles[$document->type] ?? strtoupper(str_replace('_', ' ', $document->type));
  $docNoLabel = $docLabels[$document->type] ?? 'Document No.';
  $isIgst = (float) $document->igst_amount > 0;
  $isPO = $document->type === 'purchase_order';
  $isQuote = $document->type === 'quotation';
  $isSales = $document->type === 'invoice' || $document->type === 'proforma_invoice';

  $gstPrefix = match ($document->type) {
    'purchase_order' => 'INPUT',
    'quotation' => '',
    default => 'OUTPUT',
  };

  $fmtDate = fn($date) => $date ? $date->format('d-M-Y') : '';
  $blank = '';
  $logoPath = str_replace('\\', '/', public_path('GCS logo png.PNG'));

  $taxGroups = $document->items
    ->where('gst_rate', '>', 0)
    ->groupBy(fn($i) => (string) (float) $i->gst_rate)
    ->map(function ($grp, $rate) use ($isIgst) {
      $tax = $grp->sum(fn($i) => (float) $i->gst_amount);
      return (object) [
        'rate' => (float) $rate,
        'cgst_rate' => $isIgst ? 0 : (float) $rate / 2,
        'cgst_amount' => $isIgst ? 0 : round($tax / 2, 2),
        'sgst_rate' => $isIgst ? 0 : (float) $rate / 2,
        'sgst_amount' => $isIgst ? 0 : round($tax / 2, 2),
        'igst_rate' => $isIgst ? (float) $rate : 0,
        'igst_amount' => $isIgst ? $tax : 0,
      ];
    })
    ->sortBy('rate');

  $totalQty = $document->items->sum(fn($i) => (float) $i->quantity);
  $totalTax = (float) $document->cgst_amount + (float) $document->sgst_amount + (float) $document->igst_amount;
  $fillerRows = max(0, 5 - $document->items->count());
@endphp

@if(in_array($document->type, ['invoice', 'proforma_invoice', 'purchase_order', 'quotation'], true))
@php
  $ship = $document->consignee ?? $document->customer;
  $bill = $document->customer;
  $poSupplier = $document->supplier;
  $taxPrefix = $gstPrefix ? $gstPrefix.' ' : '';
@endphp

<div class="inv-page">
  <div class="inv-title">{{ $docTitle }}</div>

  <table>
    <tr>
      <td class="inv-box inv-p" style="width: 53%; border-left: none;">
        @if($isPO)
          <div class="party-title">Invoice To</div>
        @endif
        <table>
          <tr>
            <td style="width: 58pt; vertical-align: middle; text-align: center;">
              @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" style="width: 50pt; height: 50pt; object-fit: contain;" />
              @endif
            </td>
            <td>
              <div class="inv-company">GO CARE SOLUTIONS</div>
              <div class="small">Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street,<br>Irugur, Coimbatore - 641 402.</div>
              <div class="small">Mobile No - 8148302081 / 9360740074</div>
              <div class="small">PAN Number - <strong>GTQPD3231K</strong></div>
              <div class="small">GSTIN/UIN: <strong>33GTQPD3231K1ZM</strong></div>
              <div class="small">State Name: Tamil Nadu Code: 33</div>
              <div class="small">E - Mail: gocaresolutions01@gmail.com</div>
            </td>
          </tr>
        </table>
        @if($isPO)
        <div class="inv-party-block">
          <div class="party-title">Consignee (Ship To)</div>
          <div class="party-name">GO CARE SOLUTIONS</div>
          <div class="small">Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street,<br>Irugur, Coimbatore - 641 402.</div>
          <div class="small">Mobile No - 8148302081 / 9360740074</div>
          <div class="small">PAN Number - <strong>GTQPD3231K</strong></div>
          <div class="small">GSTIN/UIN: <strong>33GTQPD3231K1ZM</strong></div>
          <div class="small">State Name: Tamil Nadu Code: 33</div>
        </div>
        <div class="inv-party-block">
          <div class="party-title">Supplier (Bill From)</div>
          @if($poSupplier)
            <div class="party-name">{{ $poSupplier->name }}</div>
            <div class="small">{{ $poSupplier->address }}</div>
            @if($poSupplier->mobile)<div class="small">Mobile No - {{ $poSupplier->mobile }}</div>@endif
            @if($poSupplier->pan_number)<div class="small">PAN Number - {{ $poSupplier->pan_number }}</div>@endif
            @if($poSupplier->gstin)<div class="small">GSTIN/UIN: {{ $poSupplier->gstin }}</div>@endif
            @if($poSupplier->state_name)<div class="small">State Name: {{ $poSupplier->state_name }}{{ $poSupplier->state_code ? ' Code: '.$poSupplier->state_code : '' }}</div>@endif
            @if($poSupplier->email)<div class="small">E - Mail: {{ $poSupplier->email }}</div>@endif
          @else
            <div class="small inv-muted">Not specified</div>
          @endif
        </div>
        @else
        @unless($isQuote)
        <div class="inv-party-block">
          <div class="party-title">Consignee (Ship To)</div>
          @if($ship)
            <div class="party-name">{{ $ship->name }}</div>
            <div class="small">{{ $ship->address }}</div>
            @if($ship->mobile)<div class="small">Mobile No - {{ $ship->mobile }}</div>@endif
            @if($ship->gstin)<div class="small">GSTIN/UIN: {{ $ship->gstin }}</div>@endif
            @if($ship->state_name)<div class="small">State Name: {{ $ship->state_name }}{{ $ship->state_code ? ' Code: '.$ship->state_code : '' }}</div>@endif
          @else
            <div class="small inv-muted">Not specified</div>
          @endif
        </div>
        @endunless
        <div class="inv-party-block">
          <div class="party-title">{{ $isQuote ? 'Buyer' : 'Buyer (Bill To)' }}</div>
          @if($bill)
            <div class="party-name">{{ $bill->name }}</div>
            <div class="small">{{ $bill->address }}</div>
            @if($bill->mobile)<div class="small">Mobile No - {{ $bill->mobile }}</div>@endif
            @if($bill->pan_number)<div class="small">PAN Number - {{ $bill->pan_number }}</div>@endif
            @if($bill->gstin)<div class="small">GSTIN/UIN: {{ $bill->gstin }}</div>@endif
            @if($bill->state_name)<div class="small">State Name: {{ $bill->state_name }}{{ $bill->state_code ? ' Code: '.$bill->state_code : '' }}</div>@endif
            @if($bill->email)<div class="small">E - Mail: {{ $bill->email }}</div>@endif
          @else
            <div class="small inv-muted">Not specified</div>
          @endif
        </div>
        @endif
      </td>
      <td class="inv-box" style="width: 47%; border-right: none;">
        <table>
          <tr>
            <td class="inv-br inv-bb inv-p" style="width: 50%;"><div class="inv-label">{{ $docNoLabel }}</div><div class="inv-value">{{ $document->doc_number }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Dated</div><div class="inv-value">{{ $fmtDate($document->date) }}</div></td>
          </tr>
          @if($isPO)
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Quotation No.</div><div class="inv-value">{{ $document->quotation_no ?: $blank }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Dated</div><div class="inv-value">{{ $fmtDate($document->quotation_date) ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Reference No. &amp; Date</div><div class="inv-value">{{ $document->reference_no ?: $blank }} @if($document->reference_date) dt. {{ $fmtDate($document->reference_date) }} @endif</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Mode/Terms of payment</div><div class="inv-value">{{ $document->payment_terms ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Packing &amp; Forwarding Charges</div><div class="inv-value">{{ $document->packing_charges ? number_format((float) $document->packing_charges, 2) : $blank }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Other References</div><div class="inv-value">{{ $document->other_reference ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Dispatched Through</div><div class="inv-value">{{ $document->dispatched_through ?: $blank }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Destination</div><div class="inv-value">{{ $document->destination ?: $blank }}</div></td>
          </tr>
          <tr>
            <td colspan="2" class="inv-p"><div class="inv-label">Terms of Delivery</div><div class="inv-value">{{ $document->terms_of_delivery ?: $blank }}</div></td>
          </tr>
          @else
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">{{ $isQuote ? 'PR No.' : 'Delivery Note' }}</div><div class="inv-value">{{ $isQuote ? ($document->pr_no ?: $blank) : ($document->delivery_note ?: $blank) }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Mode/Terms of payment</div><div class="inv-value">{{ $document->payment_terms ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Reference No. &amp; Date</div><div class="inv-value">{{ $document->reference_no ?: $blank }} @if($document->reference_date) dt. {{ $fmtDate($document->reference_date) }} @endif</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Other Reference</div><div class="inv-value">{{ $document->other_reference ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">{{ $isQuote ? 'Delivery' : "Buyer's Order No." }}</div><div class="inv-value">{{ $isQuote ? ($document->delivery_note ?: $blank) : ($document->buyers_order_no ?: $blank) }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">{{ $isQuote ? 'Quotation Validity' : 'Dated' }}</div><div class="inv-value">{{ $isQuote ? ($document->quotation_validity ?: $blank) : ($fmtDate($document->buyers_order_date) ?: $blank) }}</div></td>
          </tr>
          @unless($isQuote)
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Dispatch Doc No.</div><div class="inv-value">{{ $document->dispatch_doc_no ?: $blank }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Delivery Note Date</div><div class="inv-value">{{ $fmtDate($document->delivery_note_date) ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="inv-br inv-bb inv-p"><div class="inv-label">Dispatched Through</div><div class="inv-value">{{ $document->dispatched_through ?: $blank }}</div></td>
            <td class="inv-bb inv-p"><div class="inv-label">Destination</div><div class="inv-value">{{ $document->destination ?: $blank }}</div></td>
          </tr>
          @endunless
          <tr>
            <td colspan="2" class="inv-p"><div class="inv-label">Terms of Delivery</div><div class="inv-value">{{ $document->terms_of_delivery ?: $blank }}</div></td>
          </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  <table>
    <thead class="inv-head">
      <tr>
        <th style="width: 5%;">S.<br>No</th>
        <th style="width: 36%;">Description of Goods</th>
        <th style="width: 9%;">HSN/SAC</th>
        <th style="width: 10%;">Quantity</th>
        <th style="width: 10%;">Rate</th>
        <th style="width: 7%;">Per</th>
        <th style="width: 8%;">Disc. %</th>
        <th style="width: 15%;">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($document->items as $item)
        <tr class="inv-row">
          <td class="center">{{ $item->sl_no }}</td>
          <td><strong>{{ $item->description }}</strong></td>
          <td class="center">{{ $item->hsn_sac }}</td>
          <td class="right">{{ number_format((float) $item->quantity, 2) }}</td>
          <td class="right">{{ number_format((float) $item->rate, 2) }}</td>
          <td class="center">{{ $item->per ?: $item->unit }}</td>
          <td class="right">{{ (float) $item->discount_pct > 0 ? number_format((float) $item->discount_pct, 2) : '' }}</td>
          <td class="right bold">{{ number_format((float) $item->amount, 2) }}</td>
        </tr>
      @endforeach
      @for($r = 0; $r < $fillerRows; $r++)
        <tr class="inv-row">
          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
      @endfor
      <tr>
        <td colspan="7" class="b p right bold">Sub Total</td>
        <td class="b p right bold">{{ number_format((float) $document->subtotal, 2) }}</td>
      </tr>
      @foreach($taxGroups as $tg)
        @if($isIgst)
          <tr>
            <td colspan="7" class="b p right bold">{{ $taxPrefix }}IGST Tax @ {{ number_format($tg->igst_rate, 0) }}%</td>
            <td class="b p right bold">{{ number_format($tg->igst_amount, 2) }}</td>
          </tr>
        @else
          <tr>
            <td colspan="7" class="b p right bold">{{ $taxPrefix }}CGST Tax @ {{ number_format($tg->cgst_rate, 0) }}%</td>
            <td class="b p right bold">{{ number_format($tg->cgst_amount, 2) }}</td>
          </tr>
          <tr>
            <td colspan="7" class="b p right bold">{{ $taxPrefix }}SGST Tax @ {{ number_format($tg->sgst_rate, 0) }}%</td>
            <td class="b p right bold">{{ number_format($tg->sgst_amount, 2) }}</td>
          </tr>
        @endif
      @endforeach
      <tr>
        <td colspan="7" class="b p right bold">Round Off</td>
        <td class="b p right bold">{{ (float) $document->round_off >= 0 ? '+' : '' }}{{ number_format((float) $document->round_off, 2) }}</td>
      </tr>
      <tr>
        <td colspan="3" class="b p right bold" style="font-size: 8.5pt;">Total</td>
        <td class="b p right bold">{{ number_format($totalQty, 2) }} Nos</td>
        <td colspan="3" class="b p"></td>
        <td class="b p right bold" style="font-size: 9pt;">&#8377; {{ number_format((float) $document->grand_total, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <table>
    <tr>
      <td class="inv-box inv-p" style="width: 82%; border-left: none;">
        <div class="bold inv-accent">Amount Chargeable (in words)</div>
        <div class="bold italic">INR {{ NumberToWords::convert((float) $document->grand_total) }}</div>
      </td>
      <td class="inv-box inv-p right bold" style="vertical-align: middle; border-right: none;">E. &amp; O.E</td>
    </tr>
    @unless($isPO)
    <tr>
      <td colspan="2" class="inv-box inv-p" style="border-left: none; border-right: none;">
        <span class="bold inv-accent">Tax Amount (in words):</span>
        <span class="italic">INR {{ NumberToWords::convert($totalTax) }}</span>
      </td>
    </tr>
    @endunless
  </table>

  <table>
    @if($isPO)
    <tr>
      <td class="inv-box inv-br inv-p" style="width: 50%; border-left: none;">
        <div class="bold">Company's PAN: GTQPD3231K</div>
      </td>
      <td class="inv-box inv-p" style="width: 50%; border-right: none;">
        <div class="bold inv-accent">Company's Bank Details</div>
        <table style="margin-top: 2pt;">
          <tr><td style="width: 44%;">A/c Holder's Name :</td><td class="bold">GO CARE SOLUTIONS</td></tr>
          <tr><td>Bank Name :</td><td class="bold">INDIAN BANK</td></tr>
          <tr><td>A/c No. :</td><td class="bold">8168879467</td></tr>
          <tr><td>Branch &amp; IFS Code :</td><td class="bold">Sulur &amp; IDIB000S294</td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="inv-box inv-br inv-p bold" style="width: 33%; height: 42pt; border-left: none;">Created by</td>
      <td class="inv-box inv-br inv-p bold" style="width: 34%; height: 42pt;">Approved by</td>
      <td class="inv-box inv-p center" style="width: 33%; height: 42pt; border-right: none;">
        <div class="right">for <strong>GO CARE SOLUTIONS</strong></div>
        <div style="margin-top: 24pt; border-top: 0.6pt solid #000; padding-top: 2pt;" class="bold right">Authorised Signatory</div>
      </td>
    </tr>
    @else
    <tr>
      <td class="inv-box inv-br inv-p" style="width: 38%; border-left: none;">
        <div class="bold">Company's PAN: GTQPD3231K</div>
        <div class="bold inv-accent" style="margin-top: 4pt;">Declaration</div>
        <div class="small">We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</div>
        <div class="small">Invoices not paid beyond 30 days interest of 21% p.a shall be charged.</div>
      </td>
      <td class="inv-box inv-br inv-p" style="width: 39%;">
        <div class="bold inv-accent">Company's Bank Details</div>
        <table style="margin-top: 2pt;">
          <tr><td style="width: 44%;">A/c Holder's Name :</td><td class="bold">GO CARE SOLUTIONS</td></tr>
          <tr><td>Bank Name :</td><td class="bold">INDIAN BANK</td></tr>
          <tr><td>A/c No. :</td><td class="bold">8168879467</td></tr>
          <tr><td>Branch &amp; IFS Code :</td><td class="bold">Sulur &amp; IDIB000S294</td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="inv-box inv-br inv-p bold" style="width: 50%; height: 39pt; border-left: none;">Customer's Seal and Signature</td>
      <td class="inv-box inv-p center" colspan="2" style="width: 50%; height: 39pt; border-right: none;">
        <div class="right">for <strong>GO CARE SOLUTIONS</strong></div>
        <div style="margin-top: 22pt; border-top: 0.6pt solid #000; padding-top: 2pt;" class="bold right">Authorised Signatory</div>
      </td>
    </tr>
    @endif
  </table>
</div>

@if($isSales)
<div class="inv-footer-note">SUBJECT TO COIMBATORE JURISDICTION</div>
<div class="center italic" style="font-size: 6.5pt; color: #555; margin-top: 1pt;">This is a Computer Generated Invoice</div>
@endif

@if($document->notes)
  <div class="b p" style="margin-top: 3pt; font-size: 7pt;">
    <strong>Notes / Terms &amp; Conditions:</strong><br>{{ $document->notes }}
  </div>
@endif

@else

<div class="title">{{ $docTitle }}</div>

{{-- Header --}}
@if($isPO)
  <table class="box first-box">
    <tr>
      <td class="br p" style="width: 45%;">
        <div class="party-title">Invoice To</div>
        <div class="party-name">GO CARE SOLUTIONS</div>
        <div class="small">Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street,<br>Irugur, Coimbatore - 641 402.</div>
        <div class="small">Mobile No - 8148302081 / 9360740074</div>
        <div class="small">PAN Number - GTQPD3231K</div>
        <div class="small">GSTIN/UIN: 33GTQPD3231K1ZM</div>
        <div class="small">State Name: Tamil Nadu Code: 33</div>
        <div class="small">E - Mail: gocaresolutions01@gmail.com</div>
      </td>
      <td style="width: 55%;">
        <table>
          <tr>
            <td class="br bb p" style="width: 50%;"><div class="label">{{ $docNoLabel }}</div><div class="value">{{ $document->doc_number ?: $blank }}</div></td>
            <td class="bb p"><div class="label">Dated</div><div class="value">{{ $fmtDate($document->date) ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="br bb p"><div class="label">Quotation No.</div><div class="value">{{ $document->quotation_no ?: $blank }}</div></td>
            <td class="bb p"><div class="label">Dated</div><div class="value">{{ $fmtDate($document->quotation_date) ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="br bb p"><div class="label">Reference No. &amp; Date</div><div class="value">{{ $document->reference_no ?: $blank }} @if($document->reference_date) dt. {{ $fmtDate($document->reference_date) }} @endif</div></td>
            <td class="bb p"><div class="label">Mode/Terms of payment</div><div class="value">{{ $document->payment_terms ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="br bb p"><div class="label">Packing &amp; Forwarding Charges.</div><div class="value">{{ $document->packing_charges ? number_format((float) $document->packing_charges, 2) : $blank }}</div></td>
            <td class="bb p"><div class="label">Other References</div><div class="value">{{ $document->other_reference ?: $blank }}</div></td>
          </tr>
          <tr>
            <td class="br bb p"><div class="label">Dispatched Through</div><div class="value">{{ $document->dispatched_through ?: $blank }}</div></td>
            <td class="bb p"><div class="label">Destination</div><div class="value">{{ $document->destination ?: $blank }}</div></td>
          </tr>
          <tr>
            <td colspan="2" class="p"><div class="label">Terms of Delivery</div><div class="value">{{ $document->terms_of_delivery ?: $blank }}</div></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
@else
  <table class="box first-box">
    <tr>
      <td class="br p" style="width: 45%;">
        <table>
          <tr>
            <td style="width: 52pt; vertical-align: middle;">
              @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" style="width: 46pt; height: 46pt; object-fit: contain;" />
              @endif
            </td>
            <td>
              <div class="party-name">GO CARE SOLUTIONS</div>
              <div class="small">Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street,<br>Irugur, Coimbatore - 641 402.</div>
              <div class="small">Mobile No - 8148302081 / 9360740074</div>
              <div class="small">PAN Number - GTQPD3231K</div>
              <div class="small">GSTIN/UIN: 33GTQPD3231K1ZM</div>
              <div class="small">State Name: Tamil Nadu Code: 33</div>
              <div class="small">E - Mail: gocaresolutions01@gmail.com</div>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 55%;">
        <table>
          <tr>
            <td class="br bb p" style="width: 50%;"><div class="label">{{ $docNoLabel }}</div><div class="value">{{ $document->doc_number ?: $blank }}</div></td>
            <td class="bb p"><div class="label">Dated</div><div class="value">{{ $fmtDate($document->date) ?: $blank }}</div></td>
          </tr>
          @if($isQuote)
            <tr>
              <td class="br bb p"><div class="label">PR No.</div><div class="value">{{ $document->pr_no ?: $blank }}</div></td>
              <td class="bb p"><div class="label">Mode/Terms of payment</div><div class="value">{{ $document->payment_terms ?: $blank }}</div></td>
            </tr>
            <tr>
              <td class="br bb p"><div class="label">Reference No. &amp; Date</div><div class="value">{{ $document->reference_no ?: $blank }} @if($document->reference_date) dt. {{ $fmtDate($document->reference_date) }} @endif</div></td>
              <td class="bb p"><div class="label">Other Reference</div><div class="value">{{ $document->other_reference ?: $blank }}</div></td>
            </tr>
            <tr>
              <td class="br bb p"><div class="label">Delivery</div><div class="value">{{ $document->delivery_note ?: $blank }}</div></td>
              <td class="bb p"><div class="label">Quotation Validity</div><div class="value">{{ $document->quotation_validity ?: $blank }}</div></td>
            </tr>
            <tr>
              <td colspan="2" class="p"><div class="label">Terms of Delivery</div><div class="value">{{ $document->terms_of_delivery ?: $blank }}</div></td>
            </tr>
          @else
            <tr>
              <td class="br bb p"><div class="label">Delivery Note</div><div class="value">{{ $document->delivery_note ?: $blank }}</div></td>
              <td class="bb p"><div class="label">Mode/Terms of payment</div><div class="value">{{ $document->payment_terms ?: $blank }}</div></td>
            </tr>
            <tr>
              <td class="br bb p"><div class="label">Reference No. &amp; Date</div><div class="value">{{ $document->reference_no ?: $blank }} @if($document->reference_date) dt. {{ $fmtDate($document->reference_date) }} @endif</div></td>
              <td class="bb p"><div class="label">Other Reference</div><div class="value">{{ $document->other_reference ?: $blank }}</div></td>
            </tr>
            <tr>
              <td class="br bb p"><div class="label">Buyer's Order No.</div><div class="value">{{ $document->buyers_order_no ?: $blank }}</div></td>
              <td class="bb p"><div class="label">Dated</div><div class="value">{{ $fmtDate($document->buyers_order_date) ?: $blank }}</div></td>
            </tr>
            <tr>
              <td class="br bb p"><div class="label">Dispatch Doc No.</div><div class="value">{{ $document->dispatch_doc_no ?: $blank }}</div></td>
              <td class="bb p"><div class="label">Delivery Note Date</div><div class="value">{{ $fmtDate($document->delivery_note_date) ?: $blank }}</div></td>
            </tr>
            <tr>
              <td class="br bb p"><div class="label">Dispatched Through</div><div class="value">{{ $document->dispatched_through ?: $blank }}</div></td>
              <td class="bb p"><div class="label">Destination</div><div class="value">{{ $document->destination ?: $blank }}</div></td>
            </tr>
            <tr>
              <td colspan="2" class="p"><div class="label">Terms of Delivery</div><div class="value">{{ $document->terms_of_delivery ?: $blank }}</div></td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>
@endif

{{-- Party details --}}
@if($isPO)
  <table class="box">
    <tr>
      <td class="br p" style="width: 50%;">
        <div class="party-title">Consignee (Ship to)</div>
        <div class="party-name">GO CARE SOLUTIONS</div>
        <div class="small">Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street,<br>Irugur, Coimbatore - 641 402.</div>
        <div class="small">Mobile No - 8148302081 / 9360740074</div>
        <div class="small">PAN Number - GTQPD3231K</div>
        <div class="small">GSTIN/UIN: 33GTQPD3231K1ZM</div>
        <div class="small">State Name: Tamil Nadu Code: 33</div>
      </td>
      <td class="p" style="width: 50%;">
        <div class="party-title">Supplier (Bill from)</div>
        @if($document->supplier)
          <div class="party-name">{{ $document->supplier->name }}</div>
          <div class="small">{{ $document->supplier->address }}</div>
          @if($document->supplier->mobile)<div class="small">Mobile No - {{ $document->supplier->mobile }}</div>@endif
          @if($document->supplier->pan_number)<div class="small">PAN Number - {{ $document->supplier->pan_number }}</div>@endif
          @if($document->supplier->gstin)<div class="small">GSTIN/UIN: {{ $document->supplier->gstin }}</div>@endif
          @if($document->supplier->state_name)<div class="small">State Name: {{ $document->supplier->state_name }}{{ $document->supplier->state_code ? ' Code: '.$document->supplier->state_code : '' }}</div>@endif
          @if($document->supplier->email)<div class="small">E - Mail: {{ $document->supplier->email }}</div>@endif
        @else
          <div class="small">Not specified</div>
        @endif
      </td>
    </tr>
  </table>
@elseif($isQuote)
  <table class="box">
    <tr>
      <td class="p">
        <div class="party-title">Buyer</div>
        @if($document->customer)
          <div class="party-name">{{ $document->customer->name }}</div>
          <div class="small">{{ $document->customer->address }}</div>
          @if($document->customer->mobile)<div class="small">Mobile No - {{ $document->customer->mobile }}</div>@endif
          @if($document->customer->pan_number)<div class="small">PAN Number - {{ $document->customer->pan_number }}</div>@endif
          @if($document->customer->gstin)<div class="small">GSTIN/UIN: {{ $document->customer->gstin }}</div>@endif
          @if($document->customer->state_name)<div class="small">State Name: {{ $document->customer->state_name }}{{ $document->customer->state_code ? ' Code: '.$document->customer->state_code : '' }}</div>@endif
          @if($document->customer->email)<div class="small">E - Mail: {{ $document->customer->email }}</div>@endif
        @else
          <div class="small">Not specified</div>
        @endif
      </td>
    </tr>
  </table>
@else
  <table class="box">
    <tr>
      <td class="br p" style="width: 50%;">
        <div class="party-title">Consignee (Ship to)</div>
        @php $ship = $document->consignee ?? $document->customer; @endphp
        @if($ship)
          <div class="party-name">{{ $ship->name }}</div>
          <div class="small">{{ $ship->address }}</div>
          @if($ship->mobile)<div class="small">Mobile No - {{ $ship->mobile }}</div>@endif
          @if($ship->gstin)<div class="small">GSTIN/UIN: {{ $ship->gstin }}</div>@endif
          @if($ship->state_name)<div class="small">State Name: {{ $ship->state_name }}{{ $ship->state_code ? ' Code: '.$ship->state_code : '' }}</div>@endif
        @else
          <div class="small">Not specified</div>
        @endif
      </td>
      <td class="p" style="width: 50%;">
        <div class="party-title">Buyer (Bill to)</div>
        @if($document->customer)
          <div class="party-name">{{ $document->customer->name }}</div>
          <div class="small">{{ $document->customer->address }}</div>
          @if($document->customer->mobile)<div class="small">Mobile No - {{ $document->customer->mobile }}</div>@endif
          @if($document->customer->pan_number)<div class="small">PAN Number - {{ $document->customer->pan_number }}</div>@endif
          @if($document->customer->gstin)<div class="small">GSTIN/UIN: {{ $document->customer->gstin }}</div>@endif
          @if($document->customer->state_name)<div class="small">State Name: {{ $document->customer->state_name }}{{ $document->customer->state_code ? ' Code: '.$document->customer->state_code : '' }}</div>@endif
          @if($document->customer->email)<div class="small">E - Mail: {{ $document->customer->email }}</div>@endif
        @else
          <div class="small">Not specified</div>
        @endif
      </td>
    </tr>
  </table>
@endif

{{-- Items --}}
<table class="box">
  <thead>
    <tr>
      <th class="b p center" style="width: 5%;">SL.<br>No</th>
      <th class="b p" style="width: 36%;">Description of Goods</th>
      <th class="b p center" style="width: 9%;">HSN/SAC</th>
      <th class="b p center" style="width: 10%;">Quantity</th>
      <th class="b p center" style="width: 10%;">Rate</th>
      <th class="b p center" style="width: 7%;">Per</th>
      <th class="b p center" style="width: 8%;">Disc. %</th>
      <th class="b p right" style="width: 15%;">Amount</th>
    </tr>
  </thead>
  <tbody>
    @foreach($document->items as $item)
      <tr>
        <td class="b p center">{{ $item->sl_no }}</td>
        <td class="b p">{{ $item->description }}</td>
        <td class="b p center">{{ $item->hsn_sac }}</td>
        <td class="b p right">{{ number_format((float) $item->quantity, 2) }}</td>
        <td class="b p right">{{ number_format((float) $item->rate, 2) }}</td>
        <td class="b p center">{{ $item->per ?: $item->unit }}</td>
        <td class="b p right">{{ (float) $item->discount_pct > 0 ? number_format((float) $item->discount_pct, 2) : '' }}</td>
        <td class="b p right">{{ number_format((float) $item->amount, 2) }}</td>
      </tr>
    @endforeach
    @for($r = 0; $r < $fillerRows; $r++)
      <tr>
        <td class="b p"></td><td class="b p"></td><td class="b p"></td><td class="b p"></td>
        <td class="b p"></td><td class="b p"></td><td class="b p"></td><td class="b p"></td>
      </tr>
    @endfor
  </tbody>
  <tfoot>
    <tr>
      <td colspan="7" class="b p right bold">Sub Total</td>
      <td class="b p right bold">{{ number_format((float) $document->subtotal, 2) }}</td>
    </tr>
    @foreach($taxGroups as $tg)
      @if($isIgst)
        <tr>
          <td colspan="7" class="b p right bold">{{ $gstPrefix ? $gstPrefix.' ' : '' }}IGST Tax @ {{ number_format($tg->igst_rate, 0) }}%</td>
          <td class="b p right bold">{{ number_format($tg->igst_amount, 2) }}</td>
        </tr>
      @else
        <tr>
          <td colspan="7" class="b p right bold">{{ $gstPrefix ? $gstPrefix.' ' : '' }}CGST Tax @ {{ number_format($tg->cgst_rate, 0) }}%</td>
          <td class="b p right bold">{{ number_format($tg->cgst_amount, 2) }}</td>
        </tr>
        <tr>
          <td colspan="7" class="b p right bold">{{ $gstPrefix ? $gstPrefix.' ' : '' }}SGST Tax @ {{ number_format($tg->sgst_rate, 0) }}%</td>
          <td class="b p right bold">{{ number_format($tg->sgst_amount, 2) }}</td>
        </tr>
      @endif
    @endforeach
    <tr>
      <td colspan="7" class="b p right bold">Round Off</td>
      <td class="b p right bold">{{ (float) $document->round_off >= 0 ? '+' : '' }}{{ number_format((float) $document->round_off, 2) }}</td>
    </tr>
    <tr>
      <td colspan="3" class="b p right bold" style="font-size: 8.5pt;">Total</td>
      <td class="b p right bold">{{ number_format($totalQty, 2) }} Nos</td>
      <td colspan="3" class="b p"></td>
      <td class="b p right bold" style="font-size: 8.5pt;">&#8377; {{ number_format((float) $document->grand_total, 2) }}</td>
    </tr>
  </tfoot>
</table>

{{-- Amount in words --}}
<table class="box">
  <tr>
    <td class="br p" style="width: 82%;">
      <div class="bold">Amount Chargeable (in words)</div>
      <div class="bold italic">INR {{ NumberToWords::convert((float) $document->grand_total) }}</div>
    </td>
    <td class="p right bold" style="vertical-align: middle;">E. &amp; O.E</td>
  </tr>
  @unless($isPO)
    <tr>
      <td colspan="2" class="p" style="border-top: 0.5pt solid #000;">
        <span class="bold">Tax Amount (in words) :</span>
        <span class="italic">INR {{ NumberToWords::convert($totalTax) }}</span>
      </td>
    </tr>
  @endunless
</table>

{{-- Bank, declaration, signature --}}
<table class="box">
  @if($isPO || $isQuote)
    <tr>
      <td class="br p" style="width: 55%;">
        <div class="bold">Company's PAN: GTQPD3231K</div>
        <div class="bold" style="margin-top: 3pt;">Company's Bank Details</div>
        <table style="margin-top: 1pt;">
          <tr><td style="width: 44%;">A/c Holder's Name :</td><td class="bold">GO CARE SOLUTIONS</td></tr>
          <tr><td>Bank Name :</td><td class="bold">INDIAN BANK</td></tr>
          <tr><td>A/c No. :</td><td class="bold">8168879467</td></tr>
          <tr><td>Branch &amp; IFS Code :</td><td class="bold">Sulur &amp; IDIB000S294</td></tr>
        </table>
      </td>
      <td class="p center" style="width: 45%;">
        <div>for <strong>GO CARE SOLUTIONS</strong></div>
        <div style="margin-top: 28pt; border-top: 0.5pt solid #000; padding-top: 2pt;" class="bold">Authorised Signatory</div>
      </td>
    </tr>
    @if($isPO)
      <tr>
        <td class="br p bold" style="border-top: 0.5pt solid #000;">Created by</td>
        <td class="p bold" style="border-top: 0.5pt solid #000;">Approved by</td>
      </tr>
    @endif
  @else
    <tr>
      <td class="br p" style="width: 40%;">
        <div class="bold">Company's PAN: GTQPD3231K</div>
        <div class="bold" style="margin-top: 3pt;">Declaration</div>
        <div class="small">We Declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</div>
        <div class="small">Invoices not paid beyond 30 days interest of 21% p.a shall be charged.</div>
      </td>
      <td class="br p" style="width: 37%;">
        <div class="bold">Company's Bank Details</div>
        <table style="margin-top: 1pt;">
          <tr><td style="width: 44%;">A/c Holder's Name :</td><td class="bold">GO CARE SOLUTIONS</td></tr>
          <tr><td>Bank Name :</td><td class="bold">INDIAN BANK</td></tr>
          <tr><td>A/c No. :</td><td class="bold">8168879467</td></tr>
          <tr><td>Branch &amp; IFS Code :</td><td class="bold">Sulur &amp; IDIB000S294</td></tr>
        </table>
      </td>
      <td class="p center" style="width: 23%;">
        <div>for <strong>GO CARE SOLUTIONS</strong></div>
        <div style="margin-top: 28pt; border-top: 0.5pt solid #000; padding-top: 2pt;" class="bold">Authorised Signatory</div>
      </td>
    </tr>
    <tr>
      <td colspan="3" class="p bold" style="border-top: 0.5pt solid #000;">Customer's Seal and Signature</td>
    </tr>
  @endif
</table>

@if($isSales)
  <div class="center bold" style="font-size: 7pt; margin-top: 3pt;">SUBJECT TO COIMBATORE JURISDICTION</div>
  <div class="center italic" style="font-size: 6.5pt; color: #555; margin-top: 1pt;">This is a Computer Generated Invoice</div>
@endif

@if($document->notes)
  <div class="b p" style="margin-top: 3pt; font-size: 7pt;">
    <strong>Notes / Terms &amp; Conditions:</strong><br>{{ $document->notes }}
  </div>
@endif

@endif

</body>
</html>
