<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/28 12:01:20
 */
class Version20140528120118 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_from DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_to DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP COLUMN workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_C8EFD7EF82D40A1F'
            ) 
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT IDX_C8EFD7EF82D40A1F ELSE 
            DROP INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_from
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_to
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD workspace_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag (workspace_id)
        ");
    }
}