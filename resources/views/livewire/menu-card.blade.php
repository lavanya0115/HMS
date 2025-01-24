<div>
    <div class="container">
        <div class="row" wire:poll.3s>
            @foreach ($menuItems as $lable => $items)
                <div class="col-md-5">
                    <div class="menu-section">
                        <div class="menu-title">{{ $lable }}</div>
                        {{-- <div class="menu-title">{{ $lable }}</div> --}}
                        <ul class="menu-list">
                            @foreach ($items as $item)
                                @if ($item->is_available)
                                    <li class="menu-item">
                                        <span
                                            class="menu-item-name fw-bold">{{ $item->name }}{{ ' - ' . $item->kannada_name }}</span>
                                        {{-- <span class="menu-item-name fw-bold"></span> --}}
                                        <span class="menu-item-price">{{ '₹ ' . $item->price }}</span>
                                    </li>
                                @else
                                    <li class="menu-item not-available-menus text-muted">
                                        <small class="fw-bold text-danger animate__animated animate__fadeIn"
                                            id="text-name-{{ $item->id }}">{{ $item->name }}</small>
                                        <small class="fw-bold text-danger animate__animated animate__flash d-none"
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
@endpush
