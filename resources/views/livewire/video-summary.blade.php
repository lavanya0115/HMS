<div>
    <div class="page-body">
        <div class="container-xl">
            <div class="col-lg-12">
                @include('includes.alerts')
            </div>

            <div class="row">
                <div class="col-lg-6">
                    @livewire('video-handler', ['videoId' => $videoId])
                </div>
                <div class="col-lg-6">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <h4 class="text">List all Videos</h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>File Name</th>
                                        <th>Size</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($videos) && count($videos) > 0)
                                        @foreach ($videos as $videoIndex => $video)
                                            <tr wire:key='item-{{ $video->id }}'>
                                                <td>
                                                    {{ $videoIndex + $videos->firstItem() }}
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">{{ $video->title }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $video->size }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="#"
                                                            wire:click.prevent="$dispatch('canDeletevideo',{{ $video->id }})"
                                                            class="text-danger">
                                                            <span>@include('icons.trash')</span>
                                                        </a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($videos) && count($videos) == 0)
                                        @livewire('not-found-record-row', ['colspan' => 5])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row d-flex flex-row mb-3">
                                @if (isset($videos) && count($videos) != 0)
                                    <div class="col">
                                        <div class="d-flex flex-row mb-3">
                                            <div>
                                                <label class="p-2" for="perPage">Per Page</label>
                                            </div>
                                            <div>
                                                <select class="form-select" id="perPage" name="perPage"
                                                    wire:model="perPage"
                                                    wire:change="changePageValue($event.target.value)">
                                                    <option value=10>10</option>
                                                    <option value=50>50</option>
                                                    <option value=100>100</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col d-flex justify-content-end">
                                    @if (isset($videos) && count($videos) >= 0)
                                        {{ $videos->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
