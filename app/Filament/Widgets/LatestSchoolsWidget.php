<?php

namespace App\Filament\Widgets;

use App\Models\School;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestSchoolsWidget extends BaseWidget
{
    protected static ?string $heading = 'آخر المدارس المسجلة';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                School::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('الشعار')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('/images/school-placeholder.png'),
                    
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم المدرسة')
                    ->searchable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('branches_count')
                    ->label('عدد الفروع')
                    ->counts('branches')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->getStateUsing(function ($record) {
                        return $record->branches()->withCount('students')->get()->sum('students_count');
                    })
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-m-eye')
                    ->url(fn (School $record): string => route('filament.admin.resources.schools.view', $record)),
            ]);
    }
    
    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }
}