<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220130161721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE football_match ADD next_match_id INT DEFAULT NULL, ADD final_phases TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE football_match ADD CONSTRAINT FK_8CE33ACE12A4E038 FOREIGN KEY (next_match_id) REFERENCES football_match (id)');
        $this->addSql('CREATE INDEX IDX_8CE33ACE12A4E038 ON football_match (next_match_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE football_match DROP FOREIGN KEY FK_8CE33ACE12A4E038');
        $this->addSql('DROP INDEX IDX_8CE33ACE12A4E038 ON football_match');
        $this->addSql('ALTER TABLE football_match DROP next_match_id, DROP final_phases');
    }
}
