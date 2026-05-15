@props(['icon', 'label', 'to', 'active'])
<a href="{{ $to }}" 
   class="nav-item {{ $active ? 'active' : '' }}" 
   :title="!sidebarOpen ? '{{ $label }}' : ''"
   :style="sidebarOpen ? 'justify-content: flex-start; padding: 0.75rem 1rem; gap: 0.75rem;' : 'justify-content: center; padding: 0.75rem; gap: 0;'">
    <i data-lucide="{{ $icon }}" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
    <span x-show="sidebarOpen" style="white-space: nowrap;">{{ $label }}</span>
    @if($active)
        <i data-lucide="chevron-right" x-show="sidebarOpen" style="width: 16px; height: 16px; margin-left: auto;"></i>
    @endif
</a>
