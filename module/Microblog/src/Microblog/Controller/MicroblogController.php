<?php
namespace Microblog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Config\StandardConfig;
use Zend\Session\Container;

use Application\Mapper\PostMapper;
use Application\Model\Post;



/**
 * Webservice REST Microblog
 *
 * By André Martins Piacentini
 * 
 */
class MicroblogController extends AbstractActionController {

	protected $oServiceManager;
	protected $config;
	protected $oSessionManager;
	protected $oContainer;
	protected $oSecurity;
	protected $oRenderer;
	protected $oMapper;


	
	/* Função que instancia objetos comuns utilizados pelo controller */
	private function init() {
		$this->oServiceManager = $this->getServiceLocator();
		$this->config = $this->oServiceManager->get("config");	// Captura o config setado em global|local
		$this->oSessionManager = $this->oServiceManager->get('Zend\Session\SessionManager');
		$this->oContainer = new Container("microblog",$this->oSessionManager); // Cria o container, usando o sessionmanager criado no Module.php
   	    $this->oRenderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
   	    $this->oMapper = new PostMapper($this->oServiceManager);
	}


	/**
	 * indexAction function.
	 * 
	 * @access public
	 * @return void
	 */
	public function indexAction() {
    	$this->init();
	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode(array('status'=>200, 'message' => 'I am alive!')));
	    return $response;
	}





    /**
     * getAllPostsAction - retorna um JSON contendo todos os registros de posts
     * 
     * @access public
     * @return json
     */
    public function getAllPostsAction() {
    	$this->init();
    	
        try {
        	$aAll = $this->oMapper->fetchAll();
            // JSON de retorno
    	    $aJSON = array('status'=>200,'posts'=>$aAll);
        } 
        catch (Exception $e) {
            // JSON de retorno
    	    $aJSON = array('status'=>500,'message'=>'Internal server erro\n\nExceção capturada: '.$e->getMessage());
        }


	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode($aJSON));
	    return $response;
	}





    /**
     * getAllActivePostsAction - retorna um JSON contendo todos os registros de posts ativo, não excluídos e em ordenação por data de criação crescente
     * 
     * @access public
     * @return json
     */
    public function getAllActivePostsAction() {
    	$this->init();
    	
        try {
        	$aAll = $this->oMapper->fetchAllActived();
            // JSON de retorno
    	    $aJSON = array('status'=>200,'posts'=>$aAll);
        } 
        catch (Exception $e) {
            // JSON de retorno
    	    $aJSON = array('status'=>500,'message'=>'Internal server erro\n\nExceção capturada: '.$e->getMessage());
        }


	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode($aJSON));
	    return $response;
	}





    /**
     * getAllActivePostsReverseAction - retorna um JSON contendo todos os registros de posts ativo, não excluídos e em ordenação por data de criação decrescente
     * 
     * @access public
     * @return json
     */
    public function getAllActivePostsReverseAction() {
    	$this->init();
    	
        try {
        	$aAll = $this->oMapper->fetchAllActived(true);
            // JSON de retorno
    	    $aJSON = array('status'=>200,'posts'=>$aAll);
        } 
        catch (Exception $e) {
            // JSON de retorno
    	    $aJSON = array('status'=>500,'message'=>'Internal server erro\n\nExceção capturada: '.$e->getMessage());
        }


	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode($aJSON));
	    return $response;
	}





    /**
     * getLastActivePostsAction - retorna um JSON contendo todos os registros de posts ativo, acima do id passada por parametro, não excluídos e em ordenação por data de criação crescente
     * 
     * @access public
     * @return json
     */
    public function getLastActivePostsAction() {
    	$this->init();
        $request = $this->getRequest();
	    if ($request->isPost()) {
    	    // Recebe um object json
    	    $oJson = $request->getContent();
    	    $jsonDecoded = json_decode($oJson);
            if ($jsonDecoded !== null) {
        	    $aData = get_object_vars(json_decode($request->getContent()));
        	    $iLastPostId = (int)$aData["last_post_id"];
            	
                try {
                	$aPosts = $this->oMapper->fetchActivedFromId($iLastPostId);
                    // JSON de retorno
            	    $aJSON = array('status'=>200,'posts'=>$aPosts);
                } 
                catch (Exception $e) {
                    // JSON de retorno
            	    $aJSON = array('status'=>500,'message'=>'Internal server erro\n\nExceção capturada: '.$e->getMessage());
                }
    	    }
    	    else {
        	     $aJSON = array("status"=>400,"message"=>"Requisição inválida. Objeto JSON esperado.");
    	    }
	    }
	    else {
            // Somente método POST é aceito
            $aJSON = array("status"=>406,"message"=>"method not allowed - only post is acceptable");
	    }


	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode($aJSON));
	    return $response;
	}





    /**
     * createPostAction - cria um post
     * 
     * @access public
     * @return json
     */
    public function createPostAction() {
        $this->init();
        $aWarnings = [];
        $request = $this->getRequest();
	    if ($request->isPost()) {
    	    // Recebe um object json
    	    $oJson = $request->getContent();
    	    $jsonDecoded = json_decode($oJson);
            if ($jsonDecoded !== null) {
        	    $aData = get_object_vars(json_decode($request->getContent()));
        	    $sContent = htmlspecialchars(strip_tags($aData["message"]),ENT_QUOTES);
        	    if (strlen($sContent)>140) {
            	    array_push($aWarnings,"Quantidade limite de 140 caracteres no post foi ultrapassada. Mesmo assim POST foi criado.");
        	    }
        	    // Cria post
        	    $iPostId = $this->oMapper->create(array("id_user"=>0,"content"=>$sContent,"active"=>1,"excluded"=>0));
        	    $aFullPost = $this->oMapper->fetchOne($iPostId);
        	    $aJSON = array("status"=>200,"post"=>$iPostId,"message"=>"post created","warnings"=>$aWarnings,"post_data"=>$aFullPost);
    	    }
    	    else {
        	     $aJSON = array("status"=>400,"message"=>"Requisição inválida. Objeto JSON esperado.");
    	    }
	    }
	    else {
            // Somente método POST é aceito
            $aJSON = array("status"=>406,"message"=>"method not allowed - only post is acceptable");
	    }


	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode($aJSON));
	    return $response;
    }




    /**
     * deletePostAction - exclui um post
     * 
     * @access public
     * @return json
     */
    public function deletePostAction() {
        $this->init();
        $aWarnings = [];
        $request = $this->getRequest();
	    if ($request->isPost()) {
    	    // Recebe um object json
    	    $oJson = $request->getContent();
    	    $jsonDecoded = json_decode($oJson);
            if ($jsonDecoded !== null) {
        	    $aData = get_object_vars(json_decode($request->getContent()));
        	    $iPostId = (int)$aData["post_id"];
        	    if ($iPostId==0) {
                    $aJSON = array("status"=>400,"message"=>"Requisição inválida. ID de post deve ser um número e maior que zero.");
        	    }
        	    else {
            	    // Cria post
            	    $this->oMapper->delete($iPostId);
            	    $aJSON = array("status"=>200,"post"=>$iPostId,"message"=>"post ID {$iPostId} deleted");
        	    }
    	    }
    	    else {
        	     $aJSON = array("status"=>400,"message"=>"Requisição inválida. Objeto JSON esperado.");
    	    }
	    }
	    else {
            // Somente método POST é aceito
            $aJSON = array("status"=>406,"message"=>"method not allowed - only post is acceptable");
	    }


	    $response = $this->getResponse();
	    $response->setContent(\Zend\Json\Json::encode($aJSON));
	    return $response;
    }

}
