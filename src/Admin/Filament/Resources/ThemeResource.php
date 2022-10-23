<?php

namespace Monet\Framework\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Monet\Framework\Admin\Filament\Resources\ThemeResource\Pages\ListThemes;
use Monet\Framework\Theme\Models\Theme;
use Monet\Framework\Transformer\Facades\Transformer;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static ?string $slug = 'appearance/themes';

    protected static ?string $navigationGroup = 'Appearance';

    protected static ?string $navigationIcon = 'heroicon-o-color-swatch';

    protected static ?int $navigationSort = -9999;

    public static function table(Table $table): Table
    {
        return Transformer::transform(
            'monet.admin.themes.table',
            $table
                ->columns(
                    Transformer::transform(
                        'monet.admin.themes.table.columns',
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
                        'monet.admin.themes.table.filters',
                        [
                            Tables\Filters\TernaryFilter::make('enabled')
                                ->label('Enabled'),
                        ]
                    )
                )
                ->actions(
                    Transformer::transform(
                        'monet.admin.themes.list.table.actions',
                        [
                            Tables\Actions\ActionGroup::make([
                                Tables\Actions\Action::make('enable')
                                    ->label('Enable')
                                    ->hidden(fn(Theme $record): bool => $record->enabled)
                                    ->icon('heroicon-o-check')
                                    ->requiresConfirmation()
                                    ->action('enableTheme'),
                                Tables\Actions\Action::make('disable')
                                    ->label('Disable')
                                    ->hidden(fn(Theme $record): bool => $record->disabled)
                                    ->icon('heroicon-o-x')
                                    ->requiresConfirmation()
                                    ->action('disableTheme'),
                                Tables\Actions\Action::make('publish')
                                    ->label('Publish assets')
                                    ->icon('heroicon-o-document-duplicate')
                                    ->action('publishTheme')
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
                                    ->action('deleteTheme')
                            ])->label('Manage'),
                        ])
                )
                ->bulkActions(
                    Transformer::transform(
                        'monet.admin.themes.table.bulkActions',
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
            'monet.admin.themes.pages',
            [
                'index' => ListThemes::route('/'),
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
            'monet.admin.themes.search.title',
            $record->name,
            [
                'theme' => $record
            ]
        );
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return Transformer::transform(
            'monet.admin.themes.search.details',
            [
                'description' => $record->description,
                'version' => $record->version
            ],
            [
                'theme' => $record
            ]
        );
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('index');
    }

    protected static function getNavigationBadge(): ?string
    {
        return __(number_format(Theme::query()->count()) . ' Installed');
    }
}
