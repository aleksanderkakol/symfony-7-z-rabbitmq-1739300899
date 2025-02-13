<?php

namespace App;

use App\Core\Service\Messenger\Attribute\AsCommandValidator;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->registerValidators($container);
    }

    private function registerValidators(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            AsCommandValidator::class,
            static function (
                ChildDefinition $definition,
                AsCommandValidator $attribute,
                \Reflector $reflector
            ): void {
                $tagAttributes = get_object_vars($attribute);

                if ($reflector instanceof \ReflectionMethod) {
                    if (isset($tagAttributes['method'])) {
                        throw new LogicException(
                            sprintf(
                                'AsCommandValidator attribute cannot declare a method on "%s::%s()".',
                                $reflector->class,
                                $reflector->name
                            )
                        );
                    }
                    $tagAttributes['method'] = $reflector->getName();
                }

                $definition->addTag('command.command_validator', $tagAttributes);
            }
        );
    }
}
