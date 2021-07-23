<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/*
 * Descripción: Es clase la que define el control personalizado combo distribuciones en el paso nueva distribución 
 */
class DistribucionFormType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('distribuciones', ChoiceType::class, [
            "row_attr" => [
                "class" => "form-group"
            ],
            'choices' => $options['allowed_distribuciones'],
            'attr' => [
                'class' => 'dropdown',
                'style' => 'width: 100%'
            ],
            'label' => 'Conjuntos de datos*',
            'placeholder' => 'Selecciona una opción...',
            'required' => false,
            'help_html' => true,
            'help'=>'Si lo que deseas es añadir una nueva distribución a un conjunto de datos ya existente, selecciona el conjunto de datos y pulsa "Siguiente".<br>
                     Si lo que deseas es crear un nuevo conjunto de datos, selecciona "Nuevo conjunto de datos" y pulsa "Siguiente".'
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function getName()
    {
        return '';
    }
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allowed_distribuciones' => null,
            'csrf_protection' => false,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);

        $resolver->setAllowedTypes('allowed_distribuciones', ['null', 'null', 'array']);

        $resolver->setNormalizer('allowed_distribuciones', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states),array_keys($states));
        });
    }

    public function validate(array $data, ExecutionContextInterface $context,$payload): void
    {
        if (empty($data['distribuciones'])) {
            $context->buildViolation('El conjunto de datos no puede estar vacío, selecciona una opción según la ayuda')
                ->atPath("[distribuciones]")
                ->addViolation();
        }
        
    }
}