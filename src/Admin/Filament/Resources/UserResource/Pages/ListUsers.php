<?php

namespace Monet\Framework\Admin\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Monet\Framework\Admin\Filament\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
}
