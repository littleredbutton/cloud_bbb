<?php

declare(strict_types=1);

namespace OCA\BigBlueButton\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000000Date20200826100844 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('bbb_restrictions')) {
			$table = $schema->createTable('bbb_restrictions');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull'       => true,
			]);
			$table->addColumn('group_id', 'string', [
				'notnull' => true,
				'length'  => 200,
			]);
			$table->addColumn('max_rooms', 'integer', [
				'notnull' => false,
				'default' => -1,
			]);
			$table->addColumn('room_types', 'string', [
				'notnull' => true,
				'default' => '[]',
			]);
			$table->addColumn('max_participants', 'integer', [
				'notnull' => false,
				'default' => -1,
			]);
			$table->addColumn('allow_recording', 'boolean', [
				'notnull' => false,
				'default' => true,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['group_id'], 'restrictions_group_id_index');
		}

		return $schema;
	}
}
