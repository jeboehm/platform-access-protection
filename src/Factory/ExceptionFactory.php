<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Factory;

use JeboehmAccessProtection\Repository\ConfigValueRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

readonly class ExceptionFactory implements ExceptionFactoryInterface
{
    public function __construct(
        private ConfigValueRepository $configValueRepository
    ) {
    }

    public function createException(string $salesChannelId): UnauthorizedHttpException
    {
        return new UnauthorizedHttpException(
            sprintf('Basic realm="%s", charset="UTF-8"', $this->configValueRepository->getRealm($salesChannelId))
        );
    }
}
