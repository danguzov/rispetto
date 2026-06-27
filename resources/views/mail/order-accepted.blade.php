<x-mail::message>
# Porudžbina prihvaćena

Zdravo {{ $order->customer_name }},

Restoran Rispetto je prihvatio vašu porudžbinu **#{{ $order->order_number }}**.

@if ($order->estimated_ready_at)
**Spremno oko:** {{ $order->estimated_ready_at->format('H:i') }}
@endif

<x-mail::button :url="$trackingUrl">
Pratite porudžbinu
</x-mail::button>

Hvala što ste poručili kod nas.

Pozdrav,<br>
{{ config('site.contact.name', 'Rispetto') }}
</x-mail::message>
