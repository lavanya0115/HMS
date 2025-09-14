@push('styles')
    <style>
        .title {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
        }

        .menu-design-before,
        .menu-design-after {
            width: 15px;
            height: 15px;
            margin: 0 3px;
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
            @if (count($beverageItems) > 0 && count($beverageItems) > 0)

                @foreach ($beverageItems as $label => $items)
                    @php
                        $chunks = $items->chunk(10);
                        $fromTime = $timings[$label]['show_time_from'] ?? '';
                        $toTime = $timings[$label]['show_time_to'] ?? '';
                    @endphp

                    @foreach ($chunks as $subcategories)
                        @foreach ($subcategories as $variety => $category)
                            <div class="col-md-6 d-flex flex-column">
                                <div class="menu-section">

                                    @if ($variety !== 'Others' && count($subcategories) > 1)
                                        <div class="menu-title mx-auto p-1 rounded ps-3">
                                            {{ $variety }}
                                        </div>
                                    @else
                                        <div class="menu-title mx-auto p-1 rounded ps-3">{{ $label }} <small
                                                class="time-range">{{ '[ ' . $fromTime . ' - ' . $toTime . ' ]' }}</small>
                                        </div>
                                    @endif
                                    <ul class="menu-list mt-3">
                                        @foreach ($category as $item)
                                            @if ($item->is_available)
                                                <div class="row menu-item py-1">

                                                    <div class="col text-start">
                                                        {{ $item->name }}
                                                        @if (!empty($tag))
                                                            <small
                                                                class="badge bg-danger ms-2">{{ $tag }}</small>
                                                        @endif
                                                    </div>


                                                    <div class="col-3 text-center fw-bold text-success">
                                                        ₹ {{ $item->price }}
                                                    </div>


                                                    <div class="col text-end">
                                                        {{ $item->kannada_name }}
                                                    </div>
                                                </div>
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
                @endforeach

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
    @if (Route::currentRouteName() === 'menu.card.beverage')
        <script>
            setTimeout(function() {
                location.reload();
            }, 15 * 60 * 1000);
        </script>
    @endif
@endpush
