<?php

namespace App\Tests\Feature;

use Behat\Behat\Context\Context;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class FeatureContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When a demo scenario sends a request to :path
     *
     * @param string $path
     *
     * @throws \Exception
     */
    public function aDemoScenarioSendsARequestTo(string $path): void
    {
        $this->response = $this->kernel->handle(Request::create($path));
    }

    /**
     * @Then the success response should be received
     *
     * @throws \RuntimeException
     */
    public function theSuccessResponseShouldBeReceived(): void
    {
        if (null === $this->response) {
            throw new RuntimeException('No response received');
        }

        if (200 !== $this->response->getStatusCode()) {
            throw new RuntimeException(\sprintf('Status code was %d', $this->response->getStatusCode()));
        }
    }
}
