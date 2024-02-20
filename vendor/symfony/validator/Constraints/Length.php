<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Length extends Constraint
{
    public const TOO_SHORT_ERROR = '9ff3fdc4-b214-49db-8718-39c315e33d45';
    public const TOO_LONG_ERROR = 'd94b19cc-114f-4f44-9cc4-4138e80a87b9';
    public const NOT_EQUAL_LENGTH_ERROR = '4b6f5c76-22b4-409d-af16-fbe823ba9332';
    public const INVALID_CHARACTERS_ERROR = '35e6a710-aa2e-4719-b58e-24b35749b767';

    protected const ERROR_NAMES = [
        self::TOO_SHORT_ERROR => 'TOO_SHORT_ERROR',
        self::TOO_LONG_ERROR => 'TOO_LONG_ERROR',
        self::NOT_EQUAL_LENGTH_ERROR => 'NOT_EQUAL_LENGTH_ERROR',
        self::INVALID_CHARACTERS_ERROR => 'INVALID_CHARACTERS_ERROR',
    ];

    /**
     * @deprecated since Symfony 6.1, use const ERROR_NAMES instead
     */
    protected static $errorNames = self::ERROR_NAMES;

    public $maxMessage = 'Cette valeur est trop longue. Elle devrait comporter {{ limit }} charactères ou moins.|Cette valeur est trop longue. Elle doit comporter {{ limit }} caractères ou moins.';
    public $minMessage = 'Cette valeur est trop courte. Elle devrait comporter {{ limit }} charactères ou plus.|Cette valeur est trop courte. Elle doit comporter {{ limit }} caractères ou plus.';
    public $exactMessage = 'Cette valeur doit avoir exactement {{ limit }} charactères.|This value should have exactly {{ limit }} caractères.';
    public $charsetMessage = "Cette valeur {{ charset }} n'est pas authorisée.";
    public $max;
    public $min;
    public $charset = 'UTF-8';
    public $normalizer;

    public function __construct(
        int|array $exactly = null,
        int $min = null,
        int $max = null,
        string $charset = null,
        callable $normalizer = null,
        string $exactMessage = null,
        string $minMessage = null,
        string $maxMessage = null,
        string $charsetMessage = null,
        array $groups = null,
        mixed $payload = null,
        array $options = []
    ) {
        if (\is_array($exactly)) {
            $options = array_merge($exactly, $options);
            $exactly = $options['value'] ?? null;
        }

        $min ??= $options['min'] ?? null;
        $max ??= $options['max'] ?? null;

        unset($options['value'], $options['min'], $options['max']);

        if (null !== $exactly && null === $min && null === $max) {
            $min = $max = $exactly;
        }

        parent::__construct($options, $groups, $payload);

        $this->min = $min;
        $this->max = $max;
        $this->charset = $charset ?? $this->charset;
        $this->normalizer = $normalizer ?? $this->normalizer;
        $this->exactMessage = $exactMessage ?? $this->exactMessage;
        $this->minMessage = $minMessage ?? $this->minMessage;
        $this->maxMessage = $maxMessage ?? $this->maxMessage;
        $this->charsetMessage = $charsetMessage ?? $this->charsetMessage;

        if (null === $this->min && null === $this->max) {
            throw new MissingOptionsException(sprintf('Either option "min" or "max" must be given for constraint "%s".', __CLASS__), ['min', 'max']);
        }

        if (null !== $this->normalizer && !\is_callable($this->normalizer)) {
            throw new InvalidArgumentException(sprintf('The "normalizer" option must be a valid callable ("%s" given).', get_debug_type($this->normalizer)));
        }
    }
}
