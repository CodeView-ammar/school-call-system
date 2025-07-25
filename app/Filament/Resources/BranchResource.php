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
    
    protected static ?int $navigationSort = 3;

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
     auth()->user()?->school_id === null
                ? Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $school = \App\Models\School::find($value);

                                if (!$school) {
                                    $fail('المدرسة غير موجودة.'); // إزالة errorBag هنا
                                    return;
                                }

                                if (!$school->canAddMoreBranches()) {
                                    $remaining = $school->remaining_branches;
                                    if ($remaining === 0) {
                                        $fail("لقد وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).");
                                    }
                                }
                            };
                        },
                    ])
                    ->helperText(function (callable $get) {
                        $schoolId = $get('school_id') ?: auth()->user()?->school_id;
                        if (!$schoolId) return null;

                        $school = \App\Models\School::find($schoolId);
                        if (!$school) return null;

                        if ($school->allow_unlimited_branches) {
                            return 'هذه المدرسة لديها عدد غير محدود من الفروع.';
                        }

                        $remaining = $school->remaining_branches;
                        return "الفروع المتبقية: {$remaining} من أصل {$school->max_branches}";
                    })
                : Forms\Components\Hidden::make('school_id')
                    ->default(auth()->user()->school_id)
                    ->dehydrated(true)
                    ->required()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (empty($value)) {
                                    $fail('يجب تعيين school_id.'); // إزالة errorBag هنا
                                    return;
                                }
                                
                                $school = \App\Models\School::find($value);
                                if (!$school) {
                                    $fail('المدرسة غير موجودة.'); // إزالة errorBag هنا
                                    return;
                                }
                                
                        $remaining = $school->max_branches - $school->branches()->count();

                        // تحقق مما إذا كانت المدرسة يمكنها إضافة المزيد من الفروع
                        if ($remaining <= 0) {
                        Notification::make()
                            ->title("لقد وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).")
                            ->danger()
                            ->send();
                        $fail("لقد وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).");
                        return;
                        }
                    };
                },
                    ]),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('name_ar')
                        ->label('اسم الفرع (عربي)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_en')
                        ->label('Branch Name (English)')
                        ->maxLength(255),
                ]),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('كود الفرع')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10),
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
                                ->hidden()
                                ->dehydrated(),
                            Forms\Components\TextInput::make('longitude')
                                ->label('خط الطول (Longitude)')
                                ->numeric()
                                ->step(0.000001)
                                ->hidden()
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
