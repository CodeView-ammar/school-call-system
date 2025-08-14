<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouteResource\Pages;
use App\Models\Route;
use App\Models\School;
use App\Models\Stop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
class RouteResource extends Resource
{
    protected static ?string $model = Route::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'الطريق';
    
    protected static ?string $modelLabel = 'الطريق';
    
    protected static ?string $pluralModelLabel = 'الطرق';
    
    protected static ?string $navigationGroup = 'إدارة النقل';
    
    protected static ?int $navigationSort = 1;
    protected $listeners = ['stopAdded' => 'addStopToRepeater'];




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('اسم المسار')
                    ->required()
                    ->maxLength(255),

                Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->required(),

                Grid::make(12)
                    ->schema([
                        // العمود الأيسر: الخريطة مستقلة (7 أعمدة)
                        Section::make('الخريطة')
                            ->schema([
                                ViewField::make('map')
                                    ->label('حدد الموقع على الخريطة')
                                    ->view('filament.custom.map-picker-show')
                                    ->extraAttributes(['wire:ignore']),
                            ])
                            ->columnSpan(7),

                        // العمود الأيمن: التوقفات (5 أعمدة)
                       Repeater::make('stops_list')
            ->label('التوقفات')
            ->relationship('stops')
            ->schema([
                Select::make('stop_id')
                    ->label('اختر المحطة')
                    ->options(Stop::all()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $stop = Stop::find($state);
                        if ($stop) {
                            $set('name', $stop->name);
                            $set('address', $stop->address);
                            $set('description', $stop->description);
                            $set('latitude', $stop->latitude);
                            $set('longitude', $stop->longitude);
                        } else {
                            $set('name', "");
                            $set('address', '');
                            $set('description', "");
                            $set('latitude', '');
                            $set('longitude', '');
                        }
                        // إخفاء الحقول بعد اختيار المحطة
                        $set('hide_fields', true); // تحديث الحالة في Livewire (مثال)
                    })
                    ->extraInputAttributes([
                        'data-field' => 'stop_id',
                        'class' => 'stop-select'
                    ]),
                TextInput::make('name')
                    ->extraInputAttributes([
                        'data-field' => 'name',
                        'class' => 'stop-name',
                        'style' => 'display:none' // إخفاء الحقل بشكل افتراضي
                    ]),

                TextInput::make('description')
                    ->extraInputAttributes([
                        'data-field' => 'description',
                        'class' => 'stop-description',
                        'style' => 'display:none' // إخفاء الحقل بشكل افتراضي
                    ]),
                TextInput::make('address')
                    ->label('العنوان')
                    ->disabled()
                    ->extraInputAttributes([
                        'data-field' => 'address',
                        'class' => 'stop-address'
                    ]),

                TextInput::make('latitude')
                    ->label('خط العرض')
                    ->reactive()
                    ->extraInputAttributes([
                        'data-field' => 'latitude',
                        'class' => 'stop-latitude',
                        'style' => 'display:none'
                    ]),

                TextInput::make('longitude')
                    ->label('خط الطول')
                    ->reactive()
                    ->extraInputAttributes([
                        'data-field' => 'longitude',
                        'class' => 'stop-longitude',
                        'style' => 'display:none'
                    ]),
            ])
            ->columns(1)
            ->createItemButtonLabel('اضف محطة')
            ->deletable(true)
            ->columnSpan(5)]),
                ]);
    }

    public static function beforeSave($record, $data)
    {
        if (isset($data['stops_list'])) {
            $record->stops()->sync(array_column($data['stops_list'], 'stop_id'));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المسار')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListRoutes::route('/'),
            'create' => Pages\CreateRoute::route('/create'),
            'edit' => Pages\EditRoute::route('/{record}/edit'),
        ];
    }
}