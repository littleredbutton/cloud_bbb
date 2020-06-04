<?php

declare(strict_types=1);

namespace OCA\BigBlueButton\Migration;

use Closure;
use OCA\BigBlueButton\Db\Room;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000000Date20200604130935 extends SimpleMigrationStep
{
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options)
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('bbb_rooms')) {
			$table = $schema->getTable('bbb_rooms');

			if (!$table->hasColumn('access')) {
				$table->addColumn('access', 'string', [
					'notnull' => true,
					'default' => Room::ACCESS_PUBLIC,
				]);
			}

			if (!$table->hasColumn('password')) {
				$table->addColumn('password', 'string', [
					'length'  => 64,
					'notnull' => false,
				]);
			}

			return $schema;
		}

		return null;
	}
}
