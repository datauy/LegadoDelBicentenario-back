<?php

class dbCon {
    protected $datab;
    public $isConnected;

    public function __construct(){
        /*$server = "localhost";
        $user = "root";
        $pass = "odnan09";
        $db = "bicentenario";
        mysql_connect($server, $user, $pass) or die("Error connecting to sql server: ".mysql_error());
        mysql_select_db($db);*/
        $this->isConnected = true;
        try { 
                $this->datab = new PDO('mysql:host=localhost;dbname=estudio1_bicente;charset=utf8', 'estudio1_bicente', 'Bic_Cen32'); 
                $this->datab->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                $this->datab->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } 
            catch(PDOException $e) { 
                $this->isConnected = false;
                throw new Exception($e->getMessage());
            }
    }
    //Query para formulario de primera página
    function get_laws($filters){
        $sql = "SELECT id, nombre, prioridad, categoria, fecha FROM leyes WHERE ";
        $last = sizeof($filters);
        $row = 1;
        foreach ($filters as $field => $value) {
            if ($row == $last)
                $sql .= $field." = :".$field;
            else
                $sql .= $field." = :".$field." OR ";
            $row++;
        }
        $sql .= " ORDER BY prioridad DESC";
        try{
            $get_laws = $this->datab->prepare($sql);
            $get_laws->execute($filters);
            return $get_laws->fetchAll();
        }
        catch(PDOException $e){
            throw new Exception($e->getMessage());
            return FALSE;
        }
    }
    //Funcion para devolver categorias al inicio
    function rights(){
        try{
            $get_laws = $this->datab->prepare("SELECT * FROM derechos");
            $get_laws->execute();
            return $get_laws->fetchAll();
        }
        catch(PDOException $e){
            throw new Exception($e->getMessage());
            return FALSE;
        }
    }
    //IDs de leyes que tienen los derechos seleccionados
    function get_laws_from_rights($rights){
        try{
            $get_laws = $this->datab->prepare("SELECT ley FROM derechosxley WHERE derecho in (:rights)");
            $get_laws->execute(array(
                'rights' => $rights 
                )
            );
            return $get_laws->fetchAll();
        }
        catch(PDOException $e){
            throw new Exception($e->getMessage());
            return FALSE;
        }
    }
    //Categoría por ley o leyes (si es por leyes devuelve unique)
    function laws_categories($laws){
        if (is_array($laws)){
            //ejecutamos con unique, siempre in
        }
        else{

        }
    }

    function get_law_list($laws){
        $include = '';
        if (isset($laws['cat']) && !empty($laws['cat']))
            $include = 'where id in ('.$laws['cat'].')';
        if (!isset($laws['offset']))
            $laws['offset'] = 0;
        try{
            $sql = "SELECT * FROM leyes ".$include." ORDER BY :order DESC LIMIT :cantidad OFFSET :offset";
            $get_laws = $this->datab->prepare($sql);
            $get_laws->bindValue(':cantidad', (int) $laws['cantidad'], PDO::PARAM_INT);
            $get_laws->bindValue(':offset', (int) $laws['offset'], PDO::PARAM_INT);
            $get_laws->bindValue(':order', $laws['order']);
            $get_laws->execute();
            return $get_laws->fetchAll();
        }
        catch(PDOException $e){
            throw new Exception($e->getMessage());
            return FALSE;
        }
    }
}

?>