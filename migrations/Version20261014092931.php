<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261014092931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP CONSTRAINT fk_a45bddc12dd7fb44');
        $this->addSql('DROP INDEX idx_a45bddc12dd7fb44');
        $this->addSql('ALTER TABLE application ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE application RENAME COLUMN id_job_id TO job_id');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1BE04EA9 ON application (job_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1979B1AD6 ON application (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1BE04EA9');
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1979B1AD6');
        $this->addSql('DROP INDEX IDX_A45BDDC1BE04EA9');
        $this->addSql('DROP INDEX IDX_A45BDDC1979B1AD6');
        $this->addSql('ALTER TABLE application DROP company_id');
        $this->addSql('ALTER TABLE application RENAME COLUMN job_id TO id_job_id');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT fk_a45bddc12dd7fb44 FOREIGN KEY (id_job_id) REFERENCES job (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a45bddc12dd7fb44 ON application (id_job_id)');
    }
}
