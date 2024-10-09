<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Concerns;

use OpenApi\Annotations\OpenApi;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait ComparesSpecs
{
    /**
     * Compare OpenApi specs assuming strings to contain YAML.
     *
     * @param array|OpenApi|\stdClass|string $actual     The generated output
     * @param array|OpenApi|\stdClass|string $expected   The specification
     * @param bool                           $normalized flag indicating whether the inputs are already normalized or
     *                                                   not
     */
    protected function assertSpecEquals($actual, $expected, string $message = '', bool $normalized = false): void
    {
        $formattedValue = function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            if (is_numeric($value)) {
                return (string) $value;
            }
            if (is_string($value)) {
                return '"' . $value . '"';
            }
            if (is_object($value)) {
                return get_class($value);
            }

            return gettype($value);
        };

        $normalizeIn = function ($in) {
            if ($in instanceof OpenApi) {
                $in = $in->toYaml();
            }

            if (is_string($in)) {
                // assume YAML
                try {
                    $in = Yaml::parse($in);
                } catch (ParseException $e) {
                    $this->fail('Invalid YAML: ' . $e->getMessage() . PHP_EOL . $in);
                }
            }

            return $in;
        };

        if (!$normalized) {
            $actual = $normalizeIn($actual);
            $expected = $normalizeIn($expected);
        }

        if (is_iterable($actual) && is_iterable($expected)) {
            foreach ($actual as $key => $value) {
                $this->assertArrayHasKey($key, (array) $expected, $message . ': property: "' . $key . '" should be absent, but has value: ' . $formattedValue($value));
                $this->assertSpecEquals($value, ((array) $expected)[$key], $message . ' > ' . $key, true);
            }
            foreach ($expected as $key => $value) {
                $this->assertArrayHasKey($key, (array) $actual, $message . ': property: "' . $key . '" is missing');
                $this->assertSpecEquals(((array) $actual)[$key], $value, $message . ' > ' . $key, true);
            }
        } else {
            $this->assertEquals($expected, $actual, $message);
        }
    }
}
