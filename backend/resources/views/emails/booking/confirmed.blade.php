@extends('emails.layout')

@section('title', 'Potvrdenie rezervácie karavanu')

@section('content')

  {{-- Icon + heading --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#e8f5e9;border-radius:50%;
                width:56px;height:56px;line-height:56px;font-size:26px;margin-bottom:14px;">
      ✓
    </div>
    <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a1a;font-weight:700;letter-spacing:-0.3px;">
      Rezervácia karavanu potvrdená
    </h1>
    <p style="margin:0;font-size:15px;color:#666666;line-height:1.5;">
      Dobrý deň, <strong>{{ $booking->customer_name }}</strong>.<br>
      Vaša rezervácia karavanu bola úspešne potvrdená. Tešíme sa na vás!
    </p>
  </div>

  {{-- Booking details --}}
  <div style="background:#f7f7f7;border-radius:8px;padding:20px 24px;margin-bottom:24px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;width:40%;vertical-align:top;">
          Karavan
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;vertical-align:top;">
          {{ $booking->service->title }}
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
          {{ $booking->total_price }} €
        </td>
      </tr>
      @endif

      <tr>
        <td style="padding:7px 0;font-size:13px;color:#888888;border-top:1px solid #eeeeee;vertical-align:top;">
          Telefón
        </td>
        <td style="padding:7px 0;font-size:14px;color:#1a1a1a;font-weight:600;border-top:1px solid #eeeeee;vertical-align:top;">
          {{ $booking->customer_phone }}
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

    </table>
  </div>

  {{-- Contact note --}}
  @php $phone = \App\Models\Setting::get('phone'); $email = \App\Models\Setting::get('email'); @endphp
  <p style="margin:0;font-size:13px;color:#888888;line-height:1.6;text-align:center;">
    Ak potrebujete zmeniť alebo zrušiť termín prenájmu, kontaktujte nás
    @if($phone || $email)
      na
      @if($phone) <strong style="color:#555555;">{{ $phone }}</strong> @endif
      @if($phone && $email) alebo @endif
      @if($email) <strong style="color:#555555;">{{ $email }}</strong> @endif.
    @else
      prosím vopred.
    @endif
  </p>

@endsection
