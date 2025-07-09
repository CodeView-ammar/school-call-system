<?php
namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use App\Models\School; // تأكد من استيراد نموذج المدرسة
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'العطل';
    protected static ?string $modelLabel = 'عطلة';
    protected static ?string $pluralModelLabel = 'العطل';
    protected static ?string $navigationGroup = 'إدارة التوقيت';
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
        {
            $query = parent::getEloquentQuery();

            if (auth()->user()?->school_id) {
                $query->where('school_id', auth()->user()->school_id);
            }

            return $query;
        }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم العطلة (عربي)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_en')
                            ->label('اسم العطلة (إنجليزي)')
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('تاريخ بداية العطلة')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('تاريخ نهاية العطلة')
                            ->required()
                            ->native(false)
                            ->after('from_date'),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
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
                            ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم العطلة (عربي)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('اسم العطلة (إنجليزي)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('from_date')
                    ->label('تاريخ بداية العطلة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to_date')
                    ->label('تاريخ نهاية العطلة')
                    ->searchable(),
                Tables\Columns\BooleanColumn::make('is_active') // استخدام BooleanColumn هنا
                    ->label('نشط'),
                 Tables\Columns\TextColumn::make('school.name_ar')
                ->label('المدرسة')
                ->searchable()
                ->sortable()
                ->visible(fn () => auth()->user()?->school_id === null),
            
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListHolidays::route('/'),
            // 'create' => Pages\CreateHoliday::route('/create'),
            // 'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}