<?php

namespace Monet\Framework\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Monet\Framework\Admin\Filament\Resources\ModuleResource\Pages\ListModules;
use Monet\Framework\Module\Model\Module;
use Monet\Framework\Transformer\Facades\Transformer;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $slug = 'extend/modules';

    protected static ?string $navigationGroup = 'Extend';

    protected static ?string $navigationIcon = 'heroicon-o-puzzle';

    protected static ?int $navigationSort = -9999;

    public static function table(Table $table): Table
    {
        return Transformer::transform(
            'monet.admin.modules.table',
            $table
                ->columns(
                    Transformer::transform(
                        'monet.admin.modules.table.columns',
                        [
                            Tables\Columns\TextColumn::make('name')
                                ->label('Name')
                                ->sortable()
                                ->searchable(),
                            Tables\Columns\TextColumn::make('description')
                                ->label('Description')
                                ->sortable()
                                ->searchable()
                                ->wrap(),
                            Tables\Columns\BadgeColumn::make('version')
                                ->label('Version')
                                ->sortable()
                                ->searchable(),
                            Tables\Columns\BadgeColumn::make('enabled')
                                ->label('Status')
                                ->sortable()
                                ->enum([
                                    true => 'Enabled',
                                    false => 'Disabled'
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
                        ]
                    )
                )
                ->filters(
                    Transformer::transform(
                        'monet.admin.modules.table.filters',
                        [
                            Tables\Filters\TernaryFilter::make('enabled')
                                ->label('Enabled'),
                        ]
                    )
                )
                ->actions(
                    Transformer::transform(
                        'monet.admin.modules.list.table.actions',
                        [
                            Tables\Actions\ActionGroup::make([
                                Tables\Actions\Action::make('enable')
                                    ->label('Enable')
                                    ->hidden(fn(Module $record): bool => $record->enabled)
                                    ->icon('heroicon-o-check')
                                    ->requiresConfirmation()
                                    ->action('enableModule'),
                                Tables\Actions\Action::make('disable')
                                    ->label('Disable')
                                    ->hidden(fn(Module $record): bool => $record->disabled)
                                    ->icon('heroicon-o-x')
                                    ->requiresConfirmation()
                                    ->action('disableModule'),
                                Tables\Actions\Action::make('publish')
                                    ->label('Publish assets')
                                    ->icon('heroicon-o-document-duplicate')
                                    ->action('publishModule')
                                    ->form([
                                        Forms\Components\Checkbox::make('run_migrations')
                                            ->label('Run database migrations')
                                            ->helperText('This will ensure the database is up-to date')
                                    ]),
                                Tables\Actions\Action::make('delete')
                                    ->label('Delete')
                                    ->color('danger')
                                    ->icon('heroicon-o-trash')
                                    ->requiresConfirmation()
                                    ->action('deleteModule')
                            ])->label('Manage'),
                        ])
                )
                ->bulkActions(
                    Transformer::transform(
                        'monet.admin.modules.table.bulkActions',
                        [
                            Tables\Actions\BulkAction::make('delete')
                                ->label('Delete selected')
                                ->color('danger')
                                ->icon('heroicon-o-trash')
                                ->requiresConfirmation()
                                ->action('deleteBulk'),
                        ]
                    )
                )
        );
    }

    public static function getPages(): array
    {
        return Transformer::transform(
            'monet.admin.modules.pages',
            [
                'index' => ListModules::route('/')
            ]
        );
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return Transformer::transform(
            'monet.admin.modules.search.title',
            $record->name,
            [
                'module' => $record
            ]
        );
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return Transformer::transform(
            'monet.admin.modules.search.details',
            [
                'Description' => $record->description,
                'Version' => $record->version,
                'Status' => $record->enabled ? 'Enabled' : 'Disabled'
            ],
            [
                'module' => $record
            ]
        );
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl();
    }

    protected static function getNavigationBadge(): ?string
    {
        return __(number_format(Module::query()->count()) . ' Installed');
    }
}
