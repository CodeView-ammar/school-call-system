<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentCallResource\Pages;
use App\Models\StudentCall;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class StudentCallResource extends Resource
{
    protected static ?string $model = StudentCall::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'نداءات الطلاب';
    protected static ?string $pluralModelLabel = 'نداءات الطلاب';
    // public static function canCreate(): bool
    // {
    //     return  false;
    // }
    
    // public static function canEdit($record): bool
    // {
    //     return  false;
    // }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        return $query;
    }



    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('school_id')
                ->label('مدرسة')
                ->relationship('school', 'name_ar')
                ->required(),
            Forms\Components\Select::make('branch_id')
                ->label('فرع')
                ->relationship('branch', 'name_ar')
                ->required(),
                
            Forms\Components\Select::make('call_type_id')
                ->label('نوع النداء')
                ->relationship('callType', 'ctype_name_ar')
                ->required(),
                
            Forms\Components\Select::make('student_id')
                ->label('طالب')
                ->relationship('student', 'name_ar')
                ->required(),

            Forms\Components\DateTimePicker::make('call_cdate')
                ->label('تاريخ النداء')
                ->required(),

            Forms\Components\DateTimePicker::make('call_edate')
                ->label('تاريخ الانتهاء')
                ->nullable(),

            Forms\Components\Select::make('user_id')
                ->label('المستخدم')
                ->relationship('user', 'name')
                ->required(),


            Forms\Components\Toggle::make('status')
                ->label('حالة التنفيذ')
                ->default(0),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('call_type.name')->label('نوع النداء'),
                Tables\Columns\TextColumn::make('student.name_ar')->label('اسم الطالب'),
                Tables\Columns\TextColumn::make('school.name')->label('المدرسة'),
                Tables\Columns\TextColumn::make('call_cdate')->label('تاريخ النداء')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')
                    ->formatStateUsing(fn($state) => $state ? 'منفذ' : 'غير منفذ'),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التسجيل')->dateTime(),
            ])
            ->filters([
                // يمكنك إضافة فلاتر هنا إذا لزم الأمر
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentCalls::route('/'),
            // 'create' => Pages\CreateStudentCall::route('/create'),
            // 'edit' => Pages\EditStudentCall::route('/{record}/edit'),
        ];
    }
}