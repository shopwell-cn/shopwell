<?php declare(strict_types=1);

namespace Shopwell\Core\System\Snippet\Service;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin;

#[Package('discovery')]
abstract class AbstractTranslationLoader
{
    public const TRANSLATION_DIR = '/translation';
    public const TRANSLATION_LOCALE_SUB_DIR = 'locale';

    abstract public function getDecorated(): AbstractTranslationLoader;

    abstract public function load(string $locale, Context $context, bool $activate = true): void;

    abstract public function pluginTranslationExists(Plugin $plugin): bool;

    abstract public function getLocalesBasePath(): string;

    abstract public function getLocalePath(string $locale): string;
}
