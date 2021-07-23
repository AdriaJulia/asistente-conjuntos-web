<?php

namespace App\Form\Type;

use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoOrigenDatosEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

/*
 * Descripción: Es clase la que define el formulario paso 3 en su formato fichero      
 */
class OrigenDatosFileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoOrigen', ChoiceType::class, [
                 "row_attr" => [
                    "class" => "form-group"
                  ],
                  'choices' => TipoOrigenDatosEnum::getValues(),
                  'label'=>'Acceso al recurso:',
                  'help' => 'Seleccione la forma de cómo se va a obtener el recurso.',
                  'attr' => ['class' => 'select big'],
                  'data' => 'file'
            ])
            ->add('nombre', textType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'class' => 'select big',
                    'placeholder' => 'Escribe un texto',  
                ],
                'label'=>'Nombre:',
                'help' => 'Introduce un nombre descriptivo puedes utilizar el mismo nombre que para el conjunto de datos.',
                'required' => true
            ])
            ->add('descripcion', TextareaType::class,[
                "row_attr" => [
                  "class" => "form-group"
                ],
                'attr' => [
                    'class' => 'select big',
                    'placeholder' => 'Introduce un nombre descriptivo',  
                ],
                'label'=>'Descripción:',
                'help' => 'Si deseas dar más detalle sobre los datos',
                'required' => false
             ])
            ->add('archivo', FileType::class, [
                'label' => 'Selecciona un archivo de tipo XML, JSON, CSV, XLS, o XLSX',
                'row_attr' => array(
                    'style' => 'margin-bottom: 30px;',
                    "class" => "form-group",
                    "id" => "selectorarchivo",
                ),  
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypesMessage' => 'Por favor seleccione un archivo de los formatos señados valido',
                    ])
                ], 
            ]);
            $builder->add('modoFormulario', HiddenType::class,[
                "row_attr" => [
                    "id" => "modoFormulario",
                    "class" => "form-group",
                ],
                "data" => "test"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrigenDatosDto::class,
            'csrf_protection' => false,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
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

    public function validate($data, ExecutionContextInterface $context, $payload): void
    {
        if (empty($data->nombre)) {
            $context->buildViolation('El nombre no puede estar vacío')
            ->atPath('nombre')
            ->addViolation();
        }
        $file = $context->getObject()->get('archivo')->getData();
        if ($file==null && $data->modoFormulario=="test") {
                $context->buildViolation('Por favor inserte una archivo')
                ->atPath('archivo')
                ->addViolation();
        } else if ($data->modoFormulario=="test") {
           $originalName = $file->getClientOriginalName();
           $ext = explode(".", $originalName);
           $pos = count($ext) -1;
           $extesionNombre = $ext[$pos];
           switch ($extesionNombre) {
                case 'xml':
                    $mime = "application/xml";
                    break;
                case 'json':
                    $mime = "application/json";
                    break;
                case 'x-json':
                    $mime = "application/json";
                    break;
                case 'csv':
                    $mime =  "text/csv";;
                    break;
                case 'xls':
                    $mime = "application/xls";
                    break;
                case 'x-xls':
                    $mime = "application/xls";
                break;
                case 'xlsx':
                    $mime = "application/xlsx";
                    break;
                case 'x-xlsx':
                    $mime = "application/xlsx";
                    break;
            }
            if (empty($mime)) {
                $context->buildViolation("Por favor, selecciona un archivo que cumpla con alguno de los formatos indicados como válidos.")
                ->atPath('archivo')
                ->addViolation();
            }
         }
    }
}