<?php

declare(strict_types=1);

namespace Sf\Xai\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Sf\Whisper\Sources\AudioSourceInterface;
use Sf\Xai\WhisperProvider;

final class WhisperProviderTest extends TestCase
{
    public function testRejectsEmptyApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The xAI API key must not be empty.');

        new WhisperProvider('', $this->createStub(ClientInterface::class), new HttpFactory());
    }

    public function testTranscribesAudioAndSendsExpectedRequest(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request): Response {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame(WhisperProvider::ENDPOINT, (string) $request->getUri());
                $this->assertSame(['Bearer test-api-key'], $request->getHeader('Authorization'));

                $body = $request->getBody()->getContents();
                $this->assertStringContainsString('name="file"', $body);
                $this->assertStringContainsString('audio-data', $body);

                return new Response(200, [], 'Transcribed text');
            });

        $audio = Utils::streamFor('audio-data');
        $provider = new WhisperProvider('test-api-key', $client, new HttpFactory());

        $transcript = $provider->transcribe($this->audioSource($audio));

        $this->assertSame('Transcribed text', $transcript->getText());
    }

    private function audioSource(StreamInterface $stream): AudioSourceInterface
    {
        return new class ($stream) implements AudioSourceInterface {
            public function __construct(private StreamInterface $stream)
            {
            }

            public function extract(): StreamInterface
            {
                return $this->stream;
            }
        };
    }
}
