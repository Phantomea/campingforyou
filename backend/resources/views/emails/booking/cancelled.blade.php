@extends('emails.layout')

@section('title', 'Zrušenie rezervácie karavanu')

@section('content')

  {{-- Icon + heading --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#fbe9e7;border-radius:50%;
                width:56px;height:56px;line-height:56px;font-size:26px;margin-bottom:14px;">
      ✕
    </div>
    <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a1a;font-weight:700;letter-spacing:-0.3px;">
      Rezervácia karavanu bola zrušená
    </h1>
    <p style="margin:0;font-size:15px;color:#666666;line-height:1.5;">
      Dobrý deň, <strong>{{ $booking->customer_name }}</strong>.<br>
      Ľutujeme, Vaša rezervácia karavanu bola zrušená.
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

    </table>
  </div>

  {{-- New booking CTA --}}
  @php $phone = \App\Models\Setting::get('phone'); $email = \App\Models\Setting::get('email'); @endphp
  <p style="margin:0;font-size:13px;color:#888888;line-height:1.6;text-align:center;">
    Chcete si rezervovať nový termín prenájmu? Kontaktujte nás
    @if($phone || $email)
      na
      @if($phone) <strong style="color:#555555;">{{ $phone }}</strong> @endif
      @if($phone && $email) alebo @endif
      @if($email) <strong style="color:#555555;">{{ $email }}</strong> @endif
    @endif
    alebo si rezervujte online na našej stránke.
  </p>

@endsection
