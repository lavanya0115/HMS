<div class="page-body">
    <div class="container-xl">
        @include('includes.alerts')
        <div class="d-flex flex-row justify-content-between pb-2">
            <h4 class="text mt-2">List of Seminars</h4>
            @can('Create Seminar')
                <a href="{{ route('upsert.seminar', ['eventId' => $eventId]) }}"
                    class="btn btn-primary ">@include('icons.plus') Create
                    Seminar</a>
            @endcan
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Amount</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th colspan="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($seminars) && count($seminars) > 0)
                            @foreach ($seminars as $seminarIndex => $seminar)
                                <tr wire:key='item-{{ $seminar->id }}'>
                                    <td>
                                        {{ $seminars->firstItem() + $seminarIndex }}
                                    </td>
                                    <td>
                                        <div class="text-capitalize"> {{ $seminar->title ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">

                                            {{ $seminar->date ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">

                                            {{ $seminar->start_time ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            {{ $seminar->end_time ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            ₹{{ number_format($seminar->amount) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            {{ $seminar->_meta['location'] ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            <span @class([
                                                'badge',
                                                'bg-success' => $seminar->is_active,
                                                'bg-danger' => !$seminar->is_active,
                                                'me-1',
                                            ])></span>
                                            {{ $seminar->is_active ? 'Active' : 'Inactive' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @can('Edit Seminar')
                                                <a
                                                    href="{{ route('upsert.seminar', ['seminarId' => $seminar->id, 'eventId' => $eventId]) }}">@include('icons.edit')</a>
                                            @endcan
                                            @can('Delete Seminar')
                                                @if ($visitorSeminarsExists[$seminar->id] == false)
                                                    <a href="#" class="text-danger"
                                                        wire:click.prevent="$dispatch('candeleteSeminar',{{ $seminar->id }})">
                                                        @include('icons.trash')
                                                    </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @livewire('not-found-record-row', ['colspan' => 7])
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    @if (isset($seminars) && count($seminars) > 0)
                        {{ $seminars->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        Livewire.on('candeleteSeminar', (seminarId) => {
            if (confirm('Are you sure you want to delete this seminar ?')) {
                Livewire.dispatch('deleteSeminar', {
                    seminarId
                });
            }
        });
    </script>
@endpush
