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
 * Descripción: Es clase la que define el control personalizado "lenguaje" en el paso 1  
 */
class IdiomasType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('lenguajes', ChoiceType::class, [
            'choices' => [
                'Español' => 'Español',
                'Inglés' => 'Inglés',
                'Francés' => 'Francés',
                'Lenguas Aragonesas' => 'Lenguas Aragonesas',
                'Otro' =>'Otro'
            ],
            'attr' => [
                'class'=>'checkboxOne',
                //'style'=>'display: inline-block;position: relative;'
            ],
            'label' => ' ',
            'expanded' => true,
            'multiple' => true,
            'required' => false
        ])
        ->add('otroslenguajes',TextType::class,  
          [ 
            'attr' => [
                'placeholder' => 'Escribe el idioma..',
                'style' => 'display:block',
            ],
            'label' => ' ',
            'required' => false
         ]);
    }

    public function getBlockPrefix()
    {
        return 'my_lenguajes';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allowed_states' => null,
            'csrf_protection' => false,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);

        $resolver->setNormalizer('allowed_states', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states), array_values($states));
        });
    }

    public function validate(array $data, ExecutionContextInterface $context,$payload): void
    {
        if (in_array("Otro", $data['lenguajes']) && empty($data['otroslenguajes'])) {
            $context->buildViolation('El lenguaje no pude estar vacío')
                ->atPath("[otroslenguajes]")
                ->addViolation();
        }
        
    }
}