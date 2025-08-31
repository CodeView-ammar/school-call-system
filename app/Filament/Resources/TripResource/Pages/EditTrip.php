<?php

namespace App\Filament\Resources\TripResource\Pages;

use App\Filament\Resources\TripResource;
use App\Models\Trip;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTrip extends EditRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('configure_times')
                ->label('تكوين الأوقات')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(fn (): string => TripResource::getUrl('configure-times', ['record' => $this->record])),

            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'تعديل الرحلة';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ التغييرات بنجاح';
    }

    protected function beforeSave(): void
    {
        $this->validateConflicts();
    }

    protected function validateConflicts(): void
    {
        $data = $this->form->getState();

        $conflicts = [];

        if (isset($data['driver_id']) && $data['driver_id'] && isset($data['effective_date']) && isset($data['arrival_time_at_first_stop'])) {
            $driverConflicts = Trip::where('driver_id', $data['driver_id'])
                ->where('effective_date', $data['effective_date'])
                ->where('arrival_time_at_first_stop', $data['arrival_time_at_first_stop'])
                ->where('is_active', true)
                ->where('id', '!=', $this->record->id)
                ->with(['route', 'bus'])
                ->get();

            if ($driverConflicts->isNotEmpty()) {
                $conflicts['driver'] = $driverConflicts;
            }
        }

        if (isset($data['bus_id']) && $data['bus_id'] && isset($data['effective_date']) && isset($data['arrival_time_at_first_stop'])) {
            $busConflicts = Trip::where('bus_id', $data['bus_id'])
                ->where('effective_date', $data['effective_date'])
                ->where('arrival_time_at_first_stop', $data['arrival_time_at_first_stop'])
                ->where('is_active', true)
                ->where('id', '!=', $this->record->id)
                ->with(['route', 'driver'])
                ->get();

            if ($busConflicts->isNotEmpty()) {
                $conflicts['bus'] = $busConflicts;
            }
        }

        if (!empty($conflicts)) {
            $conflictMessages = [];

            if (isset($conflicts['driver'])) {
                foreach ($conflicts['driver'] as $conflict) {
                    $conflictMessages[] = "تعارض في السائق: {$conflict->driver->name} - الرحلة: {$conflict->route->route_ar}";
                }
            }

            if (isset($conflicts['bus'])) {
                foreach ($conflicts['bus'] as $conflict) {
                    $conflictMessages[] = "تعارض في الباص: {$conflict->bus->number} - الرحلة: {$conflict->route->route_ar}";
                }
            }

            Notification::make()
                ->title('تحذير: يوجد تعارض في الأوقات!')
                ->body('تم حفظ التعديلات ولكن يوجد تعارض في الأوقات مع الرحلات التالية:' . "\n" . implode("\n", $conflictMessages))
                ->warning()
                ->persistent()
                ->send();
        }
    }
}