<?php

declare(strict_types = 1);

namespace AppBundle\Twig\Extension;

use AppBundle\Helper\StringHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class StringExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('kebab', [$this, 'kebabFilter']),
            new TwigFilter('tel', [$this, 'telLink']),
            new TwigFilter('url', [$this, 'formattedUrl']),
        ];
    }

    /**
     * Kebab-cases a string
     *
     * @param string $string
     * @param string $glue
     * @param bool $lower
     * @param bool $removePunctuation
     * @return string
     */
    public function kebabFilter(string $string, string $glue = '-', bool $lower = true, bool $removePunctuation = true): string
    {
        return StringHelper::toKebabCase($string, $glue, $lower, $removePunctuation);
    }

    /**
     * Turns a phone number into a tel: link
     *
     * @param $phoneNumber
     * @return string
     */
    public function telLink(string $phoneNumber): string
    {
        return sprintf('tel:%s', StringHelper::removeWhitespaces($phoneNumber));
    }

    /**
     * Returns a pretty URL
     *
     * @param $url
     * @return string
     */
    public function formattedUrl($url): string
    {
        return StringHelper::toPrettyUrl($url);
    }
}
