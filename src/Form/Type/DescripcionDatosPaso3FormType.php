<?php

namespace App\Form\Type;

use App\Form\Model\DescripcionDatosDto;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Descripción: Es clase la que define el formulario paso 1.3 de la descripcion de los datos de los datos          
 */

class DescripcionDatosPaso3FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('estructura', TextareaType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                "attr"=>[
                    'placeholder' => 'Escribe un texto detallado...',
                    "spellcheck"=>"true"
                ],
                'required' => false,
                'help' => 'Introduce un resumen sobre como se encuentran estructurados los datos.'
            ])
            ->add('estructuraDenominacion', TextareaType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                "attr"=>[
                    'placeholder' => 'Escribe un texto detallado...',
                    "spellcheck"=>"true"
                ],
                'required' => false,
                 'label'=>'Denominación y orden de aspectos formales como las instancias o entidades que ofrece',
                 'help' => 'Introduce una breve descripción sobre la granularidad de los datos.'
             ])
            ->add('formatos', TextareaType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                "attr"=>[
                    'placeholder' => 'Escribe un texto detallado...',
                    "spellcheck"=>"true"
                ],
                'required' => false,
                'label'=>'Formatos disponibles',
                'help' => 'Introduce una breve descripción sobre los formatos.'
            ])
            ->add('etiquetas', TextType::class,[
                "row_attr" => [
                    'id' => 'divetiquetas',
                    "class" => "form-group"
                ],
                "attr"=>[
                    'id' => 'inputetiquetas',
                    'data-role' => 'tagsinput',
                    'placeholder' => 'Inserta una y pulsa ENTER'
                ],
                "required" => false,
                'label'=>'Etiquetas',
                'help' => 'Introduce el texto, selecciona de las opciones y pulsa enter para añadir una.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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