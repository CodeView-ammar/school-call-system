<?php

namespace App\Filament\Resources\SupervisorResource\Pages;

use App\Filament\Resources\SupervisorResource;
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
        return [
            'all' => Tab::make('جميع المساعدين')
                ->badge(fn () => \App\Models\Supervisor::count()),

            'active' => Tab::make('النشطون')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => \App\Models\Supervisor::where('is_active', true)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('غير النشطين')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => \App\Models\Supervisor::where('is_active', false)->count())
                ->badgeColor('danger'),

            'recent' => Tab::make('المضافون حديثاً')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subWeek()))
                ->badge(fn () => \App\Models\Supervisor::where('created_at', '>=', now()->subWeek())->count())
                ->badgeColor('info'),
        ];
    }
}