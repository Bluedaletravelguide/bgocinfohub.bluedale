@php
  $tz = config('app.timezone', 'Asia/Kuala_Lumpur');
  $generatedAt = \Carbon\Carbon::now()->timezone($tz)->format('d/m/Y H:i');
@endphp
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>BGOC INFORMATION HUB — Daily Reminder</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Inline styles for email-client safety -->
</head>
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
    <tr>
      <td align="center" style="padding:16px;">
        <table role="presentation" width="900" cellpadding="0" cellspacing="0" style="width:900px;max-width:100%;border-collapse:collapse;">
          <!-- Title Bar -->
          <tr>
            <td style="background:#ffe600;color:#111;padding:10px 14px;font-weight:700;font-size:16px;border:1px solid #d8cb00;">
              BGOC INFORMATION HUB — DAILY REMINDER
            </td>
          </tr>
          <!-- Generated at row -->
          <tr>
            <td style="background:#ffffcc;color:#111;padding:6px 14px;font-size:12px;border-left:1px solid #eee;border-right:1px solid #eee;border-bottom:1px solid #eee;">
              Generated at: <strong>{{ $generatedAt }}</strong>
            </td>
          </tr>

          {{-- EXPIRED --}}
          @if(!empty($data['expired']) && count($data['expired'])>0)
            @include('emails.partials._status_block', [
              'title' => 'EXPIRED',
              'rows'  => $data['expired'],
              // red theme
              'title_bg' => '#ffe9e9',
              'title_color' => '#a60000',
              'head_bg' => '#1f2937',   // dark header like your sheet
              'head_color' => '#ffffff'
            ])
          @endif

          {{-- PENDING --}}
          @if(!empty($data['pending']) && count($data['pending'])>0)
            @include('emails.partials._status_block', [
              'title' => 'PENDING',
              'rows'  => $data['pending'],
              // orange-ish theme
              'title_bg' => '#fff3e0',
              'title_color' => '#a35a00',
              'head_bg' => '#1f2937',
              'head_color' => '#ffffff'
            ])
          @endif

          {{-- IN PROGRESS --}}
          @if(!empty($data['in_progress']) && count($data['in_progress'])>0)
            @include('emails.partials._status_block', [
              'title' => 'IN PROGRESS',
              'rows'  => $data['in_progress'],
              // blue theme
              'title_bg' => '#eaf3ff',
              'title_color' => '#0b4fb3',
              'head_bg' => '#1f2937',
              'head_color' => '#ffffff'
            ])
          @endif

          {{-- COMPLETED --}}
          @if(!empty($data['completed']) && count($data['completed'])>0)
            @include('emails.partials._status_block', [
              'title' => 'COMPLETED',
              'rows'  => $data['completed'],
              // green theme
              'title_bg' => '#eaf6ed',
              'title_color' => '#176b34',
              'head_bg' => '#1f2937',
              'head_color' => '#ffffff'
            ])
          @endif

          <tr>
            <td style="padding:10px 14px;color:#6b7280;font-size:12px;border:1px solid #eee;border-top:none;">
              — BGOC System
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
