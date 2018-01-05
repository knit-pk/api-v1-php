<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180105181254 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('CREATE TABLE ratings (id CHAR(36) NOT NULL, article_id CHAR(36) DEFAULT NULL NULL, author_id CHAR(36) DEFAULT NULL NULL, value VARCHAR2(255) NOT NULL, created_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CEB607C97294869C ON ratings (article_id)');
        $this->addSql('CREATE INDEX IDX_CEB607C9F675F31B ON ratings (author_id)');
        $this->addSql('CREATE UNIQUE INDEX user_article_unique ON ratings (author_id, article_id)');
        $this->addSql('COMMENT ON COLUMN ratings.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN ratings.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN ratings.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C97294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C9F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ARTICLES MODIFY (comments_count NUMBER(10) DEFAULT NULL)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('DROP TABLE ratings');
        $this->addSql('ALTER TABLE articles MODIFY (COMMENTS_COUNT NUMBER(10) DEFAULT NULL)');
    }
}
