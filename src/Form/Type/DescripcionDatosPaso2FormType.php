<?php

namespace App\Form\Type;

use App\Enum\FinalidadDatosEnum;

use App\Service\RestApiRemote\RestApiClient;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\Model\DescripcionDatosDto;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;
/*
 * Descripción: Es clase la que define el formulario paso 1.2 de la descripcion de los datos de los datos          
 */

class DescripcionDatosPaso2FormType extends AbstractType
{
    
    private $clientHttprest;

    function __construct(RestApiClient $clientHttprest){
        $this->clientHttprest = $clientHttprest;

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organoResponsable', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' =>$this->clientHttprest->GetOrganismosPublicos(),
                'attr' => [
                    'class' => 'dropdown'
                ],
                'label' => 'Órgano responsable*',
                'placeholder' => 'Selecciona una opción...',
                'required' => false,
                'help'=>'Esta información se ha confeccionado con los datos aportados al dar de alta la organización publicadora, para modificarla utiliza la pizarra de administración de tu organización.'
            ])
            ->add('finalidad', ChoiceType::class, [ 
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => FinalidadDatosEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown'
                ],
                'label' => 'Finalidad*',
                'placeholder' => 'Selecciona una opción...',
                'required' => false,
                'help'=>'Elige el que creas que se adapta mejor a la información que contiene tu conjunto de datos.'
            ])
            /*
            ->add('condiciones', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'placeholder' => 'Escribe el texto',
                    "spellcheck"=>"true"
                ],
                'label' => 'Condiciones de uso',
                'required' => false,
                'help'=>''
           ])
           
            ->add('licencias', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'placeholder' => "Escribe el texto...",
                    "spellcheck"=>"true"
                ],
                'label' => 'Licencia tipo aplicable y Condiciones de uso',
                'required' => false,
                'help_html' => true,
                'help'=>'Para promover la máxima reutilización, en Aragón Open Data establecemos por defecto una licencia Creative Commons Attribution 4.0 según se expone en la sección Términos de uso. Si tu conjunto de datos por alguna razón legal, contractual o de otro tipo no puede ser ofrecido con esta licencia escríbenos a opendata@aragon.es y la modificaremos.'
               ])*/
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
                "required" => false,
                'help'=>'Puedes introducir una o varias URLs y pulsar ENTER para añadir cada una.'
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
               "label" => "Servicios y estándares implicados",
               "required" => false,
               'help'=>'Puedes introducir una o varias URLs y pulsar ENTER para añadir cada una'
            ]);
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
       if (empty($data->organoResponsable)){ 
            $context->buildViolation('El órgano responsable no puede estar vacío')
            ->atPath("organoResponsable")
            ->addViolation();
       }
       if (empty($data->finalidad)){
            $context->buildViolation('La finalidad no puede estar vacía')
            ->atPath("finalidad")
            ->addViolation();
      }
    }
}