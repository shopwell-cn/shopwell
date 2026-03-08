<?php declare(strict_types=1);

namespace Shopwell\Storefront\Framework\Captcha;

use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class HoneypotCaptcha extends AbstractCaptcha
{
    final public const string CAPTCHA_NAME = 'honeypot';
    final public const string CAPTCHA_REQUEST_PARAMETER = 'shopwell_surname_confirm';

    public function isValid(Request $request, array $captchaConfig): bool
    {
        return $request->request->get(self::CAPTCHA_REQUEST_PARAMETER, '') === '';
    }

    public function getName(): string
    {
        return self::CAPTCHA_NAME;
    }
}
