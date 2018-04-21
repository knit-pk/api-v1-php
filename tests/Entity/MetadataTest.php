<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function metadataProvider(): array
    {
        return [
            'valid' => [
                'metadata title',
                'metadata description',
            ],
            'empty/whitespaces' => [
                ' ',
                '',
            ],
        ];
    }

    /**
     * @dataProvider metadataProvider
     *
     * @param string $title
     * @param string $description
     */
    public function testGetters(string $title, string $description): void
    {
        $metadata = new Metadata($title, $description);

        $this->assertSame($title, $metadata->getTitle());
        $this->assertSame($description, $metadata->getDescription());
    }

    /**
     * @dataProvider metadataProvider
     *
     * @param string $title
     * @param string $description
     */
    public function testSetters(string $title, string $description): void
    {
        $notTitle = '_not_a_title';
        $notDescription = '_not_a_description';
        $this->assertNotSame($notTitle, $title);
        $this->assertNotSame($notDescription, $description);

        $metadata = new Metadata($notTitle, $notDescription);
        $metadata->setTitle($title);
        $metadata->setDescription($description);

        $this->assertSame($title, $metadata->getTitle());
        $this->assertSame($description, $metadata->getDescription());
    }
}
