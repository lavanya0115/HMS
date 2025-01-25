<?php

namespace App\Http\Livewire;

use App\Models\Video;
use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;


class VideoSummary extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $videoId = null;

    #[Url(as: 'pp')]
    public $perPage = 10;

    protected $listeners = [
        'deleteVideo' => 'deleteVideoById',
    ];

    public function mount(Request $request)
    {
        $this->videoId = $request->videoId;
    }
    public function deleteVideoById($videoId)
    {

        $video = Video::find($videoId);
        $video->update([
            'deleted_by' => getAuthData()->id,
        ]);
        if ($video) {
            $isDeleted = $video->delete();
            if ($isDeleted) {
                session()->flash("success",  "Video deleted successfully!.");
                return redirect(route('video'));
            } else {
                session()->flash("error", "Unable to delete video");
                return;
            }
        }
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }

    public function render()
    {
        $videos = Video::orderBy('id', 'desc')->paginate($this->perPage);
        return view('livewire.video-summary', [
            'videos' => $videos,
        ])->layout('layouts.admin');
    }
}
