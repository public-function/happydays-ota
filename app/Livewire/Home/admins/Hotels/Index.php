<?php

namespace App\Livewire\Home\Admins\Hotels;

use App\Models\Hotel;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $status = '';

    #[Url]
    public $sortBy = 'created_at';

    #[Url]
    public $sortDir = 'desc';

    protected $queryString = ['search', 'status', 'sortBy', 'sortDir'];

    public function mount()
    {
        //
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function getRowsProperty()
    {
        return Hotel::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('city', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.home.admins.hotels.index', [
            'hotels' => $this->rows,
        ]);
    }

    public function delete(Hotel $hotel)
    {
        $hotel->delete();
        $this->dispatch('notify', message: 'Hotel deleted successfully.', type: 'success');
    }

    public function archive(Hotel $hotel)
    {
        $hotel->update(['status' => 'archived']);
        $this->dispatch('notify', message: 'Hotel archived successfully.', type: 'success');
    }
}
