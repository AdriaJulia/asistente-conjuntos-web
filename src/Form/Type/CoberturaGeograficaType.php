<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/*
 * Descripción: Es clase la que define el control personalizado "territorio" en el paso 1 
 */
class CoberturaGeograficaType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('aragon', RadioType::class, array(
            'attr' => [
                'class'=>'form-check-input',
                'style'=>'display: inline-block;position: relative;'
            ],
            'label_attr' => [
                'class'=>'form-check-label',
                'style'=>'display: inline-block;position: absolute;'
             ],
            'label'    => 'Aragón',
            'required' => false
        ))
        ->add('provincia', RadioType::class, array(
            'attr' => [
                'class'=>'form-check-input',
                'style'=>'display: inline-block;position: relative;'
            ],
            'label_attr' => [
                'class'=>'form-check-label',
                'style'=>'display: inline-block;position: absolute;'
             ],
            'label'    => 'Provincia de',
            'required' => false
        ))
        ->add('provincias', TextType::class,
        [
            'attr' => [
                'placeholder' => 'Escribe el nombre y selecciona entre los valores propuestos...',
                'style' => 'display:block',
                'class' =>'povinciasAutoComplete',
            ],
            'label' => ' ',
            'required' => false

        ])
        ->add('comarca', RadioType::class, array(
            'attr' => [
                'class'=>'form-check-input',
                'style'=>'display: inline-block;position: relative;'
            ],
            'label_attr' => [
                'class'=>'form-check-label',
                'style'=>'display: inline-block;position: absolute;'
             ],
            'label'    => 'Comarca de',
            'required' => false
        ))
        ->add('comarcas', TextType::class,
        [
             'attr' => [
                'placeholder' => 'Escribe el nombre y selecciona entre los valores propuestos...',
                'class' =>'comarcasAutoComplete',
             ],
             'label' => ' ',
             'required' => false
        ])
        ->add('municipio', RadioType::class, array(
            'attr' => [
                'class'=>'form-check-input',
                'style'=>'display: inline-block;position: relative;'
            ],
            'label_attr' => [
                'class'=>'form-check-label',
                'style'=>'display: inline-block;position: absolute;'
             ],
            'label'    => 'Municipio de',
            'required' => false
        ))
        ->add('municipios', TextType::class,
        [
            'attr' => [
               'placeholder' => 'Escribe el nombre y selecciona entre los valores propuestos...',
               'class' =>'municipiosAutoComplete',
            ],
            'label' => ' ',
            'required' => false
        ])
        ->add('otro', RadioType::class, array(
            'attr' => [
                'class'=>'form-check-input',
                'style'=>'display: inline-block;position: relative;'
            ],
            'label_attr' => [
                'class'=>'form-check-label',
                'style'=>'display: inline-block;position: absolute;'
             ],
            'label'    => 'Otro',
            'required' => false
        ))
        ->add('otros',TextType::class,
          [ 
            'attr' => [
                'placeholder' => 'Escribe texto...',
             ],
            'label' => ' ',
            'required' => false
         ]);
    }

    public function getBlockPrefix()
    {
        return 'my_Territorio';
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
        if ($data['provincia'] && empty($data['provincias']) ) {
            $context->buildViolation('La provincia no puede estar vacía')
                ->atPath("[provincias]")
                ->addViolation();
        }

        if ($data['comarca'] && empty($data['comarcas']) ) {
            $context->buildViolation('La comarca no puede estar vacía')
                ->atPath("[comarcas]")
                ->addViolation();
        }

        if ($data['municipio'] && empty($data['municipios']) ) {
            $context->buildViolation('El municipio no puede estar vacío')
                ->atPath("[municipios]")
                ->addViolation();
        }

        if ($data['otro'] && empty($data['otros']) ) {
            $context->buildViolation('El municipio no puede estar vacío')
                ->atPath("[otros]")
                ->addViolation();
        }
    }
}