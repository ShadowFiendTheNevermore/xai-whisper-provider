# xAI Whisper Provider

## Commands

- Requires Docker with the PHP 8.4 Alpine development image. Run `make install` to build it and install Composer dependencies into the bind-mounted `vendor/` directory.
- The CI-equivalent check order is `make build`, `make lint`, then `make test`. `make lint` and `make test` run the already-built image without mounting the worktree.
- Run static analysis directly with `composer lint`; it is PHPStan at `level: max` over `src/` only.
- `composer test` invokes `phpunit tests`, but this repository currently has no `tests/` directory and the Dockerfile does not copy one into the image. Add/copy test files in the Docker build before relying on `make test` for new tests.
- Refresh only locked dependency versions with `make update-lock`.

## Provider Contract

- `Sf\Xai\WhisperProvider` in `src/` is the sole public class (`Sf\Xai\` PSR-4 namespace) and extends the `shadowfiend/whisper` base provider.
- Keep API calls injected through PSR-18 `ClientInterface` and PSR-17 `RequestFactoryInterface`; do not introduce a concrete HTTP client dependency.
- `AbstractWhisperProvider::transcribe()` owns and always closes the supplied audio stream, wrapping failures in `TranscribeException`; `doTranscribe()` implementations must not close that stream.
- Send xAI audio as a multipart `file` part to `https://api.x.ai/v1/stt` with the Bearer API key. Convert the successful response body to the required `Transcript` text value.
