<?php

namespace App\Http\Livewire;

use App\Models\Video;
use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\WithFileUploads;

class VideoHandler extends Component
{
    use WithFileUploads;

    public $videoId = null;
    public $video;

    protected $rules = [
        'video' => 'required',
    ];

    protected $messages = [
        'video.required' => 'This Field is required',
    ];

    public function mount($videoId = null)
    {
        $this->videoId = $videoId;
    }
    // public function updatedVideo()
    // {
    //     dd($this->video);
    // }
    public function create(Request $request)
    {
        $this->validate();
        $authId = getAuthData()->id;

        try {

            $file = $this->video;

            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $fileMimeType = $file->getMimeType();

            $fileSizeInMB = round($fileSize / (1024 * 1024), 2);

            $random = now()->format('H:i');
            $uniqueFileName = $fileName . '_' . $random;
            $fileDirectory = 'videos/';
            if (!file_exists($fileDirectory)) {
                mkdir($fileDirectory, 0755, true);
            }
            $filePath = $file->storeAs($fileDirectory, $uniqueFileName, 'public');
            $video = Video::create([
                'title' => $fileName,
                'path' => $filePath,
                'format' => $fileMimeType,
                'size' => $fileSizeInMB,
                'created_by' => $authId,
                'updated_by' => $authId,
            ]);
            if ($video) {
                session()->flash('success', 'Video Uploaded successfully!.');
                return redirect(route('video'));
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
    }
    public function render()
    {
        return view('livewire.video-handler')->layout('layouts.admin');
    }
}
