<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;


/*
 * Descripción: Es clase la que define el control personalizado vocabularios  en el paso 2  
 */
class DiccionarioDatosType extends AbstractType
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
            'help' => 'En este campo puedes comentar y añadir cualquier clase de información que ayude a la comprensión del conjunto de datos.',
            'required' => false
         ])
         ->add('vocabularios', TextType::class,[
            "row_attr" => [
                'id' => 'divvocabularios',
                "class" => "form-group",
            ],
            "attr"=>[
                'id' => 'inputvocabularios',
                'data-role' => 'tagsinput',
                'placeholder' => 'Inserta una URL y pulsa ENTER'
            ],
            "label" => 'URLs',
            "required" => false,
            'help'=>'Si la información de interés se encuentra publicada en un enlace externo copia aquí la dirección o direcciones.'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'my_calidad_dato';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {

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