@push('styles')
    <style>
        .counts {
            color: #ed7b34;
        }

        .counts:hover {
            background-color: #ed7b34;
            color: #fff !important;
        }
    </style>
@endpush
<div class="page-body">
    <div class="container-xl">
        <div>
            @include('includes.alerts')

            <div class="row">
                <div class="col-md-10">

                </div>

                <div class="col-md-2">

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
@endpush
