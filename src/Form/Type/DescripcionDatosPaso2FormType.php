<?php

namespace App\Form\Type;



use App\Form\Type\CalidadDatoType;
use App\Form\Type\DiccionarioDatosTypeType;
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
 * Descripción: Es clase la que define el formulario paso 2 de la descripción de los datos de los datos          
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
            ->add('publicador', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' =>$this->clientHttprest->GetOrganismosPublicos(),
                'attr' => [
                    'class' => 'select big'
                ],
                'label' => 'Publicador*',
                'placeholder' => 'Seleccione una organización entre las disponibles',
                'required' => false,
                'help'=>'En esta sección se muestran la organización encargada de la publicación de este 
                         conjunto de datos tal y cómo se facilitarán a los usuarios. Esta información se ha confeccionado con los datos aportados al dar de alta 
                         la organización publicadora, para modificarla utiliza la pizarra de administración de tu organización.'
            ])
            ->add('calidadDato', CalidadDatoType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'label' => 'Calidad del dato',
                'required' => false,
            ])
            ->add('diccionarioDatos', DiccionarioDatosType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'label' => 'Diccionario de datos',
                'required' => false,
            ])
            ->add('licencias', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'placeholder' => "Creative Commons Attribution 4.0",
                    'readonly' => 'true',
                    "value" => "Creative Commons Attribution 4.0",
                    "spellcheck"=>"true"
                ],
                'label' => 'Licencia',
                'required' => false,
                'help_html' => true,
                'help'=>'Para promover la máxima reutilización, en Aragón Open Data establecemos por defecto una licencia Creative Commons Attribution 4.0 
                         según se expone en la sección "Términos de uso" (&nbsp;<span url="http://opendata.aragon.es/terminos" data="null" class="inlineCardView-content-wrap" 
                         contenteditable="false" draggable="true"><a class="sc-clWJBl jHCBBV" href="http://opendata.aragon.es/terminos" target="_blank" tabindex="0" 
                         role="button" data-testid="inline-card-resolved-view"><img class="smart-link-icon sc-jeSenI eNjdmv" 
                         src="https://opendata.aragon.es/assets/general/favicon.ico"></span><span>Aragón Open Data</span></span></a></span></span></span></span> ). 
                         Si tu conjunto de datos por alguna razón legal, contractual o de otro tipo no puede ser ofrecido con esta licencia escríbenos 
                         a <a href="mailto:opendata@aragon.es">opendata@aragon.es</a> y la modificaremos.</p>'
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
       if (empty($data->publicador)){ 
            $context->buildViolation('El publicador no puede estar vacío')
            ->atPath("publicador")
            ->addViolation();
       }

    }
}