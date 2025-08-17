<?php

namespace App\Filament\Resources\TripResource\Pages;

use App\Filament\Resources\TripResource;
use App\Models\Trip;
use App\Models\TripStop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConfigureTripTimes extends Page
{
    protected static string $resource = TripResource::class;
    protected static string $view = 'filament.custom.configure-trip-times';
    public Trip $record;
    public $tripStops = [];
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadTripStops();
    }

    protected function resolveRecord(int | string $key): Model
    {
        return Trip::with(['route.stops', 'tripStops.stop'])->findOrFail($key);
    }

    public function loadTripStops()
    {
        // إذا لم تكن هناك محطات محفوظة، ننشئها من المسار
        if ($this->record->tripStops->isEmpty()) {
            $this->generateInitialStops();
        }
        
        $this->tripStops = $this->record->tripStops()
            ->with('stop')
            ->orderBy('stop_order')
            ->get()
            ->toArray();
    }

    public function generateInitialStops()
    {
        $route = $this->record->route()->with('stops')->first();
        
        if (!$route || !$route->stops) {
            return;
        }

        $startTime = Carbon::parse($this->record->arrival_time_at_first_stop);
        $stopInterval = $this->record->stop_to_stop_time_minutes;

        foreach ($route->stops as $index => $stop) {
            $arrivalTime = $startTime->copy()->addMinutes($index * $stopInterval);
            
            TripStop::create([
                'trip_id' => $this->record->id,
                'stop_id' => $stop->id,
                'arrival_time' => $arrivalTime->format('H:i:s'),
                'stop_order' => $index + 1,
                'is_pickup' => true,
                'is_dropoff' => true,
            ]);
        }

        // إعادة تحميل البيانات
        $this->record->refresh();
    }

    public function updateStopTime($stopId, $newTime)
    {
        $tripStop = TripStop::where('trip_id', $this->record->id)
            ->where('stop_id', $stopId)
            ->first();

        if ($tripStop) {
            $tripStop->update(['arrival_time' => $newTime]);
            $this->loadTripStops(); // إعادة تحميل البيانات
            
            $this->dispatch('stop-time-updated', [
                'message' => 'تم تحديث الوقت بنجاح',
                'stopId' => $stopId,
                'newTime' => $newTime
            ]);
        }
    }

    public function addMinutesToStop($stopId, $minutes)
    {
        $tripStop = TripStop::where('trip_id', $this->record->id)
            ->where('stop_id', $stopId)
            ->first();

        if ($tripStop) {
            $currentTime = Carbon::parse($tripStop->arrival_time);
            $newTime = $currentTime->addMinutes($minutes);
            
            $tripStop->update(['arrival_time' => $newTime->format('H:i:s')]);
            $this->loadTripStops();
            
            $this->dispatch('stop-time-updated', [
                'message' => 'تم تحديث الوقت بنجاح',
                'stopId' => $stopId,
                'newTime' => $newTime->format('H:i')
            ]);
        }
    }

    public function subtractMinutesFromStop($stopId, $minutes)
    {
        $this->addMinutesToStop($stopId, -$minutes);
    }

    public function resetToDefaultTimes()
    {
        $startTime = Carbon::parse($this->record->arrival_time_at_first_stop);
        $stopInterval = $this->record->stop_to_stop_time_minutes;

        foreach ($this->record->tripStops as $index => $tripStop) {
            $arrivalTime = $startTime->copy()->addMinutes($index * $stopInterval);
            $tripStop->update(['arrival_time' => $arrivalTime->format('H:i:s')]);
        }

        $this->loadTripStops();
        $this->dispatch('times-reset', ['message' => 'تم إعادة تعيين جميع الأوقات']);
    }

    public function getTitle(): string
    {
        return 'تكوين أوقات الرحلة - ' . $this->record->route->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reset_times')
                ->label('إعادة تعيين الأوقات')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('resetToDefaultTimes')
                ->requiresConfirmation()
                ->modalHeading('إعادة تعيين الأوقات')
                ->modalDescription('هذا سيعيد تعيين جميع أوقات المحطات إلى الأوقات الافتراضية بناءً على الوقت المحدد بين المحطات.')
                ->modalSubmitActionLabel('نعم، أعد التعيين'),
                
            \Filament\Actions\Action::make('back_to_trips')
                ->label('العودة للرحلات')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(TripResource::getUrl('index')),
        ];
    }
}