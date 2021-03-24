<?php

namespace App\Form\Type; 

use App\Form\Type\TerritorioType;
use App\Form\Model\DescripcionDatosDto;
use App\Enum\FrecuenciaActualizacionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Descripción: Es clase la que define el formulario paso 1.1 de la descripcion de los datos de los datos          
 */

class DescripcionDatosPaso1FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $myCustomFormData = array(
            'aragon' => true, // checked
            'provincia' => false, // unchecked
            'comarca' => false, // unchecked
            'localidad' => false, // unchecked
            'otros' => false, // unchecked
        );

        $builder           
            ->add('denominacion', TextType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'placeholder' => 'Escriba denominación de los datos',
                ],
                'label' => 'Denominacion',
                'required' => true,
                'help'=>'Por favor, dale una denominación del conjunto de datos. El nombre que des al conjunto de datos es importante porque se convierte en su identificador.' 
            ])
            ->add('descripcion', TextareaType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'spellcheck' => 'true',
                    'placeholder' => 'Escriba decripción de los datos',
                 ],
                'help'=>'La descripción es la primera aproximación de un usuario a tu conjunto de datos, así que se debería comenzar contando brevemente qué contiene el mismo. Si el conjunto de datos contiene informaciones parciales, limitaciones o deficiencias este es el lugar en el que puedes mencionarlas de forma que los usuarios puedan saber el alcance de la información. En algunos casos los usuarios ayudan a mejorar la información, así que no desaproveches la oportunidad de acercarles la realidad del dato.'
               
            ])
            ->add('frecuenciaActulizacion', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => FrecuenciaActualizacionEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown',
                ],
                'placeholder' => 'Seleccione una opción...',
                'help'=>'Por favor, indica la frecuencia con la que se actualiza la información del conjunto de datos,',
                'label' => 'Frecuencia de actualización',
                'required' => false])
            ->add('fechaInicio', DateType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                    'dateFormat' => 'yy-mm-dd',
               ],
               'help'=>'Por favor, indica en este campo la fecha de inicio del periodo temporal del que contiene información tu conjunto de datos. Si tu conjunto de datos está vivo y se va refrescando a medida que pasa el tiempo, deja seleccionada la casilla de selección que aparece en la parte de "hasta…". En ese caso entenderemos que tu conjunto de datos contiene información desde la fecha que indiques hasta la actualidad.',
               'label' => 'Fecha Inicio',
               'required' => false,
            ])
            ->add('fechaFin', DateType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                    'dateFormat' => 'yy-mm-dd',
                ],
                'label' => 'Fecha Fin',
                'required' => false,
                'help'=>'Por favor, indica en este campo la fecha final del  periodo temporal del que contiene información tu conjunto de datos. Si tu conjunto de datos está vivo y se va refrescando a medida que pasa el tiempo, deja seleccionada la casilla de selección que aparece en la parte de "hasta…". En ese caso entenderemos que tu conjunto de datos contiene información desde la fecha que indiques hasta la actualidad.'
            ])
            ->add('territorio', HiddenType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
            ]) 
            ->add('territorios', TerritorioType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'label' => 'Territorio que abarcan',
                'data' =>  $myCustomFormData,
                'help'=>'Por favor introduce el ámbito geográfico del que tu conjunto de datos contiene información. Únicamente es posible escribir dentro de una de las opciones que se muestran y además hay que hacerlo con uno de los territorios que se da en los listados (salvo si se rellena el campo otro)',   
              ]
            )
            ->add('instancias', TextType::class,[
                    "row_attr" => [
                        "class" => "form-group",
                        "spellcheck"=>"true"
                ],
                "attr"=>[
                    'data-role' => 'tagsinput',
                    'placeholder' => 'Inserte url y pulse enter',
                ],
                'help' =>  'Puede introducir varias instancias el campo es multivalor',
                'label' => 'Instancias o entidades que representan',
                'required' => false
               ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'help' => null,
            'data_class' => DescripcionDatosDto::class,
            'csrf_protection' => false
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
}