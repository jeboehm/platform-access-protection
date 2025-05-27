<?php
declare(strict_types=1);

namespace Jeboehm\AccessProtection\Repository;

use Shopware\Core\System\SystemConfig\SystemConfigService;

final readonly class ConfigValueRepository
{
    public function __construct(
        private SystemConfigService $systemConfigService
    ) {
    }

    public function isEnabled(string $salesChannelId): bool
    {
        return $this->systemConfigService->getBool('JeboehmAccessProtection.config.enabled', $salesChannelId);
    }

    public function getRealm(string $salesChannelId): string
    {
        return $this->systemConfigService->getString('JeboehmAccessProtection.config.realm', $salesChannelId);
    }

    /**
     * @return string[]
     */
    public function getRoleIds(string $salesChannelId): array
    {
        /** @var string[]|null $roleIds */
        $roleIds = $this->systemConfigService->get('JeboehmAccessProtection.config.aclRoles', $salesChannelId);

        if (!\is_array($roleIds)) {
            return [];
        }

        return $roleIds;
    }

    /**
     * @return string[]
     */
    public function getAllowedIps(string $salesChannelId): array
    {
        /** @var string[]|null $allowedIps */
        $allowedIps = $this->systemConfigService->get('JeboehmAccessProtection.config.allowedIps', $salesChannelId);

        if (!\is_array($allowedIps)) {
            return [];
        }

        return $allowedIps;
    }
}
