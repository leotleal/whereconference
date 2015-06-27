<?php
require '../Slim/Slim.php';
require_once 'DataBaseConection.php';
require_once 'ConferenceService.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->response()->header('Content-Type', 'application/json;charset=utf-8');

$app->config('debug', true);

$app->get('/', function () {echo "Bem Vindo!!!";});

//Conferencias
$app->get('/conferencias', function () use ($app) {

	$paginacao = "";

	if (isset($_GET['offset']) && isset($_GET['limit'])) {
		$offset = $_GET['offset'];
		$limit = $_GET['limit'];
		$paginacao = "LIMIT $offset, $limit";
	}

    $sql = "SELECT conf.ConfereciasId,conf.Nome,conf.DataIni, conf.DataFim, conf.Local, c.CategoriaId, c.Nome as nomeCategoria, a.AreaId, a.Nome as nomeArea
				FROM conferencias conf 
				INNER JOIN areas a on conf.CategoriaId = a.CategoriaId 
				INNER JOIN categorias c on a.CategoriaId = c.CategoriaId ". $paginacao;
				
			try{
				$db = getConn();
				$conferencia = $db->query($sql);
				$listConferencias = $conferencia->fetchAll(PDO::FETCH_OBJ);
				foreach($listConferencias as $row){
						$categoria = array(
								 		'id'=>$row->CategoriaId,
								 		'nome'=>$row->nomeCategoria,
								 		'area'=>array(
								 					'id'=>$row->AreaId,
								 					'nome'=>$row->nomeArea
					 					)
					 	);
					 	$conferencias[] = array(
									 		'id'=>$row->ConfereciasId,
									 		'nome'=>$row->Nome,
									 		'dataIni'=>$row->DataIni,
									 		'dataFim'=>$row->DataFim,
									 		'local'=>$row->Local,
									 		'categoria'=>$categoria
					 	);
				}

				$item = array(
			 				'response' => array(
			 					'totalRegistros'=>10,
			 					'totalPages'=>1,
			 					'conferencias'=> $conferencias
					 		),
					 		'meta' => array(
				 				'resultCode'=> 0,
								'clientMsg'=> ''
				 			)
					 	);
				echo json_encode($item);
			} catch(PDOException $e) {
				$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
		}

});


$app->get('/conferencias/:id', function ($id) use($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'conferenceid inválido'))));
	}

	$sql = "SELECT conf.ConfereciasId,conf.Nome,conf.DataIni, conf.DataFim, conf.Local, c.CategoriaId, c.Nome as nomeCategoria, a.AreaId, a.Nome as nomeArea
				FROM conferencias conf 
				INNER JOIN areas a on conf.CategoriaId = a.CategoriaId 
				INNER JOIN categorias c on a.CategoriaId = c.CategoriaId
			    WHERE ConfereciasId=:id";
		try{
			$conn = getConn();
			$conferencia = $conn->prepare($sql);
			$conferencia->bindParam("id",$id);
			$conferencia->execute();
			$count = $conferencia->rowCount();

			if (empty($count)) {
				$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2002,'clientMsg'=> 'Conferência inexistente.'))));				
			}

			$conferencias = $conferencia->fetchObject();
			$categoria = array(
							'id'=>$conferencias->CategoriaId,
							'nome'=>$conferencias->nomeCategoria,
							'area'=> array(
								'id'=>$conferencias->AreaId,
								'nome'=>$conferencias->nomeArea
							)
						);
			$item = array(
						'response' => array(
							'id'=>$conferencias->ConfereciasId,
							'nome'=>$conferencias->Nome,
							'dataIni'=>$conferencias->DataIni,
							'dataFim'=>$conferencias->DataFim,
							'local'=>$conferencias->Local,
							'categoria'=>$categoria
						),
				 		'meta' => array(
			 				'resultCode'=> 0,
							'clientMsg'=> ''
			 			)
					);

		echo json_encode($item);
		}catch(PDOException $e){
			$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));		
		}
});


$app->post('/conferencias/', function () use ($app){
	$data = $app->request;
	var_dump($data);
  	$sql = "INSERT INTO conferencias (Nome,DataIni,DataFim,Local,CategoriaId,AreaId) 
		 	values (:nome,:dataInicio,:dataFim,:local,:idCategoria,:idArea)";

 	try {
	  	$conn = getConn();
	  	$conf = $conn->prepare($sql);
	  	$result = $conf->execute(
	  							array(
							        ':nome'=> $data->post('nome'),
							        ':dataInicio'=> $data->post('dataInicio'),
							        ':dataFim'=> $data->post('dataFim'),
							        ':local'=> $data->post('local'),
							        ':idCategoria'=> $data->post('idCategoria'),
							        ':idArea'=> $data->post('idArea')
        						)
						);
	 	
	 	if ($result) {
	 		echo json_encode(
	  					array(
								'meta' => array(
		 							'resultCode'=> 0,
									'clientMsg'=> 'Conferência cadastrada com sucesso.'
		 						)
							)
	  					);	
	 	} else {
	 		$app->halt(412, json_encode(array('meta' => array('resultCode'=> 2010,'clientMsg'=> 'Algo de errado aconteceu ao inserir a nova conferência.'))));
	 	}

 	} catch (Exception $e) {
 		$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
 	}
});

