<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180504212245 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categories ADD category_image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD description LONGTEXT NOT NULL, ADD articles_count INT UNSIGNED NOT NULL, ADD metadata_title VARCHAR(255) NOT NULL, ADD metadata_description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF346688ADFA116 FOREIGN KEY (category_image_id) REFERENCES images (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_3AF346688ADFA116 ON categories (category_image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF346688ADFA116');
        $this->addSql('DROP INDEX IDX_3AF346688ADFA116 ON categories');
        $this->addSql('ALTER TABLE categories DROP category_image_id, DROP description, DROP articles_count, DROP metadata_title, DROP metadata_description');
    }
}
