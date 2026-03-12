<?php declare(strict_types=1);

namespace Shopwell\Core\Content;

use Shopwell\Core\Content\Mail\MailerConfigurationCompilerPass;
use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class Content extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('breadcrumb.php');
        $loader->load('category.php');
        $loader->load('cookie.php');
        $loader->load('media.php');
        $loader->load('media_path.php');
        $loader->load('product.php');
        $loader->load('rule.php');
        $loader->load('product_stream.php');
        $loader->load('product_export.php');
        $loader->load('property.php');
        $loader->load('mail_template.php');
        $loader->load('delivery_time.php');
        $loader->load('import_export.php');
        $loader->load('revocation_request_form.php');
        $loader->load('sitemap.php');
        $loader->load('landing_page.php');
        $loader->load('flow.php');
        $loader->load('measurement_system.php');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('media_test.php');
        }

        $container->addCompilerPass(new MailerConfigurationCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
