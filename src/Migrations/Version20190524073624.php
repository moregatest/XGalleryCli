<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190524073624 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE batdongsan_com_vn CHANGE url url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE flickr_contact CHANGE nsid nsid VARCHAR(35) NOT NULL');
        $this->addSql('ALTER TABLE flickr_photo CHANGE id id VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE nct CHANGE url url VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE batdongsan_com_vn CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE flickr_contact CHANGE nsid nsid VARCHAR(35) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE flickr_photo CHANGE id id VARCHAR(50) NOT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE nct CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
