<?php

namespace Sf\Xai;

use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Sf\Whisper\Providers\AbstractWhisperProvider;
use Sf\Whisper\Transcript;

final class WhisperProvider extends AbstractWhisperProvider
{
    /**
     * Xai API endpoint
     * @var string
     */
    public const ENDPOINT = 'https://api.x.ai/v1/stt';


    public function __construct(
        #[\SensitiveParameter]
        private readonly string $apiKey,
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
    )
    {
        if ($apiKey === '') {
            throw new \InvalidArgumentException('The xAI API key must not be empty.');
        }
    }

    protected function doTranscribe(StreamInterface $stream): Transcript
    {
        $multipart = new MultipartStream([
            ['name' => 'file', 'contents' => $stream, 'filename' => uniqid('transcript_file')]
        ]);

        $response = $this->client->sendRequest(
            $this->requestFactory->createRequest('POST', self::ENDPOINT)
                ->withHeader('Accept', 'application/json')
                ->withHeader('Authorization', 'Bearer '.$this->apiKey)
                ->withHeader(
                    'Content-type',
                    'multipart/form-data; boundary='.$multipart->getBoundary() 
                )
                ->withBody($multipart)
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('The xAI API returned an error: '.$response->getBody()->getContents());
        }

        return Transcript::create($response->getBody());
    }
}
