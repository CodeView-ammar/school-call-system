<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuardianResource\Pages;
use App\Filament\Resources\GuardianResource\RelationManagers;
use App\Filament\Resources\GuardianResource\Actions;
use App\Models\Guardian;
use App\Models\User;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Filament\Forms\Components\Actions\ActionFailedException;
class GuardianResource extends Resource
{
    protected static ?string $model = Guardian::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'أولياء الأمور';

    protected static ?string $modelLabel = 'ولي أمر';

    protected static ?string $pluralModelLabel = 'أولياء الأمور';

    protected static ?string $navigationGroup = 'إدارة المستخدمين';

    protected static ?int $navigationSort = 2;

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
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('school_id')
                                    ->label('المدرسة')
                                    ->options(fn () => \App\Models\School::pluck('name_ar', 'id'))
                                    ->default(auth()->user()?->school_id)
                                    ->disabled(fn () => auth()->user()?->school_id !== null)
                                    ->required()
                                    ->reactive(),
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم (عربي)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Name (English)')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->email(),
                                Forms\Components\TextInput::make('national_id')
                                    ->label('رقم الهوية')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20),
                            ]),
                        Forms\Components\Select::make('relationship')
                            ->label('صلة القرابة')
                            ->options([
                                'father' => 'أب',
                                'mother' => 'أم',
                                'grandfather' => 'جد',
                                'grandmother' => 'جدة',
                                'uncle' => 'عم',
                                'aunt' => 'عمة',
                                'other' => 'أخرى',
                            ])
                            ->required(),
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('address_ar')
                                    ->label('العنوان (عربي)')
                                    ->rows(2),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('job')
                                    ->label('المهنة')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('workplace')
                                    ->label('مكان العمل')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('الأطفال المرتبطين')
                    ->schema([
                        Forms\Components\Select::make('student_ids')
                            ->label('الطلاب')
                            ->multiple()
                            ->searchable()
                            ->relationship('students', 'name_ar')
                            ->options(function () {
                                return Student::query()
                                    ->where('school_id', auth()->user()->school_id ?? 1)
                                    ->where('is_active', true)
                                    ->pluck('name_ar', 'id');
                            })
                            ->preload()
                            ->helperText('اختر الطلاب الموجودين فقط لربطهم بولي الأمر'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('relationship')
                    ->label('صلة القرابة')
                    ->badge(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الأطفال')
                    ->counts('students')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('relationship')
                    ->label('صلة القرابة')
                    ->options([
                        'أب' => 'أب',
                        'أم' => 'أم',
                        'جد' => 'جد',
                        'جدة' => 'جدة',
                        'عم' => 'عم',
                        'خال' => 'خال',
                        'عمة' => 'عمة',
                        'خالة' => 'خالة',
                        'أخرى' => 'أخرى',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Actions\BulkAssignStudentsAction::make(),
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuardians::route('/'),
            'create' => Pages\CreateGuardian::route('/create'),
            'edit' => Pages\EditGuardian::route('/{record}/edit'),
            'view' => Pages\ViewGuardians::route('/{record}/view'),
        ];
    }
public static function handleRecordCreation(array $data): Model
{
    $guardian = Guardian::create(collect($data)->except(['student_ids'])->toArray());

    try {
        $user = User::create([
            'name'      => $guardian->name_ar,
            'email'     => $guardian->email ?? 'guardian' . $guardian->id . '@example.com',
            'phone'     => $guardian->phone,
            'user_type' => 'guardian',
            'password'  => bcrypt('admin123'),
            'is_active' => true,
            'school_id' => $guardian->school_id,
        ]);

        $guardian->user_id = $user->id;
        $guardian->save();
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() === '23000') { // Duplicate entry
            $guardian->delete(); // حذف الـ Guardian غير المكتمل

            // عرض Notification ودّي
            Notification::make()
                ->title('خطأ')
                ->body('البريد الإلكتروني أو الهاتف مستخدم بالفعل. الرجاء اختيار بيانات أخرى.')
                ->danger()
                ->send();

            // إنشاء مستخدم وهمي مؤقت لإرجاع Model صالح حتى لا يظهر TypeError
            return $guardian; 
        }

        throw $e; // أي خطأ آخر نعيد رميه
    }

    // ربط الطلاب
    if (!empty($data['student_ids'])) {
        $guardian->students()->attach($data['student_ids']);
    }

    return $guardian;
}

    public static function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(collect($data)->except(['student_ids'])->toArray());

        // مزامنة الطلاب الموجودين فقط
        if (isset($data['student_ids'])) {
            $record->students()->sync($data['student_ids']);
        }

        return $record;
    }
}