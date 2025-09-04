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

    private static function validateBranchLimit(?int $schoolId, \Closure $fail): void
    {
        
        if (!$schoolId) {
            $fail('يجب اختيار المدرسة.');
             Notification::make()
            ->title('خطأ')
            ->body('يجب اختيار المدرسة.')
            ->danger() // تجعل الرسالة باللون الأحمر وتُعتبر رسالة خطأ
            ->send();
            return;
        }

        $school = \App\Models\School::find($schoolId);

        if (!$school) {
            $fail('المدرسة غير موجودة.');
            Notification::make()
            ->title('خطأ')
            ->body('المدرسة غير موجودة.')
            ->danger() // تجعل الرسالة باللون الأحمر وتُعتبر رسالة خطأ
            ->send();
            return;
        }

        if (!$school->canAddMoreBranches()) {
            $fail("وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).");
             Notification::make()
            ->title('خطأ')
            ->body("وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).")
            ->danger() // تجعل الرسالة باللون الأحمر وتُعتبر رسالة خطأ
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
                            ->maxLength(10)
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
                                    // ->hidden()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('خط الطول (Longitude)')
                                    ->numeric()
                                    ->step(0.000001)
                                    // ->hidden()
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
                    ->sortable(),
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
                    ->relationship('school', 'name_ar'),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
