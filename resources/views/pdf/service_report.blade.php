<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 12mm 12mm 12mm 12mm; size: A4 portrait; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 7.2pt; color: #000; }
  table { width: 100%; border-collapse: collapse; }
  td, th { vertical-align: top; }

  .page       { border: 0.8pt solid #000; width: 100%; }
  .inv-company{ font-size: 12pt; font-weight: bold; letter-spacing: .3pt; }
  .small      { font-size: 6.5pt; line-height: 1.5; }
  .center     { text-align: center; }
  .bold       { font-weight: bold; }
  .right      { text-align: right; }

  .hdr        { border-bottom: 0.8pt solid #000; padding: 6pt 8pt; }

  .doc-title  { text-align: center; font-size: 10pt; font-weight: bold;
                letter-spacing: .5pt; padding: 4pt 0;
                border-bottom: 0.8pt solid #000; background: #f5f5f5; }

  .grid td    { border: 0.6pt solid #000; padding: 3pt 5pt; }
  .lbl        { font-size: 6pt; color: #555; margin-bottom: 1pt; }
  .val        { font-weight: bold; font-size: 7.2pt; min-height: 9pt; }

  .sec-head td{ background: #222; color: #fff; font-size: 6.8pt; font-weight: bold;
                text-transform: uppercase; letter-spacing: .4pt; padding: 3pt 5pt; }

  .params th  { background: #444; color: #fff; font-size: 6.8pt; font-weight: bold;
                border: 0.6pt solid #000; padding: 3pt 4pt; }
  .params td  { border: 0.6pt solid #000; padding: 2.5pt 4pt; font-size: 7pt; }
  .params .mand { background: #fffde7; }

  .footer     { border-top: 0.8pt solid #000; padding: 4pt 8pt;
                text-align: center; font-size: 6.5pt; color: #333; }
</style>
</head>
<body>
@php
  $logoPath = str_replace('\\', '/', public_path('GCS logo png.PNG'));
  $params   = $report->parameters ?? [];
  $fmtDate  = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '—';

  $paramDefs = [
    ['af_replaced',                    'Is AF replaced',                                 true,  true,  true],
    ['of_replaced',                    'Is OF replaced',                                 true,  true,  true],
    ['aos_replaced',                   'Is AOS replaced',                                true,  true,  true],
    ['greasing_done',                  'Is Greasing done',                               true,  true,  true],
    ['valve_kit_replaced',             'Is Valve kit replaced',                          true,  true,  true],
    ['pre_filter_replaced',            'Is Pre filter replaced',                         true,  true,  true],
    ['fine_filter_replaced',           'Is Fine filter replaced',                        true,  true,  true],
    ['carbon_filter_replaced',         'Is Carbon Filter replaced',                      true,  true,  true],
    ['oil_used',                       'Oil used',                                       true,  false, false],
    ['ambient_temperature',            'Ambient temperature (°C)',                        true,  true,  false],
    ['discharge_oil_temperature',      'Discharge oil temperature (°C)',                  true,  true,  false],
    ['room_temperature',               'Room temperature (°C)',                           true,  true,  false],
    ['aos_differential_pressure',      'AOS Differential pressure (kg/cm²)',              true,  true,  false],
    ['load_pressure',                  'Load pressure (kg/cm²)',                          true,  true,  false],
    ['unload_pressure',                'Unload pressure (kg/cm²)',                        true,  true,  false],
    ['working_pressure',               'Working pressure (kg/cm²)',                       true,  false, false],
    ['r_load_current',                 'R - Load Current (amps)',                         true,  false, false],
    ['y_load_current',                 'Y - Load Current (amps)',                         true,  false, false],
    ['b_load_current',                 'B - Load Current (amps)',                         true,  false, false],
    ['r_unload_current',               'R - Unload Current (amps)',                       true,  false, false],
    ['y_unload_current',               'Y - Unload Current (amps)',                       true,  false, false],
    ['b_unload_current',               'B - Unload Current (amps)',                       true,  false, false],
    ['fan_motor_current',              'Fan Motor Current (amps)',                        true,  false, false],
    ['incoming_single_phase_current',  'Incoming single phase current (amps)',            true,  false, false],
    ['ry_incoming_voltage',            'RY - Incoming voltage (volts)',                   true,  true,  false],
    ['yb_incoming_voltage',            'YB - Incoming voltage (volts)',                   true,  true,  false],
    ['br_incoming_voltage',            'BR - Incoming voltage (volts)',                   true,  true,  false],
    ['fan_motor_voltage',              'Fan Motor voltage (volts)',                       true,  false, false],
    ['incoming_single_phase_voltage',  'Incoming single phase voltage (volts)',           true,  false, false],
    ['earth_to_neutral_voltage',       'Earth to Neutral voltage (volts)',                true,  false, false],
    ['hmr_last_oil_changed',           'HMR - Last oil changed',                         true,  false, false],
    ['drive_coupling_condition',       'Is the Drive Coupling condition good',            false, true,  false],
    ['cooler_condition',               'Is the Cooler condition good',                    false, true,  false],
    ['pre_filter_condition',           'Is the Pre filter condition good',                false, true,  false],
    ['min_pressure_valve',             'Is the Minimum pressure valve functioning',       false, true,  false],
    ['actuator_functioning',           'Is the Actuator functioning',                    false, true,  false],
    ['intake_valve_functioning',       'Is the Intake valve functioning',                false, true,  false],
    ['blow_down_valve_functioning',    'Is the Blow down valve functioning',              false, true,  false],
    ['pressure_regulator_valve',       'Is the Pressure regulator valve functioning',    false, true,  false],
    ['thermal_valve_element',          'Is the Thermal valve element functioning',        false, true,  false],
    ['safety_valve_functioning',       'Is the Safety valve functioning',                false, true,  false],
    ['solenoid_valve_functioning',     'Is the Solenoid valve functioning',              false, true,  false],
    ['nrv_return_line',                'Is the NRV (Return line) condition good',        false, true,  false],
    ['visual_condition_oil',           'Is the Visual condition of Oil good',            false, true,  false],
    ['air_filter_condition',           'Is the Air Filter condition good',               false, true,  false],
    ['mos_adv_functioning',            'Is the MOS ADV functioning',                    false, true,  false],
    ['load_count',                     'Load count',                                     true,  true,  false],
    ['unload_sump_pressure',           'Unload Sump Pressure (kg/cm²)',                  true,  true,  false],
  ];
@endphp

<div class="page">

  {{-- HEADER --}}
  <table class="hdr">
    <tr>
      <td style="width:60pt; vertical-align:middle; text-align:center;">
        @if(file_exists($logoPath))
          <img src="{{ $logoPath }}" style="width:52pt; height:52pt; object-fit:contain;" />
        @endif
      </td>
      <td style="padding-left:6pt;">
        <div class="inv-company">GO CARE SOLUTIONS</div>
        <div class="small" style="margin-top:3pt;">
          Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street, Irugur, Coimbatore – 641 402<br>
          Mobile: 8148302081 / 9360740074 &nbsp;|&nbsp; Email: gocaresolutions01@gmail.com<br>
          GSTIN: 33GTQPD3231K1ZM &nbsp;|&nbsp; PAN: GTQPD3231K
        </div>
      </td>
      <td style="width:95pt; vertical-align:middle; text-align:right; padding-right:2pt;">
        <div style="font-size:9pt; font-weight:bold;">{{ $report->report_number }}</div>
        <div class="small" style="color:#555; margin-top:2pt;">{{ $fmtDate($report->report_date) }}</div>
      </td>
    </tr>
  </table>

  {{-- TITLE --}}
  <div class="doc-title">CUSTOMER SERVICE REPORT</div>

  {{-- INFO GRID --}}
  <table class="grid">
    <tr>
      <td style="width:25%"><div class="lbl">Report Date</div><div class="val">{{ $fmtDate($report->report_date) }}</div></td>
      <td style="width:25%"><div class="lbl">Report No</div><div class="val">{{ $report->report_number }}</div></td>
      <td style="width:25%"><div class="lbl">Type of Call</div><div class="val">{{ $report->service_type }}</div></td>
      <td style="width:25%"><div class="lbl">Fabrication Number</div><div class="val">{{ $report->fabrication_number ?? '—' }}</div></td>
    </tr>
    <tr>
      <td colspan="2"><div class="lbl">Company Name</div><div class="val">{{ $report->company_name ?? '—' }}</div></td>
      <td><div class="lbl">Compressor Model</div><div class="val">{{ $report->compressor_model ?? '—' }}</div></td>
      <td><div class="lbl">Site Location</div><div class="val">{{ $report->site_location ?? '—' }}</div></td>
    </tr>
    <tr>
      <td><div class="lbl">Site Person Name</div><div class="val">{{ $report->site_person_name ?? '—' }}</div></td>
      <td><div class="lbl">Site Person Number</div><div class="val">{{ $report->site_person_number ?? '—' }}</div></td>
      <td colspan="2"><div class="lbl">Site Person Email</div><div class="val">{{ $report->site_person_mail ?? '—' }}</div></td>
    </tr>
    <tr>
      <td><div class="lbl">AMC Status</div><div class="val">{{ $report->amc_status ?? '—' }}</div></td>
      <td><div class="lbl">AMC Registration No</div><div class="val">{{ $report->amc_registration_no ?? '—' }}</div></td>
      <td><div class="lbl">AMC Visit No</div><div class="val">{{ $report->amc_visit_no ?? '—' }}</div></td>
      <td><div class="lbl">Next Service Due On</div><div class="val">{{ $fmtDate($report->next_service_due_on) }}</div></td>
    </tr>
    <tr>
      <td><div class="lbl">Load HMR</div><div class="val">{{ $report->load_hmr ?? '—' }}</div></td>
      <td><div class="lbl">Unload HMR</div><div class="val">{{ $report->unload_hmr ?? '—' }}</div></td>
      <td><div class="lbl">Total HMR</div><div class="val bold">{{ $report->total_hmr ?? '—' }}</div></td>
      <td><div class="lbl">Service Dealer</div><div class="val">{{ $report->dealer ?? '—' }}</div></td>
    </tr>
  </table>

  {{-- PARAMETERS --}}
  <table class="params">
    <thead>
      <tr>
        <th style="width:56%; text-align:left; padding-left:6pt;">Parameters</th>
        <th style="width:22%">Actuals</th>
        <th style="width:22%">Response</th>
      </tr>
    </thead>
    <tbody>
      @foreach($paramDefs as [$key, $label, $hasActual, $hasResponse, $mandatory])
        @php
          $p             = $params[$key] ?? [];
          $actual        = $hasActual   ? ($p['actual']   ?? '') : '';
          $response      = $hasResponse ? ($p['response'] ?? '') : '';
          $displayActual = $actual !== '' ? $actual : ($mandatory ? 'N/A' : '');
        @endphp
        <tr class="{{ $mandatory ? 'mand' : '' }}">
          <td style="padding-left:6pt;">{{ $mandatory ? '★ ' : '' }}{{ $label }}</td>
          <td class="center">{{ $displayActual }}</td>
          <td class="center">{{ $response }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- WORK DONE --}}
  <table class="grid">
    <tr class="sec-head"><td colspan="3">Work Done</td></tr>
    <tr>
      <td style="width:30%"><div class="lbl">No. of Visits Made</div><div class="val">{{ $report->no_of_visits ?? '—' }}</div></td>
      <td style="width:35%"><div class="lbl">Service Charges Applicable</div><div class="val">{{ $report->service_charges_applicable ? 'Yes' : 'No' }}</div></td>
      <td><div class="lbl">Service Charges</div><div class="val">{{ $report->service_charges ? '₹ ' . number_format((float)$report->service_charges, 2) : '—' }}</div></td>
    </tr>
    <tr>
      <td colspan="3"><div class="lbl">Parts Recommended for Service</div><div class="val" style="min-height:14pt;">{{ $report->parts_recommended ?? '' }}</div></td>
    </tr>
    <tr>
      <td colspan="3"><div class="lbl">Work Done</div><div class="val" style="min-height:30pt;">{{ $report->work_done ?? '' }}</div></td>
    </tr>
  </table>

  {{-- ENGINEER & FEEDBACK --}}
  <table class="grid">
    <tr class="sec-head"><td colspan="2">Engineer &amp; Feedback</td></tr>
    <tr>
      <td style="width:50%"><div class="lbl">Engineer</div><div class="val">{{ $report->engineer ?? '—' }}</div></td>
      <td><div class="lbl">Contact No</div><div class="val">{{ $report->engineer_contact ?? '—' }}</div></td>
    </tr>
    <tr>
      <td>
        <div class="lbl">Customer Feedback</div>
        <div class="val">
          {{ $report->customer_feedback ?? '—' }}
          @if($report->customer_feedback_percentage !== null)
            &nbsp;({{ $report->customer_feedback_percentage }}%)
          @endif
        </div>
      </td>
      <td><div class="lbl">Feedback Remarks</div><div class="val">{{ $report->customer_feedback_remarks ?? '' }}</div></td>
    </tr>
    <tr>
      <td colspan="2"><div class="lbl">Engineer Remarks</div><div class="val" style="min-height:18pt;">{{ $report->engineer_remarks ?? '' }}</div></td>
    </tr>
  </table>

  {{-- SIGNATURE --}}
  <table class="grid">
    <tr class="sec-head"><td colspan="2">Authorised Signature</td></tr>
    <tr>
      <td style="width:50%; padding:6pt 8pt;">
        <div class="lbl">Name</div>
        <div style="height:42pt;"></div>
        <div class="bold" style="font-size:7pt; border-top:0.5pt solid #bbb; padding-top:3pt;">{{ $report->engineer ?? '' }}</div>
      </td>
      <td style="text-align:center; padding:6pt 8pt;">
        <div class="lbl" style="margin-bottom:4pt;">Customer Signature</div>
        @if($report->signature)
          <img src="{{ $report->signature }}" style="max-width:140pt; max-height:50pt; object-fit:contain;" />
        @else
          <div style="height:50pt;"></div>
        @endif
        <div style="border-top:0.5pt solid #bbb; margin-top:3pt; padding-top:2pt; font-size:6.5pt; color:#555;">Signature</div>
      </td>
    </tr>
  </table>

  {{-- FOOTER --}}
  <div class="footer">
    GO CARE SOLUTIONS &nbsp;|&nbsp; Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street, Irugur, Coimbatore – 641 402
    &nbsp;|&nbsp; Mobile: 8148302081 / 9360740074 &nbsp;|&nbsp; Email: gocaresolutions01@gmail.com
  </div>

</div>
</body>
</html>
