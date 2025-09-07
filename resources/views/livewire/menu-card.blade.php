@push('styles')
    <style>
        .title {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
        }

        .menu-design-before,
        .menu-design-after {
            width: 20px;
            height: 20px;
            margin: 0 5px;
        }

        #commingsoon {
            animation-iteration-count: infinite;
            /* animation-delay: 10s;     */
            animation-duration: 5s;
        }
    </style>
@endpush
<div>
    <div class="container mt-3">
        <div class="row" wire:poll.3s>
            @if (count($menuItemsEnglish) > 0 && count($menuItemsEnglish) > 0)
                <div class="d-flex justify-content-evenly">
                    @foreach ($menuItemsEnglish as $label => $items)
                        @php
                            // Chunk the items into groups of 6
                            $chunks = $items->chunk(20);
                            $fromTime = $timings[$label]['show_time_from'];
                            $toTime = $timings[$label]['show_time_to'];
                        @endphp

                        @foreach ($chunks as $chunk)
                            {{-- @dd($chunk) --}}
                            <div class="col-md-4 d-flex flex-column">
                                <div class="menu-section">
                                    <div class="menu-title mx-auto p-1 rounded ps-3">{{ $label }} <small
                                            class="time-range">{{ '[ ' . $fromTime . ' - ' . $toTime . ' ]' }}</small></div>
                                    <ul class="menu-list mt-3">
                                        @foreach ($chunk as $item)
                                            @if ($item->is_available)
                                                <li class="menu-item">                                                    
                                                    <span class="menu-item-name">
                                                     <small class="badge text-bg-danger me-2" style="background-color:#">Must Try</small>{{ $item->name }}
                                                    </span>
                                                    <span
                                                        class="menu-item-price fw-bold">{{ '₹ ' . $item->price }}</span>
                                                </li>
                                            @else
                                                <li class="menu-item not-available-menus text-muted">
                                                    <small class="fw-bold animate__animated animate__fadeIn"
                                                        style="color:#D32F2F;"
                                                        id="text-name-{{ $item->id }}">{{ $item->name }}</small>
                                                    <small class="fw-bold animate__animated animate__flash d-none"
                                                        style="color:#D32F2F;"
                                                        id="text-status-{{ $item->id }}">{{ $item->custom_status }}</small>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    @endforeach

                    @foreach ($menuItemsKannada as $label => $items)
                        @php
                            // Chunk the items into groups of 6
                            $chunks = $items->chunk(20);
                            $fromTime = $timings[$label]['show_time_from'];
                            $toTime = $timings[$label]['show_time_to'];
                        @endphp

                        @foreach ($chunks as $chunk)
                            {{-- @dd($chunk) --}}
                            <div class="col-md-4 d-flex flex-column">
                                <div class="menu-section">
                                    <div class="menu-title mx-auto p-1 rounded ps-3">{{ $label }} <small
                                            class="time-range">{{ '[ ' . $fromTime . ' - ' . $toTime . ' ]' }}</small></div>
                                    <ul class="menu-list mt-3">
                                        @foreach ($chunk as $item)
                                            @if ($item->is_available)
                                                <li class="menu-item">
                                                    {{-- <div class="d-flex justify-content-between"> --}}
                                                    <span class="menu-item-name">
                                                        {{ $item->kannada_name }}
                                                    </span>
                                                    <span
                                                        class="menu-item-price fw-bold">{{ '₹ ' . $item->price }}</span>
                                                    {{-- </div> --}}
                                                </li>
                                            @else
                                                <li class="menu-item not-available-menus text-muted">
                                                    <small class="fw-bold animate__animated animate__fadeIn"
                                                        style="color:#D32F2F;"
                                                        id="text-name-{{ $item->id }}">{{ $item->name }}</small>
                                                    <small class="fw-bold animate__animated animate__flash d-none"
                                                        style="color:#D32F2F;"
                                                        id="text-status-{{ $item->id }}">{{ $item->custom_status }}</small>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
                {{-- <div class="card">
                    <div class="card-header">
                        <h1 class=""></h1>
                    </div>
                    <div class="card-body"></div>
                </div> --}}

                <div style="padding-left: 12%;">
                    <div class="col-md-4">
                        <div class="card bg-danger-lt">
                            <div class="card-header" style="background:#D32F2F;color:#ffffff; padding-left:35%;">Add Extra</div>
                            <div class="card-body">
                                {{-- <div class="d-flex justify-content-around"> --}}
                                    {{-- <li >
                                        <span class="fw-bold text-success">{{ 'Ghee ' . 10 }}</span>
                                        <span class="fw-bold text-success">Ghee</span>
                                    </li>
                                    <li style="color: #006400;">
                                        <span class="badge text-bg-success" style="color: #006400"></span>
                                        <span class="fw-bold text-success">Podi</span>
                                    </li> --}}
                                    <li class="menu-item">
                                        {{-- <div class="d-flex justify-content-between"> --}}
                                        <span class="fw-bold text-success">
                                           Ghee
                                        </span>
                                        <span class="fw-bold text-success">{{ '₹ ' . 15 }}</span>
                                        {{-- </div> --}}
                                    </li>
                                    <li class="menu-item">
                                        {{-- <div class="d-flex justify-content-between"> --}}
                                        <span class="fw-bold text-success">
                                           Podi
                                        </span>
                                        <span class="fw-bold text-success">{{ '₹ ' . 10 }}</span>
                                        {{-- </div> --}}
                                    </li>
                                {{-- </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center mt-5">
                    <img src="{{ asset('images/commingsoon.webp') }}" alt="HMS" id="commingsoon"
                        class="img-fluid animate__animated animate__swing" style="width: 28%; height: 100%;">
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.not-available-menus');
            console.log(items);

            items.forEach(item => {
                const name = item.querySelector('[id^="text-name-"]');
                const status = item.querySelector('[id^="text-status-"]');
                console.log(name, status);

                name.addEventListener('animationend', () => {
                    name.classList.add('d-none');
                    status.classList.remove('d-none');
                    status.classList.add('animate__animated animate__flash');

                });

                status.addEventListener('animationend', () => {
                    status.classList.add('d-none');
                    name.classList.remove('d-none');
                    name.classList.add('animate__animated animate__flash');

                });
            });
        });
    </script>
    @if (Route::currentRouteName() === 'menu.card')
        <script>
            setTimeout(function() {
                location.reload();
            }, 15 * 60 * 1000);
        </script>
    @endif
@endpush
