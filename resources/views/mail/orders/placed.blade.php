<x-mail::message>
# Order placed successfully!

Thank you for your order. Your order number is: {{ $order->id }}.

<x-mail::button :url="$url">
View Order
</x-mail::button>

Thanks,<br>
Glowies
</x-mail::message>
