<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Service;

use JeboehmAccessProtection\Repository\ConfigValueRepository;
use JeboehmAccessProtection\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class AccessValidatorService implements AccessValidatorInterface
{
    public function __construct(
        private ConfigValueRepository $configValueRepository,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function isAllowed(Request $request, string $salesChannelId): bool
    {
        if (!$this->configValueRepository->isEnabled($salesChannelId)) {
            return true;
        }

        $allowedIps = $this->configValueRepository->getAllowedIps($salesChannelId);

        if (\in_array($request->getClientIp(), $allowedIps, true)) {
            return true;
        }

        $username = $request->getUser();
        $password = $request->getPassword();

        if ($username === null || $password === null) {
            return false;
        }

        try {
            $this->userRepository->getUser($username, $password, $salesChannelId);

            return true;
        } catch (\OutOfBoundsException $e) {
            $this->logger->warning($e->getMessage());
        }

        return false;
    }
}
