<?php

declare(strict_types=1);

$composerJsonPath = \sprintf('%s/composer.json', $container->getParameter('kernel.project_dir'));
if (!\file_exists($composerJsonPath)) {
    throw new RuntimeException(\sprintf('Composer.json file does not exists in path: %s', $composerJsonPath));
}

$composerJson = \json_decode(\file_get_contents($composerJsonPath), true);

$container->setParameter('version', $composerJson['version']);
