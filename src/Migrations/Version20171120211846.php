<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171120211846 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq START WITH 1 MINVALUE 1 INCREMENT BY 1');
        $this->addSql('CREATE TABLE users (id CHAR(36) NOT NULL, username VARCHAR2(180) NOT NULL, username_canonical VARCHAR2(180) NOT NULL, email VARCHAR2(180) NOT NULL, email_canonical VARCHAR2(180) NOT NULL, enabled NUMBER(1) NOT NULL, salt VARCHAR2(255) DEFAULT NULL NULL, password VARCHAR2(255) NOT NULL, last_login TIMESTAMP(0) DEFAULT NULL NULL, confirmation_token VARCHAR2(180) DEFAULT NULL NULL, password_requested_at TIMESTAMP(0) DEFAULT NULL NULL, roles CLOB NOT NULL, fullname VARCHAR2(255) DEFAULT NULL NULL, created_at TIMESTAMP(0) NOT NULL, updated_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E992FC23A8 ON users (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9A0D96FBF ON users (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9C05FB297 ON users (confirmation_token)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users.roles IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE tags (id CHAR(36) NOT NULL, code VARCHAR2(255) NOT NULL, name VARCHAR2(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FBC942677153098 ON tags (code)');
        $this->addSql('COMMENT ON COLUMN tags.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE projects (id CHAR(36) NOT NULL, author_id CHAR(36) DEFAULT NULL NULL, name VARCHAR2(255) NOT NULL, code VARCHAR2(255) NOT NULL, description VARCHAR2(255) NOT NULL, url VARCHAR2(255) NOT NULL, created_at TIMESTAMP(0) NOT NULL, updated_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C93B3A477153098 ON projects (code)');
        $this->addSql('CREATE INDEX IDX_5C93B3A4F675F31B ON projects (author_id)');
        $this->addSql('COMMENT ON COLUMN projects.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN projects.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE categories (id CHAR(36) NOT NULL, code VARCHAR2(255) NOT NULL, name VARCHAR2(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3AF3466877153098 ON categories (code)');
        $this->addSql('COMMENT ON COLUMN categories.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE articles (id CHAR(36) NOT NULL, category_id CHAR(36) DEFAULT NULL NULL, author_id CHAR(36) DEFAULT NULL NULL, code VARCHAR2(255) NOT NULL, title VARCHAR2(255) NOT NULL, content CLOB NOT NULL, description CLOB NOT NULL, published_at TIMESTAMP(0) DEFAULT NULL NULL, published NUMBER(1) NOT NULL, updated_at TIMESTAMP(0) NOT NULL, created_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFDD316877153098 ON articles (code)');
        $this->addSql('CREATE INDEX IDX_BFDD316812469DE2 ON articles (category_id)');
        $this->addSql('CREATE INDEX IDX_BFDD3168F675F31B ON articles (author_id)');
        $this->addSql('COMMENT ON COLUMN articles.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN articles.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN articles.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE articles_tags (article_id CHAR(36) NOT NULL, tag_id CHAR(36) NOT NULL, PRIMARY KEY(article_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_354053617294869C ON articles_tags (article_id)');
        $this->addSql('CREATE INDEX IDX_35405361BAD26311 ON articles_tags (tag_id)');
        $this->addSql('COMMENT ON COLUMN articles_tags.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN articles_tags.tag_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE refresh_tokens (id NUMBER(10) NOT NULL, refresh_token VARCHAR2(128) NOT NULL, username VARCHAR2(255) NOT NULL, valid TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A4F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD3168F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE articles_tags ADD CONSTRAINT FK_354053617294869C FOREIGN KEY (article_id) REFERENCES articles (id)');
        $this->addSql('ALTER TABLE articles_tags ADD CONSTRAINT FK_35405361BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('ALTER TABLE projects DROP CONSTRAINT FK_5C93B3A4F675F31B');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD3168F675F31B');
        $this->addSql('ALTER TABLE articles_tags DROP CONSTRAINT FK_35405361BAD26311');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE articles_tags DROP CONSTRAINT FK_354053617294869C');
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE articles_tags');
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
