<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
#[IsFlowEventAware]
interface MailAware
{
    public const string MAIL_STRUCT = 'mailStruct';

    public const string SALES_CHANNEL_ID = 'salesChannelId';

    public const string TIMEZONE = 'timezone';

    public function getMailStruct(): MailRecipientStruct;

    public function getSalesChannelId(): ?string;
}
