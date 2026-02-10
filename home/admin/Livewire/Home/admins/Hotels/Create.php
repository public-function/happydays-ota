<?php

namespace App\Livewire\Home\Admins\Hotels;

use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $address = '';
    public $city = '';
    public $country = '';
    public $postal_code = '';
    public $phone = '';
    public $email = '';
    public $website = '';
    public $status = 'active';
    public $checkin_time = '14:00';
    public $checkout_time = '12:00';
    public $timezone = 'UTC';

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'required|string',
        'city' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'phone' => 'required|string|max:50',
        'email' => 'required|email|max:255',
        'website' => 'nullable|url|max:255',
        'status' => 'required|in:active,inactive',
        'checkin_time' => 'required|date_format:H:i',
        'checkout_time' => 'required|date_format:H:i',
        'timezone' => 'required|string|max:50',
    ];

    public function render()
    {
        return view('livewire.home.admins.hotels.create');
    }

    public function save()
    {
        $this->validate();

        \App\Models\Hotel::create([
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'status' => $this->status,
            'checkin_time' => $this->checkin_time,
            'checkout_time' => $this->checkout_time,
            'timezone' => $this->timezone,
        ]);

        $this->dispatch('notify', message: 'Hotel created successfully.', type: 'success');
        
        return redirect()->route('admin.hotels');
    }

    public function cancel()
    {
        return redirect()->route('admin.hotels');
    }
}
