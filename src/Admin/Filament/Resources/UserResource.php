<?php

namespace Monet\Framework\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Monet\Framework\Admin\Filament\Resources\UserResource\Pages\CreateUser;
use Monet\Framework\Admin\Filament\Resources\UserResource\Pages\EditUser;
use Monet\Framework\Admin\Filament\Resources\UserResource\Pages\ListUsers;
use Monet\Framework\Auth\Models\User;
use Monet\Framework\Transformer\Facades\Transformer;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return Transformer::transform(
            'monet.admin.users.table',
            $form
                ->schema([
                    Forms\Components\TextInput::make('name')
                ])
        );
    }

    public static function table(Table $table): Table
    {
        return Transformer::transform(
            'monet.admin.users.table',
            $table
                ->columns([
                    Tables\Columns\TextColumn::make(User::getUsernameName())
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('email')
                        ->label('Email address')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\BadgeColumn::make('hasVerifiedEmail')
                        ->label('Status')
                        ->sortable()
                        ->enum([
                            true => 'Verified',
                            false => 'Unverified'
                        ])
                        ->colors([
                            'success' => true,
                            'danger' => false
                        ])
                ])
        );
    }

    public static function getPages(): array
    {
        return Transformer::transform(
            'monet.admin.users.pages',
            [
                'index' => ListUsers::route('/'),
                'create' => CreateUser::route('/create'),
                'edit' => EditUser::route('/{record}/edit'),
            ]
        );
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [User::getUsernameName(), 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return Transformer::transform(
            'monet.admin.users.search.title',
            $record->getUsername(),
            [
                'user' => $record
            ]
        );
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return Transformer::transform(
            'monet.admin.users.search.details',
            [
                'email' => $record->email
            ],
            [
                'user' => $record
            ]
        );
    }

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Your account')
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-user')
                ->sort(static::getNavigationSort() + 1)
                ->url(route('filament.resources.users.edit', [auth()->id()])),
        ];
    }
}
