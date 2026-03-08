<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Category;

use Shopwell\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopwell\Core\Framework\Feature;
use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class CategoryException extends HttpException
{
    public const string SERVICE_CATEGORY_NOT_FOUND = 'CHECKOUT__SERVICE_CATEGORY_NOT_FOUND';
    public const string FOOTER_CATEGORY_NOT_FOUND = 'CHECKOUT__FOOTER_CATEGORY_NOT_FOUND';
    public const string AFTER_CATEGORY_NOT_FOUND = 'CONTENT__AFTER_CATEGORY_NOT_FOUND';
    public const string CMS_PAGE_NOT_FOUND = 'CONTENT__CMS_PAGE_NOT_FOUND';

    public static function pageNotFound(string $pageId): ShopwellHttpException
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::CMS_PAGE_NOT_FOUND,
            self::$couldNotFindMessage,
            ['entity' => 'page', 'field' => 'ID', 'value' => $pageId]
        );
    }

    public static function categoryNotFound(string $id): ShopwellHttpException
    {
        return new CategoryNotFoundException($id);
    }

    public static function serviceCategoryNotFoundForSalesChannel(string $salesChannelName): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::SERVICE_CATEGORY_NOT_FOUND,
            'Service category, for sales channel {{ salesChannelName }}, is not set',
            ['salesChannelName' => $salesChannelName]
        );
    }

    public static function footerCategoryNotFoundForSalesChannel(string $salesChannelName): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FOOTER_CATEGORY_NOT_FOUND,
            'Footer category, for sales channel {{ salesChannelName }}, is not set',
            ['salesChannelName' => $salesChannelName]
        );
    }

    public static function afterCategoryNotFound(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::AFTER_CATEGORY_NOT_FOUND,
            'Category to insert after not found.',
        );
    }
}
