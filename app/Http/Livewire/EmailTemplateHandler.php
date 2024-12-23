<?php

namespace App\Http\Livewire;

use App\Models\Exhibitor;
use App\Models\MailTemplate;
use Livewire\Component;

class EmailTemplateHandler extends Component
{
    public $currentEventId;
    public $halls;
    public $targetElement = 'all-exhibitors';
    public $selectedHall;
    public $selectedExhibitors = [];
    public $subject;
    public $message;
    public $templateId;
    protected $listeners = ['templateSelected' => 'loadTemplate', 'sendEmail' => 'sendEmail'];
    public function mount($templateId = null)
    {
        $this->currentEventId = getCurrentEvent()->id;
        $this->halls = ['hall1', 'hall2', 'hall3', 'hall4', 'hall5'];
        if ($templateId) {
            $this->loadTemplate($templateId);
        }
    }
    public function loadTemplate($templateId)
    {
        $template = MailTemplate::find($templateId);

        if ($template) {
            $this->templateId = $templateId;
            $this->targetElement = $template->target_id;
            $this->selectedHall = $template->hall_id;
            $this->subject = $template->subject;
            $this->message = $template->message_content;
        }
    }

    public function saveEmailTemplate()
    {
        $this->validate([
            'targetElement' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = [
            'target_id' => $this->targetElement,
            'hall_id' => $this->selectedHall,
            'subject' => $this->subject,
            'message_content' => $this->message,
        ];

        if ($this->targetElement === 'specific-exhibitors') {
            $data['exhibitor_ids'] = $this->selectedExhibitors;
        }

        if ($this->templateId) {
            MailTemplate::find($this->templateId)->update($data);
            session()->flash('success', 'Email template updated successfully.');
        } else {
            MailTemplate::create($data);
            session()->flash('success', 'Email template saved successfully.');
        }
        $this->reset(['targetElement', 'selectedHall', 'selectedExhibitors', 'subject', 'message']);
        return redirect()->route('email-templates.summary');
    }

    public function render()
    {
        $exhibitors = Exhibitor::whereHas('eventExhibitors', function ($query) {
            $query->where('event_id', $this->currentEventId)
                ->where('is_active', true);
        })->get();

        return view('livewire.email-template-handler', [
            'exhibitors' => $exhibitors,
            'halls' => $this->halls,
        ])->layout('layouts.admin');
    }
}
