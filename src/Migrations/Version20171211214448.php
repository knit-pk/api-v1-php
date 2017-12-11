<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171211214448 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('CREATE TABLE teams (id CHAR(36) NOT NULL, parent_team_id CHAR(36) DEFAULT NULL NULL, name VARCHAR2(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_96C222585B24ACE8 ON teams (parent_team_id)');
        $this->addSql('COMMENT ON COLUMN teams.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN teams.parent_team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE teams_users (team_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, PRIMARY KEY(team_id, user_id))');
        $this->addSql('CREATE INDEX IDX_E0BD5D44296CD8AE ON teams_users (team_id)');
        $this->addSql('CREATE INDEX IDX_E0BD5D44A76ED395 ON teams_users (user_id)');
        $this->addSql('COMMENT ON COLUMN teams_users.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN teams_users.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE comment_replies (id CHAR(36) NOT NULL, author_id CHAR(36) DEFAULT NULL NULL, comment_id CHAR(36) DEFAULT NULL NULL, text CLOB NOT NULL, updated_at TIMESTAMP(0) NOT NULL, created_at TIMESTAMP(0) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5373053AF675F31B ON comment_replies (author_id)');
        $this->addSql('CREATE INDEX IDX_5373053AF8697D13 ON comment_replies (comment_id)');
        $this->addSql('COMMENT ON COLUMN comment_replies.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN comment_replies.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN comment_replies.comment_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE projects_teams (project_id CHAR(36) NOT NULL, team_id CHAR(36) NOT NULL, PRIMARY KEY(project_id, team_id))');
        $this->addSql('CREATE INDEX IDX_3437666166D1F9C ON projects_teams (project_id)');
        $this->addSql('CREATE INDEX IDX_3437666296CD8AE ON projects_teams (team_id)');
        $this->addSql('COMMENT ON COLUMN projects_teams.project_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN projects_teams.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE teams ADD CONSTRAINT FK_96C222585B24ACE8 FOREIGN KEY (parent_team_id) REFERENCES teams (id)');
        $this->addSql('ALTER TABLE teams_users ADD CONSTRAINT FK_E0BD5D44296CD8AE FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teams_users ADD CONSTRAINT FK_E0BD5D44A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_replies ADD CONSTRAINT FK_5373053AF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_replies ADD CONSTRAINT FK_5373053AF8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id)');
        $this->addSql('ALTER TABLE projects_teams ADD CONSTRAINT FK_3437666166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projects_teams ADD CONSTRAINT FK_3437666296CD8AE FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE IMAGES MODIFY (uploaded_at TIMESTAMP(0) DEFAULT NULL NULL)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'oracle', 'Migration can only be executed safely on \'oracle\'.');

        $this->addSql('ALTER TABLE teams DROP CONSTRAINT FK_96C222585B24ACE8');
        $this->addSql('ALTER TABLE teams_users DROP CONSTRAINT FK_E0BD5D44296CD8AE');
        $this->addSql('ALTER TABLE projects_teams DROP CONSTRAINT FK_3437666296CD8AE');
        $this->addSql('DROP TABLE teams');
        $this->addSql('DROP TABLE teams_users');
        $this->addSql('DROP TABLE comment_replies');
        $this->addSql('DROP TABLE projects_teams');
        $this->addSql('ALTER TABLE images MODIFY (UPLOADED_AT TIMESTAMP(0) NOT NULL)');
    }
}
