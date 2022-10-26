<?php

namespace Monet\Framework\Admin\Filament\Resources;

use Closure;
use DateTimeInterface;
use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Monet\Framework\Admin\Filament\Resources\UserResource\Pages\CreateUser;
use Monet\Framework\Admin\Filament\Resources\UserResource\Pages\EditUser;
use Monet\Framework\Admin\Filament\Resources\UserResource\Pages\ListUsers;
use Monet\Framework\Auth\Models\User;
use Monet\Framework\Transformer\Facades\Transformer;
use Spatie\Permission\Models\Role;

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
                    Forms\Components\Card::make()
                        ->schema([
                            Forms\Components\TextInput::make(User::getUsernameName())
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->label('Email address')
                                ->required()
                                ->email()
                                ->unique(User::class, 'email', fn($record) => $record)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->dehydrateStateUsing(
                                    fn(?string $state, Closure $get): string => Hash::make($state, ['user_id' => $get('user_id')])
                                )
                                ->dehydrated(fn($state) => filled($state))
                                ->required(fn(Page $livewire): bool => $livewire instanceof CreateRecord),
                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Confirm password')
                                ->password(),
                            Forms\Components\Select::make('roles')
                                ->label('Roles')
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->saveRelationshipsUsing(function (User $record, $state) {
                                    $record->syncRoles($state);

                                    if (
                                        $record->getAuthIdentifier() === auth()->id() &&
                                        !$record->hasPermissionTo('view admin')
                                    ) {
                                        $record->assignRole(Role::findById(2));
                                    }

                                    if (empty($record->roles)) {
                                        $record->assignRole(Role::findById(1));
                                    }
                                })
                                ->hiddenOn('create'),
                        ])
                        ->columns([
                            'sm' => 2,
                        ])
                        ->columnSpan([
                            'sm' => 2,
                        ]),

                    Forms\Components\Card::make()
                        ->schema([
                            Forms\Components\Toggle::make('email_verified_at')
                                ->label('Verified')
                                ->afterStateHydrated(
                                    function (Forms\Components\Toggle $component, $state): void {
                                        $component->state($state !== null);
                                    }
                                )
                                ->dehydrated(function (bool $state, ?User $record): bool {
                                    if ($record === null) {
                                        return true;
                                    }

                                    $verified = $record->hasVerifiedEmail();

                                    return $state ? !$verified : $verified;
                                })
                                ->dehydrateStateUsing(fn(bool $state): ?DateTimeInterface => $state ? now() : null),
                            Forms\Components\Placeholder::make('created_at')
                                ->label('Created at')
                                ->content(fn(?User $record): string => $record?->created_at?->diffForHumans() ?? '-'),
                            Forms\Components\Placeholder::make('update_time')
                                ->label('Modified at')
                                ->content(fn(?User $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                        ])
                        ->columnSpan(1),
                ])
                ->columns([
                    'sm' => 3,
                    'lg' => null,
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
                    Tables\Columns\BadgeColumn::make('email_verified_at')
                        ->label('Verified')
                        ->sortable()
                        ->hidden(!config('monet.auth.require_email_verification'))
                        ->getStateUsing(fn(User $record): bool => $record->hasVerifiedEmail())
                        ->enum([
                            true => 'Verified',
                            false => 'Unverified'
                        ])
                        ->colors([
                            'success' => true,
                            'danger' => false
                        ])
                        ->icons([
                            'heroicon-o-minus-sm',
                            'heroicon-o-x' => false,
                            'heroicon-o-check' => true,
                        ]),
                    Tables\Columns\TextColumn::make('created_at')
                        ->label('Created at')
                        ->date()
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->label('Updated at')
                        ->date()
                        ->sortable()
                        ->searchable()
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
                'Name' => $record->getUsername(),
                'Email address' => $record->email
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
