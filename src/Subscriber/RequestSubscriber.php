<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Subscriber;

use JeboehmAccessProtection\Factory\ExceptionFactoryInterface;
use JeboehmAccessProtection\Service\AccessValidatorInterface;
use Shopware\Core\PlatformRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AccessValidatorInterface $accessValidator,
        private ExceptionFactoryInterface $exceptionFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => 'onRequest'];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $salesChannelId = $request->attributes->getAlnum(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        if ($salesChannelId === '') {
            // assume that this is no storefront request
            return;
        }

        if ($this->accessValidator->isAllowed($request, $salesChannelId)) {
            return;
        }

        throw $this->exceptionFactory->createException($salesChannelId);
    }
}
