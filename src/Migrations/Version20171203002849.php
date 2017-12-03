<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171203002849 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('CREATE TABLE images (id CHAR(36) NOT NULL, author_id CHAR(36) DEFAULT NULL NULL, url VARCHAR2(255) NOT NULL, file_size NUMBER(20) DEFAULT NULL NULL, file_name VARCHAR2(255) DEFAULT NULL NULL, original_name VARCHAR2(255) DEFAULT NULL NULL, uploaded_at TIMESTAMP(0) NOT NULL, updated_at TIMESTAMP(0) NOT NULL, created_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E01FBE6AD7DF1668 ON images (file_name)');
        $this->addSql('CREATE INDEX IDX_E01FBE6AF675F31B ON images (author_id)');
        $this->addSql('COMMENT ON COLUMN images.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN images.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE comments (id CHAR(36) NOT NULL, article_id CHAR(36) DEFAULT NULL NULL, author_id CHAR(36) DEFAULT NULL NULL, text CLOB NOT NULL, updated_at TIMESTAMP(0) NOT NULL, created_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5F9E962A7294869C ON comments (article_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AF675F31B ON comments (author_id)');
        $this->addSql('COMMENT ON COLUMN comments.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN comments.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN comments.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A7294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE USERS ADD (avatar_image_id CHAR(36) DEFAULT NULL NULL)');
        $this->addSql('COMMENT ON COLUMN USERS.avatar_image_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE USERS ADD CONSTRAINT FK_1483A5E95C18B4B1 FOREIGN KEY (avatar_image_id) REFERENCES images (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E95C18B4B1 ON USERS (avatar_image_id)');
        $this->addSql('ALTER TABLE ARTICLES ADD (image_id CHAR(36) DEFAULT NULL NULL)');
        $this->addSql('COMMENT ON COLUMN ARTICLES.image_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ARTICLES ADD CONSTRAINT FK_BFDD31683DA5256D FOREIGN KEY (image_id) REFERENCES images (id)');
        $this->addSql('CREATE INDEX IDX_BFDD31683DA5256D ON ARTICLES (image_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E95C18B4B1');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD31683DA5256D');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP INDEX IDX_1483A5E95C18B4B1');
        $this->addSql('ALTER TABLE users DROP (avatar_image_id)');
        $this->addSql('DROP INDEX IDX_BFDD31683DA5256D');
        $this->addSql('ALTER TABLE articles DROP (image_id)');
    }
}
