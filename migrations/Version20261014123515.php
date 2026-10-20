<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261014123515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP CONSTRAINT fk_a45bddc1b4d2e573');
        $this->addSql('DROP INDEX idx_a45bddc1b4d2e573');
        $this->addSql('ALTER TABLE application RENAME COLUMN id_applicant_id TO user_id');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_A45BDDC1A76ED395 ON application (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1A76ED395');
        $this->addSql('DROP INDEX IDX_A45BDDC1A76ED395');
        $this->addSql('ALTER TABLE application RENAME COLUMN user_id TO id_applicant_id');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT fk_a45bddc1b4d2e573 FOREIGN KEY (id_applicant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a45bddc1b4d2e573 ON application (id_applicant_id)');
    }
}
