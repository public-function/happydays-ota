<?php

namespace App\Livewire\Home\Admins\RoomTypes;

use App\Models\RoomType;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $sortBy = 'created_at';

    #[Url]
    public $sortDir = 'desc';

    protected $queryString = ['search', 'sortBy', 'sortDir'];

    public function getRowsProperty()
    {
        return RoomType::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.home.admins.room-types.index', [
            'roomTypes' => $this->rows,
        ]);
    }

    public function delete(RoomType $roomType)
    {
        $roomType->delete();
        $this->dispatch('notify', message: 'Room type deleted successfully.', type: 'success');
    }
}
