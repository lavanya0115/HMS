@php
    $totalAnnouncements = getCurrentAnnouncement();
    $announcementsList = collect($totalAnnouncements)->take(3);
@endphp
<header class="navbar navbar-expand-sm fixed-top justify-content-center gap-4" style="background-color: #FDE9B1">
    @if (isset($announcementsList) && count($announcementsList) > 0)
        <span class="mt-1 text-yellow avatar rounded-circle">@include('icons.speakerphone')</span>
        @foreach ($announcementsList as $announcement)
            <div class="pt-3">
                <a href="{{ route('show.announcement', ['announcementId' => $announcement['id']]) }}"
                    class="text-capitalize badge bg-yellow btn-pill text-decoration-none">{{ $announcement['title'] }}</a>
            </div>
        @endforeach
    @endif
</header>
