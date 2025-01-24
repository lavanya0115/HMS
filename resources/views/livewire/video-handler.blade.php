<div>
    <h4>New</h4>
    <div class="card">
        <form wire:submit.prevent="create">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row row-cards">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label required">
                                            Upload Video
                                        </label>
                                        <div>
                                            <input type="file" accept="video/*" id="video" wire:model="video"
                                                class="form-control">
                                            @error('video')
                                                <span class="text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href={{ route('video') }} class="text-danger me-2 text-decoration-none"> Cancel </a>
                <button type="submit" class="btn btn-primary ">Upload</button>
            </div>
        </form>
    </div>
</div>
