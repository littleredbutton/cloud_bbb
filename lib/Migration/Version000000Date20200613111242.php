<?php

declare(strict_types=1);

namespace OCA\BigBlueButton\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000000Date20200613111242 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('bbb_room_shares')) {
			$table = $schema->createTable('bbb_room_shares');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('room_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('share_with', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('share_type', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('permission', 'integer', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
		}

		return $schema;
	}
}