$app->put('/conferencias/:id', function ($id) use ($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'conferenceid inválido'))));
	}

	$data = $app->request;
  	$sql = "UPDATE conferencias SET Nome=:nome, DataIni=:dataInicio, 
			DataFim=:dataFim, Local=:local, CategoriaId=:idCategoria, AreaId=:idArea
		 	WHERE ConfereciasId=:id";

 	$data = json_decode($data->getBody());

 	try {
	  	$conn = getConn();
	  	$conf = $conn->prepare($sql);
	  	$result = $conf->execute(
	  							array(
	  								':id'=> $id,
							        ':nome'=> $data->nome,
							        ':dataInicio'=> $data->dataInicio,
							        ':dataFim'=> $data->dataFim,
							        ':local'=> $data->local,
							        ':idCategoria'=> $data->idCategoria,
							        ':idArea'=> $data->idArea
        						)
						);
	 	
	 	if ($result) {
	 		echo json_encode(
	  					array(
								'meta' => array(
		 							'resultCode'=> 0,
									'clientMsg'=> 'Conferência alterada com sucesso.'
		 						)
							)
	  					);	
	 	} else {
	 		$app->halt(412, json_encode(array('meta' => array('resultCode'=> 2010,'clientMsg'=> 'Algo de errado aconteceu ao alterar a conferência.'))));
	 	}

 	} catch (Exception $e) {
 		$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
 	}
});

$app->delete('/conferencias/:id', function ($id) use ($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'conferenceid inválido'))));
	}

	$sql = "DELETE FROM conferencias WHERE ConfereciasId=:id";
	  	try {
	     	$db = getConn();
	     	$conf = $db->prepare($sql);
	     	$conf->bindParam("id", $id);
	     	$conf->execute();

	     	$count = $conf->rowCount();

			if (empty($count)) {
				$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2002,'clientMsg'=> 'Conferência inexistente.'))));				
			} else {
				echo json_encode(
								array(
			 						'meta' => array(
		 								'resultCode'=> 0,
										'clientMsg'=> 'Conferência exlcuída com sucesso!'
		 							)
								)
							);
		    }
	     	$db = null;
		  } catch(PDOException $e) {
		     $app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
		  }

});

//Categorias
$app->get('/categorias', function () use($app){
	$sql = "SELECT c.CategoriaId,c.Nome as nomeCategoria,a.AreaId,a.Nome as nomeArea 
				FROM categorias c
				INNER JOIN areas a on c.CategoriaId = a.CategoriaId";
		try {
			$db = getConn();
			$categoria = $db->query($sql);
			$categorias = $categoria->fetchAll(PDO::FETCH_OBJ);

			foreach($categorias as $row){
				$area = array(
					'id'=>$row->AreaId,
					'nome'=>$row->nomeArea
					);
				$item[] = array(
					'CategoriaId'=>$row->CategoriaId,
					'nomeCategoria'=>$row->nomeCategoria,
					'area'=>$area,	
					);
				}


			echo json_encode($item);
		}catch(PDOException $e) {	
			$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
		}
});

$app->post('/categorias', function () use($app){
	  $data = $app->request;
	  $sql = "INSERT INTO categorias (Nome) values (:nome)";
	  try {
	  	$conn = getConn();
	  	$cat = $conn->prepare($sql);
	  	$result = $cat->execute(
	  							array(
							        ':nome'=> $data->post('nome'),
        						)
						);
	 	
	 	if ($result) {
	 		echo json_encode(
	  					array(
								'meta' => array(
		 							'resultCode'=> 0,
									'clientMsg'=> 'Categoria cadastrada com sucesso.'
		 						)
							)
	  					);	
	 	} else {
	 		$app->halt(412, json_encode(array('meta' => array('resultCode'=> 2010,'clientMsg'=> 'Algo de errado aconteceu ao inserir a nova categoria.'))));
	 	}

 	} catch (Exception $e) {
 		$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
 	}

});

