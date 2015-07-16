<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\ProfileCreationType;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Persistence\ObjectManager;

class UserController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "localeManager"          = @DI\Inject("claroline.common.locale_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
    *      "profilePropertyManager" = @DI\Inject("claroline.manager.profile_property_manager")
     * })
     */
    public function __construct(
        AuthenticationManager $authenticationManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        Request $request,
        UserManager $userManager,
        GroupManager $groupManager,
        RoleManager $roleManager,
        ObjectManager $om,
        ProfilePropertyManager $profilePropertyManager
    )
    {
        $this->authenticationManager = $authenticationManager;
        $this->formFactory = $formFactory;
        $this->localeManager = $localeManager;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->om = $om;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->profilePropertyManager = $profilePropertyManager;
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getUsersAction()
    {
        return $this->userManager->getAll();
    }

    /**
     * @View(serializerGroups={"api"})
     * profile_form_creation[fieldname] for the put request
     * profile_form_creation[platformRoles][] // for the list of roles
     */
    public function postUserAction()
    {
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');

        $profileType = new ProfileCreationType(
            $this->localeManager,
            array($roleUser),
            true,
            $this->authenticationManager->getDrivers()
        );
        $profileType->enableApi();

        $form = $this->formFactory->create($profileType);
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $roles = $form->get('platformRoles')->getData();
            $user = $form->getData();
            $this->userManager->createUser($user, true, $roles);

            return $user;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api"})
     * profile_form[fieldname] for the put request
     * profile_form[platformRoles][] // for the list of roles
     */
    public function putUserAction(User $user)
    {
        $roles = $this->roleManager->getPlatformRoles($user);
        $accesses = $this->profilePropertyManager->getAccessessByRoles(array('ROLE_ADMIN'));

        $formType = new ProfileType(
            $this->localeManager,
            $roles,
            true,
            true,
            $accesses,
            $this->authenticationManager->getDrivers()
        );

        $formType->enableApi();
        $userRole = $this->roleManager->getUserRoleByUser($user);
        $form = $this->formFactory->create($formType, $user);
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->roleManager->renameUserRole($userRole, $user->getUsername());
            $this->userManager->rename($user, $user->getUsername());

            if (isset($form['platformRoles'])) {
                //verification:
                //only the admin can grant the role admin
                //simple users cannot change anything. Don't let them put whatever they want with a fake form.
                $newRoles = $form['platformRoles']->getData();
                $this->userManager->setPlatformRoles($user, $newRoles);
            }

            return $user;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * @View()
     */
    public function deleteUserAction(User $user)
    {
        $this->userManager->deleteUser($user);

        return array('success');
    }

    /**
     * @View()
     */
    public function addUserRoleAction(User $user, Role $role)
    {
        $this->roleManager->associateRole($user, $role, false);

        return array('success');
    }

    /**
     * @View()
     */
    public function removeUserRoleAction(User $user, Role $role)
    {
        $this->roleManager->dissociateRole($user, $role);

        return array('success');
    }

    /**
     * @View()
     */
    public function addUserGroupAction(User $user, Group $group)
    {
        $this->groupManager->addUsersToGroup($group, array($user));

        return array('success');
    }

    /**
     * @View()
     */
    public function removeUserGroupAction(User $user, Group $group)
    {
        $this->groupManager->removeUsersFromGroup($group, array($user));

        return array('success');
    }
}
