<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $empresaNome }}</title>
</head>
{{-- Table-based layout with inline styles throughout — no <style> block,
     since Gmail and several corporate mail clients strip <head> styles, and
     no flexbox/grid, since mail-client CSS support is much narrower than a
     browser's. Same cobalt/ink palette as tokens/colors.css and the PDF
     report (resources/views/pdf/relatorio.blade.php), hard-coded as hex
     since email clients (like Dompdf) can't read CSS custom properties. --}}
<body style="margin:0;padding:0;background-color:#f8f9fb;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8f9fb;">
<tr>
<td align="center" style="padding:32px 16px;">

<table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="width:560px;max-width:100%;background-color:#ffffff;border:1px solid #e5e8ef;border-radius:12px;">
{{-- Thin cobalt accent bar — the one deliberate brand touch, per the
     design system's "one saturated accent, used only for..." rule. --}}
<tr>
<td style="background-color:#3348d8;height:4px;line-height:4px;font-size:0;border-radius:12px 12px 0 0;">&nbsp;</td>
</tr>

{{-- Header: logo + empresa nome, same left-aligned convention as the PDF report --}}
<tr>
<td style="padding:28px 32px 0 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
@if($logoUrl)
<td style="width:40px;vertical-align:middle;padding-right:12px;">
<img src="{{ $logoUrl }}" width="36" height="36" alt="{{ $empresaNome }}" style="display:block;width:36px;height:36px;border-radius:6px;">
</td>
@endif
<td style="vertical-align:middle;">
<span style="font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;color:#14171d;">{{ $empresaNome }}</span>
</td>
</tr>
</table>
</td>
</tr>

{{-- Kicker — uppercase, letter-spaced, cobalt: the app's one established
     uppercase-eyebrow convention, doubling here as the email's headline. --}}
<tr>
<td style="padding:22px 32px 0 32px;">
<span style="font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;letter-spacing:.06em;text-transform:uppercase;color:#3348d8;">{{ $kicker }}</span>
</td>
</tr>

@if($saudacao !== '')
<tr>
<td style="padding:8px 32px 0 32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.5;color:#14171d;">
{!! nl2br(e($saudacao)) !!}
</td>
</tr>
@endif

@foreach($paragrafos as $paragrafo)
<tr>
<td style="padding:14px 32px 0 32px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#343a47;">
{!! nl2br(e($paragrafo)) !!}
</td>
</tr>
@endforeach

@if(!empty($resumo))
{{-- Small bordered summary card — same clean-table spirit as the PDF
     report's "resumo" tiles, just label:value rows instead of side-by-side
     tiles (more legible at email width). --}}
<tr>
<td style="padding:20px 32px 0 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e8ef;border-radius:8px;background-color:#f8f9fb;">
@foreach($resumo as $rotulo => $valor)
<tr>
<td style="padding:10px 14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:.03em;color:#6c7484;white-space:nowrap;width:1%;{{ $loop->last ? '' : 'border-bottom:1px solid #e5e8ef;' }}">{{ $rotulo }}</td>
<td style="padding:10px 14px;font-family:'Courier New',Courier,monospace;font-size:13px;font-weight:bold;color:#14171d;text-align:right;{{ $loop->last ? '' : 'border-bottom:1px solid #e5e8ef;' }}">{{ $valor }}</td>
</tr>
@endforeach
</table>
</td>
</tr>
@endif

@if($fecho !== '')
<tr>
<td style="padding:24px 32px 0 32px;">
<div style="border-top:1px solid #f1f3f7;line-height:0;font-size:0;">&nbsp;</div>
</td>
</tr>
<tr>
<td style="padding:14px 32px 0 32px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.6;color:#6c7484;">
{!! nl2br(e($fecho)) !!}
</td>
</tr>
@endif

<tr>
<td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td>
</tr>
</table>

{{-- Footer, outside the card — small, muted "Vencia" attribution, same tone as the PDF footer --}}
<table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="width:560px;max-width:100%;">
<tr>
<td style="padding:16px 32px;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#9aa2b4;">
Enviado automaticamente pelo Vencia.
</td>
</tr>
</table>

</td>
</tr>
</table>
</body>
</html>
