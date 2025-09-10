<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class BranchResource extends Resource
{   
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'الفروع';
    
    protected static ?string $modelLabel = 'فرع';
    
    protected static ?string $pluralModelLabel = 'الفروع';
    
    protected static ?string $navigationGroup = 'إدارة المدارس';
    
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        return $query;
    }

    /**
     * توليد كود تلقائي للفرع
     */
    private static function generateBranchCode(?int $schoolId): string
    {
        if (!$schoolId) {
            return '';
        }

        $school = \App\Models\School::find($schoolId);
        if (!$school) {
            return '';
        }

        // الحصول على أول 3 أحرف من اسم المدرسة أو استخدام SCH كافتراضي
        $schoolPrefix = 'SCH';
        if ($school->name_ar) {
            // إزالة المسافات واستخدام أول 3 أحرف
            $schoolName = str_replace(' ', '', $school->name_ar);
            $schoolPrefix = mb_strtoupper(mb_substr($schoolName, 0, 3, 'UTF-8'), 'UTF-8');
        } elseif ($school->name_en) {
            $schoolPrefix = strtoupper(substr(str_replace(' ', '', $school->name_en), 0, 3));
        }

        // الحصول على رقم تسلسلي للفرع
        $branchCount = \App\Models\Branch::where('school_id', $schoolId)->count();
        $sequenceNumber = str_pad($branchCount + 1, 3, '0', STR_PAD_LEFT);

        // توليد كود فريد
        $baseCode = $schoolPrefix . '-BR' . $sequenceNumber;
        $code = $baseCode;
        $counter = 1;

        // التحقق من الفريدية
        while (\App\Models\Branch::where('school_id', $schoolId)->where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * توليد كود بديل (طريقة ثانية)
     */
    private static function generateAlternativeBranchCode(?int $schoolId): string
    {
        if (!$schoolId) {
            return '';
        }

        // استخدام رقم المدرسة والسنة والرقم التسلسلي
        $year = date('y'); // آخر رقمين من السنة
        $schoolCode = str_pad($schoolId, 3, '0', STR_PAD_LEFT);
        
        // الحصول على آخر رقم تسلسلي
        $lastBranch = \App\Models\Branch::where('school_id', $schoolId)
            ->where('code', 'like', "B{$year}{$schoolCode}%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastBranch) {
            // استخراج الرقم التسلسلي من آخر كود
            $lastSequence = intval(substr($lastBranch->code, -3));
            $sequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        return "B{$year}{$schoolCode}{$sequence}";
    }

    private static function validateBranchLimit(?int $schoolId, \Closure $fail): void
    {
        
        if (!$schoolId) {
            $fail('يجب اختيار المدرسة.');
             Notification::make()
            ->title('خطأ')
            ->body('يجب اختيار المدرسة.')
            ->danger()
            ->send();
            return;
        }

        $school = \App\Models\School::find($schoolId);

        if (!$school) {
            $fail('المدرسة غير موجودة.');
            Notification::make()
            ->title('خطأ')
            ->body('المدرسة غير موجودة.')
            ->danger()
            ->send();
            return;
        }

        if (!$school->canAddMoreBranches()) {
            $fail("وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).");
             Notification::make()
            ->title('خطأ')
            ->body("وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).")
            ->danger()
            ->send();
        }
        
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    auth()->user()->is_super_admin
                        ? Forms\Components\Select::make('school_id')
                            ->label('المدرسة')
                            ->relationship('school', 'name_ar')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive() // جعل الحقل تفاعلي لتحديث الكود
                            ->afterStateUpdated(function (callable $set, $state) {
                                // توليد كود تلقائي عند اختيار المدرسة
                                if ($state) {
                                    $code = self::generateBranchCode($state);
                                    $set('code', $code);
                                }
                            })
                           ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (request()->routeIs('filament.resources.branches.create')) {
                                            \App\Filament\Resources\BranchResource::validateBranchLimit($value, $fail);
                                        }
                                    };
                                },
                            ])
                            ->helperText(function (callable $get) {
                                $schoolId = $get('school_id');
                                if (!$schoolId) return null;

                                $school = \App\Models\School::find($schoolId);
                                if (!$school) return null;

                                return $school->allow_unlimited_branches
                                    ? 'هذه المدرسة لديها عدد غير محدود من الفروع.'
                                    : "الفروع المتبقية: {$school->remaining_branches} من أصل {$school->max_branches}";
                            })
                        : Forms\Components\Hidden::make('school_id')
                            ->default(auth()->user()?->school_id)
                            ->dehydrated(true)
                            ->required()
                            ->afterStateHydrated(function (callable $set, $state) {
                                // توليد كود تلقائي للمدارس غير الـ super admin
                                if ($state && !request()->route('record')) {
                                    $code = self::generateBranchCode($state);
                                    $set('code', $code);
                                }
                            })
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (request()->routeIs('filament.resources.branches.create')) {
                                        \App\Filament\Resources\BranchResource::validateBranchLimit($value, $fail);
                                        }
                                    };
                                },
                            ])
                 ])->columnSpanFull(),
            
            
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم الفرع (عربي)')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                // تحديث الكود عند تغيير اسم الفرع (اختياري)
                                $schoolId = $get('school_id');
                                if ($schoolId && $state && !$get('code_manually_edited')) {
                                    // يمكنك تفعيل هذا إذا أردت تحديث الكود عند تغيير الاسم
                                    // $code = self::generateBranchCode($schoolId);
                                    // $set('code', $code);
                                }
                            })
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $schoolId = request()->input('school_id') ?? auth()->user()->school_id;
                                        $exists = \App\Models\Branch::where('school_id', $schoolId)
                                            ->where('name_ar', $value)
                                            ->when(request()->route('record'), function ($query, $recordId) {
                                                $query->where('id', '!=', $recordId);
                                            })
                                            ->exists();

                                        if ($exists) {
                                            $fail('اسم الفرع موجود مسبقًا لهذه المدرسة.');
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\TextInput::make('name_en')
                            ->label('Branch Name (English)')
                            ->maxLength(255),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('كود الفرع')
                            ->required()
                            ->maxLength(20) // زيادة الحد الأقصى لاستيعاب الأكواد الطويلة
                            ->default(function (callable $get) {
                                // توليد كود افتراضي عند فتح النموذج
                                $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                                return $schoolId ? self::generateBranchCode($schoolId) : '';
                            })
                            ->helperText('الكود يتم توليده تلقائياً، يمكنك تعديله إذا رغبت')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('regenerateCode')
                                    ->label('توليد كود جديد')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (callable $get, callable $set) {
                                        $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                                        if ($schoolId) {
                                            // يمكنك التبديل بين الطريقتين
                                            $useAlternative = $get('use_alternative_code') ?? false;
                                            $code = $useAlternative 
                                                ? self::generateAlternativeBranchCode($schoolId)
                                                : self::generateBranchCode($schoolId);
                                            $set('code', $code);
                                            $set('code_manually_edited', false);
                                            
                                            Notification::make()
                                                ->title('تم توليد كود جديد')
                                                ->body("الكود الجديد: {$code}")
                                                ->success()
                                                ->send();
                                        }
                                    })
                            )
                            ->afterStateUpdated(function (callable $set) {
                                // تحديد أن المستخدم قام بتعديل الكود يدوياً
                                $set('code_manually_edited', true);
                            })
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $schoolId = request()->input('school_id') ?? auth()->user()->school_id;
                                        $exists = \App\Models\Branch::where('school_id', $schoolId)
                                            ->where('code', $value)
                                            ->when(request()->route('record'), function ($query, $recordId) {
                                                $query->where('id', '!=', $recordId);
                                            })
                                            ->exists();

                                        if ($exists) {
                                            $fail('كود الفرع موجود مسبقًا لهذه المدرسة.');
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\FileUpload::make('logo')
                            ->label('شعار الفرع')
                            ->image()
                            ->directory('branch-logos'),
                    ]),
                
                // حقل مخفي لتتبع التعديل اليدوي للكود
                Forms\Components\Hidden::make('code_manually_edited')
                    ->default(false)
                    ->dehydrated(false),
                
                // خيار لاستخدام طريقة توليد بديلة (اختياري)
                Forms\Components\Toggle::make('use_alternative_code')
                    ->label('استخدام طريقة الترميز البديلة')
                    ->helperText('تفعيل هذا الخيار سيستخدم نمط: B + السنة + رقم المدرسة + رقم تسلسلي')
                    ->reactive()
                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                        $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                        if ($schoolId) {
                            $code = $state 
                                ? self::generateAlternativeBranchCode($schoolId)
                                : self::generateBranchCode($schoolId);
                            $set('code', $code);
                            $set('code_manually_edited', false);
                        }
                    })
                    ->dehydrated(false)
                    ->visible(false), // يمكنك جعله visible(true) إذا أردت إظهار الخيار للمستخدم
                
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Textarea::make('address_ar')
                            ->label('العنوان (عربي)')
                            ->rows(3),
                        Forms\Components\Textarea::make('address_en')
                            ->label('Address (English)')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('الموقع والخريطة')
                    ->description('حدد موقع الفرع على الخريطة')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.simple-map'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('خط العرض (Latitude)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('خط الطول (Longitude)')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->dehydrated(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('الشعار')
                    ->circular(),
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),
            
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم الفرع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('تم نسخ الكود')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color('primary'),
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
                Tables\Filters\SelectFilter::make('school')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->visible(fn () => auth()->user()?->school_id === null),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
            'view' => Pages\ViewBranch::route('/{record}'),
        ];
    }

    /**
     * إضافة هوك لتوليد الكود قبل الحفظ (كطريقة بديلة)
     */
    public static function beforeCreate(array $data): array
    {
        // إذا لم يكن هناك كود، قم بتوليده
        if (empty($data['code'])) {
            $data['code'] = self::generateBranchCode($data['school_id'] ?? auth()->user()?->school_id);
        }
        
        return $data;
    }
}