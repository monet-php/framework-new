<?php

namespace Monet\Framework\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
            ])
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
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModules::route('/')
        ];
    }
}
