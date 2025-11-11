<?php


namespace App\Tests\Trait;

use Symfony\Component\HttpFoundation\Response;

/**
 * Trait providing JSON response assertions for tests.
 */
trait JsonResponseAsserts
{
    /**
     * Asserts that the JSON response contains the expected key-value pairs.
     *
     * @param Response $response
     * @param array $expected
     * @return void
     */
    protected function assertJsonResponseContains(Response $response, array $expected): void
    {
        $this->assertJson($response->getContent(), 'Response is not valid JSON');

        $data = json_decode($response->getContent(), true);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $data, "Response missing key '$key'");
            $this->assertSame($value, $data[$key] ?? null, "Response value for '$key' does not match");
        }
    }
}
