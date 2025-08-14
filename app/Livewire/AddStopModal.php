<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Stop;

class AddStopModal extends Component
{
    public $show = false; // للتحكم بعرض المودال
    public $name;
    public $address;
    public $description;
    public $latitude;
    public $longitude;

    protected $listeners = ['openAddStopModal' => 'openModal'];

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
    ];

    public function openModal($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->show = true;
    }

    public function save()
    {
        $this->validate();

        $stop = Stop::create([
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        $this->emit('stopAdded', $stop); // إرسال الحدث للصفحة الرئيسية لتحديث الـ Repeater والخريطة
        $this->reset(['name','address','description']);
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.add-stop-modal');
    }
}
