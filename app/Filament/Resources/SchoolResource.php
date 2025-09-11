<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Filament\Resources\SchoolResource\RelationManagers;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'المدارس';
    
    protected static ?string $modelLabel = 'مدرسة';
    
    protected static ?string $pluralModelLabel = 'المدارس';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'إدارة المدارس';
    
    // إظهار الصفحة للمدير الأساسي فقط
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
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
    
    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }

   public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم المدرسة (عربي)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('name_en')
                            ->label('School Name (English)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('code')
                            ->label('كود المدرسة')
                            ->maxLength(20)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $lastSchool = School::orderByDesc('id')->first();

                                if ($lastSchool && preg_match('/school-(\d+)/', $lastSchool->code, $matches)) {
                                    $nextNumber = intval($matches[1]) + 1;
                                } else {
                                    $nextNumber = 1;
                                }

                                return 'school-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                            }),

                        Forms\Components\FileUpload::make('logo')
                            ->label('شعار المدرسة')
                            ->image()
                            ->directory('school-logos'),
                    ]),

                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Textarea::make('address_ar')
                            ->label('العنوان (عربي)')
                            ->rows(3),

                        Forms\Components\Textarea::make('address_en')
                            ->label('Address (English)')
                            ->rows(3),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('الهاتف')
                            ->tel(),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email(),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),

                // إعدادات الفروع
                Forms\Components\Section::make('إعدادات الفروع')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('max_branches')
                                    ->label('الحد الأقصى للفروع')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(50)
                                    ->required()
                                    ->helperText('العدد الأقصى للفروع المسموح بها لهذه المدرسة'),

                                Forms\Components\TextInput::make('current_branches_count')
                                    ->label('عدد الفروع الحالي')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('يتم تحديث هذا الرقم تلقائياً'),

                                Forms\Components\Toggle::make('allow_unlimited_branches')
                                    ->label('فروع غير محدودة')
                                    ->default(false)
                                    ->helperText('السماح بإنشاء عدد غير محدود من الفروع')
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $set('max_branches', null);
                                        } else {
                                            $set('max_branches', 1);
                                        }
                                    }),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('branch_settings.allow_branch_management')
                                    ->label('السماح بإدارة الفروع')
                                    ->default(true)
                                    ->helperText('السماح لمديري المدارس بإدارة الفروع'),

                                Forms\Components\Toggle::make('branch_settings.require_approval_for_new_branches')
                                    ->label('مطالبة بموافقة لفروع جديدة')
                                    ->default(false)
                                    ->helperText('يتطلب موافقة الإدارة لإنشاء فروع جديدة'),

                                Forms\Components\TextInput::make('branch_settings.max_students_per_branch')
                                    ->label('الحد الأقصى للطلاب بكل فرع')
                                    ->numeric()
                                    ->default(500)
                                    ->minValue(50)
                                    ->maxValue(2000)
                                    ->helperText('العدد الأقصى للطلاب المسموح بهم في كل فرع'),

                                Forms\Components\Toggle::make('branch_settings.allow_branch_deletion')
                                    ->label('السماح بحذف الفروع')
                                    ->default(false)
                                    ->helperText('السماح لمديري المدارس بحذف الفروع'),
                            ]),
                    ])->collapsible(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('الشعار')
                    ->circular(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم المدرسة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف'),
                Tables\Columns\TextColumn::make('branches_info')
                    ->label('الفروع')
                    ->state(function (School $record): string {
                        $current = $record->current_branches_count;
                        $max = $record->allow_unlimited_branches ? '∞' : $record->max_branches;
                        return "{$current} / {$max}";
                    })
                    ->badge()
                    ->color(function (School $record): string {
                        if ($record->allow_unlimited_branches) {
                            return 'success';
                        }
                        
                        $percentage = ($record->current_branches_count / $record->max_branches) * 100;
                        
                        if ($percentage >= 90) {
                            return 'danger';
                        } elseif ($percentage >= 70) {
                            return 'warning';
                        }
                        
                        return 'success';
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
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
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
            'view' => Pages\ViewSchool::route('/{record}'), // هذا السطر مهم
        ];
    }
}
