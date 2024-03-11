<?php
declare(strict_types=1);

namespace Jeboehm\AccessProtection\Service;

use Jeboehm\AccessProtection\Repository\ConfigValueRepository;
use Jeboehm\AccessProtection\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

final class AccessValidatorService implements AccessValidatorInterface
{
    public function __construct(
        private readonly ConfigValueRepository $configValueRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isAllowed(Request $request, string $salesChannelId): bool
    {
        if (!$this->configValueRepository->isEnabled($salesChannelId)) {
            return true;
        }

        $allowedIps = $this->configValueRepository->getAllowedIps($salesChannelId);
        $clientIp = $request->getClientIp();

        foreach ($allowedIps as $allowedIp) {
            if ($clientIp !== null && IpUtils::checkIp($clientIp, $allowedIp)) {
                return true;
            }
        }

        if (\in_array($request->getClientIp(), $allowedIps, true)) {
            return true;
        }

        $username = $request->getUser();
        $password = $request->getPassword();

        if ($username === null || $password === null) {
            return false;
        }

        try {
            $this->userRepository->checkUser($username, $password, $salesChannelId);

            return true;
        } catch (\OutOfBoundsException $e) {
            $this->logger->warning($e->getMessage());
        }

        return false;
    }
}
