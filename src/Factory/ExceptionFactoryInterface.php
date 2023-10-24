<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Factory;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

interface ExceptionFactoryInterface
{
    public function createException(string $salesChannelId): UnauthorizedHttpException;
}
