<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171125120939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('CREATE TABLE users_security_roles (user_id CHAR(36) NOT NULL, security_role_id CHAR(36) NOT NULL, PRIMARY KEY(user_id, security_role_id))');
        $this->addSql('CREATE INDEX IDX_243A54D8A76ED395 ON users_security_roles (user_id)');
        $this->addSql('CREATE INDEX IDX_243A54D8BBE829B1 ON users_security_roles (security_role_id)');
        $this->addSql('COMMENT ON COLUMN users_security_roles.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users_security_roles.security_role_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE security_roles (id CHAR(36) NOT NULL, name VARCHAR2(70) NOT NULL, role VARCHAR2(70) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A82CD6D57698A6A ON security_roles (role)');
        $this->addSql('COMMENT ON COLUMN security_roles.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE users_security_roles ADD CONSTRAINT FK_243A54D8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_security_roles ADD CONSTRAINT FK_243A54D8BBE829B1 FOREIGN KEY (security_role_id) REFERENCES security_roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE USERS ADD (super_admin NUMBER(1) NOT NULL)');
        $this->addSql('ALTER TABLE USERS RENAME COLUMN password TO hash');
        $this->addSql('ALTER TABLE USERS DROP (SALT, ROLES)');
        $this->addSql('ALTER TABLE PROJECTS DROP CONSTRAINT FK_5C93B3A4F675F31B');
        $this->addSql('ALTER TABLE PROJECTS ADD CONSTRAINT FK_5C93B3A4F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ARTICLES DROP CONSTRAINT FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE ARTICLES DROP CONSTRAINT FK_BFDD3168F675F31B');
        $this->addSql('ALTER TABLE ARTICLES ADD CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ARTICLES ADD CONSTRAINT FK_BFDD3168F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ARTICLES_TAGS DROP CONSTRAINT FK_354053617294869C');
        $this->addSql('ALTER TABLE ARTICLES_TAGS DROP CONSTRAINT FK_35405361BAD26311');
        $this->addSql('ALTER TABLE ARTICLES_TAGS ADD CONSTRAINT FK_354053617294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ARTICLES_TAGS ADD CONSTRAINT FK_35405361BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('ALTER TABLE users_security_roles DROP CONSTRAINT FK_243A54D8BBE829B1');
        $this->addSql('DROP TABLE users_security_roles');
        $this->addSql('DROP TABLE security_roles');
        $this->addSql('ALTER TABLE users ADD (SALT VARCHAR2(255) DEFAULT NULL NULL, ROLES CLOB NOT NULL)');
        $this->addSql('ALTER TABLE users RENAME COLUMN hash TO PASSWORD');
        $this->addSql('ALTER TABLE users DROP (super_admin)');
        $this->addSql('COMMENT ON COLUMN users.ROLES IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE projects DROP CONSTRAINT FK_5C93B3A4F675F31B');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A4F675F31B FOREIGN KEY (AUTHOR_ID) REFERENCES USERS (ID)');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD3168F675F31B');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (CATEGORY_ID) REFERENCES CATEGORIES (ID)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD3168F675F31B FOREIGN KEY (AUTHOR_ID) REFERENCES USERS (ID)');
        $this->addSql('ALTER TABLE articles_tags DROP CONSTRAINT FK_354053617294869C');
        $this->addSql('ALTER TABLE articles_tags DROP CONSTRAINT FK_35405361BAD26311');
        $this->addSql('ALTER TABLE articles_tags ADD CONSTRAINT FK_354053617294869C FOREIGN KEY (ARTICLE_ID) REFERENCES ARTICLES (ID)');
        $this->addSql('ALTER TABLE articles_tags ADD CONSTRAINT FK_35405361BAD26311 FOREIGN KEY (TAG_ID) REFERENCES TAGS (ID)');
    }
}
