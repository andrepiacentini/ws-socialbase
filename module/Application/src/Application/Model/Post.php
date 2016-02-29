<?php
// Classe Post

namespace Application\Model;


use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;
use Application\Mapper\PostMapper;

class Post {
	
    private $oSM;
    private $dbAdapter;
    private $id;
    private $id_user;
    private $content;
    private $dt_created;
    private $active;
    private $excluded;
    private $oMapper;
		

    /**
     * __construct function.
     * 
     * @access public
     * @param ServiceManager $oSM
     * @return void
     */
    public function __construct(ServiceManager $oSM) {
        $this->oSM = $oSM;
        $this->dbAdapter = $this->oSM->get("Zend\Db\Adapter\Adapter");
        $this->oMapper = new PostMapper($this->oSM);
    }
    
    /**
     * getArrayCopy - devolve um array com todas as propriedades do objeto
     * 
     * @access public
     * @return void
     */
    public function getArrayCopy() {
        return array(
            "id" => $this->id,
            "id_user" => $this->id_user,
            "content" => $this->content,
            "dt_created" => $this->dt_created,
            "active" => $this->active,
            "excluded" => $this->excluded,
        );
    }


    
    /**
     * exchangeArray - carrega todas as propriedade do objeto baseado em um array
     * 
     * @access public
     * @param array $array
     * @return void
     */
    public function exchangeArray(array $array) {
        $this->id = $array["id"];
        $this->id_user = $array["id_user"];
        $this->content = $array["content"];
        $this->dt_created = $array["dt_created"];
        $this->active = $array["active"];
        $this->excluded = $array["excluded"];
    }
	    


    /**
     * load - carrega, através do mapeador, as propriedades do objeto, conforme configurações do adaptador (pode ser banco de dados, etc).
     * 
     * @access public
     * @return void
     */
    public function load() {
        if (isset($this->id)) {
            $this->exchangeArray($this->oMapper->getOne($this->id));
            return true;
        }
        else {
            return false;
        }
    }
    
    
    /**
     * save - salva as propriedades do objeto, através do mapeador, conforme o adaptador em uso.
     * 
     * @access public
     * @return void
     */
    public function save() {
        $sql = new Sql($this->dbAdapter);
        if (empty($this->id)) {
            // cria novo post
            $iPostId = $this->oMapper->create($this->getArrayCopy());
        }
        else {
            // update de post existente
        }
    }
    
    
    /* Getters & Setters */


    /**
     * getId function.
     * 
     * @access public
     * @return void
     */
    public function getId(){
        return $this->id;
    }

    /**
     * getIdUser function.
     * 
     * @access public
     * @return void
     */
    public function getIdUser(){
        return $this->id_user;
    }

    /**
     * getContent function.
     * 
     * @access public
     * @return void
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * getDtCreated function.
     * 
     * @access public
     * @return void
     */
    public function getDtCreated(){
        return $this->dt_created;
    }

    /**
     * getActive function.
     * 
     * @access public
     * @return void
     */
    public function getActive(){
        return $this->active;
    }

    /**
     * getExcluded function.
     * 
     * @access public
     * @return void
     */
    public function getExcluded(){
        return $this->excluded;
    }

    /**
     * setId function.
     * 
     * @access public
     * @param mixed $value
     * @return void
     */
    public function setId($value){
        $this->id = $value;
    }

    /**
     * setIdUser function.
     * 
     * @access public
     * @param mixed $value
     * @return void
     */
    public function setIdUser($value){
        $this->id_user = $value;
    }

    /**
     * setContent function.
     * 
     * @access public
     * @param mixed $value
     * @return void
     */
    public function setContent($value){
        $this->content = $value;
    }

    /**
     * setDtCreated function.
     * 
     * @access public
     * @param mixed $value
     * @return void
     */
    public function setDtCreated($value){
        $this->dt_created = $value;
    }

    /**
     * setActive function.
     * 
     * @access public
     * @param mixed $value
     * @return void
     */
    public function setActive($value){
        $this->active = $value;
    }

    /**
     * setExcluded function.
     * 
     * @access public
     * @param mixed $value
     * @return void
     */
    public function setExcluded($value){
        $this->excluded = $value;
    }
   
    
    
}
