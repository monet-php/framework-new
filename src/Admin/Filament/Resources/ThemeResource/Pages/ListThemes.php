<?php

namespace Monet\Framework\Admin\Filament\Resources\ThemeResource\Pages;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Monet\Framework\Admin\Filament\Resources\ThemeResource;
use Monet\Framework\Theme\Models\Theme;
use Monet\Framework\Theme\Repository\ThemeRepositoryInterface;

class ListThemes extends ListRecords
{
    protected static string $resource = ThemeResource::class;

    public function installThemes(
        ThemeRepositoryInterface $themes,
        ComponentContainer       $form,
        array                    $data
    )
    {
        [$component] = $form->getComponents();

        $storage = $component->getDisk();

        $success = false;
        foreach ($data['themes'] as $path) {
            $file = $storage->path($path);

            if (!$theme = $themes->install($file, $error)) {
                Notification::make()
                    ->danger()
                    ->title(__('monet.themes.install_failed_title'))
                    ->body(__($error))
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title(__('monet.themes.install_success_title'))
                    ->body($theme->getName())
                    ->send();

                $success = true;
            }

            $storage->delete($path);
        }

        if ($success) {
            return redirect()->route('filament.resources.appearance/themes.index');
        }

        return null;
    }

    public function enableTheme(ThemeRepositoryInterface $themes, Theme $record)
    {
        if ($error = $themes->enable($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet.themes.enable_failed_title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet.themes.enable_success_title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.appearance/themes.index');
    }

    public function disableTheme(ThemeRepositoryInterface $themes, Theme $record)
    {
        if ($error = $themes->disable($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet.themes.disable_failed_title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet.themes.disable_success_title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.appearance/themes.index');
    }

    public function deleteTheme(ThemeRepositoryInterface $themes, Theme $record)
    {
        if ($error = $themes->delete($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet.themes.delete_failed_title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet.themes.delete_success_title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.appearance/themes.index');
    }

    public function publishTheme(ThemeRepositoryInterface $themes, Theme $record)
    {
        if ($error = $themes->publish($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet.themes.publish_failed_title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet.themes.publish_success_title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.appearance/themes.index');
    }

    protected function getActions(): array
    {
        return [
            Action::make('install')
                ->label('Install themes')
                ->action('installThemes')
                ->form([
                    FileUpload::make('themes')
                        ->disableLabel()
                        ->multiple()
                        ->directory('themes-tmp')
                        ->minFiles(1)
                        ->acceptedFileTypes([
                            'application/zip',
                            'application/x-zip-compressed',
                            'multipart/x-zip',
                        ])
                ])
        ];
    }
}
