<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Service;

use Symfony\Component\HttpFoundation\Request;

interface AccessValidatorInterface
{
    public function isAllowed(Request $request, string $salesChannelId): bool;
}
