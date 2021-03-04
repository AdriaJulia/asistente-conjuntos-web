<?php

namespace App\Form\Type;

use App\Service\RestApiRemote\RestApiClient;
use App\Form\Type\EntidadesCampoType;
use App\Form\Model\AlineacionDatosDto;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

class AlineacionDatosFormType extends AbstractType
{

    private $clientHttprest;

    function __construct(RestApiClient $clientHttprest){
        $this->clientHttprest = $clientHttprest;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('alineacionRelaciones', HiddenType::class,[
            "row_attr" => [
                "class" => "form-group",
            ],
            'required' => false
        ]);
        $builder->add('modoFormulario', HiddenType::class,[
            "row_attr" => [
                "class" => "form-group",
            ],
            "data"=>"seleccion",
            'required' => false
        ]);
        $builder->add('alineacionEntidad', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],   
                'choices' => $this->clientHttprest->GetOntologias(),
                'attr' => [
                    'class' => 'select big',
                    'onchange' => ''
                ],
                'placeholder' => 'Seleccione una entidad...',
                'help'=>'Por favor, indica la entidad con la que quieres relacionar tu origen de datos,',
                'label' => 'Seleccione',
                'required' => false
        ]);
        if (count($options['allowed_ontologias'])>0) {
            $builder->add('alineacionEntidades', EntidadesCampoType::class, [
                'label_attr' =>  [
                    "style" =>"display:none"
                ],
                'allowed_campos' => $options['allowed_campos'],
                'allowed_ontologias' => $options['allowed_ontologias'],
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allowed_campos' => null,
            'allowed_ontologias' => null,
            'data_class' => AlineacionDatosDto::class,
            'csrf_protection' => false,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
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
        if ($data->modoFormulario=="guardar" && !isset($data->alineacionRelaciones)) {
            $context->buildViolation('Por favor relacione algún campo con la entidad')
            ->atPath('alineacionEntidad')
            ->addViolation();
        } 
    }
}