<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Models\Trip;
use App\Models\Route;
use App\Models\Driver;
use App\Models\Bus;
use App\Models\School;
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

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'الرحلات';

    protected static ?string $modelLabel = 'رحلة';

    protected static ?string $pluralModelLabel = 'الرحلات';
  
    protected static ?string $navigationGroup = 'إدارة النقل';
    protected static ?int $navigationSort = 3;

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
                                    Forms\Components\Select::make('route_id')
                                        ->label('المسار')
                                        ->options(Route::with('school')->get()->pluck('route_ar', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->placeholder('اختر المسار')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set) => 
                                            $set('school_id', Route::find($state)?->school_id)
                                        ),
                                ])
                                ->columnSpan(1),
                            
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('effective_date')
                                        ->label('تاريخ البدء')
                                        ->required()
                                        ->default(now())
                                        ->displayFormat('Y-m-d'),
                                    
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
                                        ->displayFormat('H:i'),
                                    
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
                                    Forms\Components\Select::make('driver_id')
                                        ->label('السائق')
                                        ->options(Driver::pluck('name', 'id'))
                                        ->searchable()
                                        ->placeholder('اختر السائق')
                                        ->nullable(),
                                    
                                    Forms\Components\Select::make('bus_id')
                                        ->label('الباص')
                                        ->options(Bus::pluck('number', 'id'))
                                        ->searchable()
                                        ->placeholder('اختر الباص')
                                        ->nullable(),
                                ]),

                            Forms\Components\Hidden::make('school_id'),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('route.route_ar')
                    ->label('المسار')
                    ->sortable()
                    ->searchable(),

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
                    ->url(fn (Trip $record): string => route('filament.admin.resources.trips.configure-times', $record)),
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
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
            'configure-times' => Pages\ConfigureTripTimes::route('/{record}/configure-times'),
        ];
    }
}