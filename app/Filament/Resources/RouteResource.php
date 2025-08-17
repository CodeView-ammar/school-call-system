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

    protected static ?string $navigationLabel = 'المسارات';
    
    protected static ?string $modelLabel = 'المسارات';
    
    protected static ?string $pluralModelLabel = 'المسارات';
    
    protected static ?string $navigationGroup = 'إدارة النقل';
    
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('route_ar')
                    ->label('اسم المسار')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('route_type')
                    ->label('نوع الرحلة')
                    ->required()
                    ->options([
                        'صباح' => 'صباحي',
                        'مساء' => 'مسائي',
                    ])
                    ->default('صباحي'),
                    
                Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->required(),
              Select::make('stops')
                ->label('التوقفات')
                ->multiple()
                // ->hidden()        // مخفي عن المستخدم
                // ->default([])     // قيمة افتراضية
                ->reactive()      // يسمح بتحديث القيمة برمجياً
                ->relationship('stops', 'name'),
                // ->searchable(false),
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
                            // ->relationship('stops')
                            ->schema([
                                Select::make('stop_id')
                                    ->label('اختر المحطة')
                                    ->options(Stop::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get,callable $set) {
                                        $stop = Stop::find($state);
                                        if ($stop) {
                                            $set('name', $stop->name);
                                            $set('address', $stop->address);
                                            $set('description', $stop->description);
                                            $set('latitude', $stop->latitude);
                                            $set('longitude', $stop->longitude);
                                            $set('school_id', $stop->school_id);
                                        } else {
                                            $set('name', "");
                                            $set('address', '');
                                            $set('description', "");
                                            $set('latitude', '');
                                            $set('longitude', '');
                                            $set('school_id', null);
                                        }
                                        $currentStops = $get('stops') ?? [];
                                        if ($state && !in_array($state, $currentStops)) {
                                            $currentStops[] = $state;
                                            $set('stops', $currentStops);
                                        }
                                        // إخفاء الحقول بعد اختيار المحطة
                                        $set('hide_fields', true);
                                    }),
                                    // ->extraInputAttributes([
                                    //     'data-field' => 'stop_id',
                                    //     'class' => 'stop-select'
                                    // ]),
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
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            // عند الحذف أو التعديل يتم تحديث Select stops
                            $stopIdsInRepeater = collect($state)
                                ->pluck('stop_id')
                                ->filter()
                                ->values()
                                ->toArray();

                            $set('stops', $stopIdsInRepeater);
                        })
                        ->columnSpan(5)
                  
                  
                       
                       
                        ]),
            ]);
    }


    // protected function afterCreate($record, array $data): void
    // {
    //     $stopIds = collect($data['stops_list'])->pluck('stop_id')->filter()->toArray();
    //     $record->stops()->sync($stopIds);
    // }

    // protected function afterSave($record, array $data): void
    // {
    //     $stopIds = collect($data['stops_list'])->pluck('stop_id')->filter()->toArray();
    //     $record->stops()->sync($stopIds);
    // }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('route_ar')
                    ->label('اسم المسار')
                    ->sortable()
                    ->searchable(),

            Tables\Columns\TextColumn::make('route_type')
                ->label('نوع الرحلة')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn($state) => match($state) {
                    'صباحي' => 'صباحي',
                    'مسائي' => 'مسائي',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->sortable()
                    ->searchable(),
                     // إضافة عمود نوع الرحلة
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_route')
                    ->label('عرض المسار')
                    ->icon('heroicon-o-map')
                    ->color('primary')
                    ->modalHeading(fn ($record) => 'عرض مسار: ' . $record->name)
                    ->modalContent(fn ($record) => view('filament.custom.route-map-view', ['route' => $record]))
                    ->modalWidth('7xl')
                    ->slideOver(),
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