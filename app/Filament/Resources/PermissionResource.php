<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
// use Spatie\Permission\Models\Permission;
use App\Models\Permission;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationLabel = 'الصلاحيات';
    
    protected static ?string $modelLabel = 'صلاحية';
    
    protected static ?string $pluralModelLabel = 'الصلاحيات';
    
    protected static ?string $navigationGroup = 'إدارة المستخدمين';
    
    protected static ?int $navigationSort = 4;

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
                Forms\Components\Section::make('معلومات الصلاحية')
                    ->schema([
                    Forms\Components\Select::make('school_id')
                        ->label('المدرسة')
                        ->options(\App\Models\School::pluck('name_ar', 'id'))
                        ->default(auth()->user()?->school_id)
                        ->disabled(fn () => auth()->user()?->school_id !== null)
                        ->hidden(fn () => auth()->user()?->school_id !== null)
                        ->required(),
                        Forms\Components\TextInput::make('name')
                        ->label('اسم الصلاحية')
                        ->required()
                        ->maxLength(255)
                        ->helperText('مثال: view_students, edit_schools, manage_buses')
                        ->rules([
                            fn (callable $get) => Rule::unique('permissions', 'name')
                                ->where(fn ($query) => $query->where('school_id', auth()->user()?->school_id))
                                ->ignore($get('id')),
                        ]),
                            
                        Forms\Components\TextInput::make('guard_name')
                            ->label('نوع الحارس')
                            ->default('web')
                            ->required()
                            ->disabled(),
                    ]),
                    
                Forms\Components\Section::make('الأدوار المرتبطة')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->label('الأدوار المتاحة')
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->descriptions(Role::all()->mapWithKeys(function ($role) {
                                return [$role->id => 'دور ' . $role->name];
                            }))
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الصلاحية')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('roles_count')
                    ->label('عدد الأدوار')
                    ->counts('roles')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('users_count')
                    ->label('عدد المستخدمين')
                    ->getStateUsing(function ($record) {
                        return $record->roles()->withCount('users')->get()->sum('users_count');
                    })
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('نوع الحارس')
                    ->badge()
                    ->color('warning'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('نوع الحارس')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
                    
                Tables\Filters\SelectFilter::make('roles')
                    ->label('الأدوار')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->before(function (Permission $record) {
                        // حذف العلاقات مع الأدوار والمستخدمين قبل حذف الصلاحية
                        $record->roles()->detach();
                        $record->users()->detach();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $record->roles()->detach();
                                $record->users()->detach();
                            }
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
