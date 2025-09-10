<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Models\Trip;
use App\Models\Route;
use App\Models\Driver;
use App\Models\Bus;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Group;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class TripResource extends Resource
{
protected static ?string $model = Trip::class;

protected static ?string $navigationIcon = 'heroicon-o-truck';

protected static ?string $navigationLabel = null;

protected static ?string $modelLabel = null;

protected static ?string $pluralModelLabel = null;

protected static ?string $navigationGroup = null;

public static function getNavigationLabel(): string
{
    return __('filament.resources.trip.navigation_label');
}

public static function getModelLabel(): string
{
    return __('filament.resources.trip.label');
}

public static function getPluralModelLabel(): string
{
    return __('filament.resources.trip.plural_label');
}

public static function getNavigationGroup(): ?string
{
    return __('filament.navigation.transport_management');
}

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Group::make([
                // Step 1: Trip Information
                Card::make()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                            auth()->user()?->school_id === null
                            ? Forms\Components\Select::make('school_id')
                                ->label('المدرسة')
                                ->relationship('school', 'name_ar')
                                ->required()
                                ->searchable()
                                ->preload()
                            : Forms\Components\Hidden::make('school_id')
                                ->default(auth()->user()->school_id)
                                ->dehydrated(true)
                                ->required(),
                            // حقل الفرع
                            Forms\Components\Select::make('branch_id')
                                ->label('الفرع')
                                ->options(function (callable $get) {
                                    $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                                    if (!$schoolId) return [];
                                    return \App\Models\Branch::where('school_id', $schoolId)
                                        ->pluck('name_ar', 'id');
                                })
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // إعادة تعيين المسار عند تغيير الفرع
                                    $set('route_id', null);
                                }),

                            // حقل المسار
                            Forms\Components\Select::make('route_id')
                                ->label('المسار')
                                ->options(fn (callable $get) => Route::where('branch_id', $get('branch_id'))->pluck('route_ar', 'id'))
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $route = Route::find($state);
                                    if ($route) {
                                        $set('school_id', $route->school_id);
                                        $set('branch_id', $route->branch_id); // ← يجب التأكد من هنا
                                    }
                                }),


                            ])
                            ->columnSpan(1),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('trip_type')
                                    ->label('نوع الرحلة')
                                    ->options([
                                        'morning' => 'رحلة صباحية',
                                        'evening' => 'رحلة مسائية'
                                    ])
                                    ->default('morning')
                                    ->required()
                                    ->reactive(),
                                Forms\Components\DatePicker::make('effective_date')
                                    ->label('تاريخ البدء')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('Y-m-d')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        static::checkForConflicts($get, $set);
                                    }),
                                
                                Forms\Components\Select::make('repeated_every_days')
                                    ->label('التكرار كل (أيام)')
                                    ->options([
                                        1 => 'يومياً',
                                        7 => 'أسبوعياً',
                                        14 => 'كل أسبوعين',
                                        30 => 'شهرياً',
                                    ])
                                    ->default(1)
                                    ->required()
                                    ->helperText('0 يعني عدم التكرار'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('arrival_time_at_first_stop')
                                    ->label('وقت الوصول للمحطة الأولى')
                                    ->required()
                                    ->seconds(false)
                                    ->displayFormat('H:i')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        static::checkForConflicts($get, $set);
                                    }),
                                
                                Forms\Components\TextInput::make('stop_to_stop_time_minutes')
                                    ->label('الوقت بين المحطات (دقائق)')
                                    ->numeric()
                                    ->required()
                                    ->default(5)
                                    ->minValue(1)
                                    ->maxValue(60)
                                    ->helperText('يمكن تغييره لاحقاً'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                // الباص
                                Forms\Components\Select::make('bus_id')
                                    ->label('الباص')
                                    ->options(function (callable $get) {
                                        $schoolId = $get('school_id');
                                        if (!$schoolId) {
                                            return Bus::whereNotNull('id')->pluck('number', 'id');
                                        }
                                        // Filter buses by school if needed
                                        return Bus::whereNotNull('id')->pluck('number', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->placeholder('اختر الباص')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (!$state) {
                                            $set('driver_id', null);
                                            return;
                                        }
                                        // dd($state);
                                        $bus = Bus::with('driver')->find($state);
                                        if ($bus && $bus->driver) {
                                            $set('driver_id', $bus->driver->id);
                                        } else {
                                            $set('driver_id', null);
                                        }
                                        
                                        static::checkForConflicts($get, $set);
                                    })
                                    ->rules([
                                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                            if ($value && !Bus::find($value)) {
                                                $fail('الباص المحدد غير موجود.');
                                            }
                                        },
                                    ]),

                                Forms\Components\Select::make('driver_id')
                                ->label('السائق')
                                ->options(function (callable $get) {
                                    $busId = $get('bus_id');
                                    if (!$busId) {
                                        return Driver::pluck('name', 'id');
                                    }

                                    $bus = Bus::with('driver')->find($busId);
                                    if ($bus && $bus->driver) {
                                        return [$bus->driver->id => $bus->driver->name];
                                    }

                                    return Driver::pluck('name', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->placeholder('اختر السائق')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // تحقق من وجود السائق
                                    $driver = Driver::find($state);
                                    if (!$driver) {
                                        $set('driver_id', null); // إعادة تعيين السائق إذا لم يكن موجودًا
                                    }

                                    static::checkForConflicts($get, $set);
                                })
                                ->rules([
                                    fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                        if ($value) {
                                            $driver = Driver::find($value);
                                            if (!$driver) {
                                                $fail('السائق المحدد غير موجود.');
                                            }
                                        }
                                    },
                                ]),
                                ]),

                        // Forms\Components\Hidden::make('school_id'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('يمكن إيقاف الرحلة دون حذفها'),
                    ])
                    ->heading('معلومات الرحلة')
                    ->description('اضبط المسار والتاريخ والسائق')
                    ->icon('heroicon-o-information-circle')
                    ->columns(1),
            ])
            ->columnSpan(['lg' => 2]),
            
            Group::make([
                Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('next_step')
                            ->label('')
                            ->content('بعد حفظ الرحلة، ستتمكن من تكوين جدول الأوقات وتحديد وقت الوصول لكل محطة على حدة')
                            ->helperText('الخطوة التالية: تكوين جدول الأوقات'),
                    ])
                    ->heading('الخطوة التالية')
                    ->icon('heroicon-o-clock')
                    ->columns(1),
            ])
            ->columnSpan(['lg' => 1]),
        ])
        ->columns(3);
}

