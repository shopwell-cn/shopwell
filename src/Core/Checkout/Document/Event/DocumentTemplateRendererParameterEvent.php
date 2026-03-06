<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Event;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ExtendableTrait;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('after-sales')]
class DocumentTemplateRendererParameterEvent extends Event
{
    use ExtendableTrait;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(private readonly array $parameters)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
