<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', "CampingForYou")</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f0f0;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0"
         style="background-color:#f0f0f0;min-height:100vh;">
    <tr>
      <td align="center" style="padding:40px 16px;">

        {{-- Logo --}}
        <div style="margin-bottom:24px;">
          <img src="{{ env('FRONTEND_URL', 'http://localhost:9000') }}/logo.png"
               alt="CampingForYou"
               width="auto"
               height="56"
               style="display:block;margin:0 auto;max-width:200px;height:56px;">
        </div>

        {{-- White card --}}
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
               style="max-width:560px;background:#ffffff;border-radius:12px;
                      box-shadow:0 4px 20px rgba(0,0,0,0.10);overflow:hidden;">
          <tr>
            <td>

              {{-- Top accent stripe --}}
              <div style="height:5px;background:#1a1a1a;border-radius:12px 12px 0 0;"></div>

              {{-- Main content --}}
              <div style="padding:36px 40px 28px;">
                @yield('content')
              </div>

              {{-- Card footer --}}
              <div style="border-top:1px solid #eeeeee;padding:18px 40px;background:#f9f9f9;
                          border-radius:0 0 12px 12px;">
                <p style="margin:0;font-size:12px;color:#aaaaaa;text-align:center;line-height:1.6;">
                  CampingForYou
                  @php $addr = \App\Models\Setting::get('address'); $phone = \App\Models\Setting::get('phone'); @endphp
                  @if($addr) &nbsp;&bull;&nbsp;{{ $addr }} @endif
                  @if($phone) &nbsp;&bull;&nbsp;{{ $phone }} @endif
                </p>
              </div>

            </td>
          </tr>
        </table>

        {{-- Below-card note --}}
        <p style="margin:20px 0 0;font-size:11px;color:#cccccc;text-align:center;">
          Tento e-mail bol odoslaný automaticky. Prosím, neodpovedajte naň.
        </p>

      </td>
    </tr>
  </table>

</body>
</html>
