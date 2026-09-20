<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\Listener;

use OCA\LinkvertiseIntegration\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\LoadAdditionalScriptsEvent;
use OCP\Util;

/**
 * Adds our "Linkvertise-Link generieren" file action to the Files app.
 *
 * @template-implements IEventListener<LoadAdditionalScriptsEvent>
 */
class LoadAdditionalScriptsListener implements IEventListener {
	public function handle(Event $event): void {
		if (!($event instanceof LoadAdditionalScriptsEvent)) {
			return;
		}

		Util::addScript(Application::APP_ID, 'linkvertise-files-action');
	}
}
