<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Controller\Adminhtml\Image;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryUi\Model\GetImageDetailsByAssetId;
use Psr\Log\LoggerInterface;

/**
 * Controller getting the media gallery image details
 */
class Details extends Action implements HttpGetActionInterface
{
    private const HTTP_OK = 200;
    private const HTTP_INTERNAL_ERROR = 500;
    private const HTTP_BAD_REQUEST = 400;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Cms::media_gallery';

    /**
     * @var GetImageDetailsByAssetId
     */
    private $getImageDetailsByAssetId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Details constructor.
     *
     * @param Context $context
     * @param GetImageDetailsByAssetId $getImageDetailsByAssetId
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        GetImageDetailsByAssetId $getImageDetailsByAssetId,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->logger = $logger;
        $this->getImageDetailsByAssetId = $getImageDetailsByAssetId;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $imageId = (int) $this->getRequest()->getParam('id');

        if ($imageId === 0) {
            $responseContent = [
                'success' => false,
                'message' => __('Image ID is required.'),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($responseContent);

            return $resultJson;
        }

        try {
            $imageDetails = $this->getImageDetailsByAssetId->execute($imageId);

            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'imageDetails' => $imageDetails
            ];
        } catch (LocalizedException $exception) {
            $responseCode = self::HTTP_BAD_REQUEST;
            $responseContent = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            $responseCode = self::HTTP_INTERNAL_ERROR;
            $responseContent = [
                'success' => false,
                'message' => __('An error occurred on attempt to get image details.'),
            ];
        }

        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
