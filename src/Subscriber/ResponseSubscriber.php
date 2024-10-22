<?php
declare(strict_types=1);

namespace Jeboehm\AccessProtection\Subscriber;

use Jeboehm\AccessProtection\Factory\ResponseFactoryInterface;
use Jeboehm\AccessProtection\Service\AccessValidatorInterface;
use Shopware\Core\Framework\Event\BeforeSendResponseEvent;
use Shopware\Core\Framework\Routing\RequestTransformerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AccessValidatorInterface $accessValidator,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestTransformerInterface $requestTransformer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [BeforeSendResponseEvent::class => 'onBeforeSendResponse'];
    }

    public function onBeforeSendResponse(BeforeSendResponseEvent $event): void
    {
        $request = $this->requestTransformer->transform(clone $event->getRequest());
        $salesChannelId = $request->attributes->getAlnum(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        if (!Uuid::isValid($salesChannelId)) {
            // assume that this is no storefront request
            return;
        }

        $allowed = $this->accessValidator->isAllowed($request, $salesChannelId);

        if (!$allowed && $event->getResponse()->getStatusCode() === Response::HTTP_NOT_FOUND) {
            $event->getResponse()->setContent('Not found.');

            return;
        }

        if ($allowed) {
            return;
        }

        $event->setResponse($this->responseFactory->createResponse($salesChannelId));
    }
}
