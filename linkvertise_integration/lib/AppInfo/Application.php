<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\AppInfo;

use OCA\LinkvertiseIntegration\Listener\LoadAdditionalScriptsListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Files\Events\LoadAdditionalScriptsEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'linkvertise_integration';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		// Injects our Files-App file action script whenever the Files app UI is loaded.
		$context->registerEventListener(LoadAdditionalScriptsEvent::class, LoadAdditionalScriptsListener::class);

		// Settings\Admin and Settings\AdminSection are wired up declaratively via
		// appinfo/info.xml <settings> and are instantiated by the DI container
		// automatically, no explicit registration needed here.
	}

	public function boot(IBootContext $context): void {
	}
}
