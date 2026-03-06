<?php declare(strict_types=1);

namespace Shopwell\Core\System\Snippet\Struct;

use GuzzleHttp\Psr7\Uri;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\System\Snippet\DataTransfer\Language\LanguageCollection;
use Shopwell\Core\System\Snippet\DataTransfer\PluginMapping\PluginMappingCollection;

#[Package('discovery')]
class TranslationConfig extends Struct
{
    /**
     * @param list<string> $locales
     * @param list<string> $plugins
     * @param list<string> $excludedLocales
     *
     * @internal
     */
    public function __construct(
        public readonly Uri $repositoryUrl,
        public readonly array $locales,
        public readonly array $plugins,
        public readonly LanguageCollection $languages,
        public readonly PluginMappingCollection $pluginMapping,
        public readonly Uri $metadataUrl,
        public readonly array $excludedLocales,
    ) {
    }

    public function getMappedPluginName(Plugin $plugin): string
    {
        $pluginName = $plugin->getName();

        return $this->pluginMapping->get($pluginName)->snippetName ?? $pluginName;
    }
}
