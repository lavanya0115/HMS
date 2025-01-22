<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Category;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CategorySummary extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $categoryId = null;

    public $type = null;


    #[Url(as: 'pp')]
    public $perPage = 10;

    protected $listeners = [

        'deleteCategory' => 'deleteCategoryById',
    ];

    public function mount(Request $request)
    {
        $this->categoryId = $request->categoryId;
    }
    public function deleteCategoryById($categoryId)
    {

        $category = Category::find($categoryId);
        $category->update([
            'deleted_by' => getAuthData()->id,
        ]);
        if ($category) {
            $isDeleted = $category->delete();
            if ($isDeleted) {
                session()->flash("success",  " deleted successfully!.");
                return redirect(route('category'));
            } else {
                session()->flash("error", "Unable to delete category");
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
        $categories = Category::orderBy('id', 'desc')->paginate($this->perPage);

        // $activities = Activity::select('activity_log.*')->where('log_name', 'category_log')
        //     ->join('categories', 'activity_log.subject_id', '=', 'categories.id')
        //     ->where('categories.type', $this->type)
        //     ->orderBy('activity_log.id', 'desc')->paginate(10, pageName: 'activity');

        return view('livewire.category-summary', [
            'categories' => $categories,
            // 'activities' => $activities,
        ])->layout('layouts.admin');
    }
    public function import($data)
    {
        $authId = getAuthData()->id;
        $updatedCount = 0;
        $insertedCount = 0;

        $headings = $data[0];
        $rows = array_slice($data, 1);
        foreach ($rows as $row) {
            $categoryData = array_combine($headings, $row);

            $categoryExists = Category::where('title', 'like', '%' . $categoryData['Name'] . '%')->where('type', 'like', '%' . $categoryData['Type'] . '%')->first();
            if (!empty($categoryData['Show Time From']) && !empty($categoryData['Show Time To'])) {
                $parsedFrom =  Carbon::parse($categoryData['Show Time From'])->timezone('Asia/Kolkata');
                $from = $parsedFrom->format('H:i');
                $parsedTo =  Carbon::parse($categoryData['Show Time To'])->timezone('Asia/Kolkata');
                $to = $parsedTo->format('H:i');
                // dd($from, $to);
            }


            if ($categoryExists) {

                if ($categoryExists->title !== $categoryData['Name']) {
                    $categoryExists->title = $categoryData['Name'];
                }

                if ($categoryExists->type !== lcfirst($categoryData['Type'])) {
                    $categoryExists->type = lcfirst($categoryData['Type']);
                }

                if ($categoryExists->day !== lcfirst($categoryData['Day'])) {
                    $categoryExists->day = lcfirst($categoryData['Day']);
                }

                if (isset($from) && isset($to)) {
                    if ($categoryExists->show_time_from !== $from) {
                        $categoryExists->show_time_from = $from;
                    }

                    if ($categoryExists->show_time_to !== $to) {
                        $categoryExists->show_time_to = $to;
                    }
                }

                $categoryExists->updated_by = $authId;
                $categoryExists->save();

                $isUpdated = $categoryExists->wasChanged([
                    'title',
                    'type',
                    'day',
                    'show_time_from',
                    'show_time_to',
                    'updated_by',
                ]);
                if ($isUpdated) {
                    $updatedCount++;
                }
            } else {
                $category = Category::create([
                    'title' => $categoryData['Name'],
                    'type' => lcfirst($categoryData['Type']),
                    'day' => lcfirst($categoryData['Day']),
                    'show_time_from' => isset($from) ? $from : '',
                    'show_time_to' => isset($to) ? $to : '',
                    'is_active' => 1,
                    'created_by' => $authId,
                    'updated_by' => $authId,
                ]);

                if ($category) {
                    $insertedCount++;
                }
            }
        }

        $messages = [];
        if ($insertedCount > 0) {
            $messages[] = "$insertedCount categories inserted.";
        }
        if ($updatedCount > 0) {
            $messages[] = "$updatedCount categories updated.";
        }

        session()->flash('success', implode(' ', $messages) ?: 'No changes made.');
        return redirect()->route('category');
    }
}
