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
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

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
                    'placeholder' => 'Escribe un texto',
                ],
                'label' => 'Denominación*',
                'required' => false,
                'help'=>'Debe ser único porque se va a convertir en su identificador.' 
            ])
            ->add('descripcion', TextareaType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'spellcheck' => 'true',
                    'placeholder' => 'Escribe un texto detallado',
                 ],
                'required' => false,
                'label' => 'Descripción',
                'help'=>'La descripción es la primera aproximación de un usuario a tu conjunto de datos, así que se debería comenzar contando brevemente qué contiene el mismo. Si el conjunto de datos contiene informaciones parciales, limitaciones o deficiencias, este es el lugar en el que puedes mencionarlas de forma que los usuarios puedan saber el alcance de la información.'
            ])
            ->add('frecuenciaActulizacion', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => FrecuenciaActualizacionEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown',
                ],
                'placeholder' => 'Selecciona una opción...',
                'help'=>'Este campo sirve para indicar con qué frecuencia (Anual, Semestral, Cuatrimestral, Trimestral, Mensual, Diaria o Instantánea) se actualiza la información aquí contenida.',
                'label' => 'Frecuencia de actualización',
                'required' => false])
            ->add('fechaInicio', DateType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'placeholder' => 'Escoge una fecha...',
                    'class' => 'datepicker',
                    'class' => 'datepicker',
                    'dateFormat' => 'yy-mm-dd',
               ],
               'help'=>'',
               'label' => 'Fecha de inicio',
               'placeholder' => 'Escoge una fecha...',
               'required' => false,
            ])
            ->add('fechaFin', DateType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'placeholder' => 'Escoge una fecha o déjalo en blanco...',
                    'class' => 'datepicker',
                    'dateFormat' => 'yy-mm-dd',
                ],
                'label' => 'Fecha final',
                'required' => false,
                'help'=>'Si tu conjunto de datos está vivo y se va refrescando a medida que pasa el tiempo, deja este campo en blanco. Si has indicado una fecha de inicio, en ese caso entenderemos que tu conjunto de datos contiene información desde la fecha que indiques hasta la actualidad.'
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
                'help' => 'Selecciona el ámbito deseado y si es "Provincia", "Comarca" o "Localidad", escribe el nombre y la función de autocompletado te permitirá seleccionar el valor normalizado del que disponemos. En el caso de no encontrar el nombre requerido, introduce el valor en "Otros".'
              ]
            );
            /*
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
            )
            */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'help' => null,
            'data_class' => DescripcionDatosDto::class,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
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

    public function validate($data, ExecutionContextInterface $context,$payload): void
    {
       if (empty($data->denominacion)){
            $context->buildViolation('La denominación no puede estar vacía')
            ->atPath("[denominacion]")
            ->addViolation();
       }
       if (empty($data->descripcion)){
            $context->buildViolation('La descripción no puede estar vacía')
            ->atPath("[descripcion]")
            ->addViolation();
       }

    }
}