protected static function checkForConflicts($get, $set)
{
    $driverId = $get('driver_id');
    $busId = $get('bus_id');
    $effectiveDate = $get('effective_date');
    $arrivalTime = $get('arrival_time_at_first_stop');
    
    if (!$driverId && !$busId) {
        return;
    }

    $conflicts = [];

    if ($driverId && $effectiveDate && $arrivalTime) {
        // Validate driver exists first
        if (!Driver::find($driverId)) {
            Notification::make()
                ->title('خطأ!')
                ->body('السائق المحدد غير موجود في قاعدة البيانات')
                ->danger()
                ->send();
            return;
        }

        $driverConflicts = Trip::where('driver_id', $driverId)
            ->where('effective_date', $effectiveDate)
            ->where('arrival_time_at_first_stop', $arrivalTime)
            ->where('is_active', true)
            ->with(['route', 'bus'])
            ->get();

        if ($driverConflicts->isNotEmpty()) {
            $conflicts['driver'] = $driverConflicts;
        }
    }

    if ($busId && $effectiveDate && $arrivalTime) {
        // Validate bus exists first
        if (!Bus::find($busId)) {
            Notification::make()
                ->title('خطأ!')
                ->body('الباص المحدد غير موجود في قاعدة البيانات')
                ->danger()
                ->send();
            return;
        }

        $busConflicts = Trip::where('bus_id', $busId)
            ->where('effective_date', $effectiveDate)
            ->where('arrival_time_at_first_stop', $arrivalTime)
            ->where('is_active', true)
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
                $driverName = $conflict->driver->name ?? 'غير محدد';
                $conflictMessages[] = "تعارض في السائق: {$driverName} - الرحلة: {$conflict->route->route_ar} - الباص: " . ($conflict->bus->number ?? 'غير محدد');
            }
        }

        if (isset($conflicts['bus'])) {
            foreach ($conflicts['bus'] as $conflict) {
                $driverName = $conflict->driver->name ?? 'غير محدد';
                $conflictMessages[] = "تعارض في الباص: {$conflict->bus->number} - الرحلة: {$conflict->route->route_ar} - السائق: {$driverName}";
            }
        }

        Notification::make()
            ->title('تعارض في الأوقات!')
            ->body(implode("\n", $conflictMessages))
            ->danger()
            ->persistent()
            ->send();
    }
}

