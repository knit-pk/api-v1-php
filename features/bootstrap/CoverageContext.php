<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP;

final class CoverageContext implements Context
{
    private const APP_ROOT = __DIR__.'/../../';
    private const COVERAGE_PATH = self::APP_ROOT.'var/cov/behat.cov';
    private const SRC_DIR = self::APP_ROOT.'src';

    /**
     * @var CodeCoverage
     */
    private static $coverage;

    /**
     * @BeforeSuite
     */
    public static function setup(): void
    {
        if (!\file_exists(\dirname(self::COVERAGE_PATH))) {
            throw new \RuntimeException(\sprintf('Code coverage directory "%s" does not exist.', \dirname(self::COVERAGE_PATH)));
        }

        $filter = new Filter();
        $filter->addDirectoryToWhitelist(self::SRC_DIR);
        self::$coverage = new CodeCoverage(null, $filter);
    }

    /**
     * @AfterSuite
     */
    public static function tearDown(): void
    {
        (new PHP())->process(self::$coverage, self::COVERAGE_PATH);
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function startCoverage(BeforeScenarioScope $scope): void
    {
        self::$coverage->start($this->generateCoverageId($scope));
    }

    /**
     * @AfterScenario
     *
     * @throws \ReflectionException
     */
    public function stopCoverage(): void
    {
        self::$coverage->stop();
    }

    private function generateCoverageId(BeforeScenarioScope $scope): string
    {
        return \sprintf('%s::%s', $scope->getFeature()->getTitle(), $scope->getScenario()->getTitle());
    }
}
