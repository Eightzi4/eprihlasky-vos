@props([
    'as' => 'a',
    'href' => null,
    'type' => 'button',
    'text' => '',
    'icon' => null,
    'iconPosition' => 'left',
    'iconAnimation' => 'color',
    'variant' => 'primary',
    'size' => 'lg',
    'rounded' => 'xl',
    'fullWidth' => false,
    'click' => null,
    'onclick' => null,
    'target' => null,
    'disabled' => false,
    'extraClass' => '',
    'spanClass' => '',
])

@php
$variants = [
    'primary'   => ['shadow' => 'shadow-xl hover:shadow-2xl', 'borderB' => 'border-b-4', 'opacity' => 'opacity-50'],
    'secondary' => ['shadow' => 'shadow-sm hover:shadow-md', 'borderB' => 'border-b-2', 'opacity' => 'opacity-40'],
    'ghost'     => ['shadow' => 'shadow-sm hover:shadow-md', 'borderB' => 'border-b-2', 'opacity' => 'opacity-30'],
];

$sizes = [
    'icon-only' => ['padding' => '',           'text' => '',                              'iconSize' => 'text-[18px]'],
    'xs'        => ['padding' => 'px-3 py-2',  'text' => 'text-xs font-bold',             'iconSize' => 'text-[16px]'],
    'sm'        => ['padding' => 'px-4 py-2',  'text' => 'text-sm font-bold',             'iconSize' => 'text-[18px]'],
    'md'        => ['padding' => 'px-5 py-2.5','text' => 'text-sm font-bold',             'iconSize' => 'text-[18px]'],
    'lg'        => ['padding' => 'px-6 py-3',  'text' => 'text-sm font-bold',             'iconSize' => 'text-[18px]'],
    'wide'      => ['padding' => 'px-8 py-4',  'text' => 'text-base font-bold',           'iconSize' => 'text-[20px]'],
    'xl'        => ['padding' => 'px-6 py-3 sm:px-8 sm:py-4', 'text' => 'text-base sm:text-lg font-bold', 'iconSize' => 'text-[20px]'],
    'hero'      => ['padding' => 'px-10 py-5', 'text' => 'text-xl font-bold',             'iconSize' => 'text-[24px]'],
];

$animations = [
    'color'       => 'group-hover/btn:text-school-primary transition-colors duration-300',
    'back'        => 'group-hover/btn:text-school-primary group-hover/btn:-translate-x-1 transition-all duration-300',
    'forward'     => 'group-hover/btn:text-school-primary group-hover/btn:translate-x-1 transition-transform duration-300',
    'rotate'      => 'group-hover/btn:text-school-primary group-hover/btn:rotate-90 transition-all duration-300',
    'none'        => 'transition-colors duration-300',
];

$v = $variants[$variant] ?? $variants['primary'];
$s = $sizes[$size] ?? $sizes['lg'];
$anim = $animations[$iconAnimation] ?? $animations['color'];
$roundedCls = $rounded === '2xl' ? 'rounded-2xl' : 'rounded-xl';

$wrapperClasses = collect([
    'group/btn relative',
    $size !== 'icon-only' ? 'flex items-center justify-center' : 'inline-flex items-center justify-center',
    $size === 'icon-only' ? 'w-10 h-10' : '',
    $s['padding'],
    $roundedCls,
    'overflow-hidden',
    $v['shadow'],
    'transition-all duration-300',
    'cursor-pointer',
    $fullWidth ? 'w-full' : '',
    $disabled ? 'disabled:opacity-40 disabled:cursor-not-allowed' : '',
    $extraClass,
])->filter()->join(' ');
@endphp

@if ($as === 'a')
    <a href="{{ $href ?? '#' }}" class="{{ $wrapperClasses }}"
        @if($target) target="{{ $target }}" @endif
        @if($onclick) onclick="{{ $onclick }}" @endif
        @if($click) @click="{{ $click }}" @endif
    >
@else
    <button type="{{ $type }}" class="{{ $wrapperClasses }}"
        @if($disabled) disabled @endif
        @if($onclick) onclick="{{ $onclick }}" @endif
        @if($click) @click="{{ $click }}" @endif
    >
@endif
    <div class="absolute inset-0 topo-bg {{ $v['opacity'] }} transition-opacity duration-300"></div>
    <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover/btn:backdrop-blur-[4px] transition-all duration-300"></div>
    <div class="absolute inset-0 {{ $roundedCls }} border border-white/60 {{ $v['borderB'] }} border-b-gray-200/50"></div>

    @if ($slot->isNotEmpty())
        <span class="relative z-10 {{ $s['text'] }} flex items-center whitespace-nowrap {{ $spanClass }}">
            {{ $slot }}
        </span>
    @else
        <span class="relative z-10 text-gray-900 {{ $s['text'] }} flex items-center drop-shadow-sm whitespace-nowrap {{ $spanClass }}">
            @if ($icon && $iconPosition === 'left')
                <span class="material-symbols-rounded mr-2 {{ $s['iconSize'] }} text-gray-600 {{ $anim }}">{{ $icon }}</span>
            @endif
            @if ($size !== 'icon-only')
                {!! $text !!}
            @endif
            @if ($icon && $iconPosition === 'right')
                <span class="material-symbols-rounded ml-2 {{ $s['iconSize'] }} text-gray-600 {{ $anim }}">{{ $icon }}</span>
            @endif
        </span>
    @endif
@if ($as === 'a')
    </a>
@else
    </button>
@endif
