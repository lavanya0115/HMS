<?php

namespace App\Http\Livewire;

use App\Models\Video;
use Livewire\Component;
use Livewire\WithFileUploads;

class VideoHandler extends Component
{
    use WithFileUploads;

    public $videoId = null;
    public $video;

    protected $rules = [
        'video' => 'required|mimes:mp4,avi,mkv|max:50000',
    ];

    protected $messages = [
        'video.required' => 'This Field is required',
    ];

    public function mount($videoId = null)
    {
        $this->videoId = $videoId;
    }
    public function create()
    {
        $this->validate();
        $authId = getAuthData()->id;

        try {
            dd($this->video);
            // $video = Video::create([
            //     'title' =>,
            //     'path' => ,
            //     'format' => ,
            //     'size' =>,
            //     'created_by' =>,
            //     'updated_by' =>,
            // ]);
            if ($video) {
                session()->flash('success', 'Video Uploaded successfully!.');
                return redirect(route('category'));
            }
            session()->flash('error', 'Error while uploading video');
            return;
        } catch (\Exception $e) {
            session()->flash("error", $e->getMessage());
            return;
        }
    }


    public function resetFields()
    {
        $this->videoId = null;
        $this->category = [

            'name' => null,
            'description' => null,

        ];
    }
    public function render()
    {
        return view('livewire.video-handler')->layout('layouts.admin');
    }
}
