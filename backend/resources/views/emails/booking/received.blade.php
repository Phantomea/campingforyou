@extends('emails.layout')

@section('title', 'Žiadosť o rezerváciu prijatá')

@php
  $fmt = fn(float $n) => number_format($n, 0, ',', ' ');
  $fmtDate = function(?string $d): string {
    if (!$d) return '';
    $dt = \Carbon\Carbon::parse($d);
    $months = ['januára','februára','marca','apríla','mája','júna',
               'júla','augusta','septembra','októbra','novembra','decembra'];
    return $dt->day . '. ' . $months[$dt->month - 1] . ' ' . $dt->year;
  };
@endphp

@section('content')

  {{-- Icon + heading --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#fff8e1;border-radius:50%;
                width:56px;height:56px;line-height:56px;font-size:26px;margin-bottom:14px;">
      ⏳
    </div>
    <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a1a;font-weight:700;letter-spacing:-0.3px;">
      Rezervácia prijatá, čaká na schválenie
    </h1>
    <p style="margin:0;font-size:15px;color:#666666;line-height:1.5;">
      Dobrý deň, <strong>{{ $booking->customer_name }}</strong>.<br>
      Vašu žiadosť o rezerváciu sme prijali.
    </p>
  </div>

  {{-- Caravan photo --}}
  @if($serviceImageUrl)
  <div style="margin-bottom:20px;border-radius:8px;overflow:hidden;">
    @if($serviceUrl)
      <a href="{{ $serviceUrl }}" style="display:block;">
        <img src="{{ $serviceImageUrl }}" alt="{{ $booking->service->title }}"
             style="display:block;width:100%;max-height:220px;object-fit:cover;border-radius:8px;" />
      </a>
    @else
      <img src="{{ $serviceImageUrl }}" alt="{{ $booking->service->title }}"
           style="display:block;width:100%;max-height:220px;object-fit:cover;border-radius:8px;" />
    @endif
  </div>
  @endif

  {{-- Booking details --}}
  <div style="background:#f7f7f7;border-radius:8px;padding:20px 24px;margin-bottom:24px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;width:40%;vertical-align:top;">Karavan</td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;vertical-align:top;">
          @if($serviceUrl)
            <a href="{{ $serviceUrl }}" style="color:#1a1a1a;text-decoration:none;">{{ $booking->service->title }}</a>
          @else
            {{ $booking->service->title }}
          @endif
        </td>
      </tr>

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">Prevzatie</td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->date_from_formatted }}
          @if($pickupTime)
            <span style="font-size:12px;color:#888888;font-weight:400;margin-left:4px;">
              od {{ $pickupTime }}@if($latestPickupTime) do {{ $latestPickupTime }}@endif
            </span>
          @endif
        </td>
      </tr>

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">Vrátenie</td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->date_to_formatted }}
          @if($returnTime)
            <span style="font-size:12px;color:#888888;font-weight:400;margin-left:4px;">do {{ $returnTime }}</span>
          @endif
        </td>
      </tr>

      @if($booking->total_days)
      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">Počet dní</td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->total_days }}
        </td>
      </tr>
      @endif

      @if($booking->total_price)
      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">Celková cena</td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $fmt($booking->total_price) }} Kč
        </td>
      </tr>
      @endif

    </table>
  </div>

  {{-- Pending notice --}}
  <div style="background:#fff8e1;border-left:4px solid #f9a825;border-radius:4px;
              padding:14px 18px;margin-bottom:24px;">
    <p style="margin:0;font-size:13px;color:#7a5f00;line-height:1.6;">
      <strong>Rezervácia ešte nie je záväzne potvrdená.</strong><br>
      Rezervácia bude potvrdená po prijatí platby na náš bankový účet. Po prijatí platby vám zašleme potvrdzovací e-mail.
    </p>
  </div>

  {{-- Payment section --}}
  @if($upfrontAmount > 0)
  <div style="border:1px solid #e5e5e5;border-radius:8px;overflow:hidden;margin-bottom:24px;">

    {{-- Header --}}
    <div style="background:#1a1a1a;padding:12px 20px;">
      <span style="color:#ffffff;font-size:14px;font-weight:600;">
        💳 Platobné údaje
      </span>
    </div>

    <div style="padding:20px 24px;">

      @php
        $bankDetail = function(string $msg) use ($accountHolderName, $iban, $bankName, $bankCode, $bankBic, $booking) {
          $rows = [];
          if ($accountHolderName) $rows[] = ['Príjemca', '<strong>' . e($accountHolderName) . '</strong>'];
          if ($iban)              $rows[] = ['IBAN', '<span style="font-family:monospace;font-size:12px;">' . e($iban) . '</span>'];
          if ($bankName)          $rows[] = ['Banka', e($bankName) . ($bankCode ? ' (' . e($bankCode) . ')' : '')];
          if ($bankBic)           $rows[] = ['BIC / SWIFT', e($bankBic)];
          $rows[] = ['Variabilný symbol', '<strong>' . e($booking->id) . '</strong>'];
          $rows[] = ['Poznámka', e($msg)];
          $html = '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:16px;border-top:1px solid #e5e5e5;padding-top:12px;">';
          foreach ($rows as [$lbl, $val]) {
            $html .= '<tr>'
              . '<td style="padding:4px 0;font-size:11px;color:#999999;width:38%;vertical-align:top;">' . e($lbl) . '</td>'
              . '<td style="padding:4px 0;font-size:12px;color:#1a1a1a;vertical-align:top;">' . $val . '</td>'
              . '</tr>';
          }
          $html .= '</table>';
          return $html;
        };
      @endphp

      @if($singlePayment)
        {{-- Single payment --}}
        <div style="padding:16px;background:#f7f7f7;border-radius:8px;">
          <div style="text-align:center;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#888888;margin-bottom:6px;">
              Celková platba
            </div>
            <div style="font-size:28px;font-weight:800;color:#1a1a1a;margin-bottom:4px;">
              {{ $fmt($upfrontAmount) }} Kč
            </div>
            @if($deadline1)
            <div style="font-size:13px;color:#2d6a4f;font-weight:600;margin-bottom:16px;">
              splatné do {{ $fmtDate($deadline1) }}
            </div>
            @endif
            @if($qr1)
            <img src="{{ $message->embedData($qr1, 'qr1.png', 'image/png') }}"
                 width="180" height="180" alt="QR platba"
                 style="display:block;margin:0 auto;border-radius:6px;border:1px solid #e5e5e5;" />
            <p style="margin:8px 0 0;font-size:11px;color:#aaaaaa;">Naskenujte QR kód aplikáciou vašej banky</p>
            @endif
          </div>
          {!! $bankDetail($msg1) !!}
        </div>

      @else
        {{-- Split payment --}}
        <div style="padding:16px;background:#f7f7f7;border-radius:8px;margin-bottom:16px;">
          <div style="text-align:center;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#888888;margin-bottom:6px;">
              1. Rezervačná záloha
            </div>
            <div style="font-size:28px;font-weight:800;color:#1a1a1a;margin-bottom:4px;">
              {{ $fmt($upfrontAmount) }} Kč
            </div>
            @if($deadline1)
            <div style="font-size:13px;color:#2d6a4f;font-weight:600;margin-bottom:16px;">
              splatné do {{ $fmtDate($deadline1) }}
            </div>
            @endif
            @if($qr1)
            <img src="{{ $message->embedData($qr1, 'qr1.png', 'image/png') }}"
                 width="180" height="180" alt="QR záloha"
                 style="display:block;margin:0 auto;border-radius:6px;border:1px solid #e5e5e5;" />
            <p style="margin:8px 0 0;font-size:11px;color:#aaaaaa;">QR kód pre zálohu</p>
            @endif
          </div>
          {!! $bankDetail($msg1) !!}
        </div>

        <div style="padding:16px;background:#f7f7f7;border-radius:8px;">
          <div style="text-align:center;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#888888;margin-bottom:6px;">
              2. Doplatok
            </div>
            <div style="font-size:28px;font-weight:800;color:#1a1a1a;margin-bottom:4px;">
              {{ $fmt($remainingAmount) }} Kč
            </div>
            @if($deadline2)
            <div style="font-size:13px;color:#2d6a4f;font-weight:600;margin-bottom:16px;">
              splatné do {{ $fmtDate($deadline2) }}
            </div>
            @endif
            @if($qr2)
            <img src="{{ $message->embedData($qr2, 'qr2.png', 'image/png') }}"
                 width="180" height="180" alt="QR doplatok"
                 style="display:block;margin:0 auto;border-radius:6px;border:1px solid #e5e5e5;" />
            <p style="margin:8px 0 0;font-size:11px;color:#aaaaaa;">QR kód pre doplatok</p>
            @endif
          </div>
          {!! $bankDetail($msg2) !!}
        </div>
      @endif

    </div>
  </div>
  @endif

  {{-- Status URL button --}}
  <div style="text-align:center;margin-bottom:24px;">
    <a href="{{ $statusUrl }}"
       style="display:inline-block;background:#1a1a1a;color:#ffffff;text-decoration:none;
              padding:12px 28px;border-radius:6px;font-size:14px;font-weight:600;">
      Sledovať stav rezervácie
    </a>
    <p style="margin:10px 0 0;font-size:11px;color:#aaaaaa;">{{ $statusUrl }}</p>
  </div>

  {{-- Contact note --}}
  @php $phone = \App\Models\Setting::get('phone'); $contactEmail = \App\Models\Setting::get('email'); @endphp
  <p style="margin:0;font-size:13px;color:#888888;line-height:1.6;text-align:center;">
    Otázky? Kontaktujte nás
    @if($phone || $contactEmail)
      na
      @if($phone) <strong style="color:#555555;">{{ $phone }}</strong> @endif
      @if($phone && $contactEmail) alebo @endif
      @if($contactEmail) <strong style="color:#555555;">{{ $contactEmail }}</strong> @endif.
    @else
      prosím kedykoľvek.
    @endif
  </p>

@endsection
