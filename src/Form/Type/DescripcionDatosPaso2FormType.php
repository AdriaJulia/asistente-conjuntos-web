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
                'placeholder' => 'Seleccione una opción...',
                'required' => true,
                'help'=>'Seleccione una organización entre las disponibles. En esta sección se muestran la organización encargada de la publicación de este conjunto de datos tal y cómo se facilitarán a los usuarios. Esta información se ha confeccionado con los datos aportados al dar de alta la organización publicadora, para modificarla utiliza la pizarra de administración de tu organización.'
            ])
            ->add('finalidad', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => FinalidadDatosEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown'
                ],
                'placeholder' => 'Seleccione una opción...',
                'required' => true,
                'help'=>'Estos son los temas conforme a la Norma Técnica de Interoperabilidad: elige el que crea que se adapta mejor a la información que contiene tu conjunto de datos.'
            ])
            ->add('condiciones', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'placeholder' => 'Escriba condiciones de uso de los datos',
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
                    'placeholder' => 'Escriba licencias aplicables de uso de los datos',
                    "spellcheck"=>"true"
                ],
                'label' => 'Licencia tipo aplicable',
                'required' => false,
                'help_html' => true,
                'help'=>'Para promover la máxima reutilización, en Aragón Open Data establecemos por defecto una licencia Creative Commons Attribution 4.0 según se expone en la sección <a href="http://opendata.aragon.es/terminos">Términos de uso</a>. Si tu conjunto de datos por alguna razón legal, contractual o de otro tipo no puede ser ofrecido con esta licencia escríbenos a opendata@aragon.es y la modificaremos'
                ])
            ->add('vocabularios', TextType::class,[
                "row_attr" => [
                    'id' => 'divvocabularios',
                    "class" => "form-group",
                ],
                "attr"=>[
                    'id' => 'inputvocabularios',
                    'data-role' => 'tagsinput',
                    'placeholder' => 'Inserte url y pulse enter'
                ],
                "required" => false
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
                    'placeholder' => 'Inserte url y pulse enter',
               ],
               "label" => "Servicios y estándares implicados",
               "required" => false,
               'help'=>' Si has utilizado alguna metodología para controlar la calidad de los datos este es el lugar para explicarla, por ejemplo normas ISO, normas concretas… etc Si tus metodologías de control de calidad están explicadas en un enlace externo copia aquí la dirección o direcciones.' 
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