<?php

declare(strict_types=1);

namespace App\Core\Service\Messenger\Middleware;

use App\Core\Service\Messenger\Attribute\AsCommandValidator;
use App\Exception\ValidateFail;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class ValidateMiddleware implements MiddlewareInterface
{
    public const string COMMAND_SUFFIX = 'Command';
    public const string VALIDATOR_SUFFIX = 'Validator';

    public function __construct(
        #[TaggedLocator(tag: 'command.command_validator', indexAttribute: 'key')]
        private readonly ServiceLocator $locator,
        private readonly LoggerInterface $messengerLogger,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $isReceivedMessage = (bool) $envelope->all(ReceivedStamp::class);
        $message = $envelope->getMessage();
        $validatorName = $this->createServiceName($message::class, self::COMMAND_SUFFIX, self::VALIDATOR_SUFFIX);

        if ($this->locator->has($validatorName)) {
            $validatorService = $this->locator->get($validatorName);

            $this->messengerLogger->info('[ValidatorMiddleware] Run validator {validator}', [
                'validator' => $validatorService::class,
                'command' => $message::class,
            ]);

            try {
                $this->runService($validatorService, $message);
            } catch (ValidateFail $validateFail) {
                if ($isReceivedMessage) {
                    $this->messengerLogger->warning(
                        'Message validate fail',
                        [
                            'message' => $message,
                            'validator' => $validateFail::class,
                            'validateMessage' => $validateFail->getMessage(),
                        ]
                    );

                    return $envelope;
                }

                throw $validateFail;
            }
        } else {
            $this->messengerLogger->info('[ValidatorMiddleware] Validator not found', ['service' => $validatorName]);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function runService(object $validator, object $command): void
    {
        $validatorReflection = new \ReflectionClass($validator);
        $validatorAttributeReflections = $validatorReflection->getAttributes(AsCommandValidator::class);
        /** @var AsCommandValidator $asCommandValidator */
        $asCommandValidator = $validatorAttributeReflections[0]->newInstance();

        if ($asCommandValidator->method) {
            $this->messengerLogger->debug(
                '[ValidatorMiddleware] Run validator with method',
                ['validator' => $validator::class, 'method' => $asCommandValidator->method]
            );
            $validator->{$asCommandValidator->method}($command);

            return;
        }
        if (!\is_callable($validator)) {
            throw new \RuntimeException('Class '.$validator::class.' must be callable');
        }

        $this->messengerLogger->debug('[ValidatorMiddleware] Run validator as callable',
            ['validator' => $validator::class]);
        $validator($command);
    }

    /**
     * @throws \RuntimeException
     */
    private function createServiceName(string $commandClassName, string $commandSuffix, string $serviceSuffix): string
    {
        $commandSuffixLength = mb_strlen($commandSuffix);
        if (mb_substr($commandClassName, -$commandSuffixLength) !== $commandSuffix) {
            throw new \RuntimeException(sprintf('Class %s is no command class', $commandClassName));
        }

        return mb_substr($commandClassName, 0, -$commandSuffixLength).$serviceSuffix;
    }

}
