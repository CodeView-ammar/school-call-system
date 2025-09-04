<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusRouteResource\Pages;
use App\Models\BusRoute;
use App\Models\School;
use App\Models\Bus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusRouteResource extends Resource
{
    protected static ?string $model = BusRoute::class;

    // protected static ?string $navigationIcon = 'heroicon-o-map';
    
    // protected static ?string $navigationLabel = 'مسارات الباصات';
    
    
    // protected static ?string $navigationGroup = 'إدارة النقل';
    
    protected static ?string $modelLabel = 'مسار باص';
    
    protected static ?string $pluralModelLabel = 'مسارات الباصات';
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationIcon = null; // إخفاء الأيقونة
    protected static ?string $navigationLabel = null; // إخفاء الاسم
    protected static ?string $navigationGroup = null; // إذا أردت إخفاء المجموعة
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                Forms\Components\Select::make('school_id')
                            ->label('المدرسة')
                            ->options(School::query()->pluck('name_ar', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('bus_id', null)),

    Forms\Components\Select::make('bus_id')
        ->label('الحافلة')
        ->options(function (callable $get) {
            $schoolId = $get('school_id');
            if (!$schoolId) return []; // إرجاع مصفوفة فارغة إذا لم يتم اختيار مدرسة

            return Bus::whereHas('branch', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->pluck('number', 'id'); // إرجاع قائمة الباصات
        })
        ->required()
        ->searchable()
        ->preload()
        ->reactive(),
                            ]),             
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('اسم المسار (عربي)')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('name_en')
                                    ->label('اسم المسار (إنجليزي)')
                                    ->maxLength(255),
                            ]),
                            
                        Forms\Components\TextInput::make('code')
                            ->label('كود المسار')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                    ]),

                Forms\Components\Section::make('نوع المسار')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('route_is_go')
                                    ->label('رحلة ذهاب')
                                    ->default(true),
                                    
                                Forms\Components\Toggle::make('route_is_return')
                                    ->label('رحلة عودة')
                                    ->default(false),
                            ]),
                    ]),

                Forms\Components\Section::make('تحديد المسار على الخريطة')
                    ->description('حدد نقاط البداية والنهاية للمسار')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.bus-route-map'),
                        // ->visible(fn (callable $get) => $get('bus_id') !== null),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('route_road_from_lat')
                                    ->label('خط العرض (نقطة البداية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->hidden()
                                    ->dehydrated()
                                     ->required(), // اختياري للتأكيد فقط
                                Forms\Components\TextInput::make('route_road_from_lng')
                                    ->label('خط الطول (نقطة البداية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->hidden()
                                    ->dehydrated()
                                     ->required(), // اختياري للتأكيد فقط
                                Forms\Components\TextInput::make('route_road_from_lat')
                                    ->label('خط العرض (نقطة البداية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->default(24.7136) // القيمة الافتراضية
                                    ->required(),

                                Forms\Components\TextInput::make('route_road_from_lng')
                                    ->label('خط الطول (نقطة البداية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->default(46.6753) // القيمة الافتراضية
                                    ->required(),

                                Forms\Components\Textarea::make('route_road_from_address')
                                    ->label('عنوان نقطة البداية')
                                    ->rows(2)
                                    ->default('العنوان الافتراضي لنقطة البداية'), // القيمة الافتراضية

                                Forms\Components\TextInput::make('route_road_to_lat')
                                    ->label('خط العرض (نقطة النهاية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->default(24.7500) // القيمة الافتراضية
                                    ->required(),

                                Forms\Components\TextInput::make('route_road_to_lng')
                                    ->label('خط الطول (نقطة النهاية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->default(46.7000) // القيمة الافتراضية
                                    ->required(),

                                Forms\Components\Textarea::make('route_road_to_address')
                                    ->label('عنوان نقطة النهاية')
                                    ->rows(2)
                                    ->default('العنوان الافتراضي لنقطة النهاية'), // القيمة الافتراضية
                                    ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('route_road_to_lat')
                                    ->label('خط العرض (نقطة النهاية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->hidden()
                                    ->dehydrated()
                                    ->required(), // اختياري للتأكيد فقط
                                Forms\Components\TextInput::make('route_road_to_lng')
                                    ->label('خط الطول (نقطة النهاية)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->hidden()
                                    ->dehydrated(),
                            ]),
                            
                        Forms\Components\Textarea::make('route_road_from_address')
                            ->label('عنوان نقطة البداية')
                            ->rows(2)
                            ->hidden()
                            ->dehydrated(),
                            
                        Forms\Components\Textarea::make('route_road_to_address')
                            ->label('عنوان نقطة النهاية')
                            ->rows(2)
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Forms\Components\Section::make('معلومات إضافية')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('estimated_time')
                                    ->label('الوقت المقدر (بالدقائق)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix('دقيقة'),
                                    
                                Forms\Components\TextInput::make('distance_km')
                                    ->label('المسافة (بالكيلومتر)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->suffix('كم'),
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('وصف المسار')
                            ->rows(3),
                            
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعل')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('كود المسار')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم المسار')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),
                    
                Tables\Columns\TextColumn::make('bus.number')
                    ->label('رقم الحافلة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('route_type')
                    ->label('نوع الرحلة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ذهاب وعودة' => 'success',
                        'ذهاب' => 'info',
                        'عودة' => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
                    
                Tables\Columns\TextColumn::make('estimated_time')
                    ->label('الوقت المقدر')
                    ->suffix(' د')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('distance_km')
                    ->label('المسافة')
                    ->suffix(' كم')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->options(School::query()->pluck('name_ar', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('bus_id')
                    ->label('الحافلة')
                    ->options(Bus::query()->pluck('number', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('route_is_go')
                    ->label('رحلة ذهاب')
                    ->query(fn (Builder $query): Builder => $query->where('route_is_go', true)),
                    
                Tables\Filters\Filter::make('route_is_return')
                    ->label('رحلة عودة')
                    ->query(fn (Builder $query): Builder => $query->where('route_is_return', true)),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('مفعل')
                    ->falseLabel('غير مفعل')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusRoutes::route('/'),
            'create' => Pages\CreateBusRoute::route('/create'),
            'edit' => Pages\EditBusRoute::route('/{record}/edit'),
        ];
    }
}
