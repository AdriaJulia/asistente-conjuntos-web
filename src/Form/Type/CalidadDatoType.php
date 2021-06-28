<?php
// src/Form/Type/PostalAddressType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;


/*
 * Descripción: Es clase la que define el control personalizado "teritorio" en el paso 1.1  
 */
class CalidadDatoType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('descripcion',TextareaType::class,  
          [ 
            'attr' => [
                'placeholder' => '',
                'style' => 'display:block',
            ],
            'label' => 'Descripción',
            'help' => 'Si has utilizado alguna metodología para controlar la calidad de los datos este es el lugar para explicarla, por ejemplo normas ISO, normas concretas etc',
            'required' => false
         ])
         ->add('servicios', TextType::class,[
            "row_attr" => [
                'id' => 'divservicios',
                'class' => 'form-group',
                "style" => "margin-top: 20px;"
            ],
            "attr"=>[
                'id' => 'inputservicios',
                'data-role' => 'tagsinput',
                'placeholder' => 'Inserta una URL y pulsa ENTER',
           ],
           "label" => "URLs",
           "required" => false,
           'help'=>'Si tus metodologías de control de calidad están explicadas en un enlace externo copia aquí la dirección o direcciones.'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'my_calidad_dato';
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