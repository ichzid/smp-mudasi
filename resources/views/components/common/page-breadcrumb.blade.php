@props(['pageTitle' => 'Page', 'pageSubtitle' => ''])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ $pageTitle }}
        </h2>
        @if($pageSubtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $pageSubtitle }}
            </p>
        @endif
    </div>

    @isset($action)
        <div class="shrink-0">
            {{ $action }}
        </div>
    @endisset
</div>
