<?php

namespace App\Filament\Resources;

use App\Models\EarlyArrival;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use App\Filament\Resources\EarlyArrivalResource\Pages;
use Illuminate\Database\Eloquent\Builder;

class EarlyArrivalResource extends Resource
{
    protected static ?string $model = EarlyArrival::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'النداء المبكر';
    protected static ?string $navigationLabel = 'النداء المبكر';
    protected static ?string $modelLabel = 'النداء المبكر';
    protected static ?string $pluralModelLabel = 'النداءات المبكرة';
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function getEloquentQuery(): Builder
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
                Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name_ar')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'name_ar')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('guardian_id')
                    ->label('ولي الأمر')
                    ->relationship('guardian', 'name_ar')
                    ->searchable()
                    ->nullable(),

                // Forms\Components\Select::make('user_id')
                //     ->label('الموظف المسجل')
                //     ->relationship('user', 'name_ar')
                //     ->nullable(),

                Forms\Components\DatePicker::make('pickup_date')
                    ->label('تاريخ الاستلام')
                    ->required(),

                Forms\Components\TimePicker::make('pickup_time')
                    ->label('وقت الاستلام')
                    ->required(),

                Forms\Components\TextInput::make('pickup_reason')
                    ->label('سبب الاستلام')
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        'completed' => 'مكتمل',
                        'canceled' => 'ملغى',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name_ar')->label('الطالب'),
                Tables\Columns\TextColumn::make('guardian.name_ar')->label('ولي الأمر')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('school.name_ar')->label('المدرسة'),
                Tables\Columns\TextColumn::make('branch.name_ar')->label('الفرع'),
                // Tables\Columns\TextColumn::make('user.name_ar')->label('الموظف المسجل'),
                Tables\Columns\TextColumn::make('pickup_date')->label('تاريخ الاستلام')->date(),
                Tables\Columns\TextColumn::make('pickup_time')->label('وقت الاستلام'),
                Tables\Columns\TextColumn::make('status')
                ->label('الحالة')
                ->badge(true)
                ->color(fn ($state) => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'completed' => 'primary',
                    'canceled' => 'secondary',
                    default => 'gray',
                })
                ->formatStateUsing(fn ($state) => match ($state) {
                    'pending' => 'قيد الانتظار',
                    'approved' => 'مقبول',
                    'rejected' => 'مرفوض',
                    'completed' => 'مكتمل',
                    'canceled' => 'ملغى',
                    default => $state,
                }),
            ])
            ->filters([
                // يمكنك إضافة عوامل تصفية هنا إذا لزم الأمر
            ])
            ->actions([
                Tables\Actions\Action::make('updateStatus')
                    ->label('تحديث الحالة')
                    ->form(fn (EarlyArrival $record) => [
                        Forms\Components\Select::make('new_status')
                            ->label('اختر الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'approved' => 'مقبول',
                                'rejected' => 'مرفوض',
                                'completed' => 'مكتمل',
                                'canceled' => 'ملغى',
                            ])
                            ->default($record->status) // 👈 هنا نضع القيمة الحالية
                            ->required(),
                    ])
                    ->action(function (EarlyArrival $record, array $data) {
                        $record->status = $data['new_status'];
                        $record->save();
                    })
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('تحديث الحالة')
                    ->modalButton('حفظ'),
                    
                Tables\Actions\EditAction::make(),
            ])
    ->bulkActions([
        Tables\Actions\DeleteBulkAction::make(),
    ]);
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; // أو 'primary', 'success', 'warning', 'info', 'gray'
    }

    public static function getRelations(): array
    {
        return [
            // يمكنك إضافة العلاقات هنا إذا لزم الأمر
        ];
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['status'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEarlyArrivals::route('/'),
            // 'create' => Pages\CreateEarlyArrival::route('/create'),
            // 'edit' => Pages\EditEarlyArrival::route('/{record}/edit'),
        ];
    }
}