<div>
    <div>
        <svg viewBox="0 0 1440 150" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%; height:auto;">
            <path d="M0,50 C360,150 1080,-50 1440,50 L1440,0 L0,0 Z" fill="#f7a94d"></path>
            <path d="M0,80 C480,180 960,-30 1440,80 L1440,0 L0,0 Z" fill="#f5c377" opacity="0.7"></path>
        </svg>
        <div class="d-flex justify-content-between">
            <h3 class="animated-text ms-5 ps-4" style="margin-top:-2%;">Magical Monday Menu</h3>
            {{-- <div class="text me-2 " style="margin-top:-5%; border:rgb(29, 66, 4);">
                <img src="{{ asset('theme/logo/corner-design1.png') }}" alt="corner-design1" class="logo">
            </div> --}}
        </div>
    </div>

    <div class="container">
        <!--Section -->
        <div class="row" wire:poll.3s>
            @foreach ($menuItems as $lable => $items)
                <div class="ms-3 col-md-3">
                    <div class="menu-section">
                        <div class="menu-title">{{ $lable }}</div>
                        <ul class="menu-list">
                            @foreach ($items as $item)
                                <li class="menu-item">
                                    <span class="menu-item-name">{{ $item->name . ' ( ' . $item->nos . ' Nos)' }}</span>
                                    <span class="menu-item-price">{{ '₹ ' . $item->price }}</span>
                                </li>
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
