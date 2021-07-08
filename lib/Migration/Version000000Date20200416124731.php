<?php

declare(strict_types=1);

namespace OCA\BigBlueButton\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000000Date20200416124731 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('bbb_rooms')) {
			$table = $schema->createTable('bbb_rooms');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull'       => true,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length'  => 64
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length'  => 200,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length'  => 200
			]);
			$table->addColumn('attendee_password', 'string', [
				'notnull' => true,
				'length'  => 32
			]);
			$table->addColumn('moderator_password', 'string', [
				'notnull' => true,
				'length'  => 32
			]);
			$table->addColumn('welcome', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('max_participants', 'integer', [
				'notnull' => false,
			]);
			$table->addColumn('record', 'boolean', [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['uid'], 'bbb_rooms_uid_index');
			$table->addIndex(['user_id'], 'bbb_rooms_user_id_index');
		}
		return $schema;
	}
}
