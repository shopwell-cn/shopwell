<?php declare(strict_types=1);

namespace Shopwell\Storefront\Framework\Captcha\BasicCaptcha;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractBasicCaptchaGenerator
{
    abstract public function generate(): BasicCaptchaImage;
}
