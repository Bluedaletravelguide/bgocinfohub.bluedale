<tr>
  <td style="padding:0;border-left:1px solid #eee;border-right:1px solid #eee;">
    <!-- Section Title -->
    <div style="background:{{ $title_bg }};color:{{ $title_color }};padding:8px 14px;
                font-weight:700;border-top:2px solid {{ $title_color }};">
      {{ $title }}
    </div>

    <!-- Table -->
    <table role="table" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
      <thead>
        <tr>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">DATE IN</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">DEADLINE</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">ASSIGN BY</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">ASSIGN TO</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">COMPANY</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">PIC</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">PRODUCT</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">TASK</th>
          <th align="left" style="background:{{ $head_bg }};color:{{ $head_color }};padding:8px 10px;font-size:12px;">REMARKS</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $r)
          <tr>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">
              {{ optional(\Carbon\Carbon::parse($r->date_in ?? $r->created_at))->format('d/m/Y') }}
            </td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">
              {{ $r->deadline ? \Carbon\Carbon::parse($r->deadline)->format('d/m/Y') : '-' }}
            </td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->assign_by_id ?? '-' }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->assign_to_id ?? '-' }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->company_id ?? '-' }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->pic_name ?? '-' }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->product_id ?? '-' }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->task ?? '-' }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $r->remarks ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </td>
</tr>
