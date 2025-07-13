<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Validation\Rule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'المستخدمين';
    
    protected static ?string $modelLabel = 'مستخدم';
    
    protected static ?string $pluralModelLabel = 'المستخدمين';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'إدارة النظام';
    
    // إظهار الصفحة للمدير الأساسي فقط
    // public static function canViewAny(): bool
    // {
    //     return auth()->user()?->hasRole('super_admin') ?? false;
    // }
    
    // public static function canCreate(): bool
    // {
    //     return auth()->user()?->hasRole('super_admin') ?? false;
    // }
    
    // public static function canEdit($record): bool
    // {
    //     return auth()->user()?->hasRole('super_admin') ?? false;
    // }
    
    // public static function canDelete($record): bool
    // {
    //     return auth()->user()?->hasRole('super_admin') ?? false;
    // }
    
    // public static function canDeleteAny(): bool
    // {
    //     return auth()->user()?->hasRole('super_admin') ?? false;
    // }
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
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(255),
                       Select::make('user_type')
                            ->label('نوع المستخدم')
                            ->required()
                            ->options([
                                'school_admin'  => 'مدير المدرسة',
                                'teacher'       => 'معلم',
                                'guardian'         => 'ولي امر',
                                'driver'        => 'سائق',
                                'supervisor'    => 'مشرف',
                            ])
                            ->native(false), 
                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state)),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('المدرسة والأدوار')
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

                      Forms\Components\Select::make('roles')
                        ->label('الأدوار')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('الأدوار')
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'مدير أساسي',
                        'school_admin' => 'مدير مدرسة',
                        'teacher' => 'معلم',
                        'supervisor' => 'مشرف',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
