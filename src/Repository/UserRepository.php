<?php

declare(strict_types=1);

namespace Jeboehm\AccessProtection\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ConfigValueRepository $configValueRepository,
    ) {
    }

    public function checkUser(string $username, string $password, string $salesChannelId): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('u.password')
            ->from('`user`', 'u')
            ->andWhere($qb->expr()->eq('u.username', ':username'))
            ->setMaxResults(1)
            ->setParameter('username', $username);

        if (($roleIds = $this->configValueRepository->getRoleIds($salesChannelId)) !== []) {
            $qb
                ->leftJoin('u', '`acl_user_role`', 'r', 'u.id = r.user_id')
                ->andWhere(
                    $qb->expr()->or(
                        $qb->expr()->eq('u.admin', 1),
                        $qb->expr()->in('r.acl_role_id', ':roleIds')
                    )
                )
                ->setParameter('roleIds', Uuid::fromHexToBytesList($roleIds), ArrayParameterType::STRING);
        }

        $userPassword = $qb->executeQuery()->fetchOne();

        if (!\is_string($userPassword)) {
            throw new \OutOfBoundsException(sprintf('User with username "%s" not found', $username));
        }

        if (!password_verify($password, $userPassword)) {
            throw new \OutOfBoundsException(sprintf('Wrong password for username "%s".', $username));
        }
    }
}
