<?php

declare(strict_types=1);

namespace Magna\Plugins\Exceptions;

use RuntimeException;

/**
 * Thrown when a plugin's declared dependencies cannot be satisfied: a required
 * plugin is missing, a version constraint is unmet, two plugins conflict, or the
 * dependency graph contains a cycle. Messages are written for the plugin
 * developer/operator — they state what failed and how to resolve it.
 */
final class DependencyException extends RuntimeException {}
