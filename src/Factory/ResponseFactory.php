<?php
declare(strict_types=1);

namespace Jeboehm\AccessProtection\Factory;

use Jeboehm\AccessProtection\Repository\ConfigValueRepository;
use Symfony\Component\HttpFoundation\Response;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private readonly ConfigValueRepository $configValueRepository
    ) {
    }

    public function createResponse(string $salesChannelId): Response
    {
        $headerValue = sprintf('Basic realm="%s", charset="UTF-8"', $this->configValueRepository->getRealm($salesChannelId));

        $response = new Response(
            'Unauthorized',
            Response::HTTP_UNAUTHORIZED,
            ['WWW-Authenticate' => $headerValue]
        );
        $response->setCache(
            [
                'private' => true,
                'no_store' => true,
            ]
        );

        return $response;
    }
}
