@props(['backRoute' => 'home', 'backLabel' => 'Volver', 'rightLabel' => null])

<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route($backRoute) }}" class="btn btn-ghost btn-sm">
            <i data-lucide="arrow-left"></i>
            {{ $backLabel }}
        </a>
    </div>
    @if($rightLabel)
        <div class="topbar-right">
            <span class="mode-badge">{{ $rightLabel }}</span>
        </div>
    @endif
</div>