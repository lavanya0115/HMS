<div class="page-body">
    <div class="container-xl">
        @include('includes.alerts')
        <div class="d-flex flex-row justify-content-between pb-2">
            <h4 class="text mt-2">List of Announcements</h4>
            @can('Create Announcement')
                <a href="{{ route('announcement', ['eventId' => $eventId]) }}"
                    class="btn btn-primary ">@include('icons.plus') Create
                    Announcement</a>
            @endcan
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Visible Type</th>
                            <th>Is Active</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($announcements) && count($announcements) > 0)
                            @foreach ($announcements as $announcementIndex => $announcement)
                                <tr wire:key='item-{{ $announcement->id }}'>
                                    <td>
                                        {{ $announcementIndex + $announcements->firstItem() }}
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $announcement->title }}</div>
                                    </td>
                                    <td>
                                        <div>
                                            @if ($announcement->visible_type == 'visitors_only')
                                                Visitors Only
                                            @elseif($announcement->visible_type == 'exhibitors_only')
                                                Exhibitors Only
                                            @else
                                                Both
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div @class([
                                            'badge',
                                            'me-1',
                                            'bg-success' => $announcement->is_active,
                                            'bg-danger' => !$announcement->is_active,
                                        ])></div>
                                        {{ $announcement->is_active == 1 ? 'Active' : 'Inactive' }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @can('Edit Announcement')
                                                <a
                                                    href="{{ route('announcement', ['eventId' => $eventId, 'announcementId' => $announcement->id]) }}">@include('icons.edit')</a>
                                            @endcan
                                            @can('Delete Announcement')
                                                <a href="#" class="text-danger ms-2"
                                                    wire:confirm="Are you sure you want to delete this announcement?"
                                                    wire:click='delete({{ $announcement->id }})'>@include('icons.trash')</a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        @if (isset($announcements) && count($announcements) == 0)
                            @livewire('not-found-record-row', ['colspan' => 5])
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
