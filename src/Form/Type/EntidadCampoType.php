<?php
// src/Form/Type/PostalAddressType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;

/*
 * Descripción: Es clase la que define el control personalizado consistente en:
 *    Nombre del campo
 *    Combo con las entidades principales a seleccionar
 *    Botón para asignar.
 * 
 * El funcionamiento del botón y la composicion del json , etc.. se realiza con javascript
 *             
 */
class EntidadCampoType extends AbstractType
{
  
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder->add('alineacion', ChoiceType::class, [
                      'choices' =>  $options['allowed_ontologias'],
                      'attr' => [
                          'class' => 'select medium',
                      ],
                      'label_attr' =>  [
                          "style" =>"display:none"
                      ],
                     'placeholder' => 'Introduzca...',
                     'required' => false
        ]);
        $builder->add('boton', ButtonType::class, array(
                      'label'  => 'Asignar Atributo',
                      'attr' => [
                       'class' => 'btnAsignar',
                      ],
        ));
    }

    public function getBlockPrefix()
    {
        return 'my_alineacion';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
            // this defines the available options and their default values when
        // they are not configured explicitly when using the form type
        $resolver->setDefaults([
            'allowed_ontologias' => null,
            'atribute_name' => null,
            'csrf_protection' => false,
        ]);

        $resolver->setAllowedTypes('allowed_ontologias', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('atribute_name','string');

        $resolver->setNormalizer('allowed_ontologias', static function (Options $options, $states) {
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