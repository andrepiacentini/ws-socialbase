<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Config\StandardConfig;
use Zend\Session\Container;

class IndexController extends AbstractActionController {
    protected $oServiceManager;
    protected $config;
    protected $oSessionManager;
    protected $oContainer;
    protected $oSecurity;
    protected $oRenderer;
    
    /* Função que instancia objetos comuns utilizados pelo objeto atual */
    private function init() {
        $this->oServiceManager = $this->getServiceLocator();
        $this->config = $this->oServiceManager->get("config");	// Captura o config setado em global|local
        $this->oSessionManager = $this->oServiceManager->get('Zend\Session\SessionManager');
        $this->oContainer = new Container("microblog",$this->oSessionManager); // Cria o container, usando o sessionmanager criado no Module.php
       	$this->oRenderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
    }
    

    /* registra os  Getters and Setters */
    // Retorna (GET) umContainer
    public function getContainer() {
        return $this->oContainer;
    }
    
    
    
    
    public function indexAction() {
        $this->init();
        $umViewModel = new ViewModel();
        $umViewModel->setTemplate("app/index");
        $umViewModel->setVariable("fb_id",$this->config["fb"]["app_id"]);
        $umViewModel->setTerminal(false);
        return $umViewModel;
    }




    public function simAction() {
        $umViewModel = new ViewModel();
        $umViewModel->setTemplate("app/sim");
        $umViewModel->setTerminal(true);
        return $umViewModel;
    }

    




