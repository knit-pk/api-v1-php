<?php

declare(strict_types=1);

namespace App\Bundle\SwooleBundle\Bridge\Doctrine\ORM;

use App\Bundle\SwooleBundle\Driver\HttpDriverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class EntityManagerHttpDriver implements HttpDriverInterface
{
    private $decorated;
    private $connection;
    private $entityManager;

    public function __construct(HttpDriverInterface $decorated, EntityManagerInterface $entityManager)
    {
        $this->decorated = $decorated;
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(array $configuration = []): void
    {
        $this->decorated->boot($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, Response $response): void
    {
        if (!$this->connection->ping()) {
            $this->connection->close();
            $this->connection->connect();
        }

        $this->decorated->handle($request, $response);

        $this->entityManager->clear();
    }
}