public static function table(Table $table): Table
{
    return $table
        ->recordClasses(fn (Trip $record) => static::hasConflicts($record) ? 'bg-red-50 border-l-4 border-red-500' : null)
        ->columns([
            Tables\Columns\TextColumn::make('route_info')
            ->label('المسار / نوع الرحلة')
            ->sortable()
            ->searchable()
            ->getStateUsing(function ($record) {
                return $record->route->route_ar . ' - ' . $record->route->route_type;
            }),
            Tables\Columns\TextColumn::make('effective_date')
                ->label('تاريخ البدء')
                ->date('Y-m-d')
                ->sortable(),

            Tables\Columns\TextColumn::make('arrival_time_at_first_stop')
                ->label('وقت البدء')
                ->time('H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('repeated_every_days')
                ->label('التكرار')
                ->formatStateUsing(fn ($state) => match($state) {
                    1 => 'يومياً',
                    7 => 'أسبوعياً', 
                    14 => 'كل أسبوعين',
                    30 => 'شهرياً',
                    default => "كل {$state} أيام"
                })
                ->badge()
                ->color('success'),

            Tables\Columns\TextColumn::make('driver.name')
                ->label('السائق')
                ->sortable()
                ->searchable()
                ->placeholder('غير محدد'),

            Tables\Columns\TextColumn::make('bus.number')
                ->label('رقم الباص')
                ->sortable()
                ->placeholder('غير محدد'),

            Tables\Columns\IconColumn::make('is_active')
                ->label('نشط')
                ->boolean()
                ->sortable(),

            Tables\Columns\IconColumn::make('has_conflicts')
                ->label('تعارض')
                ->boolean()
                ->getStateUsing(fn (Trip $record) => static::hasConflicts($record))
                ->trueIcon('heroicon-o-exclamation-triangle')
                ->falseIcon('heroicon-o-check-circle')
                ->trueColor('danger')
                ->falseColor('success')
                ->tooltip(fn (Trip $record) => static::hasConflicts($record) ? 'يوجد تعارض في الأوقات' : 'لا يوجد تعارض'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime('Y-m-d H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('route_id')
                ->label('المسار')
                ->options(Route::pluck('route_ar', 'id'))
                ->searchable(),

            Tables\Filters\TernaryFilter::make('is_active')
                ->label('الحالة')
                ->boolean()
                ->trueLabel('نشط فقط')
                ->falseLabel('غير نشط فقط')
                ->native(false),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('configure_times')
                ->label('تكوين الأوقات')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(fn (Trip $record): string => TripResource::getUrl('configure-times', ['record' => $record])),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

protected static function hasConflicts(Trip $record): bool
{
    if (!$record->is_active) {
        return false;
    }

    $driverConflicts = false;
    $busConflicts = false;

    if ($record->driver_id) {
        $driverConflicts = Trip::where('driver_id', $record->driver_id)
            ->where('effective_date', $record->effective_date)
            ->where('arrival_time_at_first_stop', $record->arrival_time_at_first_stop)
            ->where('is_active', true)
            ->where('id', '!=', $record->id)
            ->exists();
    }

    if ($record->bus_id) {
        $busConflicts = Trip::where('bus_id', $record->bus_id)
            ->where('effective_date', $record->effective_date)
            ->where('arrival_time_at_first_stop', $record->arrival_time_at_first_stop)
            ->where('is_active', true)
            ->where('id', '!=', $record->id)
            ->exists();
    }

    return $driverConflicts || $busConflicts;
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
        'index' => Pages\ListTrips::route('/'),
        'create' => Pages\CreateTrip::route('/create'),
        'edit' => Pages\EditTrip::route('/{record}/edit'),
        'configure-times' => Pages\ConfigureTripTimesNew::route('/{record}/configure-times'),
    ];
}
}