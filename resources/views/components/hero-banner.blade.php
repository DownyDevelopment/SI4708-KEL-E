@props(['title', 'description'])

@push('styles')
<style>
    /* Global Hero Banner */
    .global-hero-banner {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 50%, #0891b2 100%) !important;
        border-radius: var(--radius-lg) !important;
        padding: 2rem 2.25rem !important;
        color: white !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 1.5rem !important;
        margin-bottom: 1.75rem !important;
        position: relative !important;
        overflow: hidden !important;
        box-shadow: 0 12px 40px rgba(15, 118, 110, 0.22) !important;
        text-align: left !important;
    }

    .global-hero-banner::before {
        content: '' !important;
        position: absolute !important;
        top: -40% !important;
        right: -5% !important;
        width: 340px !important;
        height: 340px !important;
        background: rgba(255, 255, 255, 0.07) !important;
        border-radius: 50% !important;
        pointer-events: none !important;
    }

    .global-hero-banner::after {
        content: '' !important;
        position: absolute !important;
        bottom: -50% !important;
        left: 20% !important;
        width: 220px !important;
        height: 220px !important;
        background: rgba(255, 255, 255, 0.04) !important;
        border-radius: 50% !important;
        pointer-events: none !important;
    }

    .global-hero-banner-content {
        position: relative !important;
        z-index: 1 !important;
    }

    .global-hero-banner-content h1 {
        font-size: 1.75rem !important;
        font-weight: 700 !important;
        color: white !important;
        margin: 0 0 0.4rem !important;
        line-height: 1.25 !important;
    }

    .global-hero-banner-content p {
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 0.92rem !important;
        margin: 0 !important;
        max-width: 600px !important;
        line-height: 1.55 !important;
    }

    .global-hero-banner-actions {
        display: flex !important;
        gap: 0.65rem !important;
        flex-wrap: wrap !important;
        position: relative !important;
        z-index: 1 !important;
        flex-shrink: 0 !important;
        align-items: flex-start !important;
    }

    .global-hero-banner-btn-white {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.4rem !important;
        background: white !important;
        color: var(--primary, #0f766e) !important;
        border: none !important;
        border-radius: 99px !important;
        padding: 0.65rem 1.25rem !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: transform 0.15s, box-shadow 0.15s !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
    }

    .global-hero-banner-btn-white:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
    }

    .global-hero-banner-btn-ghost {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.4rem !important;
        background: rgba(255, 255, 255, 0.12) !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        border-radius: 99px !important;
        padding: 0.65rem 1.25rem !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        text-decoration: none !important;
        backdrop-filter: blur(4px) !important;
        transition: background 0.15s, transform 0.15s !important;
    }

    .global-hero-banner-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }
</style>
@endpush

<div class="global-hero-banner animate-fade-in">
    <div class="global-hero-banner-content">
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
    </div>
    @if(isset($actions))
        <div class="global-hero-banner-actions">
            {{ $actions }}
        </div>
    @endif
</div>
