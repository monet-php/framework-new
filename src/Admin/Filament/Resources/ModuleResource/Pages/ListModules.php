<?php

namespace Monet\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Monet\Framework\Admin\Filament\Resources\ModuleResource;
use Monet\Framework\Module\Model\Module;
use Monet\Framework\Module\Repository\ModuleRepositoryInterface;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    public function installModules(
        ModuleRepositoryInterface $modules,
        ComponentContainer $form,
        array $data
    )
    {
        [$component] = $form->getComponents();

        $storage = $component->getDisk();

        $success = false;
        foreach ($data['modules'] as $path) {
            $file = $storage->path($path);

            if (!$module = $modules->install($file, $error)) {
                Notification::make()
                    ->danger()
                    ->title(__('monet::modules.installer.failed.title'))
                    ->body(__($error))
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title(__('monet::modules.installer.success.title'))
                    ->body($module->getName())
                    ->send();

                $success = true;
            }

            $storage->delete($path);
        }

        if ($success) {
            return redirect()->route('filament.resources.extend/modules.index');
        }

        return null;
    }

    public function enableModule(ModuleRepositoryInterface $modules, Module $record)
    {
        if ($error = $modules->enable($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet::modules.enable.failed.title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet::modules.enable.success.title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.extend/modules.index');
    }

    public function disableModule(ModuleRepositoryInterface $modules, Module $record)
    {
        if ($error = $modules->disable($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet::modules.disable.failed.title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet::modules.disable.success.title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.extend/modules.index');
    }

    public function deleteModule(ModuleRepositoryInterface $modules, Module $record)
    {
        if ($error = $modules->delete($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet::modules.delete.failed.title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet::modules.delete.success.title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.extend/modules.index');
    }

    public function publishModule(ModuleRepositoryInterface $modules, Module $record)
    {
        if ($error = $modules->publish($record->name)) {
            Notification::make()
                ->danger()
                ->title(__('monet::modules.publish.failed.title'))
                ->body(__($error))
                ->send();

            return null;
        }

        Notification::make()
            ->success()
            ->title(__('monet::modules.publish.success.title'))
            ->body($record->name)
            ->send();

        return redirect()->route('filament.resources.extend/modules.index');
    }

    protected function getActions(): array
    {
        return [
            Action::make('install')
                ->label('Install modules')
                ->action('installModules')
                ->form([
                    FileUpload::make('modules')
                        ->disableLabel()
                        ->multiple()
                        ->directory('modules-tmp')
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
