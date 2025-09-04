<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationLabel = 'الحضور والغياب';
    
    protected static ?string $modelLabel = 'حضور';
    
    protected static ?string $pluralModelLabel = 'الحضور والغياب';
    
    protected static ?string $navigationGroup = 'التقارير';
    
    protected static ?int $navigationSort = 1;
      // إظهار الصفحة للمدير الأساسي فقط

    
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
            Forms\Components\Section::make('بيانات الحضور')
                ->schema([

                    Forms\Components\Grid::make(2)
                        ->schema([

                            // المدرسة
                            auth()->user()?->school_id === null
                                ? Forms\Components\Select::make('school_id')
                                    ->label('المدرسة')
                                    ->relationship('school', 'name_ar')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                : Forms\Components\Hidden::make('school_id')
                                    ->default(auth()->user()->school_id)
                                    ->dehydrated(true)
                                    ->required(),

                            // الفرع
                            Forms\Components\Select::make('branch_id')
                                ->label('الفرع')
                                ->searchable()
                                ->preload()
                                ->options(function (callable $get) {
                                    $schoolId = $get('school_id') ?? auth()->user()->school_id;
                                    if (!$schoolId) return [];
                                    return \App\Models\Branch::where('school_id', $schoolId)
                                        ->pluck('name_ar', 'id');
                                })
                                ->reactive()
                                ->required(),

                            // الصف الدراسي
                            Forms\Components\Select::make('grade_class_id')
                                ->label('الصف الدراسي')
                                ->searchable()
                                ->preload()
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');
                                    if (!$branchId) return [];
                                    return \App\Models\GradeClass::where('branch_id', $branchId)
                                        ->pluck('name_ar', 'id');
                                })
                                ->reactive()
                                ->required(),

                            // الطالب
                            Forms\Components\Select::make('student_id')
                                ->label('الطالب')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->options(function (callable $get) {
                                    $gradeClassId = $get('grade_class_id');
                                    if (!$gradeClassId) return [];
                                    return \App\Models\Student::where('grade_class_id', $gradeClassId)
                                        ->pluck('name_ar', 'id');
                                })
                                ->reactive(),
                                
                            // التاريخ
                            Forms\Components\DatePicker::make('attendance_date')
                                ->label('تاريخ الحضور')
                                ->required()
                                ->default(today()),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([

                            Forms\Components\Select::make('status')
                                ->label('الحالة')
                                ->required()
                                ->options([
                                    'present'   => 'حاضر',
                                    'absent'    => 'غائب',
                                    'late'      => 'متأخر',
                                    'picked_up' => 'تم استلامه',
                                ])
                                ->default('present'),

                            Forms\Components\TimePicker::make('check_in_time')
                                ->label('وقت الدخول')
                                ->seconds(false)
                                ->nullable(),

                            Forms\Components\TimePicker::make('check_out_time')
                                ->label('وقت الخروج')
                                ->seconds(false)
                                ->nullable(),
                        ]),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->nullable(),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => auth()->id())
                    ->dehydrated(true)
                    ->required(),
                  
              Forms\Components\Hidden::make('recorded_by')
                ->default(fn () => auth()->id()) // id من جدول users
                ->dehydrated(true)
                ->nullable(),
                ]),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->sortable(),

                    
                    Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('user.name')
                        ->label('مشرف التحضير')
                        ->sortable(),
                Tables\Columns\TextColumn::make('gradeClass.name_ar')
                    ->label('الصف الدراسي')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name_ar')
                    ->label('الطالب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('تاريخ الحضور')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('الحالة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                    ]),
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
            // يمكنك إضافة علاقات هنا إذا كنت بحاجة إليها
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
        ];
    }
}