$app->put('/categorias/:id', function ($id) use ($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'categoriaId inválido'))));
	}

	$data = $app->request;
  	$sql = "UPDATE categorias SET Nome=:nome WHERE CategoriaId=:id";

 	$data = json_decode($data->getBody());

 	try {
	  	$conn = getConn();
	  	$cat = $conn->prepare($sql);
	  	$result = $cat->execute(
	  							array(
	  								':id'=> $id,
							        ':nome'=> $data->nome
							        
        						)
						);
	 	
	 	if ($result) {
	 		echo json_encode(
	  					array(
								'meta' => array(
		 							'resultCode'=> 0,
									'clientMsg'=> 'Categoria alterada com sucesso.'
		 						)
							)
	  					);	
	 	} else {
	 		$app->halt(412, json_encode(array('meta' => array('resultCode'=> 2010,'clientMsg'=> 'Algo de errado aconteceu ao alterar a categoria.'))));
	 	}

 	} catch (Exception $e) {
 		$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
 	}
});

$app->delete('/categorias/:id', function ($id) use ($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'categoriaId inválido'))));
	}

	$sql = "DELETE FROM categorias WHERE CategoriaId=:id";
	  	try {
	     	$db = getConn();
	     	$conf = $db->prepare($sql);
	     	$conf->bindParam("id", $id);
	     	$conf->execute();

	     	$count = $conf->rowCount();

			if (empty($count)) {
				$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2002,'clientMsg'=> 'Categoria inexistente.'))));				
			} else {
				echo json_encode(
								array(
			 						'meta' => array(
		 								'resultCode'=> 0,
										'clientMsg'=> 'Categoria exlcuída com sucesso!'
		 							)
								)
							);
		    }
	     	$db = null;
		  } catch(PDOException $e) {
		     $app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
		  }

});

//Areas
$app->post('/areas', function () use($app){
	  $data = $app->request;
	  $sql = "INSERT INTO areas (Nome,CategoriaId) values (:nome, :idCategoria)";
	  try {
	  	$conn = getConn();
	  	$area = $conn->prepare($sql);
	  	$result = $area->execute(
	  							array(
							        ':nome'=> $data->post('nome'),
							        ':idCategoria'=> $data->post('idCategoria')
        						)
						);
	 	
	 	if ($result) {
	 		echo json_encode(
	  					array(
								'meta' => array(
		 							'resultCode'=> 0,
									'clientMsg'=> 'Área cadastrada com sucesso.'
		 						)
							)
	  					);	
	 	} else {
	 		$app->halt(412, json_encode(array('meta' => array('resultCode'=> 2010,'clientMsg'=> 'Algo de errado aconteceu ao inserir a nova área.'))));
	 	}

 	} catch (Exception $e) {
 		$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
 	}

});

$app->delete('/areas/:id', function ($id) use ($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'areaId inválido'))));
	}

	$sql = "DELETE FROM areas WHERE AreaId=:id";
	  	try {
	     	$db = getConn();
	     	$area = $db->prepare($sql);
	     	$area->bindParam("id", $id);
	     	$area->execute();

	     	$count = $area->rowCount();

			if (empty($count)) {
				$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2002,'clientMsg'=> 'Área inexistente.'))));				
			} else {
				echo json_encode(
								array(
			 						'meta' => array(
		 								'resultCode'=> 0,
										'clientMsg'=> 'Área exlcuída com sucesso!'
		 							)
								)
							);
		    }
	     	$db = null;
		  } catch(PDOException $e) {
		     $app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
		  }

});

$app->put('/areas/:id', function ($id) use ($app){

	if (!is_numeric($id)) {
		$app->halt(400, json_encode(array('meta' => array('resultCode'=> 2001,'clientMsg'=> 'areaId inválido'))));
	}

	$data = $app->request;
  	$sql = "UPDATE areas SET Nome=:nome WHERE AreaId=:id";

 	$data = json_decode($data->getBody());

 	try {
	  	$conn = getConn();
	  	$area = $conn->prepare($sql);
	  	$result = $area->execute(
	  							array(
	  								':id'=> $id,
							        ':nome'=> $data->nome
							        
        						)
						);
	 	
	 	if ($result) {
	 		echo json_encode(
	  					array(
								'meta' => array(
		 							'resultCode'=> 0,
									'clientMsg'=> 'Área alterada com sucesso.'
		 						)
							)
	  					);	
	 	} else {
	 		$app->halt(412, json_encode(array('meta' => array('resultCode'=> 2010,'clientMsg'=> 'Algo de errado aconteceu ao alterar a área.'))));
	 	}

 	} catch (Exception $e) {
 		$app->halt(500, json_encode(array('meta' => array('resultCode'=> 500,'clientMsg'=> $e->getMessage()))));
 	}
});

$app->run();

?>