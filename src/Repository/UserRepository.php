<?php
declare(strict_types=1);

namespace JeboehmAccessProtection\Repository;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\User\UserCollection;
use Shopware\Core\System\User\UserEntity;

readonly class UserRepository implements UserRepositoryInterface
{
    /**
     * @param EntityRepository<UserCollection> $userRepository
     */
    public function __construct(
        private EntityRepository $userRepository,
        private ConfigValueRepository $configValueRepository,
    ) {
    }

    public function getUser(string $username, string $password, string $salesChannelId): UserEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('username', $username),
            new EqualsFilter('active', true),
        );

        if (($roleIds = $this->configValueRepository->getRoleIds($salesChannelId)) !== []) {
            $criteria->addAssociation('aclRoles');
            $criteria->addFilter(
                new OrFilter([
                    new EqualsAnyFilter('aclRoles.id', $roleIds),
                    new EqualsFilter('admin', true),
                ])
            );
        }

        $user = $this->userRepository->search(
            $criteria,
            Context::createDefaultContext(new SystemSource())
        )->first();

        if ($user === null) {
            throw new \OutOfBoundsException(sprintf('User with username "%s" not found', $username));
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new \OutOfBoundsException(sprintf('Wrong password for username "%s".', $username));
        }

        return $user;
    }
}
