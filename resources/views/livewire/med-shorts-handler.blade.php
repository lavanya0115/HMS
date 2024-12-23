<div class="page-body">
    <div class="container row">
        @include('includes.alerts')
        <div class="col-md-12">
            <div class="card">
                <form wire:submit='create'>
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label required">Rss Feeder Link</label>
                                <input type="text" class="form-control" placeholder="Enter Rss Feeder"
                                    wire:model="link" required>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary ">Create</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-12 pt-3">
            <div class="d-flex flex-row justify-content-between align-items-center">
                <div>
                    <h3 class="text">List of MedShorts</h3>
                </div>
            </div>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>MedShorts</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($medShorts) && count($medShorts) > 0)
                                @foreach ($medShorts as $medShortIndex => $medShort)
                                    <tr wire:key='{{ $medShort->id }}'>
                                        <td>
                                            {{ $medShortIndex + $medShorts->firstItem() }}
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $medShort->link }}</div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @can('Delete MedShorts')
                                                <a href="#" class="text-danger ms-2"
                                                    wire:confirm="Are you sure you want to delete this medShort?"
                                                    wire:click='delete({{ $medShort->id }})'>@include('icons.trash')</a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            @if (isset($medShorts) && count($medShorts) == 0)
                                @livewire('not-found-record-row', ['colspan' => 2])
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        {{ $medShorts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
