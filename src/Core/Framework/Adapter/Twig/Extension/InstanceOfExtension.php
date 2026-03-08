<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Twig\Extension;

use Shopwell\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

/**
 * @internal
 */
#[Package('framework')]
class InstanceOfExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            'instanceof' => new TwigTest('instanceof', $this->isInstanceOf(...)),
        ];
    }

    public function isInstanceOf(object $var, string $class): bool
    {
        return new \ReflectionClass($class)->isInstance($var);
    }
}
