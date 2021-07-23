<?php

namespace App\Form\Type;
use Symfony\Component\Validator\Constraints\File;
use App\Form\Model\AlineacionDatosDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

/*
 * Descripción: Es la clase la que define el formulario de la alineación de los datos   
 *              cuando se selecciona alinear con un XML       
 */

class AlineacionDatosXmlFormType extends AbstractType
{

    function __construct(){
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('modoFormulario', HiddenType::class,[
            "row_attr" => [
                "class" => "form-group",
            ],
            "data"=>"seleccion",
            'required' => false
        ]);

        $builder->add('tipoAlineacion', ChoiceType::class, [
            "row_attr" => [
                "class" => "form-group"
            ],   
            'choices' => ["Alineamiento mediante XML de mapeo"=>"xml", "Alineamiento mediante asignación de atributos"=>"campos"],
            'attr' => [
                'class' => 'select big',
                'onchange' => ''
            ],
           // 'placeholder' => 'Selecciona un modo de alineamiento...',
            'help'=>'',
            'label' => 'Seleccione',
            'data' => 'xml',
            'required' => true
        ]);

        $builder->add('archivo', FileType::class, [
            'label' => 'Selecciona un archivo de tipo XML',
            'row_attr' => array(
                'style' => 'margin-bottom: 30px;',
                "class" => "form-group",
                "id" => "selectorarchivo",
            ),  
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '10240k',
                    'mimeTypesMessage' => 'Por favor seleccione un archivo de los formatos señados valido',
                ])
            ], 
        ]);

        $builder->add('alineacionXml', HiddenType::class,[
            "row_attr" => [
                "class" => "form-group",
                'id' => 'alineacionXml'
            ],
            'required' => false,
            "label" => "Mapeo xml ",
            'help'=>'Introduce el mapeo de alineación EI2A entre tus campos y la ontologia seleccionada en formato XML'
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AlineacionDatosDto::class,
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
    
    public function validate($data, ExecutionContextInterface $context,$payload): void
    {
        if ($data->modoFormulario=="guardar") {
            $file = $context->getObject()->get('archivo')->getData();
            if ($file==null) {
                    $context->buildViolation('Por favor inserte una archivo')
                    ->atPath('archivo')
                    ->addViolation();
            } else  {
               $originalName = $file->getClientOriginalName();
               $ext = explode(".", $originalName);
               $pos = count($ext) -1;
               $extesionNombre = $ext[$pos];
               switch ($extesionNombre) {
                    case 'xml':
                        $mime = "application/xml";
                        break;
                }
                if (empty($mime)) {
                    $context->buildViolation("Por favor, selecciona un archivo que cumpla con alguno de los formatos indicados como válidos.")
                    ->atPath('archivo')
                    ->addViolation();
                }
                $errorxml = "";
                $alineacionXml = file_get_contents($file);
                libxml_use_internal_errors(true);
                $sxe = simplexml_load_string($alineacionXml);
                if (!$sxe) {
                    $errorxml= "XML mal formado, o no valido";
                    foreach(libxml_get_errors() as $error) {
                        $errorxml .=  "\t" . $error->message;
                    }
                }
                if (!empty($errorxml)){
                    $context->buildViolation($errorxml)
                    ->atPath('archivo')
                    ->addViolation();
                } else {
                    $data->alineacionXml = $alineacionXml;
                }
            }  
        }        
    }
}