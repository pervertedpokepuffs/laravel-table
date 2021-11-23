<x-st::base-action class="st-action-create {{ $create_action['class'] }}" title="{{ $create_action['title'] }}"
    href="{{ route($create_action['route']) }}">
    <span class="st-action-create-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                clip-rule="evenodd" />
        </svg>
    </span>
    <span class="st-action-create-text">Create new {{ ucwords($modelName) }}</span>
</x-st::base-action>