    /* Login por facebook usando o SDK v5 */
    public function facebookLoginAction() {
        $this->init();
        $fb = new \Facebook\Facebook([
            'app_id' => $this->config["fb"]["app_id"],
            'app_secret' => $this->config["fb"]["app_secret"],
            'default_graph_version' => 'v2.2',
        ]);
        
        $sAction = $this->params()->fromRoute('id', null);
    
        if ($sAction=="show") {
            
            $helper = $fb->getRedirectLoginHelper();
            //$permissions = ['email', 'user_location'];
            $permissions = ['email'];
            switch (getenv('APPLICATION_ENV')) {
                case "andre"        :   $sURL = 'http://casas.portobello.localhost/facebookLogin';
                                        break;
                case "homologacao"  :   $sURL = 'http://pbl-casas.prototipos.dzigual.com.br/facebookLogin';
                                        break;
                default             :   $sURL = 'http://casas.portobello.com.br/facebookLogin';
                                        break;
            }
            $loginUrl = $helper->getLoginUrl($sURL, $permissions);
    
            //echo "<strong>FB URL : </strong>".$loginUrl."<br/><br/>";        
            echo '<button class="expand" type="button" href="#" onClick="document.location=\'' . $loginUrl . '\'">Login com o Facebook</button>';
            exit;
        }
        else {
            // Dados do login do FB
            
            $helper = $fb->getRedirectLoginHelper();
            try {
                $accessToken = $helper->getAccessToken();
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo '[GET ACCESS TOKEN] Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo '[GET ACCESS TOKEN] Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            
            if (isset($accessToken)) {
                // Logged in!
                $_SESSION['facebook_access_token'] = (string) $accessToken;
                
                // Now you can redirect to another page and use the
                // access token from $_SESSION['facebook_access_token']

                // OAuth 2.0 client handler
                $oAuth2Client = $fb->getOAuth2Client();
                
                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                $_SESSION['facebook_longlived_access_token'] = (string) $longLivedAccessToken;
                
                // Captura os dados
                $fb->setDefaultAccessToken($accessToken);
                try {
                    $response = $fb->get('/me?fields=name,email');
                    $user = $response->getGraphUser();
                    // Registra
                    $oUser = new User($this->oServiceManager);
                    $oUser->setEmail($user->getProperty("email"));
                    $oUser->setFbId($user->getProperty("id"));
                    $oUser->loadByFbId();
                    $bExiste = false;
                    if (empty($oUser->getId())) {
                        // Procura por email
                        $oUser->setEmail($user->getProperty("email"));
                        $oUser->loadByEmail();
                        if (!empty($oUser->getEmail())) {
                            $bExiste = true;
                        }
                    }
                    else {
                        $bExiste = true;
                    }
                    if ($bExiste) {
                        $bNewUser = false;
                        // Atualiza o usuário existente com os dados de login do facebook
                        $oUser->load();
                        $oUser->setName($user->getProperty("name"));
                        if (!empty($user->getProperty("email"))) {
                            $oUser->setEmail($user->getProperty("email"));
                        }
                        $oUser->setFbId($user->getProperty("id"));
                        $oUser->setFbLongLivedToken($longLivedAccessToken);
                        $oUser->setActived(1);
                        // Verifica se já tem foto. Se não tiver, usa do facebook
                        $sArquivoFoto = $oUser->getProfilePicture();
                        if (is_null($sArquivoFoto)) {
                            $img = file_get_contents('https://graph.facebook.com/'.$user->getProperty("id").'/picture?type=small');
                            $file = $_SERVER["DOCUMENT_ROOT"].'/users_files/'.$oUser->getId().'/profile.jpg';
                            file_put_contents($file, $img);
                            $oUser->setProfilePicture("profile.jpg");
                        }
                        $oUser->save();
                    }
                    else {
                        // Cria o usuário
                        $bNewUser = true;
                        $oUser->setName($user->getProperty("name"));
                        if (!empty($user->getProperty("email"))) {
                            $oUser->setEmail($user->getProperty("email"));
                        }
                        $oUser->setFbId($user->getProperty("id"));
                        $oUser->setFbLongLivedToken($longLivedAccessToken);
                        $oUser->setActived(1);
                        $oUser->save();
                        // Imagem
                        $sArquivoSaida = "/users_files/".$oUser->getId()."/";
                        if (!file_exists($_SERVER["DOCUMENT_ROOT"].$sArquivoSaida)) {
                            // Cria o diretorio
                            mkdir($_SERVER["DOCUMENT_ROOT"].$sArquivoSaida,0777);
                        }
                        $img = file_get_contents('https://graph.facebook.com/'.$user->getProperty("id").'/picture?type=small');
                        $file = $_SERVER["DOCUMENT_ROOT"].'/users_files/'.$oUser->getId().'/profile.jpg';
                        file_put_contents($file, $img);
                        $oUser->setProfilePicture("profile.jpg");
                        $oUser->save();
                    }
                    // Registra variáveis de sessão
                    $iUserId = $oUser->getId();
                    if (isset($iUserId)) {
                        $this->oContainer->sUserSession = $iUserId;
                        $this->oContainer->iIdUserSession = $iUserId;
                        $umViewModel = new ViewModel();
                        $umViewModel->setTerminal(true);
                        $umViewModel->setTemplate("app/facebook");
                        $umViewModel->setVariable("sUserId",$iUserId);
                        return $umViewModel;
                    }
                    else {
                        echo "Erro de login. Id não existe";
                        exit;
                    }
/*
                    echo "Email: ".$user->getProperty("email")."<BR><BR>";
                    var_dump($user);
*/
                    exit;
                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
                    echo '[GET USER OBJ] Graph returned an error: ' . $e->getMessage();
                    exit;
                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
                    echo '[GET USER OBJ] Facebook SDK returned an error: ' . $e->getMessage();
                    exit;
                }
                
            }            
        }      
    }




    public function facebookLogin4Action() {
        $this->init();
        FacebookSession::setDefaultApplication($this->config["fb"]["app_id"], $this->config["fb"]["app_secret"]);
        // Descomentar abaixo para gerar a URL
/*
        	    $helper = new FacebookRedirectLoginHelper('http://casas.portobello.com.br/facebookLogin');
        	    $loginUrl = $helper->getLoginUrl(array('scope' => 'email, user_location'));
        	    echo $loginUrl; exit;
*/
    
        //$helper = new FacebookRedirectLoginHelper($helper);
    
        // login helper with redirect_uri
        switch (getenv('APPLICATION_ENV')) {
            case "andre"        :   $helper = new FacebookRedirectLoginHelper('http://casas.portobello.localhost/facebookLogin');
                                    break;
            case "homologacao"  :   $helper = new FacebookRedirectLoginHelper('http://casas.andrepiacentini.com.br/facebookLogin');
                                    break;
            default             :   $helper = new FacebookRedirectLoginHelper('http://casas.portobello.com.br/facebookLogin');
                                    break;
        }



    
        // see if a existing session exists
        if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
            // create new session from saved access_token
            $session = new FacebookSession( $_SESSION['fb_token'] );
    
            // validate the access_token to make sure it's still valid
            try {
                if ( !$session->validate() ) {
                    $session = null;
                }
            } catch ( Exception $e ) {
                // catch any exceptions
                $session = null;
            }
        }
    
        if ( !isset( $session ) || $session === null ) {
            // no session exists
    
            try {
                $session = $helper->getSessionFromRedirect();
            } catch( FacebookRequestException $ex ) {
                // When Facebook returns an error
                // handle this better in production code
                print_r( $ex );
            } catch( Exception $ex ) {
                // When validation fails or other local issues
                // handle this better in production code
                print_r( $ex );
            }
    
        }
    
        if (isset($session)) {
            // Logged in
            try {
                // User logged in, get the AccessToken entity.
                $accessToken = $session->getAccessToken();
                // Exchange the short-lived token for a long-lived token.
                $longLivedAccessToken = $accessToken->extend();
    
    
                $request = new FacebookRequest($session, 'GET', '/me');
                $response = $request->execute();
    
                // Profile
                $user = $response->getGraphObject();

                             echo "Session token: ".$longLivedAccessToken."<BR><BR>";
                             print("<pre>"); var_dump($user); print("</pre><hr>");
    
                             echo "Name: " . $user->getProperty("name")."<BR>";
                             echo "Email: " . $user->getProperty("email")."<BR><BR><BR>";
    
                             echo "<input type=\"button\" value=\"Reutilizar sessão\" onClick=\"document.location='/seguro/facebookReuse/".$longLivedAccessToken."'\"><br/>";
                             exit;
    
    
                // Registra
                $oUser = new User($this->oServiceManager);
                $oUser->setEmail($user->getProperty("email"));
                $oUser->loadByEmail();
                if ($oUser->getId()!=null) {
                    $bNewUser = false;
                    // Atualiza o usuário existente com os dados de login do facebook
                    $oUser->load();
                    $oUser->setFbId($user->getProperty("id"));
                    $oUser->setFbLongLivedToken($longLivedAccessToken);
                    $oUser->setActived(1);
                    // Verifica se já tem foto. Se não tiver, usa do facebook
                    $sArquivoFoto = $oUser->getProfilePicture();
                    if (is_null($sArquivoFoto)) {
                        $img = file_get_contents('https://graph.facebook.com/'.$user->getProperty("id").'/picture?type=small');
                        $file = $_SERVER["DOCUMENT_ROOT"].'/users_files/'.$oUser->getId().'/profile.jpg';
                        file_put_contents($file, $img);
                        $oUser->setProfilePicture("profile.jpg");
                    }
                    $oUser->save();
                }
                else {
                    // Cria o usuário
                    $bNewUser = true;
                    $oUser->setName($user->getProperty("name"));
                    $oUser->setEmail($user->getProperty("email"));
                    $oUser->setFbId($user->getProperty("id"));
                    $oUser->setFbLongLivedToken($longLivedAccessToken);
                    $oUser->setActived(1);
                    $oUser->save();
                    // Imagem
                    $sArquivoSaida = "/users_files/".$oUser->getId()."/";
                    if (!file_exists($_SERVER["DOCUMENT_ROOT"].$sArquivoSaida)) {
                        // Cria o diretorio
                        mkdir($_SERVER["DOCUMENT_ROOT"].$sArquivoSaida,0777);
                    }
                    $img = file_get_contents('https://graph.facebook.com/'.$user->getProperty("id").'/picture?type=small');
                    $file = $_SERVER["DOCUMENT_ROOT"].'/users_files/'.$oUser->getId().'/profile.jpg';
                    file_put_contents($file, $img);
                    $oUser->setProfilePicture("profile.jpg");
                    $oUser->save();
                }
                // Registra variáveis de sessão
                $iUserId = $oUser->getId();
                if (isset($iUserId)) {
                    $this->oContainer->sUserSession = $iUserId;
                    $this->oContainer->iIdUserSession = $iUserId;
                    //$_COOKIE["user_logged_id"] = $iUserId;
                    setcookie("user_logged_id",$iUserId);
	                // Sempre envia para a HOME
                    return $this->redirect()->toURL($this->oRenderer->basePath('/'));
                    exit;
                }
                else {
                    echo "Erro de login. Id não existe";
                    exit;
                }
    
    
    
            } catch(FacebookRequestException $e) {
    
                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
    
            }
    
        }
        else {
            // redirect forçado para logar novamente
            header("Location: ". $helper->getLoginUrl( array( 'email', 'user_location' ) ) );
            exit;
        }
    }
    
    
    
