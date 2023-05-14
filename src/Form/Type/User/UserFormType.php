<?php

namespace App\Form\Type\User;

use App\Entity\Main\Role;
use App\Entity\Main\RoleGroup;
use App\Form\Model\User\UserDto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatar')
            ->add('base64File', TextType::class)
            ->add('fileName', null, array(
                'mapped' => false
            ))

            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('active')
            ->add('dni')
            ->add('expiredAt')
            ->add('roleGroups', EntityType::class, array(
                'class'        => RoleGroup::class,
                'multiple'     => true,
                'expanded' => true,
                'label' => false,
                'choice_value' => 'uuid'
            ))
            ->add('roles', EntityType::class, array(
                'class'        => Role::class,
                'multiple'     => true,
                'expanded' => true,
                'label' => false,
                'choice_value' => 'uuid'
            ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {

            $data = $event->getData();

            $form = $event->getForm();

            if (!$data->getUuid()) {
                $form->add('password', PasswordType::class, array(
                    // 'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => array(
                        new NotBlank(array(
                            'message' => 'Please enter a password',
                        )),
                        new Length(array(
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        )),
                    ),
                ));
            } else {
                //TODO implement method for update unitOwner in same form
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserDto::class,
            'csrf_protection' => false,
            'validation_groups' => ["Default"]
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
