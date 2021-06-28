<?php
// src/Form/Type/PostalAddressType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/*
 * Descripción: Es clase la que define el control personalizado "teritorio" en el paso 1.1  
 */
class CoberturaTemporalType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('fechaInicio', DateType::class, [
            "row_attr" => [
                "class" => "form-group fecha-inicio-group"
            ],
            'invalid_message' => 'La fecha introducida no tiene un formato correcto',
            'widget' => 'single_text',
            'html5' => false,
            'label_attr' => [
                'style' => 'display: inline-block;margin-right: 10px;'
            ],
            'attr' => [
                'placeholder' => 'Escoge una fecha',
                'class' => 'datepicker',
                'style' => 'width: 190px;display: inline-block;',
                'dateFormat' => 'yy-mm-dd',
           ],
           'label' => 'Este conjunto de datos contiene información desde:', 
           'help'=>'',
           'required' => false,
        ])
        ->add('fechaFin', DateType::class, [
            "row_attr" => [
                "class" => "form-group fecha-fin-group"
            ],
            'label_attr' => [
                'style' => 'display: inline-block;margin-right: 10px;margin-left: 20px;'
            ],
            'invalid_message' => 'La fecha introducida no tiene un formato correcto',
            'widget' => 'single_text',
            'html5' => false,
            'attr' => [
                'placeholder' => 'Escoge una fecha',
                'class' => 'datepicker',
                'style' => 'width: 190px;display: inline-block;',
                'dateFormat' => 'yy-mm-dd',
            ],
            'label' => 'Hasta:',
            'required' => false,
            'help'=>''
        ]);
    }

    public function getBlockPrefix()
    {
        return 'my_cobertura_temporal';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
            // this defines the available options and their default values when
        // they are not configured explicitly when using the form type
        $resolver->setDefaults([
            'allowed_states' => null,
            'csrf_protection' => false,
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
}