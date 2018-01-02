<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180102120132 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('ALTER TABLE ARTICLES ADD (comments_count NUMBER(10) NOT NULL)');
        $this->addSql('CREATE OR REPLACE TRIGGER ADD_COMMENT AFTER INSERT ON comments
  FOR EACH ROW
  BEGIN
    UPDATE articles SET comments_count = comments_count + 1 WHERE id = :new.article_id;
  END;');
        $this->addSql('CREATE OR REPLACE TRIGGER ADD_COMMENT_REPLY AFTER INSERT ON comment_replies
  FOR EACH ROW
  DECLARE
    updated_article_id CHAR(36);
  BEGIN
    SELECT article_id INTO updated_article_id FROM comments WHERE comments.id = :new.comment_id;
    UPDATE articles SET comments_count = comments_count + 1 WHERE articles.id = updated_article_id;
  END;');
        $this->addSql('CREATE OR REPLACE TRIGGER REMOVE_COMMENT AFTER DELETE ON comments
  FOR EACH ROW
  BEGIN
    UPDATE articles SET comments_count = comments_count - 1 WHERE id = :old.article_id;
  END;');
        $this->addSql('CREATE OR REPLACE TRIGGER REMOVE_COMMENT_REPLY AFTER DELETE ON comment_replies
  FOR EACH ROW
  DECLARE
    updated_article_id CHAR(36);
  BEGIN
    SELECT article_id INTO updated_article_id FROM comments WHERE comments.id = :old.comment_id;
    UPDATE articles SET comments_count = comments_count - 1 WHERE articles.id = updated_article_id;
  END;');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('ALTER TABLE articles DROP (comments_count)');
        $this->addSql('DROP TRIGGER ADD_COMMENT');
        $this->addSql('DROP TRIGGER ADD_COMMENT_REPLY');
        $this->addSql('DROP TRIGGER REMOVE_COMMENT');
        $this->addSql('DROP TRIGGER REMOVE_COMMENT_REPLY');
    }
}