    public function facebookReuseAction() {
        $this->init();
        FacebookSession::setDefaultApplication($this->config["fb"]["app_id"], $this->config["fb"]["app_secret"]);
    
	    //$longLivedAccessToken = 'CAAJYRHChZBjwBAOdczVZApWiMIuN3oeEfOM6NQRiDGMn840AlnWgRT6szbwHljSi3qpQFzi4fqZB2DynaoukvMpfEIFnUdfigIL1VzQXg5JMTyZAbOIEp2ZCMXxZBFBHogZBYa1pZCSVbZBOx7cZAHEklNbmfFr3tFtBGJ5bOQCKyLKRzNn0CCJQVoUMVsnQnE1lkZD';
        $longLivedAccessToken = $this->params()->fromRoute('id', 0);
        $session = new FacebookSession($longLivedAccessToken);
    
        if($session) {
    
            try {
    
                $request = new FacebookRequest($session, 'GET', '/me');
                $response = $request->execute();
    
                                    // Session token
    
                                    // Profile
                                    $user = $response->getGraphObject();
    
                                    echo "Session token REUSE: ".$longLivedAccessToken."<BR><BR>";
                                    print("<pre>"); var_dump($user); print("</pre><hr>");
    
                                    echo "Name: " . $user->getProperty("name")."<BR>";
                                    echo "Location: " . $user->getProperty("hometown")->getProperty("name");
    
   
        	} catch(FacebookRequestException $e) {
       
        	    echo "Exception occured, code: " . $e->getCode();
        	    echo " with message: " . $e->getMessage();
       
        	}
    
    	}
    	exit;
    	}
    
    
    
    
    	public function facebookRedirectURLAction() {
    	    $this->init();
    	    FacebookSession::setDefaultApplication($this->config["fb"]["app_id"], $this->config["fb"]["app_secret"]);
    
    	    // Descomentar abaixo para gerar a URL
    	    $helper = new FacebookRedirectLoginHelper("http://".$_SERVER["HTTP_HOST"].$this->config["view_manager"]["base_path"].'facebookLogin');
    	    $loginUrl = $helper->getLoginUrl(array('scope' => 'email, user_location'));
    	    echo $loginUrl; exit;
    
    	}
    
    
    
    
    public function converteCidadesAction() {
        echo "Encerrado"; exit;
        $this->init();
        $aOriginalUsers = array();
        $oAdapter = $this->oServiceManager->get("Zend\Db\Adapter\Adapter");
        $sQuery = "select u.id, u.city_id, c.city from users as u left join cities as c on c.id=u.city_id";
        if ($umResultSet = $oAdapter->query($sQuery)->execute()) {
            if ($umResultSet->count()>0) {
                while ($linha = $umResultSet->next()) {
                    array_push($aOriginalUsers,array("id"=>$linha["id"],"city_id"=>$linha["city_id"],"city"=>$linha["city"]));
                }
            }
        }
        // Altera a cidade
        $aTemp = array();
        foreach ($aOriginalUsers as $aData) {
            $iUserId = $aData["id"];
            $iCityOld = $aData["city_id"];
            $sCityName = $aData["city"];
            $sQuery = "select id,city from cities_new where city='".$sCityName."'";
            if ($umResultSet = $oAdapter->query($sQuery)->execute()) {
                $linha = $umResultSet->current();
                if ($umResultSet->count()>0) {
                    $aData["new_id"] = $linha["id"];
                    $aData["new_city"] = $linha["city"];
                }
                else {
                    $aData["new_id"] = null;
                    $aData["new_city"] = null;
                }
                array_push($aTemp,$aData);
            }
            $aOriginalUsers = $aTemp;
        }
//print_r($aOriginalUsers); exit;
        
        // Atualiza o banco de dados
        foreach ($aOriginalUsers as $aData) {
            $iUserId = $aData["id"];
            $iCityOld = $aData["city_id"];
            $iCity = $aData["new_id"];
            if ($iUserId>0) {
                if (is_null($iCity)) {
                    $iCity = "null";
                }
                $sQuery = "update users set id_city_old=".$iCityOld.",city_id=".$iCity." where id=".$iUserId;
//print($sQuery); exit;
                $umResultSet = $oAdapter->query($sQuery)->execute();
            }
        }
        
        print("<pre>"); print_r($aOriginalUsers);
        exit;
    }



	



