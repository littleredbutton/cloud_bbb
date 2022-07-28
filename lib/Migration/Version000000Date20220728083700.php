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
class Version000000Date20220728083700 extends SimpleMigrationStep {


	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$this->convertToNotNullable($schema, 'bbb_rooms', 'record');
		$this->convertToNotNullable($schema, 'bbb_rooms', 'everyone_is_moderator');
		$this->convertToNotNullable($schema, 'bbb_rooms', 'require_moderator');
		$this->convertToNotNullable($schema, 'bbb_rooms', 'listen_only');
		$this->convertToNotNullable($schema, 'bbb_rooms', 'media_check');
		$this->convertToNotNullable($schema, 'bbb_rooms', 'clean_layout');

		$this->convertToNotNullable($schema, 'bbb_restrictions', 'allow_recording');

		return null;
	}

	private function convertToNotNullable(ISchemaWrapper $schema, string $table, string $column): void {
		if ($schema->hasTable($table)) {
			$table = $schema->getTable('bbb_rooms');

			if ($table->hasColumn($column) && $table->getColumn($column)->getNotnull()) {
				$table->getColumn($column)->setNotnull(false);
			}
		}
	}
}
