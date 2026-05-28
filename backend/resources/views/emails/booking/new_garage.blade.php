@extends('emails.layout')

@section('title', $autoConfirmed ? 'Nová rezervácia karavanu (automaticky potvrdená)' : 'Nová rezervácia karavanu čaká na schválenie')

@section('content')

  {{-- Icon + heading --}}
  <div style="text-align:center;margin-bottom:28px;">
    @if($autoConfirmed)
      <div style="display:inline-block;background:#e8f5e9;border-radius:50%;
                  width:56px;height:56px;line-height:56px;font-size:26px;margin-bottom:14px;">
        ✓
      </div>
      <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a1a;font-weight:700;letter-spacing:-0.3px;">
        Nová rezervácia karavanu
      </h1>
      <p style="margin:0;font-size:15px;color:#666666;line-height:1.5;">
        Zákazník si zarezervoval karavan, ktorý bol <strong>automaticky potvrdený</strong>.
      </p>
    @else
      <div style="display:inline-block;background:#fff8e1;border-radius:50%;
                  width:56px;height:56px;line-height:56px;font-size:26px;margin-bottom:14px;">
        !
      </div>
      <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a1a;font-weight:700;letter-spacing:-0.3px;">
        Rezervácia karavanu čaká na schválenie
      </h1>
      <p style="margin:0;font-size:15px;color:#666666;line-height:1.5;">
        Zákazník si zarezervoval karavan a čaká na <strong>vaše schválenie</strong>.
      </p>
    @endif
  </div>

  {{-- Booking details --}}
  <div style="background:#f7f7f7;border-radius:8px;padding:20px 24px;margin-bottom:24px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;width:38%;vertical-align:top;">
          Zákazník
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;vertical-align:top;">
          {{ $booking->customer_name }}
        </td>
      </tr>

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Telefón
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->customer_phone }}
        </td>
      </tr>

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          E-mail
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->customer_email }}
        </td>
      </tr>

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Dátum prevzatia
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->date_from_formatted }}
        </td>
      </tr>

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Dátum vrátenia
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->date_to_formatted }}
        </td>
      </tr>

      @if($booking->total_days)
      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Počet dní
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->total_days }}
        </td>
      </tr>
      @endif

      @if($booking->total_price)
      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Celková cena
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ number_format($booking->total_price, 0, ',', ' ') }} Kč
        </td>
      </tr>
      @endif

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Karavan
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->service->title }}
        </td>
      </tr>

      @if($booking->note)
      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Poznámka
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->note }}
        </td>
      </tr>
      @endif

      @if($booking->billing_company || $booking->billing_street || $booking->billing_ico)
      <tr>
        <td colspan="2" style="padding:10px 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#aaaaaa;border-top:2px solid #eeeeee;">
          Fakturačné údaje
        </td>
      </tr>
      @if($booking->billing_company)
      <tr>
        <td style="padding:5px 0;font-size:13px;color:#888888;width:38%;vertical-align:top;">Firma</td>
        <td style="padding:5px 0;font-size:14px;color:#1a1a1a;font-weight:600;vertical-align:top;">{{ $booking->billing_company }}</td>
      </tr>
      @endif
      @if($booking->billing_ico)
      <tr>
        <td style="padding:5px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">IČO</td>
        <td style="padding:5px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">{{ $booking->billing_ico }}</td>
      </tr>
      @endif
      @if($booking->billing_dic)
      <tr>
        <td style="padding:5px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">DIČ</td>
        <td style="padding:5px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">{{ $booking->billing_dic }}</td>
      </tr>
      @endif
      @if($booking->billing_street)
      <tr>
        <td style="padding:5px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">Adresa</td>
        <td style="padding:5px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->billing_street }}<br/>
          {{ trim($booking->billing_zip . ' ' . $booking->billing_city) }}
        </td>
      </tr>
      @endif
      @endif

    </table>
  </div>

  {{-- CTA --}}
  @if(!$autoConfirmed)
  <p style="margin:0;font-size:13px;color:#888888;line-height:1.6;text-align:center;">
    Rezerváciu môžete potvrdiť alebo zamietnuť v
    <a href="{{ env('FRONTEND_URL', 'http://localhost:9001') }}/admin/rezervacie"
       style="color:#1a1a1a;font-weight:600;">administrácii</a>.
  </p>
  @endif

@endsection
