<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\Settings;

use OCA\LinkvertiseIntegration\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

/**
 * Registers the "Linkvertise" section/tab inside the Nextcloud admin settings.
 */
class AdminSection implements IIconSection {
	public function __construct(
		private IL10N $l,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l->t('Linkvertise');
	}

	public function getPriority(): int {
		return 75;
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}
}
