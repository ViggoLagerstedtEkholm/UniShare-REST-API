<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Login;
use App\Models\Register;
use App\Models\Users;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Authentication controller for handling login/register/logout.
 * @author Viggo Lagestedt Ekholm
 */
class AuthenticationController extends Controller
{
    private Users $users;
    private Login $login;
    private Register $register;

    public function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['logout']));

        $this->users = new Users();
        $this->login = new Login();
        $this->register = new Register();
    }

    public function verify(Handler $handler)
    {
        $body =$handler->getRequest()->getBody();
        $email = $body['email'];
        $hash = $body['hash'];

        $result = $this->users->verifyUser($email);

        $verificationHash = $result[0]['verificationHash'];

        $verifiedUser = false;
        if($hash === $verificationHash){
            $verifiedUser = $this->users->setVerified($email);
        }

        if($verifiedUser){
            $handler->getResponse()->setStatusCode(200);
        }else{
            $handler->getResponse()->setStatusCode(500);
        }
    }

    /**
     * Login using cookie with session ID.
     */
    public function loginWithCookie()
    {
        $this->login->loginFromCOOKIE();
    }

    /**
     * Logout and redirect to start page.
     * @param Handler $handler
     */
    public function logout(Handler $handler)
    {
        $this->users->logout();
        $handler->getResponse()->setStatusCode(200);
    }

    /**
     * Get login status.
     * @param Handler $handler
     * @return bool|string
     */
    public function isLoggedIn(Handler $handler): bool|string
    {
        $isLoggedIn = Session::isLoggedIn();
        $resp = ['success' => true, 'data' => ['LoggedIn' => $isLoggedIn]];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * This method handles logging in a user.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function login(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $params = [
            'email' => $body["email"],
            'password' => $body["password"],
            'rememberMe' => $body['rememberMe']
        ];

        $response = $this->login->login($params);

        if ($response['success']) {
            $resp = ['userID' => Session::get(SESSION_USERID), 'privilege' => Session::get(SESSION_PRIVILEGE)];
            return $handler->getResponse()->jsonResponse($resp, 200);
        }

        return $handler->getResponse()->jsonResponse($response['ERRORS'], 403);
    }

    /**
     * This method handles registering in a user.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function register(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $params = [
            'first_name' => $body["first_name"],
            'last_name' => $body["last_name"],
            'email' => $body['email'],
            'display_name' => $body['display_name'],
            'password' => $body['password'],
            'password_repeat' => $body['password_repeat'],
        ];

        $validationErrors = $this->register->validate($params);

        if (count($validationErrors) > 0) {
            return $handler->getResponse()->jsonResponse($validationErrors, 422);
        }

        $result = $this->register->register($params);

        $didRegister = $result['registered'];
        $verificationHash = $result['hash'];

        if($didRegister){
            $verificationLink = "http://localhost:3000/verify/" . $params['email'] . "/" . $verificationHash;

            $mail = new PHPMailer(true);

            $sentVerification = $this->sendVerificationMail($mail, $verificationLink, $params['email']);

            if($sentVerification['success']){
                $handler->getResponse()->setStatusCode(200);
            }else{
                return $handler->getResponse()->jsonResponse($sentVerification['ERROR'], 500);
            }
        }else{
             $handler->getResponse()->setStatusCode( 500);
        }
        return null;
    }

    private function sendVerificationMail(PHPMailer $mail, string $verificationLink, string $recipientMail): array
    {
        try {
            //Server settings
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username = EMAIL;
            $mail->Password = EMAIL_PASSWORD;                            //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(EMAIL, 'UniShare');
            $mail->addAddress($recipientMail);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Verification email from UniShare';
            $mail->Body    = 'Verify email by clicking this link: <b>'. $verificationLink.
                            '</b> <br/> If you did not register an account ignore this mail';
            $mail->AltBody = 'Copy and paste this link into the URL: ' . $verificationLink;

            $mail->send();

            return [
                'ERROR' => [],
                'success' => true
            ];
        } catch (Exception $e) {
            return [
                'ERROR' => $mail->ErrorInfo,
                'success' => false
            ];
        }
    }
}
