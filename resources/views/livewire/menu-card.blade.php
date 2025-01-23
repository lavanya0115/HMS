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
            /* Adjust height of the images */
            margin: 0 5px;
            /* Add spacing around the images */
        }
    </style>
@endpush
<div>
    <div class="container">
        <div class="row" wire:poll.3s>
            @foreach ($menuItems as $lable => $items)
                <div class="ms-3 col-md-5">
                    <div class="menu-section">
                        {{-- <div class="title"> --}}
                            {{-- <img src="{{ asset('designs/lable.png') }}" class="menu-design-before"> --}}
                            <div class="menu-title">{{ $lable }}</div>
                            {{-- <img src="{{ asset('designs/Star_Header-02.png') }}" class="menu-design-after"> --}}
                        {{-- </div> --}}
                        <ul class="menu-list">
                            @foreach ($items as $item)
                                @if ($item->is_available)
                                    <li class="menu-item">
                                        <span
                                            class="menu-item-name ">{{ $item->name }}{{ ' - ' . $item->kannada_name }}</span>
                                        {{-- <span class="menu-item-name fw-bold"></span> --}}
                                        <span class="menu-item-price">{{ '₹ ' . $item->price }}</span>
                                    </li>
                                @else
                                    <li class="menu-item text-muted">
                                        <small class="fw-bold text-danger item-text"
                                            id="text-name-{{ $item->id }}">{{ $item->name }}</small>
                                        <small class="fw-bold text-danger item-status "
                                            id="text-status-{{ $item->id }}">{{ $item->custom_status }}</small>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@push('scripts')
    @if (Route::currentRouteName() === 'menu.card')
        <!-- Only for the menu card page -->
        <script>
            setTimeout(function() {
                location.reload(); // Reload the page after 15 minutes (900,000 ms)
            }, 15 * 60 * 1000); // 15 minutes
        </script>
    @endif
@endpush
