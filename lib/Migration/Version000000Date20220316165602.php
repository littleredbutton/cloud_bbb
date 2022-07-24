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
class Version000000Date20220316165602 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		$schema = $schemaClosure();

		if ($schema->hasTable('bbb_rooms')) {
			$table = $schema->getTable('bbb_rooms');

			if (!$table->hasColumn('logout_u_r_l')) {
				$table->addColumn('logout_u_r_l', 'string', [
					'notnull' => false
				]);
			}
		}

		if ($schema->hasTable('bbb_restrictions')) {
			$table = $schema->getTable('bbb_restrictions');

			if (!$table->hasColumn('allow_logout_u_r_l')) {
				$table->addColumn('allow_logout_u_r_l', 'boolean', [
					'notnull' => false,
					'default' => false
				]);
			}
		}

		return $schema;
	}
}
