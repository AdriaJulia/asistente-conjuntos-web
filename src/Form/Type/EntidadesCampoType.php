<?php
// src/Form/Type/PostalAddressType.php
namespace App\Form\Type;

use App\Form\Type\EntidadCampoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormBuilderInterface;


/*
 * Descripción: Es clase la que define el control que contiene el conjunto de controles
 * EntidadeCampoType. Symfony pide un control para unificar. 
*/

class EntidadesCampoType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $contador =0;
        foreach($options['allowed_campos'] as $campo){
            $name = "campoalineado_{$contador}";
            $builder->add( $name, EntidadCampoType::class,[
                "row_attr" => [
                    "class" => "form-group form-atributo",
                    "name" => $campo
                ],
                "atribute_name" => $campo,
                "allowed_ontologias" => $options['allowed_ontologias'],
                "label" =>  $campo,
                'required' => false
            ]);
            $contador++;
        }
    }

    public function getBlockPrefix()
    {
        return 'my_alineaciones';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
            // this defines the available options and their default values when
        // they are not configured explicitly when using the form type
        $resolver->setDefaults([
            'allowed_campos' => null,
            'allowed_ontologias' => null,
            'atribute_name' => null,
            'csrf_protection' => false,
        ]);

        
        $resolver->setAllowedTypes('allowed_campos', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('allowed_ontologias', ['null', 'string', 'array']);
        
        $resolver->setNormalizer('allowed_campos', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states), array_values($states));
        });

               
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