@extends('log-viewer::tailwind._master')

@section('content')
    <form id="bulk-delete-form" action="{{ route('log-viewer::logs.bulk-delete') }}" method="POST">
        @csrf
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">@lang('Log Files')</h1>
            @if ($userRole === 'admin')
                <div id="bulk-actions" class="hidden">
                    <button type="button" onclick="confirmBulkDelete()"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa fa-trash-alt mr-2"></i> @lang('Delete Selected') (<span id="selected-count">0</span>)
                    </button>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="px-6 py-4 w-10">
                                @if ($userRole === 'admin')
                                    <input type="checkbox" id="select-all"
                                        class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                                @endif
                            </th>
                            @foreach ($headers as $key => $header)
                                <th
                                    class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider {{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                    @if ($key == 'date')
                                        {{ $header }}
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-level-{{ $key }} text-white">
                                            <span class="mr-1">{!! log_styler()->icon($key) !!}</span> {{ $header }}
                                        </span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">
                                @lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $date => $row)
                            <tr class="hover:bg-slate-50 transition-colors duration-150 group">
                                <td class="px-6 py-4">
                                    @if ($userRole === 'admin')
                                        <input type="checkbox" name="dates[]" value="{{ $date }}"
                                            class="log-checkbox w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                                    @endif
                                </td>
                                @foreach ($row as $key => $value)
                                    <td
                                        class="px-6 py-4 whitespace-nowrap {{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                        @if ($key == 'date')
                                            <a href="{{ route('log-viewer::logs.show', [$date]) }}"
                                                class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                                                @php
                                                    $displayValue = $value;
                                                    try {
                                                        if (
                                                            preg_match('/^(\d{4}-\d{2}-\d{2})(.*)$/', $value, $matches)
                                                        ) {
                                                            $displayValue =
                                                                \Carbon\Carbon::parse($matches[1])->format('M d, Y') .
                                                                $matches[2];
                                                        }
                                                    } catch (\Exception $e) {
                                                    }
                                                @endphp
                                                {{ $displayValue }}
                                            </a>
                                        @elseif ($value == 0)
                                            <span class="text-slate-300">-</span>
                                        @else
                                            <a href="{{ route('log-viewer::logs.filter', [$date, $key]) }}"
                                                class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-level-{{ $key }} text-white hover:opacity-90 transition-opacity">
                                                {{ $value }}
                                            </a>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div
                                        class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('log-viewer::logs.show', [$date]) }}"
                                            class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                            title="@lang('Show')">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if ($userRole === 'admin' || $userRole === 'auditor')
                                            <a href="{{ route('log-viewer::logs.download', [$date]) }}"
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                title="@lang('Download')">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        @endif
                                        @if ($userRole === 'admin')
                                            <button type="button"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors delete-log-btn"
                                                data-log-date="{{ $date }}" title="@lang('Delete')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center">
                                        <i class="fa fa-inbox text-4xl mb-3 opacity-20"></i>
                                        <span>@lang('The list of logs is empty!')</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $rows->render('pagination::tailwind') }}
            </div>
        </div>
    </form>
@endsection

@section('modals')
    <!-- Delete Modal -->
    <div id="delete-log-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="date" value="">

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fa fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">
                                        @lang('Delete log file')</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500 modal-body-text">@lang('Are you sure you want to delete this log file? This action cannot be undone.')</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                @lang('Delete')
                            </button>
                            <button type="button" onclick="closeModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                                @lang('Cancel')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div id="bulk-delete-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeBulkModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-slate-900">@lang('Delete selected logs')</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">@lang('Are you sure you want to delete the selected log files? This action cannot be undone.')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" onclick="submitBulkDelete()"
                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                            @lang('Delete Selected')
                        </button>
                        <button type="button" onclick="closeBulkModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                            @lang('Cancel')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const modal = document.getElementById('delete-log-modal');
        const bulkModal = document.getElementById('bulk-delete-modal');
        const form = document.getElementById('delete-log-form');
        const bulkForm = document.getElementById('bulk-delete-form');

        window.closeModal = function() {
            modal.classList.add('hidden');
        }
        window.closeBulkModal = function() {
            bulkModal.classList.add('hidden');
        }

        window.confirmBulkDelete = function() {
            bulkModal.classList.remove('hidden');
        }
        window.submitBulkDelete = function() {
            bulkForm.submit();
        }

        ready(() => {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.log-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            function updateBulkActions() {
                const checked = document.querySelectorAll('.log-checkbox:checked').length;
                if (checked > 0) {
                    bulkActions.classList.remove('hidden');
                    selectedCount.textContent = checked;
                } else {
                    bulkActions.classList.add('hidden');
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkActions();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    updateBulkActions();
                    if (!cb.checked) selectAll.checked = false;
                });
            });

            document.querySelectorAll(".delete-log-btn").forEach((elt) => {
                elt.addEventListener('click', (event) => {
                    event.preventDefault();
                    let date = event.currentTarget.getAttribute('data-log-date');
                    form.querySelector('input[name=date]').value = date;
                    modal.classList.remove('hidden');
                });
            });
        });
    </script>
@endsection
