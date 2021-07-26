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
class Version000000Date20210419132000 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		$schema = $schemaClosure();

		if ($schema->hasTable('bbb_rooms')) {
			$table = $schema->getTable('bbb_rooms');

			if (!$table->hasColumn('listen_only')) {
				$table->addColumn('listen_only', 'boolean', [
					'notnull' => false,
					'default' => true
				]);
			}

			if (!$table->hasColumn('media_check')) {
				$table->addColumn('media_check', 'boolean', [
					'notnull' => false,
					'default' => true
				]);
			}

			if (!$table->hasColumn('clean_layout')) {
				$table->addColumn('clean_layout', 'boolean', [
					'notnull' => false,
					'default' => false
				]);
			}

			return $schema;
		}

		return null;
	}
}
