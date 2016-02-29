<?php
namespace Application\Mapper;

use Zend\Db\Sql\Sql;

class PostMapper
{
    private $oSM;
    private $dbAdapter;

    /**
     * __construct - construtor, inicializa o adaptador
     * 
     * @access public
     * @param mixed $oSM
     * @return void
     */
    public function __construct($oSM) {
        $this->oSM = $oSM;
        $this->dbAdapter = $this->oSM->get("Zend\Db\Adapter\Adapter");
    }

    
    
    /**
     * fetchAll - retorna todos os registros
     * 
     * @access public
     * @return void
     */
    public function fetchAll($bReverse = false) {
        $array = array();
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()->from('posts');
        if ($bReverse) {
            $select->order("dt_created desc");
        }
        $resultSet = $sql->prepareStatementForSqlObject($select)->execute();
        while ($line = $resultSet->next()) {
            array_push($array,array("id"=>$line["id"],"id_user"=>$line["id_user"],"content"=>$line["content"],"dt_created"=>$line["dt_created"],"human_date"=>date("d/m/Y H:i:s",strtotime($line["dt_created"])),"active"=>$line["active"],"excluded"=>$line["excluded"]));
        }
        return $array;
    }

    
    
    /**
     * fetchAllActived - retorna todos os registros ativos e não excluidos
     * 
     * @access public
     * @return void
     */
    public function fetchAllActived($bReverse = false) {
        $array = array();
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()->from('posts')->where(array("active"=>1,"excluded"=>0));
        if ($bReverse) {
            $select->order("dt_created desc");
        }
        $resultSet = $sql->prepareStatementForSqlObject($select)->execute();
        while ($line = $resultSet->next()) {
            array_push($array,array("id"=>$line["id"],"id_user"=>$line["id_user"],"content"=>$line["content"],"dt_created"=>$line["dt_created"],"human_date"=>date("d/m/Y H:i:s",strtotime($line["dt_created"])),"active"=>$line["active"],"excluded"=>$line["excluded"]));
        }
        return $array;
    }

    
    
    /**
     * fetchAllActived - retorna todos os registros ativos e não excluidos
     * 
     * @access public
     * @return void
     */
    public function fetchActivedFromId($iFromPostId) {
        $array = array();
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()->from('posts');
        $select->where
                ->equalTo("active",1)
                ->and
                ->equalTo("excluded",0)
                ->and
                ->greaterThan("id",$iFromPostId);
        $resultSet = $sql->prepareStatementForSqlObject($select)->execute();
        while ($line = $resultSet->next()) {
            array_push($array,array("id"=>$line["id"],"id_user"=>$line["id_user"],"content"=>$line["content"],"dt_created"=>$line["dt_created"],"human_date"=>date("d/m/Y H:i:s",strtotime($line["dt_created"])),"active"=>$line["active"],"excluded"=>$line["excluded"]));
        }
        return $array;
    }



    /**
     * fetchOne - retorna um registro, em formato de array
     * 
     * @access public
     * @param mixed $iPostsId
     * @return void
     */
    public function fetchOne($iPostsId) {
        $array = array();
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()->from('posts')->where(array("id"=>$iPostsId));
        $resultSet = $sql->prepareStatementForSqlObject($select)->execute();
        while ($line = $resultSet->next()) {
            array_push($array,array("id"=>$iPostsId,"id_user"=>$line["id_user"],"content"=>$line["content"],"dt_created"=>$line["dt_created"],"human_date"=>date("d/m/Y H:i:s",strtotime($line["dt_created"])),"active"=>$line["active"],"excluded"=>$line["excluded"]));
        }
        return $array;
    }
    
    
    /**
     * create - cria um novo registro, baseado em dados de um array
     * 
     * @access public
     * @param mixed $aData
     * @return void
     */
    public function create($aData) {
        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert()->into('posts')->values($aData);
        if ($resultSet = $sql->prepareStatementForSqlObject($insert)->execute()) {
            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        }
        else {
            return false;
        }
        
    }
    
    
    /**
     * update - atualiza um registro baseado em dados de um array
     * 
     * @access public
     * @param mixed $iPostId
     * @param mixed $aData
     * @return void
     */
    public function update($iPostId,$aData) {
        if ($iPostId>0) {
            $sql = new Sql($this->dbAdapter);
            $update = $sql->update('posts')->set($aData)->where(array("id"=>$iPostId));
            $resultSet = $sql->prepareStatementForSqlObject($update)->execute();
        }
        else {
            return false;
        }
    }
    
    
    /**
     * delete - Apaga um registro
     * 
     * @access public
     * @param mixed $iPostId
     * @return void
     */
    public function delete($iPostId) {
        if ($iPostId>0) {
            $sql = new Sql($this->dbAdapter);
            $delete = $sql->update('posts')->set(array("active"=>0,"excluded"=>1))->where(array("id"=>$iPostId));
            $resultSet = $sql->prepareStatementForSqlObject($delete)->execute();
        }
        else {
            return false;
        }
        
    }
    
    
}
