<div>
    <h4>{{ isset($videoId) ? 'Edit ' : 'New ' }}</h4>
    <div class="card">
        <form wire:submit={{ isset($videoId) ? 'update' : 'create' }}>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row row-cards">
                            <div class="col-md-12">
                                <div>
                                    <label class="form-label required" for="name">Name</label>
                                    <input type="file" id ="name" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('video') ? true : false,
                                    ])accept="video/*"
                                        required placeholder="Upload Video" wire:model="video">
                                    @error('video')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                @if ($videoId)
                    <a href={{ route('video') }} class="text-danger me-2 text-decoration-none"> Cancel </a>
                @else
                    <a href=# wire:click.prevent ="resetFields" class="text-danger me-2 text-decoration-none"> Reset
                    </a>
                @endif
                <button type="submit" class="btn btn-primary ">Upload</button>
            </div>
        </form>
    </div>
</div>
