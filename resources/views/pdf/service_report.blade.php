<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 10mm 10mm 10mm 10mm; size: A4 portrait; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 7pt; color: #000; }
  table { width: 100%; border-collapse: collapse; }
  td, th { vertical-align: top; }
  .page { border: 0.8pt solid #000; }
  .header-row { border-bottom: 0.8pt solid #000; padding: 5pt 7pt; }
  .co-name { font-size: 10pt; font-weight: bold; }
  .co-info { font-size: 6.5pt; line-height: 1.5; margin-top: 2pt; }
  .report-no { font-size: 8pt; font-weight: bold; text-align: right; }
  .report-date-lbl { font-size: 6pt; color: #555; text-align: right; }
  .doc-title { text-align: center; font-size: 10.5pt; font-weight: bold; padding: 4pt 0; border-bottom: 0.8pt solid #000; background: #f2f2f2; }
  .info-grid td { border: 0.5pt solid #000; padding: 2.5pt 4pt; }
  .lbl { font-size: 6pt; color: #555; }
  .val { font-weight: bold; font-size: 7pt; min-height: 9pt; }
  .section-head { background: #333; color: #fff; font-size: 7pt; font-weight: bold; padding: 3pt 5pt; text-transform: uppercase; letter-spacing: 0.3pt; }
  .params th { background: #555; color: #fff; text-align: center; padding: 3pt; font-size: 6.5pt; border: 0.5pt solid #000; }
  .params td { border: 0.5pt solid #000; padding: 2.5pt 4pt; font-size: 6.5pt; }
  .params .mandatory { background: #fffde7; }
  .center { text-align: center; }
  .bold { font-weight: bold; }
  .page-footer { text-align: center; font-size: 6pt; color: #444; border-top: 0.8pt solid #000; padding: 3pt; margin-top: 4pt; }
</style>
</head>
<body>
@php
  $logoPath = public_path('logo.png');
  $params   = $report->parameters ?? [];
  $fmtDate  = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '—';

  // [key, label, hasActual, hasResponse, mandatory]
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
  <table class="header-row">
    <tr>
      <td style="width: 55pt; vertical-align: middle;">
        @if(file_exists($logoPath))
          <img src="{{ $logoPath }}" style="width: 50pt; height: 50pt; object-fit: contain;" />
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
      <td style="width: 90pt; vertical-align: middle; text-align: right; padding-right: 3pt;">
        <div class="report-no">{{ $report->report_number }}</div>
        <div class="report-date-lbl">{{ $fmtDate($report->report_date) }}</div>
      </td>
    </tr>
  </table>

  {{-- TITLE --}}
  <div class="doc-title">CUSTOMER SERVICE REPORT</div>

  {{-- INFO GRID --}}
  <table class="info-grid">
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
  <div style="margin-top: 5pt;">
    <table class="params">
      <thead>
        <tr>
          <th style="width:55%; text-align:left; padding-left:5pt;">PARAMETERS</th>
          <th style="width:22%">ACTUALS</th>
          <th style="width:23%">RESPONSE</th>
        </tr>
      </thead>
      <tbody>
        @foreach($paramDefs as [$key, $label, $hasActual, $hasResponse, $mandatory])
          @php
            $p        = $params[$key] ?? [];
            $actual   = $hasActual   ? ($p['actual']   ?? '') : '';
            $response = $hasResponse ? ($p['response'] ?? '') : '';
            $displayActual = $actual !== '' ? $actual : ($mandatory ? 'N/A' : '');
          @endphp
          <tr class="{{ $mandatory ? 'mandatory' : '' }}">
            <td>{{ $label }}</td>
            <td class="center">{{ $displayActual }}</td>
            <td class="center">{{ $response }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- WORK DONE --}}
  <div style="margin-top: 5pt;">
    <table class="info-grid">
      <tr>
        <td style="width:30%"><div class="lbl">NO OF VISITS MADE</div><div class="val">{{ $report->no_of_visits ?? '—' }}</div></td>
        <td style="width:35%"><div class="lbl">IS SERVICE CHARGES APPLICABLE</div><div class="val">{{ $report->service_charges_applicable ? 'Yes' : 'No' }}</div></td>
        <td><div class="lbl">SERVICE CHARGES</div><div class="val">{{ $report->service_charges ? '₹ ' . number_format((float)$report->service_charges, 2) : '—' }}</div></td>
      </tr>
      <tr>
        <td colspan="3"><div class="lbl">PARTS RECOMMENDED FOR SERVICE</div><div class="val" style="min-height:16pt;">{{ $report->parts_recommended ?? '' }}</div></td>
      </tr>
      <tr>
        <td colspan="3"><div class="lbl">WORK DONE</div><div class="val" style="min-height:35pt;">{{ $report->work_done ?? '' }}</div></td>
      </tr>
    </table>
  </div>

  {{-- ENGINEER / FEEDBACK --}}
  <div style="margin-top: 5pt;">
    <table class="info-grid">
      <tr>
        <td style="width:50%"><div class="lbl">ENGINEER</div><div class="val">{{ $report->engineer ?? '—' }}</div></td>
        <td><div class="lbl">CONTACT NO</div><div class="val">{{ $report->engineer_contact ?? '—' }}</div></td>
      </tr>
      <tr>
        <td colspan="2"><div class="lbl">DEALER</div><div class="val">{{ $report->dealer ?? '—' }}</div></td>
      </tr>
      <tr>
        <td>
          <div class="lbl">CUSTOMER FEEDBACK</div>
          <div class="val">
            {{ $report->customer_feedback ?? '—' }}
            @if($report->customer_feedback_percentage !== null)
              &nbsp;({{ $report->customer_feedback_percentage }}%)
            @endif
          </div>
        </td>
        <td><div class="lbl">FEEDBACK REMARKS</div><div class="val">{{ $report->customer_feedback_remarks ?? '' }}</div></td>
      </tr>
      <tr>
        <td colspan="2"><div class="lbl">ENGINEER REMARKS</div><div class="val" style="min-height:20pt;">{{ $report->engineer_remarks ?? '' }}</div></td>
      </tr>
    </table>
  </div>

  {{-- SIGNATURE --}}
  <div style="margin-top: 5pt;">
    <table class="info-grid">
      <tr>
        <td style="width:50%; min-height:65pt; padding: 5pt 8pt;">
          <div class="lbl">NAME</div>
          <div style="height: 45pt;"></div>
          <div class="bold" style="font-size:7pt;">{{ $report->engineer ?? '' }}</div>
        </td>
        <td style="text-align:center; padding:5pt;">
          <div class="lbl" style="margin-bottom:4pt;">SIGNATURE</div>
          @if($report->signature)
            <img src="{{ $report->signature }}" style="max-width:140pt; max-height:55pt; object-fit:contain;" />
          @else
            <div style="height:55pt;"></div>
          @endif
        </td>
      </tr>
    </table>
  </div>

  {{-- FOOTER --}}
  <div class="page-footer">
    GO CARE SOLUTIONS &nbsp;|&nbsp; Old No. 14/36-D, New No. 36-D, Mahaliamman Kovil Street, Irugur, Coimbatore – 641 402
    &nbsp;|&nbsp; Mobile: 8148302081 / 9360740074 &nbsp;|&nbsp; Email: gocaresolutions01@gmail.com
  </div>

</div>
</body>
</html>
