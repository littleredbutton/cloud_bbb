<?php

declare(strict_types=1);

namespace OCA\BigBlueButton\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000000Date20200617055735 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('bbb_rooms')) {
			$table = $schema->getTable('bbb_rooms');

			if (!$table->hasColumn('everyone_is_moderator')) {
				$table->addColumn('everyone_is_moderator', 'boolean', [
					'notnull' => false,
					'default' => false,
				]);
			}

			return $schema;
		}

		return null;
	}
}
