<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015142231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP CONSTRAINT fk_a45bddc1be04ea9');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE job DROP CONSTRAINT fk_fbd8e0f832119a01');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F832119A01 FOREIGN KEY (id_company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d649979b1ad6');
        $this->addSql('DROP INDEX uniq_8d93d649979b1ad6');
        $this->addSql('ALTER TABLE "user" DROP company_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC1BE04EA9');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT fk_a45bddc1be04ea9 FOREIGN KEY (job_id) REFERENCES job (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE job DROP CONSTRAINT FK_FBD8E0F832119A01');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT fk_fbd8e0f832119a01 FOREIGN KEY (id_company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d649979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649979b1ad6 ON "user" (company_id)');
    }
}
