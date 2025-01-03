<div>
    <div class="container">
        <!--Section -->
        <div class="row" wire:poll.3s>
            @foreach ($menuItems as $lable => $items)
                <div class="ms-3 col-md-5">
                    <div class="menu-section">
                        <div class="menu-title">{{ $lable }}</div>
                        <ul class="menu-list">
                            @foreach ($items as $item)
                                @if ($item->is_available)
                                    <li class="menu-item">
                                        <span
                                            class="menu-item-name fw-bold">{{ $item->name . ' ( ' . $item->qty . ' Nos)' }}</span>
                                        <span class="menu-item-price">{{ '₹ ' . $item->price }}</span>
                                    </li>
                                @else
                                    <li class="menu-item text-muted">
                                        <small
                                            class=" text-muted ">{{ $item->name . ' ( ' . $item->qty . ' Nos)' }}</small>
                                        <span class="menu-item-price">{{ '₹ ' . $item->price }}</span>
                                        <small class="fw-bold text-danger ">{{ $item->custom_status }}</small>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    {{-- <div >
        <svg viewBox="0 0 1440 150" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:auto;">
            <path d="M0,100 C300,200 1140,0 1440,100 L1440,150 L0,150 Z" fill="#f7a94d"></path>
        </svg>
    </div> --}}

    {{-- <div class="menu-footer">
        <svg viewBox="0 0 1440 150" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:auto;">
            <path d="M0,100 C300,200 1140,0 1440,100 L1440,150 L0,150 Z" fill="#f7a94d"></path>
            <path d="M0,120 C400,250 1040,-30 1440,120 L1440,150 L0,150 Z" fill="#f5c377" opacity="0.7"></path>
        </svg>
    </div> --}}

</div>
