<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisorResource\Pages;
use App\Filament\Resources\SupervisorResource\RelationManagers;
use App\Models\Supervisor;
use App\Models\School;
use App\Models\User;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Tables\Actions\Action;

class SupervisorResource extends Resource
{
    protected static ?string $model = Supervisor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'المساعدين';
    
    protected static ?string $modelLabel = 'مساعد';
    
    protected static ?string $pluralModelLabel = 'المساعدين';

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
                Section::make('المعلومات الأساسية')
                    ->description('البيانات الشخصية الأساسية للمساعد')
                    ->schema([
                        Grid::make(2)
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
                            


                                Forms\Components\TextInput::make('name')
                                    ->label('الاسم بالعربية')
                                    ->required()
                                    ->maxLength(255),


                                Forms\Components\TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('national_id')
                                    ->label('رقم الهوية')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20),

                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('تاريخ الميلاد')
                                    ->before('today'),

                                Forms\Components\Select::make('gender')
                                    ->label('الجنس')
                                    ->options([
                                        'male' => 'ذكر',
                                        'female' => 'أنثى',
                                    ])
                                    ->native(false),

                                Forms\Components\TextInput::make('emergency_contact')
                                    ->label('رقم الطوارئ')
                                    ->tel()
                                    ->maxLength(20),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('معلومات العمل')
                    ->description('البيانات المتعلقة بالوظيفة والعمل')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('hire_date')
                                    ->label('تاريخ التوظيف')
                                    ->required()
                                    ->default(now()),


                                Forms\Components\TextInput::make('position')
                                    ->label('المنصب')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('salary')
                                    ->label('الراتب')
                                    ->numeric()
                                    ->prefix('ريال')
                                    ->step(0.01),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Supervisor $record): string => $record->name_en ?? ''),

                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),



                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->copyable(),

             

                Tables\Columns\TextColumn::make('position')
                    ->label('المنصب')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('غير محدد'),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('guardians_count')
                    ->label('عدد أولياء الأمور')
                    ->counts('guardians')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label('تاريخ التوظيف')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->options(School::pluck('name_ar', 'id'))
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()?->school_id === null), // 👈 التحقق هنا

              

                SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ])
                    ->native(false),

              
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع الحالات')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                Action::make('toggle_status')
                    ->label(fn (Supervisor $record) => $record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
                    ->icon(fn (Supervisor $record) => $record->is_active ? 'heroicon-o-x-mark' : 'heroicon-o-check')
                    ->color(fn (Supervisor $record) => $record->is_active ? 'danger' : 'success')
                    ->action(function (Supervisor $record) {
                        $record->toggleStatus();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Supervisor $record) => 
                        $record->is_active ? 'إلغاء تفعيل المساعد' : 'تفعيل المساعد'
                    )
                    ->modalDescription(fn (Supervisor $record) => 
                        'هل أنت متأكد من تغيير حالة المساعد: ' . $record->name . '؟'
                    ),

                Tables\Actions\ViewAction::make()
                    ->label('عرض'),

                Tables\Actions\EditAction::make()
                    ->label('تعديل'),

                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->before(function (Supervisor $record) {
                        // فصل جميع العلاقات قبل الحذف
                        $record->students()->detach();
                        $record->guardians()->detach();
                        // $record->buses()->detach();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $record->students()->detach();
                                $record->guardians()->detach();
                                // $record->buses()->detach();
                            }
                        }),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                            }
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('المعلومات الشخصية')
                    ->schema([
                        TextEntry::make('name')
                            ->label('الاسم بالعربية'),

                        
                        TextEntry::make('phone')
                            ->label('رقم الهاتف'),
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->placeholder('غير محدد'),
                        TextEntry::make('national_id')
                            ->label('رقم الهوية')
                            ->placeholder('غير محدد'),
                        TextEntry::make('date_of_birth')
                            ->label('تاريخ الميلاد')
                            ->date('Y-m-d')
                            ->placeholder('غير محدد'),
                        TextEntry::make('gender')
                            ->label('الجنس')
                            ->formatStateUsing(fn (string $state): string => 
                                $state === 'male' ? 'ذكر' : 'أنثى'
                            )
                            ->placeholder('غير محدد'),
                        TextEntry::make('address')
                            ->label('العنوان')
                            ->placeholder('غير محدد')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                InfoSection::make('معلومات العمل')
                    ->schema([
                        TextEntry::make('school.name_ar')
                            ->label('المدرسة'),
                        TextEntry::make('position')
                            ->label('المنصب')
                            ->placeholder('غير محدد'),
                        TextEntry::make('hire_date')
                            ->label('تاريخ التوظيف')
                            ->date('Y-m-d'),
                        TextEntry::make('salary')
                            ->label('الراتب')
                            ->money('SAR')
                            ->placeholder('غير محدد'),
                        TextEntry::make('work_years')
                            ->label('سنوات الخبرة')
                            ->state(function (Supervisor $record): string {
                                return $record->work_years . ' سنة';
                            }),
                        TextEntry::make('is_active')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'نشط' : 'غير نشط')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ])
                    ->columns(2),

                InfoSection::make('إحصائيات')
                    ->schema([
                        TextEntry::make('students_count')
                            ->label('عدد الطلاب المرتبطين')
                            ->state(function (Supervisor $record): string {
                                return $record->students()->count() . ' طالب';
                            }),
                        TextEntry::make('guardians_count')
                            ->label('عدد أولياء الأمور المرتبطين')
                            ->state(function (Supervisor $record): string {
                                return $record->guardians()->count() . ' ولي أمر';
                            }),
                        TextEntry::make('active_students_count')
                            ->label('الطلاب النشطون')
                            ->state(function (Supervisor $record): string {
                                return $record->students()->where('is_active', true)->count() . ' طالب';
                            }),
                        TextEntry::make('recent_assignments')
                            ->label('التكليفات الحديثة (الشهر الماضي)')
                            ->state(function (Supervisor $record): string {
                                $recentStudents = $record->students()
                                    ->wherePivot('assigned_date', '>=', now()->subMonth())
                                    ->count();
                                $recentGuardians = $record->guardians()
                                    ->wherePivot('assigned_date', '>=', now()->subMonth())
                                    ->count();
                                return "{$recentStudents} طلاب، {$recentGuardians} أولياء أمور";
                            }),
                    ])
                    ->columns(2),
            ]);
    }
    public static function handleRecordCreation(array $data): Model
    {
        // إنشاء Guardian بدون user_id
        $supervisor = Supervisor::create(collect($data)->except(['student_ids'])->toArray());
        // إنشاء المستخدم
        $user = User::create([
            'name'      => $supervisor->name_ar,
            'email'     => $supervisor->email ?? 'supervisor' . $supervisor->id . '@example.com',
            'phone'     => $supervisor->phone,
            'user_type' => 'supervisor',
            'password'  => bcrypt('admin123'),
            'is_active' => true,
            'school_id' => $supervisor->school_id,
        ]);

        // ربط Guardian بالمستخدم
        $supervisor->user_id = $user->id;
        $supervisor->save();


        return $supervisor;
    }



    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\GuardiansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupervisors::route('/'),
            'create' => Pages\CreateSupervisor::route('/create'),
            'view' => Pages\ViewSupervisor::route('/{record}'),
            'edit' => Pages\EditSupervisor::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)
            ->where('school_id', auth()->user()->school_id)
            ->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name',   'phone', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'المدرسة' => $record->school?->name,
            'الهاتف' => $record->phone,
        ];
    }
}