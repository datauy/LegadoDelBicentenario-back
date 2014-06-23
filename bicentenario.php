<?php
/*
Espera la forma equivalente a GET bicentenario.php?tipo=''&data=''
Donde tipo puede ser:

    leyes,
        Listado de leyes que lo influyen:
            id
            nombre
            prioridad
            categoría
            fecha
        Campos necesarios en data:
            sexo
            genero
            edad
            discapacidad
            civil
            racial
            uruguay
            hijos
            */
error_reporting(E_ALL);
ini_set('display_errors', '1');
global $db;

//Espera la forma equivalente a GET bicentenario.php?tipo=''&data=''
if (isset($_REQUEST['tipo']) && isset($_REQUEST['data'])){
	header('Content-Type: application/json');
	$data = str_replace('\\', '', $_REQUEST['data']);
	if ($data = json_decode($data)){
		include_once 'db.php';
		$db = new dbCon;
		switch ($_REQUEST['tipo']){
			case 'leyes':
				print leyes($data);
				break;
			case 'listado':
				print listado_de_leyes($data);
				break;
			case 'categories':
				print get_categories();
			default:
				print json_encode(array('error' => 'Tipo inválido'));
		}

	}
	else{
		print json_encode(array('error' => 'JSON inválido'));
	}
}
else{
	echo json_encode(array('error' => 'Parámetros incorrectos'));
}

function leyes($data){
	global $db;

	if (property_exists($data, 'sexo') && 
		property_exists($data, 'edad')) {
		//property_exists($data, 'racial') && 
		//property_exists($data, 'extranjero') && 
		/*property_exists($data, 'hijos')){*/
		//estado: 1 Casado, 2 soltero, 3 concubinato, 4 viudo
		$laws_filter['sexo'] = $data->sexo;
		if (property_exists($data, 'civil'))
			$laws_filter['estado_civil'] = $data->civil;
		if (property_exists($data, 'genero'))
			$laws_filter['identidad_genero'] = $data->genero;
		if (property_exists($data, 'discapacidad'))
			$laws_filter['identidad_genero'] = $data->discapacidad;
		if (property_exists($data, 'racial'))
			$laws_filter['identidad_racial'] = $data->racial;
		if (property_exists($data, 'extranjero'))
			$laws_filter['extranjero'] = $data->extranjero;
		if (property_exists($data, 'hijos'))
			$laws_filter['hijos'] = $data->hijos;
		switch ($data->edad){
			case ($data->edad <= 15):
				$laws_filter['ninos'] = 1;
				break;
			case (15 < $data->edad && $data->edad <= 32):
				$laws_filter['jovenes'] = 1;
				break;
			case (33 < $data->edad && $data->edad <= 65):
				$laws_filter['adultos'] = 1;
				break;
			case (66 < $data->edad):
				$laws_filter['adulto_mayor'] = 1;
				break;
		}
		$leyes = $db->get_laws($laws_filter);
		if ($leyes)
			$response = $leyes;
		else
			$response['error'] = 'Problema en base de datos';	
	}
	else {
		$response['error'] = 'Datos incompletos';
	}
	return json_encode($response);
	//devuelve id: , Nombre, priorida, categoría, fecha.
}
/*
	Paginado:
		pagina
		cantidad
	Filtros:
		categoria
	Orden:
		alfabético (defecto)
		fecha
		Alcance	
	id, fecha, nombre, art1, alcance, categorias
	
	*/
function listado_de_leyes($data){
	global $db;
	
	if (property_exists($data, 'cantidad') && is_numeric($data->cantidad)){
		$laws['cantidad'] = $data->cantidad;
		//Paginado
		if (property_exists($data, 'pagina') && is_numeric($data->pagina)){
			$laws['offset'] = $data->cantidad*($data->pagina - 1);
		}
		else{
			$laws['offset'] = 0;
		}
		//Orden
		if (property_exists($data, 'orden')){
			foreach ($data->order as $order) {
				$laws['order'] = implode(',', $data->order);
			}
		}
		else
			$laws['order'] = 'fecha';
		//Categorias
		if (property_exists($data, 'categorias')){
			if (!empty($data->categorias)){
				$cats = implode(',', $data->categorias);
				$laws_filtered_arr = $db->get_laws_from_rights($cats);
				$laws_filtered = array();
				foreach ($laws_filtered_arr as $law_filtered) {
					$laws_filtered[] = $law_filtered['ley'];
				}
				$laws['cat'] = implode(',', $laws_filtered);
			}
		}
		//Query
		$response = $db->get_law_list($laws);
		foreach ($response as $key => $law) {
			$path = 'leyes/ley'.$law['id'].'.xml';
			if (file_exists($path)) {
				//Esto lo agregamos porque los archivos no están en utf8
				$content = file_get_contents($path);
				$content_utf8 = utf8_encode($content);
				$law_obj = simplexml_load_string($content_utf8);
    			//$law_obj = simplexml_load_file($path);
    			//$law_obj->norma->articulos->articulo;
    			$response[$key]['art1'] = $law_obj->norma->articulos->articulo[0]->texto;
    			$response[$key]['art2'] = $law_obj->norma->articulos->articulo[1]->texto;
    			$response[$key]['url'] = "http://$_SERVER[HTTP_HOST]/bicentenario/$path";
			}
		}
	}
	else {
		$response['error'] = 'Datos incompletos';
	}
	return json_encode($response);
}

/**
	Función para obtener las categorías en la base
*/
function get_categories(){
	return categories();
}

//No se implementa por ahora
function busqueda(){

}

?>