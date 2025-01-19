<div>
    <div class="container">
        <div class="row" wire:poll.3s>
            @foreach ($menuItems as $lable => $items)
                <div class="ms-3 col-md-5">
                    <div class="menu-section">
                        <div class="menu-title">{{ $lable }}</div>
                        <ul class="menu-list">
                            @foreach ($items as $item)
                                @if ($item->is_available)
                                    <li class="menu-item">
                                        <span class="menu-item-name fw-bold">{{ $item->name }}</span>
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
{{-- @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            const items = @json($items);

            items.forEach(item => {
                const textName = document.getElementById(`text-name-${item.id}`);
                const textStatus = document.getElementById(`text-status-${item.id}`);

                if (textName && textStatus) {
                    function toggleText() {
                        if (textName.style.display !== 'none') {

                            textName.classList.add('fade-out');
                            textName.addEventListener('animationend', () => {
                                textName.style.display = 'none';
                                textName.classList.remove('fade-out');
                                textStatus.style.display = 'inline';
                                textStatus.classList.add('fade-in');
                            }, {
                                once: true
                            });
                        } else {

                            textStatus.classList.add('fade-out');
                            textStatus.addEventListener('animationend', () => {
                                textStatus.style.display = 'none';
                                textStatus.classList.remove('fade-out');
                                textName.style.display = 'inline';
                                textName.classList.add('fade-in');
                            }, {
                                once: true
                            });
                        }
                    }

                    textName.style.display = 'inline';
                    textStatus.style.display = 'none';

                    setInterval(toggleText, 4000);
                }
            });
        });
    </script>
@endpush --}}
