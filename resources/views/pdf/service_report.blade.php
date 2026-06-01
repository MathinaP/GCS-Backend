<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 33mm 10mm 16mm 10mm; size: A4 portrait; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 7.5pt; color: #000; }
  table { width: 100%; border-collapse: collapse; }
  td, th { vertical-align: middle; }

  /* ── Fixed header — repeats every page ── */
  #hdr {
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 30mm;
    background: #fff;
    border-bottom: 1.2pt solid #000;
    padding: 5pt 8pt 4pt;
  }
  #hdr table { border: none; border-collapse: collapse; }
  #hdr td    { border: none; padding: 0; vertical-align: middle; }
  .co-name   { font-size: 13pt; font-weight: bold; letter-spacing: .3pt; }
  .co-info   { font-size: 6.8pt; color: #333; line-height: 1.6; margin-top: 3pt; }

  /* ── Fixed footer — repeats every page ── */
  #ftr {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 13mm;
    background: #fff;
    border-top: 1pt solid #000;
    padding: 3pt 8pt;
    text-align: center;
    font-size: 6.5pt;
    color: #333;
  }

  /* ── Doc title ── */
  .doc-title {
    text-align: center; font-size: 10pt; font-weight: bold;
    letter-spacing: .5pt; padding: 4pt 0;
    background: #efefef;
    border: 0.6pt solid #000; border-bottom: none;
  }

  /* ── Info grid ── */
  .ig td  { border: 0.6pt solid #000; padding: 3pt 5pt; vertical-align: middle; }
  .lbl    { font-weight: bold; font-size: 6.8pt; text-transform: uppercase;
            white-space: nowrap; width: 1%; }
  .val    { font-size: 7.5pt; }

  /* ── Parameters ── */
  .pt th  { border: 0.6pt solid #000; padding: 3.5pt 5pt; font-size: 7.5pt;
            font-weight: bold; text-transform: uppercase; background: #d8d8d8; }
  .pt td  { border: 0.6pt solid #000; padding: 2.5pt 5pt; font-size: 7pt;
            vertical-align: middle; }
  .mand   { background: #fffde7; }

  /* ── Section head row ── */
  .sh td  { border: 0.6pt solid #000; padding: 3pt 5pt; font-weight: bold;
            font-size: 7.5pt; text-transform: uppercase; background: #d8d8d8; }

  /* ── Signature ── */
  .sig-td { border: 0.6pt solid #000; padding: 5pt 8pt; height: 58pt;
            text-align: center; font-weight: bold; font-size: 7pt;
            text-transform: uppercase; vertical-align: bottom; }
</style>
</head>
<body>
@php
  $logoPath = str_replace('\\', '/', public_path('GCS logo png.PNG'));
  $p        = $report->parameters ?? [];
  $fmtD     = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '';

  $paramDefs = [
    ['af_replaced',                   'Is AF replaced',                               true,  true,  true ],
    ['of_replaced',                   'Is OF replaced',                               true,  true,  true ],
    ['aos_replaced',                  'Is AOS replaced',                              true,  true,  true ],
    ['greasing_done',                 'Is Greasing done',                             true,  true,  true ],
    ['valve_kit_replaced',            'Is Valve kit replaced',                        true,  true,  true ],
    ['pre_filter_replaced',           'Is Pre filter replaced',                       true,  true,  true ],
    ['fine_filter_replaced',          'Is Fine filter replaced',                      true,  true,  true ],
    ['carbon_filter_replaced',        'Is Carbon Filter replaced',                    true,  true,  true ],
    ['oil_used',                      'Oil used',                                     true,  false, false],
    ['ambient_temperature',           'Ambient temperature (°C)',                      true,  true,  false],
    ['discharge_oil_temperature',     'Discharge oil temperature (°C)',                true,  true,  false],
    ['room_temperature',              'Room temperature (°C)',                         true,  true,  false],
    ['aos_differential_pressure',     'AOS Differential pressure (kg/cm²)',            true,  true,  false],
    ['load_pressure',                 'Load pressure (kg/cm²)',                        true,  true,  false],
    ['unload_pressure',               'Unload pressure (kg/cm²)',                      true,  true,  false],
    ['working_pressure',              'Working pressure (kg/cm²)',                     true,  false, false],
    ['r_load_current',                'R - Load Current (amps)',                       true,  false, false],
    ['y_load_current',                'Y - Load Current (amps)',                       true,  false, false],
    ['b_load_current',                'B - Load Current (amps)',                       true,  false, false],
    ['r_unload_current',              'R - Unload Current (amps)',                     true,  false, false],
    ['y_unload_current',              'Y - Unload Current (amps)',                     true,  false, false],
    ['b_unload_current',              'B - Unload Current (amps)',                     true,  false, false],
    ['fan_motor_current',             'Fan Motor Current (amps)',                      true,  false, false],
    ['incoming_single_phase_current', 'Incoming single phase current (amps)',          true,  false, false],
    ['ry_incoming_voltage',           'RY - Incoming voltage (volts)',                 true,  true,  false],
    ['yb_incoming_voltage',           'YB - Incoming voltage (volts)',                 true,  true,  false],
    ['br_incoming_voltage',           'BR - Incoming voltage (volts)',                 true,  true,  false],
    ['fan_motor_voltage',             'Fan Motor voltage (volts)',                     true,  false, false],
    ['incoming_single_phase_voltage', 'Incoming single phase voltage (volts)',         true,  false, false],
    ['earth_to_neutral_voltage',      'Earth to Neutral voltage (volts)',               true,  false, false],
    ['hmr_last_oil_changed',          'HMR - Last oil changed',                        true,  false, false],
    ['drive_coupling_condition',      'Is the Drive Coupling condition good',           false, true,  false],
    ['cooler_condition',              'Is the Cooler condition good',                   false, true,  false],
    ['pre_filter_condition',          'Is the Pre filter condition good',               false, true,  false],
    ['min_pressure_valve',            'Is the Minimum pressure valve functioning',      false, true,  false],
    ['actuator_functioning',          'Is the Actuator functioning',                   false, true,  false],
    ['intake_valve_functioning',      'Is the Intake valve functioning',               false, true,  false],
    ['blow_down_valve_functioning',   'Is the Blow down valve functioning',             false, true,  false],
    ['pressure_regulator_valve',      'Is the Pressure regulator valve functioning',   false, true,  false],
    ['thermal_valve_element',         'Is the Thermal valve element functioning',       false, true,  false],
    ['safety_valve_functioning',      'Is the Safety valve functioning',               false, true,  false],
    ['solenoid_valve_functioning',    'Is the Solenoid valve functioning',             false, true,  false],
    ['nrv_return_line',               'Is the NRV (Return line) condition good',       false, true,  false],
    ['visual_condition_oil',          'Is the Visual condition of Oil good',           false, true,  false],
    ['air_filter_condition',          'Is the Air Filter condition good',              false, true,  false],
    ['mos_adv_functioning',           'Is the MOS ADV functioning',                   false, true,  false],
    ['load_count',                    'Load count',                                    true,  true,  false],
    ['unload_sump_pressure',          'Unload Sump Pressure (kg/cm²)',                 true,  true,  false],
  ];
@endphp

{{-- ══ FIXED HEADER ══ --}}
<div id="hdr">
  <table>
    <tr>
      <td style="width:62pt; text-align:center; padding-right:6pt;">
        @if(file_exists($logoPath))
          <img src="{{ $logoPath }}" style="width:56pt; height:56pt; object-fit:contain;" />
        @endif
      </td>
      <td>
        <div class="co-name">GO CARE SOLUTIONS</div>
        <div class="co-info">
          Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street, Irugur, Coimbatore – 641 402<br>
          Mobile: 8148302081 / 9360740074 &nbsp;|&nbsp; Email: gocaresolutions01@gmail.com<br>
          GSTIN: 33GTQPD3231K1ZM &nbsp;|&nbsp; PAN: GTQPD3231K
        </div>
      </td>
      <td style="width:88pt; text-align:right;">
        <div style="font-size:9pt; font-weight:bold;">{{ $report->report_number }}</div>
        <div style="font-size:7pt; color:#555; margin-top:2pt;">{{ $fmtD($report->report_date) }}</div>
      </td>
    </tr>
  </table>
</div>

{{-- ══ FIXED FOOTER ══ --}}
<div id="ftr">
  GO CARE SOLUTIONS &nbsp;|&nbsp; Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street, Irugur, Coimbatore – 641 402
  &nbsp;|&nbsp; Mobile: 8148302081 / 9360740074 &nbsp;|&nbsp; Email: gocaresolutions01@gmail.com
</div>

{{-- ══ CONTENT ══ --}}

<div class="doc-title">CUSTOMER SERVICE REPORT</div>

<table class="ig">
  <tr>
    <td class="lbl">Report Date</td>
    <td class="val">{{ $fmtD($report->report_date) }}</td>
    <td class="lbl">Report No</td>
    <td class="val">{{ $report->report_number }}</td>
  </tr>
  <tr>
    <td class="lbl">Type of Call</td>
    <td class="val">{{ $report->service_type }}</td>
    <td class="lbl">Fabrication Number</td>
    <td class="val">{{ $report->fabrication_number ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Company Name</td>
    <td class="val" colspan="3">{{ $report->company_name ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Compressor Model</td>
    <td class="val">{{ $report->compressor_model ?? '' }}</td>
    <td class="lbl">Site Location</td>
    <td class="val">{{ $report->site_location ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Site Person Name</td>
    <td class="val">{{ $report->site_person_name ?? '' }}</td>
    <td class="lbl">Site Person Number</td>
    <td class="val">{{ $report->site_person_number ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Site Person Email</td>
    <td class="val" colspan="3">{{ $report->site_person_mail ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">AMC Status</td>
    <td class="val">{{ $report->amc_status ?? '' }}</td>
    <td class="lbl">AMC Registration No</td>
    <td class="val">{{ $report->amc_registration_no ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">AMC Visit No</td>
    <td class="val">{{ $report->amc_visit_no ?? '' }}</td>
    <td class="lbl">Next Service Due On</td>
    <td class="val">{{ $fmtD($report->next_service_due_on) }}</td>
  </tr>
  <tr>
    <td class="lbl">Load HMR</td>
    <td class="val">{{ $report->load_hmr ?? '' }}</td>
    <td class="lbl">Unload HMR</td>
    <td class="val">{{ $report->unload_hmr ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Total HMR</td>
    <td class="val"><strong>{{ $report->total_hmr ?? '' }}</strong></td>
    <td class="lbl">Service Dealer</td>
    <td class="val">{{ $report->dealer ?? '' }}</td>
  </tr>
</table>

<table class="pt">
  <thead>
    <tr>
      <th style="width:58%; text-align:left;">Parameters</th>
      <th style="width:21%; text-align:center;">Actuals</th>
      <th style="width:21%; text-align:center;">Response</th>
    </tr>
  </thead>
  <tbody>
    @foreach($paramDefs as [$key, $label, $hasActual, $hasResponse, $mandatory])
      @php
        $pv      = $p[$key] ?? [];
        $actual  = $hasActual   ? ($pv['actual']   ?? '') : '';
        $resp    = $hasResponse ? ($pv['response'] ?? '') : '';
        $display = $actual !== '' ? $actual : ($mandatory ? 'NA' : '');
      @endphp
      <tr class="{{ $mandatory ? 'mand' : '' }}">
        <td>{{ $label }}</td>
        <td style="text-align:center;">{{ $display }}</td>
        <td style="text-align:center;">{{ $resp }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<table class="ig">
  <tr class="sh"><td colspan="4">Work Done</td></tr>
  <tr>
    <td class="lbl">No of Visits Made</td>
    <td class="val">{{ $report->no_of_visits ?? '' }}</td>
    <td class="lbl">Is Service Charges Applicable</td>
    <td class="val">{{ $report->service_charges_applicable ? 'Yes' : 'No' }}</td>
  </tr>
  <tr>
    <td class="lbl">Service Charges</td>
    <td class="val" colspan="3">{{ $report->service_charges ? '₹ ' . number_format((float)$report->service_charges, 2) : '' }}</td>
  </tr>
  <tr>
    <td class="lbl" style="white-space:normal;">Parts Recommended<br>for Service</td>
    <td class="val" colspan="3" style="min-height:16pt;">{{ $report->parts_recommended ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Work Done</td>
    <td class="val" colspan="3" style="min-height:36pt;">{{ $report->work_done ?? '' }}</td>
  </tr>
</table>

<table class="ig">
  <tr>
    <td class="lbl">Engineer</td>
    <td class="val">{{ $report->engineer ?? '' }}</td>
    <td class="lbl">Contact No</td>
    <td class="val">{{ $report->engineer_contact ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Dealer</td>
    <td class="val" colspan="3">{{ $report->dealer ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Customer Feedback</td>
    <td class="val">
      {{ $report->customer_feedback ?? '' }}
      @if($report->customer_feedback_percentage !== null)
        &nbsp;{{ $report->customer_feedback_percentage }}%
      @endif
    </td>
    <td class="lbl">Feedback Remarks</td>
    <td class="val">{{ $report->customer_feedback_remarks ?? '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Engineer Remarks</td>
    <td class="val" colspan="3" style="min-height:20pt;">{{ $report->engineer_remarks ?? '' }}</td>
  </tr>
</table>

<table style="border-collapse:collapse; width:100%;">
  <tr>
    <td class="sig-td" style="width:50%;">&nbsp;</td>
    <td class="sig-td" style="width:50%;">
      @if($report->signature)
        <img src="{{ $report->signature }}" style="max-width:155pt; max-height:50pt; object-fit:contain; display:block; margin:0 auto 4pt;" />
      @endif
      Signature
    </td>
  </tr>
</table>

</body>
</html>
