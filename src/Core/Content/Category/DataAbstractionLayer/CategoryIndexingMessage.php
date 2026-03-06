<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Category\DataAbstractionLayer;

use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopwell\Core\Framework\Log\Package;

#[Package('discovery')]
class CategoryIndexingMessage extends EntityIndexingMessage
{
}
