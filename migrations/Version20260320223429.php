<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320223429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_stress_threshold (quiz_id UUID NOT NULL, stress_threshold_id UUID NOT NULL, PRIMARY KEY (quiz_id, stress_threshold_id))');
        $this->addSql('CREATE INDEX IDX_E3C70C07853CD175 ON quiz_stress_threshold (quiz_id)');
        $this->addSql('CREATE INDEX IDX_E3C70C07A4A4F468 ON quiz_stress_threshold (stress_threshold_id)');
        $this->addSql('ALTER TABLE quiz_stress_threshold ADD CONSTRAINT FK_E3C70C07853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_stress_threshold ADD CONSTRAINT FK_E3C70C07A4A4F468 FOREIGN KEY (stress_threshold_id) REFERENCES stress_threshold (id) ON DELETE CASCADE');
        
        // Data migration: move existing links to the junction table
        $this->addSql('INSERT INTO quiz_stress_threshold (quiz_id, stress_threshold_id) SELECT quiz_id, id FROM stress_threshold WHERE quiz_id IS NOT NULL');
        
        $this->addSql('ALTER TABLE stress_threshold DROP CONSTRAINT fk_ff9069df853cd175');
        $this->addSql('DROP INDEX idx_ff9069df853cd175');
        $this->addSql('ALTER TABLE stress_threshold DROP quiz_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_stress_threshold DROP CONSTRAINT FK_E3C70C07853CD175');
        $this->addSql('ALTER TABLE quiz_stress_threshold DROP CONSTRAINT FK_E3C70C07A4A4F468');
        $this->addSql('DROP TABLE quiz_stress_threshold');
        $this->addSql('ALTER TABLE stress_threshold ADD quiz_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE stress_threshold ADD CONSTRAINT fk_ff9069df853cd175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_ff9069df853cd175 ON stress_threshold (quiz_id)');
    }
}
