<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StopResource\Pages;
use App\Filament\Resources\StopResource\RelationManagers;
use App\Models\Stop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StopResource extends Resource
{
    protected static ?string $model = Stop::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    
    protected static ?string $navigationLabel = 'توقف';
    
    protected static ?string $modelLabel = 'توقف';
    
    protected static ?string $pluralModelLabel = 'توقف';
    
    protected static ?string $navigationGroup = 'إدارة النقل';
    
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->columns(12)
                    ->schema([
                        // العمود الأيمن: الحقول
                        Forms\Components\Section::make('الحقول')
                            ->schema([
                                Forms\Components\Select::make('school_id')
                                    ->label('المدرسة')
                                    ->relationship('school', 'name_ar')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('name')
                                    ->label('اسم نقطة التوقف')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('address')
                                    ->label('العنوان')
                                    ->maxLength(500)
                                    ->id('data.address'),

                                Forms\Components\Textarea::make('description')
                                    ->label('الوصف')
                                    ->maxLength(1000),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('latitude')
                                        ->label('خط العرض')
                                        ->required()
                                        ->numeric()
                                        ->reactive()
                                        ->id('data.latitude'),

                                    Forms\Components\TextInput::make('longitude')
                                        ->label('خط الطول')
                                        ->required()
                                        ->numeric()
                                        ->reactive()
                                        ->id('data.longitude'),
                                ]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true),
                            ])
                            ->columnSpan(5),
               
                        // العمود الأيسر: الخريطة
                        Forms\Components\Section::make('الخريطة')
                            ->schema([
                                Forms\Components\ViewField::make('map')
                                    ->label('حدد الموقع على الخريطة')
                                    ->view('filament.custom.map-picker')
                                    ->extraAttributes(['wire:ignore']),
                            ])
                            ->columnSpan(7),

                    ]),
            ]);
    }


public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('اسم التوقف')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('address')
                ->label('العنوان')
                ->limit(50)
                ->searchable(),

            Tables\Columns\TextColumn::make('school.name_ar')
                ->label('المدرسة')
                ->sortable()
                ->searchable(),

            Tables\Columns\IconColumn::make('is_active')
                ->label('نشط')
                ->boolean(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('تاريخ الإضافة')
                ->dateTime()
                ->sortable(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
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
            'index' => Pages\ListStops::route('/'),
            'create' => Pages\CreateStop::route('/create'),
            'edit' => Pages\EditStop::route('/{record}/edit'),
        ];
    }
}
