<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        /*
        * Defines map region for example different maps for each floor.
        Structure:
        * map -> Holds symbolic link to map file.
        */
        $this->createTable('{{%plane}}', [
            'id' => $this->primaryKey(),
            'map' => $this->string(),

            'name' => $this->string(32)->notNull()->unique(),
            'description' => $this->string()->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        /*
        * Defines types of things.
        Structure:
        * name -> Holds displayable name of thing.
        * sockets -> Number of connection sockets that will be generated in object owned group.
        * action -> Non standard action.
        */
        $this->createTable('{{%type}}', [
            'id' => $this->primaryKey(),
            'sockets' => $this->integer()->defaultValue(0),

            'name' => $this->string(48)->notNull()->unique(),
            'description' => $this->string()->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        /*
        * Holds all grouped objects like rack cabinets or groups of LAN sockets.
        * Structure:
        * plane -> Define plane of existence. eg. first floor, second floor.
        * parent -> Could have parent group set. eg. Group of socket inside a cabinet.
        * position -> Link with position. Could be virtual (null - no real position set) or act as a position for all child objects.
        */
        $this->createTable('{{%group}}', [
            'id' => $this->primaryKey(),
            'plane' => $this->integer(),
            'parent' => $this->integer()->defaultValue(null),
            'position' => $this->integer()->defaultValue(null),

            'name' => $this->string(32)->notNull()->unique(),
            'description' => $this->string()->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        /*
        * Defines object set on map.
        * Structure:
        * plane -> Define plane of existence.
        * parent -> Define group which this object is child of. If null then object is standalone.
        * position -> Link to position. If group has position set then it's overrided by it.
        * type -> Defines type of object. eg. socket, switch, router.
        * group -> If can be treated as group eg. switch witch groups some port objects.
        */
        $this->createTable('{{%object}}', [
            'id' => $this->primaryKey(),
            'plane' => $this->integer(),
            'parent' => $this->integer()->defaultValue(null),
            'position' => $this->integer()->notNull(),
            'type' => $this->integer(),
            'group' => $this->integer()->defaultValue(null)->unique(),

            'name' => $this->string(32)->notNull()->unique(),
            'description' => $this->string()->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        /*
        * Defines position of object or group.
        * Structure:
        * plane -> Define plane of existence.
        * name -> Optional human readable name (optional).
        * pos_x,pos_y -> Coordinates on plane.
        */
        $this->createTable('{{%position}}', [
            'id' => $this->primaryKey(),
            'plane' => $this->integer()->notNull(),
            'pos_x' => $this->float(10),
            'pos_y' => $this->float(10),

            'name' => $this->string(32),
            'description' => $this->string()->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        /*
        * Defines connection of two objects.
        * Structure:
        * start, end -> link to object.
        */
        $this->createTable('{{%connection}}', [
            'id' => $this->primaryKey(),
            'start' => $this->integer()->notNull(),
            'end' => $this->integer()->notNull(),

            'description' => $this->string()->defaultValue(null),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        // addForeignKey NameOfKey [ReferenceTable_Table_desc], TableWhereKeyBeAdded, ColumnWhereKeyBeAdded, TableOfReference, ColumnOfReference.
        $this->addForeignKey('plane_group_0', '{{%group}}', 'plane', '{{%plane}}', 'id');
        $this->addForeignKey('plane_object_0', '{{%object}}', 'plane', '{{%plane}}', 'id');
        $this->addForeignKey('plane_position_0', '{{%position}}', 'plane', '{{%plane}}', 'id');

        $this->addForeignKey('type_object_0', '{{%object}}', 'type', '{{%type}}', 'id');

        $this->addForeignKey('group_object_parent', '{{%object}}', 'parent', '{{%group}}', 'id');
        $this->addForeignKey('group_object_group', '{{%object}}', 'group', '{{%group}}', 'id');

        $this->addForeignKey('object_position_0', '{{%object}}', 'position', '{{%position}}', 'id');
        $this->addForeignKey('group_position_0', '{{%group}}', 'position', '{{%position}}', 'id');

        $this->addForeignKey('group_group_parent', '{{%group}}', 'parent', '{{%group}}', 'id');

        $this->addForeignKey('object_connection_0', '{{%connection}}', 'start', '{{%object}}', 'id');
        $this->addForeignKey('object_connection_1', '{{%connection}}', 'end', '{{%object}}', 'id');

        $this->insert('{{%user}}',[
            'id' => 1,
            'username' => 'administrator',
            'password_hash' => '$2y$13$e1dEJFbLQiaFOrJtucp5wOLuFP3O1217OOwt4UxDxK2xuRKnPjvKK',
            'email' => 'test@test.test',
            'status' => 10,
        ]);
    }

    public function safeDown()
    {
        // Drop Foreign Keys
        $this->dropForeignKey('plane_group_0', '{{%group}}');
        $this->dropForeignKey('plane_object_0', '{{%object}}');
        $this->dropForeignKey('plane_position_0', '{{%position}}');

        $this->dropForeignKey('type_object_0', '{{%object}}');

        $this->dropForeignKey('group_object_parent', '{{%object}}');
        $this->dropForeignKey('group_object_group', '{{%object}}');

        $this->dropForeignKey('object_position_0', '{{%object}}');
        $this->dropForeignKey('group_position_0', '{{%group}}');

        $this->dropForeignKey('group_group_parent', '{{%group}}');

        $this->dropForeignKey('object_connection_0', '{{%connection}}');
        $this->dropForeignKey('object_connection_1', '{{%connection}}');

        // Drop tables
        $this->dropTable('{{%user}}');
        $this->dropTable('{{%plane}}');
        $this->dropTable('{{%group}}');
        $this->dropTable('{{%object}}');
        $this->dropTable('{{%position}}');
        $this->dropTable('{{%type}}');
        $this->dropTable('{{%connection}}');
    }
}
