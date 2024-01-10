<?php

namespace App\Controller;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security; 
use Symfony\Component\Routing\Annotation\Route;



class SecurityController extends AbstractController
{
    #[Route('/security', name: 'app_security')]
    public function index(): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
* @Route("/", name="user_registration")


*/
public function register(Request $request,
UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $authenticationUtils)
{
// 1) build the form
$user = new User();
$form = $this->createForm(UserType::class, $user);
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
    $user->setPassword($password);

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($user);
    $entityManager->flush();

    // Login the user after registration
    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    $this->get('security.token_storage')->setToken($token);
    $this->get('session')->set('_security_main', serialize($token));

    return $this->redirectToRoute('show', ['id' => $user->getId()]);
}

// get the login error if there is one
$error = $authenticationUtils->getLastAuthenticationError();
// last username entered by the user
$lastUsername = $authenticationUtils->getLastUsername();

return $this->render('security/form.html.twig', [
    'form' => $form->createView(),
    'last_username' => $lastUsername,
    'errors' => $error,
]);
}


/**
 * @Route("/show/{id}", name="show")
* @ParamConverter("user", class="App\Entity\User")
  * @Security("is_authenticated()")
 */
public function show2(User $user): Response
{
    return $this->render('security/show.html.twig', [
        'user' => $user,
    ]);
}


/**
* @Route("/login", name="login")



*/
public function login(AuthenticationUtils $authenticationUtils)
{
// get the login error if there is one
$error = $authenticationUtils->getLastAuthenticationError();
// last username entered by the user
$lastUsername = $authenticationUtils->getLastUsername();
return $this->render('security/login.html.twig', [
'last_username' => $lastUsername,
'errors' => $error,
]);
}

/**
* @Route("/users", name="users")
* @Security("is_authenticated()")
*/
public function users()  {
    return $this->render('security/users.html.twig',[
        'controller_name'=>"AdminController",
    ]);
    
}

/**
* @Route("/security_logout", name="security_logout")
*/
public function logoutAction()  {
    return $this->redirectToRoute('login');
    
}
}
