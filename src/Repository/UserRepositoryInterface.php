<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Repository;

use Shopware\Core\System\User\UserEntity;

interface UserRepositoryInterface
{
    public function getUser(string $username, string $password, string $salesChannelId): UserEntity;
}
