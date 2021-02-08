<?php
namespace LynkCMS\Component\Event;

use LynkCMS\Component\Container\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContainerAwareEventSubscriber implements EventSubscriberInterface {
	use ContainerAwareTrait;
	public static function getSubscribedEvents() {
		return Array();
	}
}