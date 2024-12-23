<?php

namespace App\Http\Livewire;

use Exception;
use Livewire\Component;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class FollowupSummary extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $potentialId = null;

    #[Url(as: 'pp')]
    public $perPage = 10;

    public $followUp;
    public function mount(Request $request)
    {
        $this->potentialId = $request->potentialId ?? null;
    }
    public function render()
    {
        $followUps = FollowUp::where('potential_id', $this->potentialId)->orderBy('id', 'desc')->paginate($this->perPage);
        return view(
            'livewire.followup-summary',
            [
                'followups' => $followUps
            ]
        )->layout("layouts.admin");
    }
    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }

    public function deleteFollowup($id)
    {
        try {
            $followup = Followup::find($id);
            $followup->update([
                "deleted_by" => getAuthData()->id,
            ]);
            $isDeleted = $followup->delete();
            if ($isDeleted) {
                session()->flash('success', 'Follow-up deleted successfully!');
                return redirect(route('followup-summary', ['potentialId' => $this->potentialId]));
            } else {
                session()->flash('error', 'Followup deletion failed!');
                return;
            }
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->route('followup-summary', ['potentialId' => $this->potentialId]);
        }
    }
}
