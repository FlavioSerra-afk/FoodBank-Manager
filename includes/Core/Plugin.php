<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Admin\Menu;
use FoodBankManager\Mail\Logger;
use FoodBankManager\Rest\Api;
use FoodBankManager\Db\Migrations;
use FoodBankManager\Auth\Roles;

final class Plugin {

    public const FBM_VERSION = '0.1.1';


	private static ?Plugin $instance = null;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	public function init(): void {
		( new Hooks() )->register();
		( new Assets() )->register();
	}

	public function activate(): void {
		( new Migrations() )->maybe_migrate();
		( new Roles() )->register();
	}

	public function deactivate(): void {
		// Placeholder for future deactivation routines.
	}
}
