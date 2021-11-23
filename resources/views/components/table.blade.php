<div class="st-container">
    @if (count(array_filter($actions, fn ($row) => $row['type'] == 'create')) > 0)
    @php
    $create_action = array_values(array_filter($actions, fn ($row) => $row['type'] == 'create'))[0];
    @endphp
    <div class="st-action-create-container">
        @include('st::table-actions.create-action')
    </div>
    @endif

    {{-- Date Range filter implementation. --}}
    @php
    $daterangefilterables = array_filter($columns, fn ($row) => $row['date_range_filter'] ?? false);
    @endphp

    @if (count($daterangefilterables) > 0)
    <div class="st-date-filter-container">
        @foreach ($daterangefilterables as $index => $daterangefilterable)
        <div class="flex flex-col">
            <label>{{ $daterangefilterable['header'] ?? '' }}</label>
            <div class="flex gap-2 items-center">
                <input type="date" id="st-{{ $id }}_min-{{ $index }}" />
                <span>to</span>
                <input type="date" id="st-{{ $id }}_max-{{ $index }}" />
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <table id="st-{{ $id }}" class="st-table">
        <thead class="st-thead">
            @foreach ($columns as $column)
            <th class="st-head {{ $column['class'] ? 'st-'.$column['class'].'-header' : '' }} {{ $column['class'] }}">
                {{ $column['header'] }}</th>
            @endforeach
            @if (count(array_filter($actions, fn ($row) => $row['type'] != 'download')) > 0)
            <th data-priority="1" class="st-head st-action-cell-header"><span>Actions</span></th>
            @endif
        </thead>

        @if ($models != null)
        <tbody class="st-tbody">
            @foreach ($models as $index => $model)
            <tr class="st-row">
                @foreach ($columns as $column)
                <td class="st-data {{ $column['class'] ? 'st-'.$column['class'] : '' }}  {{ $column['class'] }}">
                    {{-- If the data type is index --}}
                    @if ($column['type'] == 'index')

                    {{-- If the data type is model --}}
                    @elseif ($column['type'] == 'model')
                    {{ $columnTraverse($column['content'], $model) }}
                    {{-- If the data type is html --}}
                    @elseif ($column['type'] == 'html')
                    {!! $column['content'] !!}
                    {{-- If the data type is a view. --}}
                    @elseif ($column['type'] == 'view')
                    @include($column['content']['view'], $column['content']['data'])
                    {{-- If the data type is callback --}}
                    @elseif ($column['type'] == 'callback')
                    {!! $column['content']($model) ?? 'N/A' !!}
                    @endif
                </td>
                @endforeach
                @if ((count($actions) - count(array_filter($actions, fn ($r) => $r['type'] == 'download'))) > 0)
                <td class="st-data st-action-cell">
                    <div id="st-showmore-{{ $model->id }}" class="st-action-showmore" title="Show More">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </div>
                    <div id="st-actions-{{ $model->id }}"
                        class="st-action-container hidden md:flex md:justify-center md:w-full">
                        @php
                        $show_action = array_filter($actions, fn ($row) => $row['type'] == 'show') ?? [];
                        $show_action = end($show_action);
                        $edit_action = array_filter($actions, fn ($row) => $row['type'] == 'edit') ?? [];
                        $edit_action = end($edit_action);
                        $delete_action = array_filter($actions, fn ($row) => $row['type'] == 'destroy') ?? [];
                        $delete_action = end($delete_action);
                        $custom_actions = array_filter($actions, fn ($row) => $row['type'] == 'custom') ?? [];
                        @endphp
                        {{-- If the show action exist. --}}
                        @if ($show_action != null)
                        @include('st::table-actions.show-action')
                        @endif
                        {{-- If the edit action exist. --}}
                        @if ($edit_action != null)
                        @include('st::table-actions.edit-action')
                        @endif
                        {{-- If the delete action exist. --}}
                        @if ($delete_action != null)
                        @include('st::table-actions.destroy-action')
                        @endif
                        {{-- Render custom actions. --}}
                        @if ($custom_actions != null)
                        @foreach ($custom_actions as $custom_action)
                        @include($custom_action['content']['view'], $custom_action['content']['data'])
                        @endforeach
                        @endif
                    </div>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
        @endif
    </table>
</div>

@once
@push('scripts')
@laravelTableAssets
{{-- <script src="{{ url('dev_assets/app-laravel-table.js') }}"></script> --}}
@endpush
@endonce

<script>
    window.addEventListener('load', () => {
        let table = LaravelTable.init("st-{{ $id }}", @json($columns), @json($actions), {!! "'{$exportTitle}'" ?? 'null' !!}, {!! "'{$sourceAjax}'" ?? 'null' !!})
    })
</script>

