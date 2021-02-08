<?php
namespace BGStudios\Component\Event;

use BGStudios\Component\Container\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;

class ContainerAwareEvent extends Event {
	use ContainerAwareTrait;
}