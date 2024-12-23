<div class="page-body">
    <div class="container-xl p-4 px-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mx-auto fw-bold">Announcements List</h3>
                </div>
                <div class="list-group list-group-flush ps-4">
                    @if (isset($announcements) && count($announcements) > 0)
                        @foreach ($announcements as $announcement)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <a href="#">
                                            <span
                                                class="avatar overflow-hidden">{{ getNameFirstChars($announcement['title'] ?? '') }}</span>
                                        </a>
                                    </div>
                                    <div class="col">
                                        <a href="{{ route('show.announcement', ['announcementId' => $announcement['id']]) }}"
                                            class="text-reset d-block">{{ $announcement['title'] ?? '' }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @if (isset($announcements) && count($announcements) == 0)
                        <div class="list-group-item mx-auto ps-1 text-danger fw-bold">
                            No Announcements.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
