<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6;">
<p>Dear {{ $report->site_person_name ?? 'Customer' }},</p>
<p>
  Please find attached the <strong>Service Report {{ $report->report_number }}</strong>
  for Fabrication Number <strong>{{ $report->fabrication_number }}</strong>.
</p>
<p>
  <strong>Report Date:</strong> {{ $report->report_date?->format('d-m-Y') ?? '—' }}<br>
  <strong>Type of Call:</strong> {{ $report->service_type }}<br>
  <strong>Engineer:</strong> {{ $report->engineer }}
</p>
<p>Thank you for choosing Go Care Solutions.</p>
<p>
  Regards,<br>
  <strong>{{ $report->engineer ?? 'Go Care Solutions' }}</strong><br>
  {{ $report->engineer_contact ?? '' }}<br>
  Go Care Solutions
</p>
</body>
</html>
