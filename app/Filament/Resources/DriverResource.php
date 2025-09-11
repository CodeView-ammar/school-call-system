<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use App\Models\User;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DriverResource extends Resource
{
     protected static ?string $model = Driver::class; 


    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    
    protected static ?string $navigationLabel = 'السائق';
    
    protected static ?string $modelLabel = 'سائق';
    
    protected static ?string $pluralModelLabel = 'سائق';
    
    protected static ?string $navigationGroup = 'إدارة المستخدمين';
    
    protected static ?int $navigationSort = 3;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Select::make('school_id')
                ->label('المدرسة')
                ->relationship('school', 'name_ar')
                ->searchable()
                ->preload()
                ->required()
                ->default(fn () => auth()->user()->school_id ?? null)
                ->reactive(),

            Forms\Components\Select::make('branch_id')
                ->label('الفرع')
                ->options(function (callable $get) {
                    $schoolId = $get('school_id');

                    if (!$schoolId) {
                        return [];
                    }

                    return \App\Models\Branch::where('school_id', $schoolId)
                        ->pluck('name_ar', 'id');
                })
                ->searchable()
                ->preload()
                ->required()
                ->default(fn () => auth()->user()->branch_id ?? null),

        
            Forms\Components\Select::make('user_id')
                ->label('السائق')
                ->relationship(
                    'user', 
                    'name_ar',
                    fn ($query) => $query->where('user_type', 'driver')
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? $record->name_ar)
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->tel()
                ->required(),
            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),

            Forms\Components\DatePicker::make('license_expiry')
                ->required(),

                Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('اسم المستخدم'),
                Tables\Columns\TextColumn::make('name')->label('اسم السائق'),
                Tables\Columns\TextColumn::make('phone')->label('رقم الهاتف'),
                Tables\Columns\TextColumn::make('license_number')->label('رقم الرخصة'),
                Tables\Columns\TextColumn::make('license_status')->label('حالة الرخصة'),
                Tables\Columns\TextColumn::make('experience_years')->label('سنوات الخبرة'),
                Tables\Columns\ToggleColumn::make('is_active')->label('نشط'),
            ])
            ->filters([
                // يمكنك إضافة فلاتر مخصصة هنا
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
