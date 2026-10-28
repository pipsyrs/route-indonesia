@php
    $number = \App\Support\PhoneNumber::whatsapp(config('services.whatsapp.number'));
    $message = config('services.whatsapp.message');
    $href = $number ? 'https://wa.me/'.$number : null;

    if ($href && is_string($message) && trim($message) !== '') {
        $href .= '?text='.rawurlencode(mb_substr(trim($message), 0, 500));
    }
@endphp

@if ($href)
    {{-- z-30: di bawah panel filter mobile (z-40) dan toast (z-50) --}}
    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Chat with us on WhatsApp') }}"
       class="fixed right-4 bottom-[calc(1rem+env(safe-area-inset-bottom))] z-30 inline-flex size-14 items-center justify-center rounded-full bg-black text-white shadow-lg shadow-black/15 transition-transform duration-200 hover:scale-105 active:scale-95 print:hidden lg:right-6 lg:bottom-6"
       data-whatsapp-float>
        <x-icon name="brand-whatsapp" class="size-8" />
    </a>
@endif
