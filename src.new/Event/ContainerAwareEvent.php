<?php
namespace LynkCMS\Component\Event;

use LynkCMS\Component\Container\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;

class ContainerAwareEvent extends Event {
	use ContainerAwareTrait;
}