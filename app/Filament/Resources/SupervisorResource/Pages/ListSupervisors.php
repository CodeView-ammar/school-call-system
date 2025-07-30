<?php

namespace App\Filament\Resources\SupervisorResource\Pages;

use App\Filament\Resources\SupervisorResource;
use App\Models\Supervisor;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSupervisors extends ListRecords
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مساعد جديد'),
        ];
    }

    public function getTabs(): array
    {
        $schoolId = auth()->user()->school_id;

        return [
            'all' => Tab::make('جميع المساعدين')
                ->badge(fn () => Supervisor::where('school_id', $schoolId)->count()),

            'active' => Tab::make('النشطون')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('is_active', true)->where('school_id', $schoolId)
                )
                ->badge(fn () => Supervisor::where('is_active', true)->where('school_id', $schoolId)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('غير النشطين')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('is_active', false)->where('school_id', $schoolId)
                )
                ->badge(fn () => Supervisor::where('is_active', false)->where('school_id', $schoolId)->count())
                ->badgeColor('danger'),

            'recent' => Tab::make('المضافون حديثاً')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('created_at', '>=', now()->subWeek())->where('school_id', $schoolId)
                )
                ->badge(fn () => Supervisor::where('created_at', '>=', now()->subWeek())->where('school_id', $schoolId)->count())
                ->badgeColor('info'),
        ];
    }
}
