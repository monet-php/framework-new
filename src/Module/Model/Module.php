<?php

namespace Monet\Framework\Module\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Monet\Framework\Module\Facades\Modules;
use Monet\Framework\Support\Macroable;
use Monet\Framework\Transformer\Facades\Transformer;
use Sushi\Sushi;

class Module extends Model
{
    use Macroable;
    use Sushi;

    public $incrementing = false;

    public function getKeyType(): string
    {
        return Transformer::transform(
            'monet.modules.module.model.keyType',
            'string'
        );
    }

    public function getSchema(): array
    {
        return Transformer::transform(
            'monet.modules.module.model.schema',
            [
                'id' => 'string',
                'name' => 'string',
                'description' => 'string',
                'version' => 'string',
                'path' => 'string',
                'enabled' => 'boolean',
            ]
        );
    }

    public function getRows(): array
    {
        return Transformer::transform(
            'monet.modules.module.model.rows',
            collect(Modules::all())
                ->map(fn($module): array => [
                    'id' => $module->getName(),
                    'name' => $module->getName(),
                    'description' => $module->getDescription(),
                    'version' => $module->getVersion(),
                    'path' => $module->getPath(),
                    'enabled' => $module->enabled()
                ])
                ->values()
                ->all()
        );
    }

    public function disabled(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => !$this->enabled
        );
    }
}