    /**
     * emailRecoveryAction function.
     * 
     * @access public
     * @return void
     */
    public function contactSendAction() {
	    $this->init();
	    $request = $this->getRequest();
	    if ($request->isPost()) {
	        $sName = $request->getPost("nome");
	        $sEmail = $request->getPost("email");
	        $sMessage = $request->getPost("mensagem");
	        // Envia o email
	        $oEmail = new Email($this->oServiceManager);
	        $oEmail->setTitle("Casas Portobello - Pedido de contato");
	        $sHTML = "
	        O formulário de contato do site foi preenchido com os seguintes dados:<br/>
	        <br/>
	        Nome: <strong>".$sName."</strong><br/>
	        E-mail: <strong>".$sEmail."</strong><br/>
	        Mensagem:<br/>
	        <strong>".$sMessage."</strong><br/>
	        ";
	        $oEmail->setMessage($sHTML);
	        $oEmail->setFrom(array("email"=>"relacionamento@portobello.com.br","name"=>"Casas Portobello"));
	        $oEmail->setTo(array("email"=>"relacionamento@portobello.com.br","name"=>"Casas Portobello"));
	        $aJSONArray = $oEmail->send();
        }   
	    else {
	        $aJSONArray = array("status"=>406,"message"=>"method not allowed - only post is acceptable");
	    }
	    $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($aJSONArray));
	    return $response;
     
    }

 
}
